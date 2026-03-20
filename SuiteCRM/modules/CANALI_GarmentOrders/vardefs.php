<?php
/**
 * CANALI Luxury Tailoring — Garment Orders vardefs (schema & field definitions).
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['CANALI_GarmentOrders'] = array(
    'table'                     => 'canali_garment_orders',
    'audited'                   => true,
    'unified_search'            => true,
    'unified_search_default_enabled' => true,
    'duplicate_merge'           => true,
    'fields' => array(

        // ── Order Identity ────────────────────────────────────────────────
        'order_number' => array(
            'name'       => 'order_number',
            'vname'      => 'LBL_CANALI_ORDER_NUMBER',
            'type'       => 'varchar',
            'len'        => '50',
            'required'   => true,
            'unified_search' => true,
            'comment'    => 'Unique order reference number',
        ),

        'name' => array(
            'name'       => 'name',
            'vname'      => 'LBL_CANALI_ORDER_NAME',
            'type'       => 'varchar',
            'len'        => '255',
            'required'   => true,
            'unified_search' => true,
            'comment'    => 'Short order description / title',
        ),

        'order_date' => array(
            'name'    => 'order_date',
            'vname'   => 'LBL_CANALI_ORDER_DATE',
            'type'    => 'date',
            'required' => true,
        ),

        // ── Garment Details ───────────────────────────────────────────────
        'garment_type' => array(
            'name'    => 'garment_type',
            'vname'   => 'LBL_CANALI_GARMENT_TYPE',
            'type'    => 'enum',
            'options' => 'canali_garment_type_list',
            'len'     => '32',
            'required' => true,
        ),

        'order_status' => array(
            'name'    => 'order_status',
            'vname'   => 'LBL_CANALI_ORDER_STATUS',
            'type'    => 'enum',
            'options' => 'canali_order_status_list',
            'len'     => '32',
            'required' => true,
            'default'  => 'Consultation',
        ),

        'fabric_category' => array(
            'name'    => 'fabric_category',
            'vname'   => 'LBL_CANALI_FABRIC_CATEGORY',
            'type'    => 'enum',
            'options' => 'canali_fabric_category_list',
            'len'     => '32',
        ),

        'fabric_description' => array(
            'name'    => 'fabric_description',
            'vname'   => 'LBL_CANALI_FABRIC_DESCRIPTION',
            'type'    => 'varchar',
            'len'     => '255',
            'comment' => 'Fabric mill, weight, article number, colourway',
        ),

        'fit_style' => array(
            'name'    => 'fit_style',
            'vname'   => 'LBL_CANALI_FIT_STYLE',
            'type'    => 'enum',
            'options' => 'canali_fit_style_list',
            'len'     => '32',
        ),

        'lining_description' => array(
            'name'    => 'lining_description',
            'vname'   => 'LBL_CANALI_LINING_PREF',
            'type'    => 'varchar',
            'len'     => '255',
        ),

        'button_description' => array(
            'name'    => 'button_description',
            'vname'   => 'LBL_CANALI_BUTTON_PREF',
            'type'    => 'varchar',
            'len'     => '255',
        ),

        'monogram' => array(
            'name'    => 'monogram',
            'vname'   => 'LBL_CANALI_MONOGRAM',
            'type'    => 'varchar',
            'len'     => '50',
        ),

        // ── Fitting Schedule ──────────────────────────────────────────────
        'consultation_date' => array(
            'name'    => 'consultation_date',
            'vname'   => 'LBL_CANALI_CONSULTATION_DATE',
            'type'    => 'datetime',
        ),

        'fitting1_date' => array(
            'name'    => 'fitting1_date',
            'vname'   => 'LBL_CANALI_FITTING1_DATE',
            'type'    => 'datetime',
        ),

        'fitting2_date' => array(
            'name'    => 'fitting2_date',
            'vname'   => 'LBL_CANALI_FITTING2_DATE',
            'type'    => 'datetime',
        ),

        'delivery_date' => array(
            'name'    => 'delivery_date',
            'vname'   => 'LBL_CANALI_DELIVERY_DATE',
            'type'    => 'date',
        ),

        // ── Financials ────────────────────────────────────────────────────
        'total_price' => array(
            'name'    => 'total_price',
            'vname'   => 'LBL_CANALI_TOTAL_PRICE',
            'type'    => 'currency',
            'required' => true,
        ),

        'deposit_paid' => array(
            'name'    => 'deposit_paid',
            'vname'   => 'LBL_CANALI_DEPOSIT_PAID',
            'type'    => 'currency',
        ),

        'balance_due' => array(
            'name'    => 'balance_due',
            'vname'   => 'LBL_CANALI_BALANCE_DUE',
            'type'    => 'currency',
            'source'  => 'non-db',
            'comment' => 'Computed: total_price - deposit_paid',
        ),

        // ── Notes ─────────────────────────────────────────────────────────
        'garment_notes' => array(
            'name'    => 'garment_notes',
            'vname'   => 'LBL_CANALI_GARMENT_NOTES',
            'type'    => 'text',
        ),

        'alteration_notes' => array(
            'name'    => 'alteration_notes',
            'vname'   => 'LBL_CANALI_ALTERATION_NOTES',
            'type'    => 'text',
        ),

        // ── Client Relationship (Contacts) ────────────────────────────────
        'contact_id' => array(
            'name'       => 'contact_id',
            'vname'      => 'LBL_CANALI_CLIENT',
            'type'       => 'id',
            'reportable' => true,
            'comment'    => 'FK to contacts.id',
        ),

        'contact_name' => array(
            'name'       => 'contact_name',
            'rname'      => 'name',
            'id_name'    => 'contact_id',
            'vname'      => 'LBL_CANALI_CLIENT',
            'join_name'  => 'contacts',
            'type'       => 'relate',
            'link'       => 'contact_link',
            'table'      => 'contacts',
            'isnull'     => 'true',
            'module'     => 'Contacts',
            'dbType'     => 'varchar',
            'len'        => '255',
            'source'     => 'non-db',
            'unified_search' => true,
            'reportable' => true,
        ),

        'contact_link' => array(
            'name'         => 'contact_link',
            'type'         => 'link',
            'relationship' => 'canali_garment_orders_contacts',
            'source'       => 'non-db',
            'vname'        => 'LBL_CANALI_CLIENT',
        ),

    ),

    'relationships' => array(
        'canali_garment_orders_contacts' => array(
            'lhs_module'        => 'Contacts',
            'lhs_table'         => 'contacts',
            'lhs_key'           => 'id',
            'rhs_module'        => 'CANALI_GarmentOrders',
            'rhs_table'         => 'canali_garment_orders',
            'rhs_key'           => 'contact_id',
            'relationship_type' => 'many-to-one',
            'rhs_rel_key'       => 'contact_id',
        ),
    ),

    'indices' => array(
        array('name' => 'canali_go_contact',   'type' => 'index',    'fields' => array('contact_id')),
        array('name' => 'canali_go_status',    'type' => 'index',    'fields' => array('order_status')),
        array('name' => 'canali_go_assigned',  'type' => 'index',    'fields' => array('assigned_user_id')),
    ),
);

VardefManager::createVardef('CANALI_GarmentOrders', 'CANALI_GarmentOrders', array('default', 'assignable'));
