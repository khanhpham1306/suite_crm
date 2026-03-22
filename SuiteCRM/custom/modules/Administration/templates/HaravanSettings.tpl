{*
  Haravan Integration — Admin Settings Template
  Rendered by AdministrationViewHaravansettings::display()
*}

<div class="moduleTitle">
    <h2>Haravan API Integration</h2>
</div>

{if $saveMessage}
<div class="sugar_body_td" style="padding:8px 0;">
    <div class="statusGood" style="padding:8px 12px; border-radius:4px; background:#d4edda; color:#155724; border:1px solid #c3e6cb;">
        &#10003; {$saveMessage}
    </div>
</div>
{/if}

{if $saveError}
<div class="sugar_body_td" style="padding:8px 0;">
    <div class="statusBad" style="padding:8px 12px; border-radius:4px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;">
        &#9888; {$saveError}
    </div>
</div>
{/if}

<form method="POST" action="{$moduleUrl}">
    <input type="hidden" name="haravan_save"  value="1">
    <input type="hidden" name="form_key"      value="{$formToken}">

    <!-- ── Credentials ───────────────────────────────────────────────────── -->
    <div class="view-content">
        <table class="edit view" cellspacing="0" cellpadding="0" width="100%">
            <thead>
                <tr>
                    <th colspan="4" scope="colgroup">
                        <h3 style="margin:8px 0;">API Credentials</h3>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="dataLabel" width="20%">
                        <label for="haravan_shop_domain">Shop Domain <span class="required">*</span></label>
                    </td>
                    <td class="dataField" width="30%">
                        <input type="text"
                               id="haravan_shop_domain"
                               name="haravan_shop_domain"
                               value="{$shopDomain}"
                               placeholder="myshop.haravan.com"
                               style="width:100%; max-width:320px;"
                               class="input-lg">
                        <p class="help-block" style="color:#777; font-size:0.85em; margin-top:4px;">
                            Your Haravan store domain (without https://).
                        </p>
                    </td>
                    <td class="dataLabel" width="20%">
                        <label for="haravan_access_token">Access Token <span class="required">*</span></label>
                    </td>
                    <td class="dataField" width="30%">
                        <input type="password"
                               id="haravan_access_token"
                               name="haravan_access_token"
                               value="{$accessToken}"
                               placeholder="Private app access token"
                               style="width:100%; max-width:320px;"
                               class="input-lg">
                        <p class="help-block" style="color:#777; font-size:0.85em; margin-top:4px;">
                            Found in Haravan Admin → Apps → Private Apps → Access Token.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="dataLabel">Sync Enabled</td>
                    <td class="dataField" colspan="3">
                        <input type="checkbox"
                               id="haravan_sync_enabled"
                               name="haravan_sync_enabled"
                               value="1"
                               {if $syncEnabled}checked="checked"{/if}>
                        <label for="haravan_sync_enabled" style="font-weight:normal;">
                            Enable scheduled sync (Customers &amp; Orders)
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- ── Sync Status (read-only) ──────────────────────────────────────── -->
        <table class="edit view" cellspacing="0" cellpadding="0" width="100%" style="margin-top:16px;">
            <thead>
                <tr>
                    <th colspan="4" scope="colgroup">
                        <h3 style="margin:8px 0;">Sync Status</h3>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="dataLabel" width="20%">Last Customer Sync</td>
                    <td class="dataField" width="30%">{$lastCustomerSync}</td>
                    <td class="dataLabel" width="20%">Customers Synced</td>
                    <td class="dataField" width="30%">{$customersSynced}</td>
                </tr>
                <tr>
                    <td class="dataLabel">Last Order Sync</td>
                    <td class="dataField">{$lastOrderSync}</td>
                    <td class="dataLabel">Orders Synced</td>
                    <td class="dataField">{$ordersSynced}</td>
                </tr>
            </tbody>
        </table>

        <!-- ── Help ─────────────────────────────────────────────────────────── -->
        <table class="edit view" cellspacing="0" cellpadding="0" width="100%" style="margin-top:16px;">
            <thead>
                <tr>
                    <th colspan="4" scope="colgroup">
                        <h3 style="margin:8px 0;">How to Set Up the Scheduled Sync</h3>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="dataField" style="padding:8px 12px; color:#555;">
                        <ol style="margin:0; padding-left:1.4em; line-height:1.8;">
                            <li>Save your API credentials above and enable sync.</li>
                            <li>Go to <strong>Admin &rarr; Schedulers &rarr; Create Scheduler</strong>.</li>
                            <li>Set <em>Job</em> to <code>syncHaravanData</code>.</li>
                            <li>Set the interval (e.g. <code>*/30 * * * *</code> for every 30 minutes).</li>
                            <li>Set <em>Status</em> to <strong>Active</strong> and save.</li>
                        </ol>
                        <p style="margin-top:8px;">
                            The scheduler pulls new and updated records from Haravan and upserts them
                            into SuiteCRM Contacts and Garment Orders. Re-runs are idempotent.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- ── Actions ──────────────────────────────────────────────────────── -->
        <div style="margin-top:20px; padding:8px 0; border-top:1px solid #ddd;">
            <input type="submit"
                   name="haravan_save"
                   value="Save Settings"
                   class="button primary"
                   style="margin-right:8px;">
            <a href="index.php?module=Administration&action=index" class="button">
                &laquo; Back to Admin
            </a>
        </div>
    </div>
</form>
