<?php
/**
 * CANALI Luxury Tailoring — Registers the CANALI_GarmentOrders module with SuiteCRM.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$beanList['CANALI_GarmentOrders']  = 'CANALI_GarmentOrders';
$beanFiles['CANALI_GarmentOrders'] = 'modules/CANALI_GarmentOrders/CANALI_GarmentOrders.php';
$moduleList[]                       = 'CANALI_GarmentOrders';
