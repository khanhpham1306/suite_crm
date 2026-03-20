<?php
/**
 * CANALI Luxury Tailoring — Contacts (Clients) ListView
 *
 * Columns: Name | Tier | Personal Stylist | Phone | Email | Fit Style | Last Measured
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$listViewDefs['Contacts'] = array(
    'NAME' => array(
        'width'          => '20%',
        'label'          => 'LBL_LIST_NAME',
        'link'           => true,
        'default'        => true,
        'related_fields' => array('first_name', 'last_name', 'salutation'),
    ),
    'CANALI_CLIENT_TIER_C' => array(
        'width'   => '8%',
        'label'   => 'LBL_CANALI_CLIENT_TIER',
        'default' => true,
    ),
    'ASSIGNED_USER_NAME' => array(
        'width'   => '12%',
        'label'   => 'LBL_ASSIGNED_TO_NAME',
        'module'  => 'Users',
        'id'      => 'ASSIGNED_USER_ID',
        'default' => true,
    ),
    'PHONE_WORK' => array(
        'width'   => '11%',
        'label'   => 'LBL_OFFICE_PHONE',
        'default' => true,
    ),
    'EMAIL1' => array(
        'width'          => '14%',
        'label'          => 'LBL_EMAIL_ADDRESS',
        'default'        => true,
        'studio'         => 'false',
        'related_fields' => array('email1'),
    ),
    'CANALI_FIT_STYLE_C' => array(
        'width'   => '9%',
        'label'   => 'LBL_CANALI_FIT_STYLE',
        'default' => true,
    ),
    'CANALI_LAST_MEASURED_C' => array(
        'width'   => '10%',
        'label'   => 'LBL_CANALI_LAST_MEASURED',
        'default' => true,
    ),
    'ACCOUNT_NAME' => array(
        'width'   => '10%',
        'label'   => 'LBL_ACCOUNT_NAME',
        'module'  => 'Accounts',
        'id'      => 'ACCOUNT_ID',
        'default' => false,
    ),
    'CANALI_CLIENT_SINCE_C' => array(
        'width'   => '8%',
        'label'   => 'LBL_CANALI_CLIENT_SINCE',
        'default' => false,
    ),
);
