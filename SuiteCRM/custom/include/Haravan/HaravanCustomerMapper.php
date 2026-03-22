<?php
/**
 * Haravan Integration — Customer → Contact Mapper
 *
 * Pure stateless transformation: converts a Haravan customer object (decoded
 * associative array) into a flat array of SuiteCRM Contact field values.
 * Does NOT touch the database or create beans.
 *
 * Caller is responsible for setting the bean fields and saving.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class HaravanCustomerMapper
{
    /**
     * Tier keyword mapping: Haravan tag substring → canali_client_tier_c value.
     * Keys are lower-case substrings to detect in the tag list.
     */
    private static $tierKeywords = array(
        'bespoke'  => 'Bespoke',
        'platinum' => 'Platinum',
        'gold'     => 'Gold',
        'silver'   => 'Silver',
        'bronze'   => 'Bronze',
    );

    /**
     * Map a Haravan customer object to SuiteCRM Contact field values.
     *
     * @param  array $customer  Decoded Haravan customer JSON object
     * @return array            field_name => value pairs ready to apply to a Contact bean
     */
    public static function toContactFields(array $customer)
    {
        $fields = array();

        // --- Primary dedup key ---
        $fields['haravan_customer_id_c'] = (string) ($customer['id'] ?? '');

        // --- Name ---
        $fields['first_name'] = self::str($customer['first_name'] ?? '');
        $fields['last_name']  = self::str($customer['last_name']  ?? '');

        // Ensure at least a placeholder last name so the bean saves cleanly
        if ($fields['first_name'] === '' && $fields['last_name'] === '') {
            $fields['last_name'] = 'Haravan Customer ' . ($customer['id'] ?? '');
        }

        // --- Contact details ---
        $fields['phone_mobile'] = self::str($customer['phone'] ?? '');

        // email is handled separately via $bean->emailAddress->addAddress()
        // but we store it here so the engine can use it for email dedup lookup
        $fields['_email'] = self::str($customer['email'] ?? '');

        // --- VIP / financial ---
        $totalSpent = (float) ($customer['total_spent'] ?? 0);
        if ($totalSpent > 0) {
            $fields['canali_lifetime_value_c'] = $totalSpent;
        }

        // --- Client tier from tags ---
        $tags = self::str($customer['tags'] ?? '');
        if ($tags !== '') {
            $tier = self::mapTagsToTier($tags);
            if ($tier !== '') {
                $fields['canali_client_tier_c'] = $tier;
            }
        }

        // --- Address (primary) ---
        $addr = $customer['default_address'] ?? array();
        if (!empty($addr)) {
            $fields['primary_address_street']  = self::str($addr['address1'] ?? '');
            $fields['primary_address_city']    = self::str($addr['city']    ?? '');
            $fields['primary_address_country'] = self::str($addr['country'] ?? '');
            if (!empty($addr['province'])) {
                $fields['primary_address_state'] = self::str($addr['province']);
            }
            if (!empty($addr['zip'])) {
                $fields['primary_address_postalcode'] = self::str($addr['zip']);
            }
        }

        // --- Client since (only set on new Contact; engine decides whether to apply) ---
        if (!empty($customer['created_at'])) {
            $fields['_client_since'] = substr($customer['created_at'], 0, 10); // YYYY-MM-DD
        }

        return $fields;
    }

    /**
     * Map Haravan customer tags (comma-separated string) to a canali_client_tier_c value.
     * Returns the first matching tier keyword or empty string if none match.
     *
     * Priority order: Bespoke > Platinum > Gold > Silver > Bronze
     *
     * @param  string $tags  Comma-separated tag string from Haravan
     * @return string        Tier value or '' if no match
     */
    public static function mapTagsToTier($tags)
    {
        $lower = strtolower($tags);
        foreach (self::$tierKeywords as $keyword => $value) {
            if (strpos($lower, $keyword) !== false) {
                return $value;
            }
        }
        return '';
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private static function str($value)
    {
        return is_string($value) ? trim($value) : (string) $value;
    }
}
