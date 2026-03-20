<?php
/**
 * CANALI Luxury Tailoring — Garment Orders ListView
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$listViewDefs['CANALI_GarmentOrders'] = array(
    'ORDER_NUMBER' => array(
        'width'   => '10%',
        'label'   => 'LBL_LIST_ORDER_NUMBER',
        'link'    => true,
        'default' => true,
    ),
    'NAME' => array(
        'width'   => '18%',
        'label'   => 'LBL_CANALI_ORDER_NAME',
        'link'    => true,
        'default' => true,
    ),
    'CONTACT_NAME' => array(
        'width'   => '15%',
        'label'   => 'LBL_LIST_CLIENT',
        'module'  => 'Contacts',
        'id'      => 'CONTACT_ID',
        'default' => true,
    ),
    'GARMENT_TYPE' => array(
        'width'   => '10%',
        'label'   => 'LBL_LIST_GARMENT_TYPE',
        'default' => true,
    ),
    'ORDER_STATUS' => array(
        'width'   => '12%',
        'label'   => 'LBL_LIST_ORDER_STATUS',
        'default' => true,
    ),
    'ORDER_DATE' => array(
        'width'   => '9%',
        'label'   => 'LBL_LIST_ORDER_DATE',
        'default' => true,
    ),
    'DELIVERY_DATE' => array(
        'width'   => '9%',
        'label'   => 'LBL_LIST_DELIVERY_DATE',
        'default' => true,
    ),
    'TOTAL_PRICE' => array(
        'width'   => '9%',
        'label'   => 'LBL_LIST_TOTAL_PRICE',
        'default' => true,
    ),
    'ASSIGNED_USER_NAME' => array(
        'width'   => '10%',
        'label'   => 'LBL_LIST_ASSIGNED_USER',
        'module'  => 'Users',
        'id'      => 'ASSIGNED_USER_ID',
        'default' => true,
    ),
);
