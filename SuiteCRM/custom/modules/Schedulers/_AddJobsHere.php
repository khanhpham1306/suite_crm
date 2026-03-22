<?php
/**
 * Haravan Integration — Scheduler Job Registration
 *
 * SuiteCRM's core modules/Schedulers/_AddJobsHere.php sources this file at
 * line ~795 via:
 *   if (file_exists('custom/modules/Schedulers/_AddJobsHere.php'))
 *       require('custom/modules/Schedulers/_AddJobsHere.php');
 *
 * This appends syncHaravanData to the job dropdown, making it selectable
 * when creating a new Scheduler record in Admin → Schedulers.
 *
 * After deploying this file, go to Admin → Schedulers → Create Scheduler:
 *   Job:      syncHaravanData
 *   Interval: * /30 * * * *  (every 30 minutes — remove the space)
 *   Status:   Active
 */

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

// ── Register the job function name in the dropdown list ──────────────────────
$job_strings[] = 'syncHaravanData';


// ── Job function definition ──────────────────────────────────────────────────

/**
 * Pull new/updated customers and orders from the Haravan API into SuiteCRM.
 *
 * Called by the SuiteCRM scheduler daemon with no arguments.
 * Must return true on success, false (or nothing) on failure.
 *
 * @return bool
 */
function syncHaravanData()
{
    $GLOBALS['log']->info('----->Scheduler fired job: syncHaravanData()');

    // Bootstrap Haravan library classes
    require_once 'custom/include/Haravan/HaravanHttpClient.php';
    require_once 'custom/include/Haravan/HaravanApiClient.php';
    require_once 'custom/include/Haravan/HaravanCustomerMapper.php';
    require_once 'custom/include/Haravan/HaravanOrderMapper.php';
    require_once 'custom/include/Haravan/HaravanSyncEngine.php';

    // Load stored credentials from the config table
    $admin = BeanFactory::newBean('Administration');
    $admin->retrieveSettings('haravan');

    $syncEnabled = $admin->settings['haravan_haravan_sync_enabled'] ?? '0';
    if ($syncEnabled !== '1') {
        $GLOBALS['log']->info('syncHaravanData: sync is disabled in settings. Skipping.');
        return true; // return true so scheduler doesn't mark as permanently failed
    }

    $shopDomain  = trim($admin->settings['haravan_haravan_shop_domain']  ?? '');
    $accessToken = trim($admin->settings['haravan_haravan_access_token'] ?? '');

    if (empty($shopDomain) || empty($accessToken)) {
        $GLOBALS['log']->error(
            'syncHaravanData: shop_domain or access_token not configured. ' .
            'Go to Admin → Haravan Integration to enter your API credentials.'
        );
        return false;
    }

    try {
        $http   = new HaravanHttpClient($shopDomain, $accessToken);
        $api    = new HaravanApiClient($http);
        $engine = new HaravanSyncEngine($api);

        $result = $engine->runFullSync();
        $GLOBALS['log']->info('syncHaravanData: completed with result=' . ($result ? 'OK' : 'PARTIAL_FAILURE'));
        return $result;
    } catch (Exception $e) {
        $GLOBALS['log']->fatal('syncHaravanData: unexpected exception: ' . $e->getMessage());
        return false;
    }
}
