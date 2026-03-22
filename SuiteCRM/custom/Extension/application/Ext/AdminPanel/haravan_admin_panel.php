<?php
/**
 * Haravan Integration — Admin panel entry.
 *
 * Adds a "Haravan API Settings" link under a "CANALI Integration" group
 * on the Admin panel landing page (index.php?module=Administration&action=index).
 *
 * Array format:
 *   $admin_option_defs[GROUP][KEY] = [
 *       title_label_key,
 *       description_label_key,
 *       icon_class (unused in SuiteCRM 7 but required),
 *       link_url,
 *       security_group ('' = always visible),
 *   ]
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$admin_option_defs['CANALI Integration']['haravan_settings'] = array(
    'LBL_HARAVAN_SETTINGS',
    'LBL_HARAVAN_SETTINGS_DESC',
    '',
    'index.php?module=Administration&action=HaravanSettings',
    '',
);
