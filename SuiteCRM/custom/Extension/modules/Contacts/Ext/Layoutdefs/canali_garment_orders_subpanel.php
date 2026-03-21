<?php
/**
 * CANALI Luxury Tailoring — Garment Orders subpanel on the Client (Contact) detail view.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$layout_defs['Contacts']['subpanel_setup']['canali_garment_orders'] = array(
    'order'                 => 30,
    'module'                => 'CANALI_GarmentOrders',
    'subpanel_name'         => 'default',
    'sort_order'            => 'desc',
    'sort_by'               => 'order_date',
    'title_key'             => 'LBL_CANALI_GARMENT_ORDERS_SUBPANEL_TITLE',
    'get_subpanel_data'     => 'function:getGarmentOrdersForContact',
    'top_buttons'           => array(
        array('widget_class' => 'SubpanelTopCreateButtonQuick',
              'module'       => 'CANALI_GarmentOrders',
              'additionalFormFields' => array('contact_id' => '{parent.id}', 'contact_name' => '{parent.full_name}'),
        ),
        array('widget_class' => 'SubpanelTopSelectButton', 'mode' => 'MultiSelect'),
    ),
);
