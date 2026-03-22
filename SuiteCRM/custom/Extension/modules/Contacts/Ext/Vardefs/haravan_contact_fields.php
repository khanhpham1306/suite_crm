<?php
/**
 * Haravan Integration — extra field on the Contacts module.
 * Stores the Haravan customer ID so the sync engine can detect
 * already-imported records without relying on email matching alone.
 *
 * After adding this file run Quick Repair & Rebuild → Repair Database
 * so SuiteCRM ALTERs the contacts_cstm table to add the column.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['Contact']['fields']['haravan_customer_id_c'] = array(
    'name'       => 'haravan_customer_id_c',
    'vname'      => 'LBL_HARAVAN_CUSTOMER_ID',
    'type'       => 'varchar',
    'len'        => 64,
    'comment'    => 'Haravan customer ID — used as deduplication key during API sync',
    'studio'     => false,
    'reportable' => false,
    'importable' => false,
    'duplicate_merge' => 'disabled',
);
