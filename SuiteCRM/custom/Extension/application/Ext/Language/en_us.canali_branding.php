<?php
/**
 * CANALI Luxury Tailoring — Application Branding
 * Renames the application and key modules to match the CANALI store identity.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// Application name
$app_strings['LBL_BROWSER_TITLE']   = 'CANALI';
$app_strings['LBL_SUITECRM_TITLE']  = 'CANALI';
$app_strings['LBL_ALT_LOGO_TEXT']   = 'CANALI';

// Rename Contacts → Clients
$app_list_strings['moduleList']['Contacts']          = 'Clients';
$app_list_strings['moduleListSingular']['Contacts']  = 'Client';

// Rename Accounts → Ateliers (corporate client accounts / tailoring houses)
$app_list_strings['moduleList']['Accounts']          = 'Ateliers';
$app_list_strings['moduleListSingular']['Accounts']  = 'Atelier';

// Garment Orders module labels
$app_list_strings['moduleList']['CANALI_GarmentOrders']         = 'Garment Orders';
$app_list_strings['moduleListSingular']['CANALI_GarmentOrders'] = 'Garment Order';

// Lead source options adapted for luxury tailoring
$app_list_strings['lead_source_dom'] = array(
    ''                   => '',
    'Referral'           => 'Client Referral',
    'In-Store Visit'     => 'In-Store Visit',
    'Private Event'      => 'Private Event',
    'Trunk Show'         => 'Trunk Show',
    'Digital'            => 'Digital / Online',
    'Press / Editorial'  => 'Press / Editorial',
    'Personal Network'   => 'Personal Network',
    'Other'              => 'Other',
);

// Client VIP tier dropdown
$app_list_strings['canali_client_tier_list'] = array(
    ''          => '',
    'Bronze'    => 'Bronze',
    'Silver'    => 'Silver',
    'Gold'      => 'Gold',
    'Platinum'  => 'Platinum',
    'Bespoke'   => 'Bespoke (Private)',
);

// Preferred fit style dropdown
$app_list_strings['canali_fit_style_list'] = array(
    ''             => '',
    'Slim'         => 'Slim',
    'Contemporary' => 'Contemporary',
    'Classic'      => 'Classic',
    'Full'         => 'Full / Relaxed',
    'Bespoke'      => 'Bespoke',
);

// Garment type dropdown
$app_list_strings['canali_garment_type_list'] = array(
    ''                 => '',
    'Suit'             => 'Suit (2-piece)',
    'Suit_3pc'         => 'Suit (3-piece)',
    'Jacket'           => 'Sport Coat / Jacket',
    'Tuxedo'           => 'Tuxedo / Evening',
    'Trouser'          => 'Trousers',
    'Overcoat'         => 'Overcoat',
    'Shirt'            => 'Dress Shirt',
    'Knitwear'         => 'Knitwear',
    'Accessory'        => 'Accessory',
    'Other'            => 'Other',
);

// Garment order status dropdown
$app_list_strings['canali_order_status_list'] = array(
    ''                   => '',
    'Consultation'       => 'Consultation',
    'Fabric Selected'    => 'Fabric Selected',
    'Measurements Taken' => 'Measurements Taken',
    'In Production'      => 'In Production',
    'First Fitting'      => 'First Fitting',
    'Alterations'        => 'Alterations',
    'Final Fitting'      => 'Final Fitting',
    'Ready'              => 'Ready for Collection',
    'Delivered'          => 'Delivered',
    'Cancelled'          => 'Cancelled',
);

// Fabric category dropdown
$app_list_strings['canali_fabric_category_list'] = array(
    ''           => '',
    'Wool'       => 'Wool',
    'Cashmere'   => 'Cashmere',
    'Silk'       => 'Silk',
    'Linen'      => 'Linen',
    'Cotton'     => 'Cotton',
    'Flannel'    => 'Flannel',
    'Tweed'      => 'Tweed',
    'Velvet'     => 'Velvet',
    'Blend'      => 'Blend',
    'Other'      => 'Other',
);
