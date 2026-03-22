<?php
/**
 * Haravan Integration — Sync Engine
 *
 * Orchestrates the full pull-sync loop:
 *   1. Read last-sync timestamps from config table
 *   2. Fetch updated customers from Haravan API (paginated)
 *   3. Upsert each customer into SuiteCRM Contacts
 *   4. Fetch updated orders from Haravan API (paginated)
 *   5. Upsert each order into SuiteCRM CANALI_GarmentOrders
 *   6. Write new timestamps back to config table
 *
 * Dedup strategy:
 *   - Contacts: look up by haravan_customer_id_c; fall back to email match
 *   - Orders:   look up by haravan_order_id (no email fallback)
 *
 * Per-record failures are caught and logged without aborting the page; the
 * sync continues with the next record. A page-level API failure does abort
 * the current sync and returns false so the scheduler can retry later.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once dirname(__FILE__) . '/HaravanHttpClient.php';
require_once dirname(__FILE__) . '/HaravanApiClient.php';
require_once dirname(__FILE__) . '/HaravanCustomerMapper.php';
require_once dirname(__FILE__) . '/HaravanOrderMapper.php';

class HaravanSyncEngine
{
    /** Config table category for all Haravan settings */
    const CONFIG_CATEGORY = 'haravan';

    /** Fallback "since" date used on the very first sync (full history) */
    const DEFAULT_SINCE = '2020-01-01T00:00:00+00:00';

    /** @var HaravanApiClient */
    private $api;

    /** @var object  DBManager instance */
    private $db;

    /**
     * @param HaravanApiClient $api
     */
    public function __construct(HaravanApiClient $api)
    {
        $this->api = $api;
        $this->db  = DBManagerFactory::getInstance();
    }

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Run a full incremental sync: customers then orders.
     *
     * @return bool  true on complete success; false if any phase failed
     */
    public function runFullSync()
    {
        $GLOBALS['log']->info('HaravanSyncEngine: starting full sync');
        $success = true;

        // --- Customers ---
        $customerSince = $this->getSyncState('haravan_last_customer_sync');
        try {
            $this->syncCustomers($customerSince);
            $this->setSyncState('haravan_last_customer_sync', date('c'));
        } catch (Exception $e) {
            $GLOBALS['log']->error('HaravanSyncEngine: customer sync failed: ' . $e->getMessage());
            $success = false;
        }

        // --- Orders (run even if customer sync partially failed) ---
        $orderSince = $this->getSyncState('haravan_last_order_sync');
        try {
            $this->syncOrders($orderSince);
            $this->setSyncState('haravan_last_order_sync', date('c'));
        } catch (Exception $e) {
            $GLOBALS['log']->error('HaravanSyncEngine: order sync failed: ' . $e->getMessage());
            $success = false;
        }

        $GLOBALS['log']->info('HaravanSyncEngine: full sync complete, success=' . ($success ? 'true' : 'false'));
        return $success;
    }

    /**
     * Sync all Haravan customers updated since $since into SuiteCRM Contacts.
     *
     * @param string $since  ISO-8601 datetime
     */
    public function syncCustomers($since)
    {
        $created = 0;
        $updated = 0;
        $errors  = 0;

        $GLOBALS['log']->info('HaravanSyncEngine: syncing customers since ' . $since);

        foreach ($this->api->fetchCustomers($since) as $page) {
            foreach ($page as $customer) {
                try {
                    $fields = HaravanCustomerMapper::toContactFields($customer);
                    list($bean, $isNew) = $this->findOrInitContact($fields);

                    $this->applyContactFields($bean, $fields, $isNew);
                    $bean->save();

                    if ($isNew) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (Exception $e) {
                    $errors++;
                    $GLOBALS['log']->error(
                        'HaravanSyncEngine: failed to upsert customer id=' .
                        ($customer['id'] ?? 'unknown') . ': ' . $e->getMessage()
                    );
                }
            }
        }

        $this->setSyncState('haravan_customers_synced', ($created + $updated));
        $GLOBALS['log']->info(
            "HaravanSyncEngine: customers done — created={$created} updated={$updated} errors={$errors}"
        );
    }

    /**
     * Sync all Haravan orders updated since $since into CANALI_GarmentOrders.
     *
     * @param string $since  ISO-8601 datetime
     */
    public function syncOrders($since)
    {
        $created = 0;
        $updated = 0;
        $errors  = 0;

        $GLOBALS['log']->info('HaravanSyncEngine: syncing orders since ' . $since);

        foreach ($this->api->fetchOrders($since) as $page) {
            foreach ($page as $order) {
                try {
                    $fields = HaravanOrderMapper::toGarmentOrderFields($order);

                    // Resolve the linked SuiteCRM contact
                    $haravanCustomerId = (int) ($fields['_haravan_customer_id'] ?? 0);
                    unset($fields['_haravan_customer_id']); // internal key — not a bean field

                    $contactId = $haravanCustomerId
                        ? $this->findContactIdByHaravanId($haravanCustomerId)
                        : null;

                    list($bean, $isNew) = $this->findOrInitOrder($fields);

                    $this->applyOrderFields($bean, $fields, $contactId);
                    $bean->save();

                    if ($isNew) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (Exception $e) {
                    $errors++;
                    $GLOBALS['log']->error(
                        'HaravanSyncEngine: failed to upsert order id=' .
                        ($order['id'] ?? 'unknown') . ': ' . $e->getMessage()
                    );
                }
            }
        }

        $this->setSyncState('haravan_orders_synced', ($created + $updated));
        $GLOBALS['log']->info(
            "HaravanSyncEngine: orders done — created={$created} updated={$updated} errors={$errors}"
        );
    }

    // =========================================================================
    // Dedup / bean lookup
    // =========================================================================

    /**
     * Find an existing Contact by haravan_customer_id_c, falling back to email,
     * or initialise a fresh Contact bean if no match is found.
     *
     * @param  array $fields  Mapped contact fields (must contain haravan_customer_id_c and _email)
     * @return array  [Contact $bean, bool $isNew]
     */
    private function findOrInitContact(array $fields)
    {
        $haravanId = $fields['haravan_customer_id_c'] ?? '';
        $email     = $fields['_email'] ?? '';

        // 1. Fast path: look up by Haravan ID in contacts_cstm
        if ($haravanId !== '') {
            $sql = "SELECT bean_id FROM contacts_cstm
                    WHERE haravan_customer_id_c = " . $this->db->quoted($haravanId) . "
                    LIMIT 1";
            $row = $this->db->fetchOne($sql);
            if (!empty($row['bean_id'])) {
                $bean = BeanFactory::getBean('Contacts', $row['bean_id']);
                if ($bean && !empty($bean->id)) {
                    return array($bean, false);
                }
            }
        }

        // 2. Fallback: match by email address
        if ($email !== '') {
            $emailSafe = $this->db->quoted(strtolower($email));
            $sql = "SELECT bean_id FROM email_addr_bean_rel r
                    JOIN email_addresses e ON e.id = r.email_address_id
                    WHERE r.bean_module = 'Contacts'
                      AND r.deleted = 0
                      AND e.deleted = 0
                      AND LOWER(e.email_address) = {$emailSafe}
                    LIMIT 1";
            $row = $this->db->fetchOne($sql);
            if (!empty($row['bean_id'])) {
                $bean = BeanFactory::getBean('Contacts', $row['bean_id']);
                if ($bean && !empty($bean->id)) {
                    return array($bean, false);
                }
            }
        }

        // 3. Create new
        return array(BeanFactory::newBean('Contacts'), true);
    }

    /**
     * Find an existing CANALI_GarmentOrders by haravan_order_id, or initialise a new bean.
     *
     * @param  array $fields  Mapped order fields (must contain haravan_order_id)
     * @return array  [CANALI_GarmentOrders $bean, bool $isNew]
     */
    private function findOrInitOrder(array $fields)
    {
        $haravanOrderId = $fields['haravan_order_id'] ?? '';

        if ($haravanOrderId !== '') {
            $sql = "SELECT id FROM canali_garment_orders
                    WHERE haravan_order_id = " . $this->db->quoted($haravanOrderId) . "
                      AND deleted = 0
                    LIMIT 1";
            $row = $this->db->fetchOne($sql);
            if (!empty($row['id'])) {
                $bean = BeanFactory::getBean('CANALI_GarmentOrders', $row['id']);
                if ($bean && !empty($bean->id)) {
                    return array($bean, false);
                }
            }
        }

        return array(BeanFactory::newBean('CANALI_GarmentOrders'), true);
    }

    /**
     * Look up the SuiteCRM Contact UUID by Haravan customer ID.
     *
     * @param  int $haravanCustomerId
     * @return string|null  SuiteCRM contact UUID or null if not found
     */
    private function findContactIdByHaravanId($haravanCustomerId)
    {
        if (!$haravanCustomerId) {
            return null;
        }
        $sql = "SELECT bean_id FROM contacts_cstm
                WHERE haravan_customer_id_c = " . $this->db->quoted((string) $haravanCustomerId) . "
                LIMIT 1";
        $row = $this->db->fetchOne($sql);
        return !empty($row['bean_id']) ? $row['bean_id'] : null;
    }

    // =========================================================================
    // Field application
    // =========================================================================

    /**
     * Apply mapped customer fields to a Contact bean.
     * Handles email via the emailAddress sub-object and skips internal keys.
     *
     * @param object $bean    Contact bean (new or existing)
     * @param array  $fields  Output of HaravanCustomerMapper::toContactFields()
     * @param bool   $isNew   True when this is a freshly initialised bean
     */
    private function applyContactFields($bean, array $fields, $isNew)
    {
        $skipKeys = array('_email', '_client_since');

        foreach ($fields as $key => $value) {
            if (in_array($key, $skipKeys, true)) {
                continue;
            }
            $bean->$key = $value;
        }

        // Email — must go via the emailAddress relationship object
        $email = $fields['_email'] ?? '';
        if ($email !== '' && is_object($bean->emailAddress)) {
            $bean->emailAddress->addAddress($email, true);
        }

        // client_since — only set when creating a new contact
        if ($isNew && !empty($fields['_client_since'])) {
            $bean->canali_client_since_c = $fields['_client_since'];
        }

        // Assign to the admin user if no owner is set
        if (empty($bean->assigned_user_id)) {
            $bean->assigned_user_id = '1';
        }
    }

    /**
     * Apply mapped order fields to a CANALI_GarmentOrders bean.
     *
     * @param object      $bean       GarmentOrders bean
     * @param array       $fields     Output of HaravanOrderMapper::toGarmentOrderFields()
     * @param string|null $contactId  Resolved SuiteCRM contact UUID (may be null)
     */
    private function applyOrderFields($bean, array $fields, $contactId)
    {
        foreach ($fields as $key => $value) {
            $bean->$key = $value;
        }

        if ($contactId !== null) {
            $bean->contact_id = $contactId;
        }

        // Assign to the admin user if no owner is set
        if (empty($bean->assigned_user_id)) {
            $bean->assigned_user_id = '1';
        }
    }

    // =========================================================================
    // Config state helpers
    // =========================================================================

    /**
     * Read a haravan config value from the config table.
     * Returns DEFAULT_SINCE for timestamp keys when no value is stored yet.
     *
     * @param  string $key  Config name under category 'haravan'
     * @return string
     */
    private function getSyncState($key)
    {
        $sql = "SELECT value FROM config
                WHERE category = 'haravan' AND name = " . $this->db->quoted($key);
        $row = $this->db->fetchOne($sql);
        if (!empty($row['value'])) {
            return $row['value'];
        }
        // Default for timestamp keys
        if (strpos($key, '_sync') !== false) {
            return self::DEFAULT_SINCE;
        }
        return '';
    }

    /**
     * Persist a haravan config value to the config table (upsert).
     *
     * @param string $key
     * @param mixed  $value
     */
    private function setSyncState($key, $value)
    {
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting(self::CONFIG_CATEGORY, $key, (string) $value);
    }
}
