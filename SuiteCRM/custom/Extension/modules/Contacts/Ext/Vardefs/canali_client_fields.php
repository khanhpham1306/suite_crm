<?php
/**
 * CANALI Luxury Tailoring — Custom Client Fields on Contacts module.
 *
 * Groups:
 *   1. Body Measurements
 *   2. Style Preferences
 *   3. VIP Profile
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// ─── 1. BODY MEASUREMENTS ────────────────────────────────────────────────────

$dictionary['Contact']['fields']['canali_chest_c'] = array(
    'name'       => 'canali_chest_c',
    'vname'      => 'LBL_CANALI_CHEST',
    'type'       => 'decimal',
    'len'        => '7,2',
    'comment'    => 'Chest circumference (cm)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_waist_c'] = array(
    'name'       => 'canali_waist_c',
    'vname'      => 'LBL_CANALI_WAIST',
    'type'       => 'decimal',
    'len'        => '7,2',
    'comment'    => 'Waist circumference (cm)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_hips_c'] = array(
    'name'       => 'canali_hips_c',
    'vname'      => 'LBL_CANALI_HIPS',
    'type'       => 'decimal',
    'len'        => '7,2',
    'comment'    => 'Hip circumference (cm)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_shoulders_c'] = array(
    'name'       => 'canali_shoulders_c',
    'vname'      => 'LBL_CANALI_SHOULDERS',
    'type'       => 'decimal',
    'len'        => '7,2',
    'comment'    => 'Shoulder width (cm)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_inseam_c'] = array(
    'name'       => 'canali_inseam_c',
    'vname'      => 'LBL_CANALI_INSEAM',
    'type'       => 'decimal',
    'len'        => '7,2',
    'comment'    => 'Inseam length (cm)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_sleeve_c'] = array(
    'name'       => 'canali_sleeve_c',
    'vname'      => 'LBL_CANALI_SLEEVE',
    'type'       => 'decimal',
    'len'        => '7,2',
    'comment'    => 'Sleeve length (cm)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_neck_c'] = array(
    'name'       => 'canali_neck_c',
    'vname'      => 'LBL_CANALI_NECK',
    'type'       => 'decimal',
    'len'        => '7,2',
    'comment'    => 'Neck circumference (cm)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_jacket_size_c'] = array(
    'name'       => 'canali_jacket_size_c',
    'vname'      => 'LBL_CANALI_JACKET_SIZE',
    'type'       => 'varchar',
    'len'        => '20',
    'comment'    => 'Jacket size (e.g. 50 R, 52 L)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_trouser_size_c'] = array(
    'name'       => 'canali_trouser_size_c',
    'vname'      => 'LBL_CANALI_TROUSER_SIZE',
    'type'       => 'varchar',
    'len'        => '20',
    'comment'    => 'Trouser size (e.g. 50 / 32)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_shirt_collar_c'] = array(
    'name'       => 'canali_shirt_collar_c',
    'vname'      => 'LBL_CANALI_SHIRT_COLLAR',
    'type'       => 'varchar',
    'len'        => '10',
    'comment'    => 'Shirt collar size (e.g. 41)',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_measurement_notes_c'] = array(
    'name'       => 'canali_measurement_notes_c',
    'vname'      => 'LBL_CANALI_MEASUREMENT_NOTES',
    'type'       => 'text',
    'comment'    => 'Additional measurement notes (posture, asymmetries, special requirements)',
    'studio'     => true,
    'reportable' => false,
);

$dictionary['Contact']['fields']['canali_last_measured_c'] = array(
    'name'       => 'canali_last_measured_c',
    'vname'      => 'LBL_CANALI_LAST_MEASURED',
    'type'       => 'date',
    'comment'    => 'Date of last body measurement',
    'studio'     => true,
    'reportable' => true,
);

// ─── 2. STYLE PREFERENCES ────────────────────────────────────────────────────

$dictionary['Contact']['fields']['canali_fit_style_c'] = array(
    'name'       => 'canali_fit_style_c',
    'vname'      => 'LBL_CANALI_FIT_STYLE',
    'type'       => 'enum',
    'options'    => 'canali_fit_style_list',
    'len'        => '32',
    'comment'    => 'Preferred fit style',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_preferred_fabric_c'] = array(
    'name'       => 'canali_preferred_fabric_c',
    'vname'      => 'LBL_CANALI_PREFERRED_FABRIC',
    'type'       => 'enum',
    'options'    => 'canali_fabric_category_list',
    'len'        => '32',
    'comment'    => 'Preferred fabric category',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_preferred_colors_c'] = array(
    'name'       => 'canali_preferred_colors_c',
    'vname'      => 'LBL_CANALI_PREFERRED_COLORS',
    'type'       => 'varchar',
    'len'        => '255',
    'comment'    => 'Preferred colours and patterns (e.g. Navy, Charcoal, Chalk stripe)',
    'studio'     => true,
    'reportable' => false,
);

$dictionary['Contact']['fields']['canali_lining_pref_c'] = array(
    'name'       => 'canali_lining_pref_c',
    'vname'      => 'LBL_CANALI_LINING_PREF',
    'type'       => 'varchar',
    'len'        => '255',
    'comment'    => 'Preferred lining style / colour',
    'studio'     => true,
    'reportable' => false,
);

$dictionary['Contact']['fields']['canali_button_pref_c'] = array(
    'name'       => 'canali_button_pref_c',
    'vname'      => 'LBL_CANALI_BUTTON_PREF',
    'type'       => 'varchar',
    'len'        => '255',
    'comment'    => 'Preferred button style / material',
    'studio'     => true,
    'reportable' => false,
);

$dictionary['Contact']['fields']['canali_monogram_c'] = array(
    'name'       => 'canali_monogram_c',
    'vname'      => 'LBL_CANALI_MONOGRAM',
    'type'       => 'varchar',
    'len'        => '50',
    'comment'    => 'Monogram / personalisation preference',
    'studio'     => true,
    'reportable' => false,
);

$dictionary['Contact']['fields']['canali_style_notes_c'] = array(
    'name'       => 'canali_style_notes_c',
    'vname'      => 'LBL_CANALI_STYLE_NOTES',
    'type'       => 'text',
    'comment'    => 'Detailed style notes and special client preferences',
    'studio'     => true,
    'reportable' => false,
);

// ─── 3. VIP PROFILE ──────────────────────────────────────────────────────────

$dictionary['Contact']['fields']['canali_client_tier_c'] = array(
    'name'       => 'canali_client_tier_c',
    'vname'      => 'LBL_CANALI_CLIENT_TIER',
    'type'       => 'enum',
    'options'    => 'canali_client_tier_list',
    'len'        => '20',
    'comment'    => 'Client tier / loyalty level',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_client_since_c'] = array(
    'name'       => 'canali_client_since_c',
    'vname'      => 'LBL_CANALI_CLIENT_SINCE',
    'type'       => 'date',
    'comment'    => 'Date client first purchased / became a CANALI client',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_annual_spend_c'] = array(
    'name'       => 'canali_annual_spend_c',
    'vname'      => 'LBL_CANALI_ANNUAL_SPEND',
    'type'       => 'currency',
    'comment'    => 'Estimated annual spend',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_lifetime_value_c'] = array(
    'name'       => 'canali_lifetime_value_c',
    'vname'      => 'LBL_CANALI_LIFETIME_VALUE',
    'type'       => 'currency',
    'comment'    => 'Total lifetime value',
    'studio'     => true,
    'reportable' => true,
);

$dictionary['Contact']['fields']['canali_special_occasions_c'] = array(
    'name'       => 'canali_special_occasions_c',
    'vname'      => 'LBL_CANALI_SPECIAL_OCCASIONS',
    'type'       => 'text',
    'comment'    => 'Notable dates: anniversaries, events, wardrobe occasions to remember',
    'studio'     => true,
    'reportable' => false,
);

$dictionary['Contact']['fields']['canali_private_notes_c'] = array(
    'name'       => 'canali_private_notes_c',
    'vname'      => 'LBL_CANALI_PRIVATE_NOTES',
    'type'       => 'text',
    'comment'    => 'Private staff notes (never shown to client)',
    'studio'     => true,
    'reportable' => false,
);
