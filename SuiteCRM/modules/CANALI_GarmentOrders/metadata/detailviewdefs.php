<?php
/**
 * CANALI Luxury Tailoring — Garment Orders DetailView
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$viewdefs['CANALI_GarmentOrders']['DetailView'] = array(
    'templateMeta' => array(
        'form' => array('buttons' => array('EDIT', 'DUPLICATE', 'DELETE')),
        'maxColumns' => '2',
        'widths' => array(
            array('label' => '10', 'field' => '30'),
            array('label' => '10', 'field' => '30'),
        ),
        'useTabs' => false,
    ),
    'panels' => array(

        'lbl_garment_order_overview' => array(
            array(
                array('name' => 'name',         'label' => 'LBL_CANALI_ORDER_NAME'),
                array('name' => 'order_number', 'label' => 'LBL_CANALI_ORDER_NUMBER'),
            ),
            array(
                array('name' => 'contact_name', 'label' => 'LBL_CANALI_CLIENT'),
                array('name' => 'order_date',   'label' => 'LBL_CANALI_ORDER_DATE'),
            ),
            array(
                array('name' => 'garment_type', 'label' => 'LBL_CANALI_GARMENT_TYPE'),
                array('name' => 'order_status', 'label' => 'LBL_CANALI_ORDER_STATUS'),
            ),
            array(
                array('name' => 'assigned_user_name', 'label' => 'LBL_ASSIGNED_TO_NAME'),
                '',
            ),
        ),

        'lbl_canali_fabric_style' => array(
            array(
                array('name' => 'fabric_category',    'label' => 'LBL_CANALI_FABRIC_CATEGORY'),
                array('name' => 'fabric_description', 'label' => 'LBL_CANALI_FABRIC_DESCRIPTION'),
            ),
            array(
                array('name' => 'fit_style',           'label' => 'LBL_CANALI_FIT_STYLE'),
                array('name' => 'monogram',            'label' => 'LBL_CANALI_MONOGRAM'),
            ),
            array(
                array('name' => 'lining_description', 'label' => 'LBL_CANALI_LINING_PREF'),
                array('name' => 'button_description', 'label' => 'LBL_CANALI_BUTTON_PREF'),
            ),
        ),

        'lbl_canali_fitting_schedule' => array(
            array(
                array('name' => 'consultation_date', 'label' => 'LBL_CANALI_CONSULTATION_DATE'),
                array('name' => 'fitting1_date',     'label' => 'LBL_CANALI_FITTING1_DATE'),
            ),
            array(
                array('name' => 'fitting2_date',     'label' => 'LBL_CANALI_FITTING2_DATE'),
                array('name' => 'delivery_date',     'label' => 'LBL_CANALI_DELIVERY_DATE'),
            ),
        ),

        'lbl_canali_pricing' => array(
            array(
                array('name' => 'total_price',  'label' => 'LBL_CANALI_TOTAL_PRICE'),
                array('name' => 'deposit_paid', 'label' => 'LBL_CANALI_DEPOSIT_PAID'),
            ),
            array(
                array('name' => 'balance_due',  'label' => 'LBL_CANALI_BALANCE_DUE'),
                '',
            ),
        ),

        'lbl_canali_notes' => array(
            array(
                array('name' => 'garment_notes',    'label' => 'LBL_CANALI_GARMENT_NOTES'),
                '',
            ),
            array(
                array('name' => 'alteration_notes', 'label' => 'LBL_CANALI_ALTERATION_NOTES'),
                '',
            ),
        ),
    ),
);
