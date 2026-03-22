<?php
/**
 * Haravan Integration — extra field on the CANALI_GarmentOrders module.
 * Stores the Haravan order ID so the sync engine can detect
 * already-imported records and avoid creating duplicates.
 *
 * After adding this file run Quick Repair & Rebuild → Repair Database
 * so SuiteCRM ALTERs the canali_garment_orders table to add the column.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$dictionary['CANALI_GarmentOrders']['fields']['haravan_order_id'] = array(
    'name'       => 'haravan_order_id',
    'vname'      => 'LBL_HARAVAN_ORDER_ID',
    'type'       => 'varchar',
    'len'        => 64,
    'comment'    => 'Haravan order ID — used as deduplication key during API sync',
    'studio'     => false,
    'reportable' => false,
    'importable' => false,
    'duplicate_merge' => 'disabled',
);
