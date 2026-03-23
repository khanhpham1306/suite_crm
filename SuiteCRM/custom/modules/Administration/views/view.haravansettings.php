<?php
/**
 * Haravan Integration — Admin Settings View
 *
 * Handles GET (render form) and POST (save settings then render form with
 * success or error message). Credentials are stored in the `config` table
 * under category 'haravan' via Administration::saveSetting().
 *
 * Accessible at: index.php?module=Administration&action=HaravanSettings
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'include/MVC/View/SugarView.php';

class AdministrationViewHaravansettings extends SugarView
{
    public function display()
    {
        // Restrict to admins only
        if (empty($GLOBALS['current_user']) || !$GLOBALS['current_user']->is_admin) {
            sugar_die('Access denied: administrator rights required.');
        }

        $admin = BeanFactory::newBean('Administration');

        // ── Handle POST (save) ────────────────────────────────────────────────
        $saveMessage = '';
        $saveError   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['haravan_save'])) {
            // Basic CSRF check via sugar_token
            if (function_exists('validate_form_key') && !validate_form_key()) {
                $saveError = 'Invalid form token. Please reload the page and try again.';
            } else {
                $shopDomain  = trim(strip_tags($_POST['haravan_shop_domain']  ?? ''));
                $accessToken = trim(strip_tags($_POST['haravan_access_token'] ?? ''));
                $syncEnabled = isset($_POST['haravan_sync_enabled']) ? '1' : '0';

                $admin->saveSetting('haravan', 'haravan_shop_domain',  $shopDomain);
                $admin->saveSetting('haravan', 'haravan_access_token', $accessToken);
                $admin->saveSetting('haravan', 'haravan_sync_enabled', $syncEnabled);

                $saveMessage = 'Haravan settings saved successfully.';
                $GLOBALS['log']->info(
                    'HaravanSettings: settings saved by user ' . $GLOBALS['current_user']->id
                );
            }
        }

        // ── Load current settings ─────────────────────────────────────────────
        $admin->retrieveSettings('haravan');
        $shopDomain  = $admin->settings['haravan_haravan_shop_domain']  ?? '';
        $accessToken = $admin->settings['haravan_haravan_access_token'] ?? '';
        $syncEnabled = $admin->settings['haravan_haravan_sync_enabled'] ?? '0';

        // Sync state (read-only display)
        $lastCustomerSync = $admin->settings['haravan_haravan_last_customer_sync'] ?? '';
        $lastOrderSync    = $admin->settings['haravan_haravan_last_order_sync']    ?? '';
        $customersSynced  = $admin->settings['haravan_haravan_customers_synced']   ?? '0';
        $ordersSynced     = $admin->settings['haravan_haravan_orders_synced']      ?? '0';

        // ── Render ────────────────────────────────────────────────────────────
        $smarty = new Sugar_Smarty();
        $smarty->assign('saveMessage',      $saveMessage);
        $smarty->assign('saveError',        $saveError);
        $smarty->assign('shopDomain',       htmlspecialchars($shopDomain,  ENT_QUOTES, 'UTF-8'));
        $smarty->assign('accessToken',      htmlspecialchars($accessToken, ENT_QUOTES, 'UTF-8'));
        $smarty->assign('syncEnabled',      $syncEnabled === '1');
        $smarty->assign('lastCustomerSync', $lastCustomerSync ?: 'Never');
        $smarty->assign('lastOrderSync',    $lastOrderSync    ?: 'Never');
        $smarty->assign('customersSynced',  $customersSynced);
        $smarty->assign('ordersSynced',     $ordersSynced);
        $smarty->assign('formToken',        function_exists('get_form_key') ? get_form_key() : '');
        $smarty->assign('moduleUrl',        'index.php?module=Administration&action=HaravanSettings');

        echo $smarty->fetch(
            'custom/modules/Administration/templates/HaravanSettings.tpl'
        );
    }
}
