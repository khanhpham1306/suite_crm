<?php
/**
 * Haravan Integration — Admin panel entry.
 *
 * Adds a "CANALI Integration" group with a "Haravan API Settings" link
 * on the Admin panel landing page (index.php?module=Administration&action=index).
 *
 * This file is merged by Quick Repair & Rebuild into:
 *   custom/modules/Administration/Ext/Administration/administration.ext.php
 * which is included at the end of modules/Administration/metadata/adminpaneldefs.php.
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$admin_option_defs = [];
$admin_option_defs['haravan_settings'] = [
    'LBL_HARAVAN_SETTINGS',
    'LBL_HARAVAN_SETTINGS_DESC',
    '',
    'index.php?module=Administration&action=HaravanSettings',
    '',
];

$admin_group_header[] = [
    'CANALI Integration',
    '',
    false,
    $admin_option_defs,
    '',
];
