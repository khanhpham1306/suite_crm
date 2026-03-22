<?php
/**
 * Haravan Integration — Order → GarmentOrder Mapper
 *
 * Pure stateless transformation: converts a Haravan order object (decoded
 * associative array) into a flat array of SuiteCRM CANALI_GarmentOrders
 * field values.
 *
 * The `contact_id` field is intentionally NOT set here — the sync engine
 * resolves it separately by looking up the linked Haravan customer.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class HaravanOrderMapper
{
    /**
     * Map Haravan financial_status → canali_order_status_list key.
     * Values must exactly match the keys defined in canali_order_status_list.
     */
    private static $statusMap = array(
        'pending'        => 'Consultation',
        'authorized'     => 'Fabric Selected',
        'paid'           => 'In Production',
        'partially_paid' => 'In Production',
        'refunded'       => 'Cancelled',
        'voided'         => 'Cancelled',
    );

    /**
     * Garment type keyword rules: [pattern (regex), garment_type value].
     * Matched against the line-item title in declaration order (first match wins).
     * Values must match keys in canali_garment_type_list.
     */
    private static $garmentRules = array(
        array('/3[\s-]?piece/i',          'Suit 3pc'),
        array('/tuxedo|dinner\s+jacket/i', 'Tuxedo'),
        array('/suit/i',                   'Suit'),
        array('/jacket|blazer/i',          'Jacket'),
        array('/trouser|pant/i',           'Trouser'),
        array('/overcoat|topcoat|\bcoat\b/i', 'Overcoat'),
        array('/shirt/i',                  'Shirt'),
        array('/knit|sweater|knitwear/i',  'Knitwear'),
    );

    /**
     * Map a Haravan order object to CANALI_GarmentOrders field values.
     *
     * @param  array $order  Decoded Haravan order JSON object
     * @return array         field_name => value pairs (contact_id NOT included)
     */
    public static function toGarmentOrderFields(array $order)
    {
        $fields = array();

        // --- Primary dedup key ---
        $fields['haravan_order_id'] = (string) ($order['id'] ?? '');

        // --- Order number: Haravan uses names like "#1042"; strip the hash ---
        $rawName = self::str($order['name'] ?? '');
        $cleanNumber = ltrim($rawName, '#');
        $fields['order_number'] = $cleanNumber;

        // --- Garment type from first line item ---
        $lineItems   = $order['line_items'] ?? array();
        $firstTitle  = isset($lineItems[0]['title']) ? self::str($lineItems[0]['title']) : '';
        $garmentType = self::mapLineItemToGarmentType($firstTitle);

        // --- Bean display name ---
        $displayName = 'Order ' . ($rawName ?: '#' . ($order['id'] ?? ''));
        if ($firstTitle !== '') {
            $displayName .= ' \u2014 ' . $firstTitle;
        }
        $fields['name'] = $displayName;

        // --- Dates ---
        if (!empty($order['created_at'])) {
            $fields['order_date'] = substr($order['created_at'], 0, 10); // YYYY-MM-DD
        }

        // --- Financials ---
        $fields['total_price'] = (float) ($order['total_price'] ?? 0);

        // --- Status ---
        $financialStatus    = strtolower(self::str($order['financial_status'] ?? ''));
        $fields['order_status'] = self::mapFinancialStatus($financialStatus);

        // --- Garment type ---
        $fields['garment_type'] = $garmentType;

        // --- Notes ---
        $note = self::str($order['note'] ?? '');
        if ($note !== '') {
            $fields['garment_notes'] = $note;
        }

        // --- Internal: Haravan customer ID for contact resolution (not a bean field) ---
        $fields['_haravan_customer_id'] = (int) ($order['customer']['id'] ?? 0);

        return $fields;
    }

    /**
     * Map Haravan financial_status to a canali_order_status_list key.
     *
     * @param  string $status  Lower-cased financial_status from Haravan
     * @return string          Order status value
     */
    public static function mapFinancialStatus($status)
    {
        return isset(self::$statusMap[$status])
            ? self::$statusMap[$status]
            : 'Consultation';
    }

    /**
     * Map a line item title to a garment_type enum value via keyword matching.
     * Returns 'Other' when no rule matches.
     *
     * @param  string $title  Line item title string
     * @return string         Garment type value from canali_garment_type_list
     */
    public static function mapLineItemToGarmentType($title)
    {
        if ($title === '') {
            return 'Other';
        }
        foreach (self::$garmentRules as $rule) {
            list($pattern, $value) = $rule;
            if (preg_match($pattern, $title)) {
                return $value;
            }
        }
        return 'Other';
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private static function str($value)
    {
        return is_string($value) ? trim($value) : (string) $value;
    }
}
