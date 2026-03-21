<?php
/**
 * CANALI Luxury Tailoring — Garment Orders module bean.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class CANALI_GarmentOrders extends Basic
{
    public $module_dir   = 'CANALI_GarmentOrders';
    public $object_name  = 'CANALI_GarmentOrders';
    public $table_name   = 'canali_garment_orders';
    public $importable   = true;
    public $new_schema   = true;

    // Fields surfaced via getter/setter for display
    public $order_number;
    public $order_date;
    public $garment_type_c;
    public $order_status_c;
    public $fabric_category_c;
    public $fabric_description;
    public $consultation_date;
    public $fitting1_date;
    public $fitting2_date;
    public $delivery_date;
    public $total_price;
    public $deposit_paid;
    public $balance_due;
    public $garment_notes;
    public $contact_id;
    public $contact_name;
    public $assigned_user_id;
}
