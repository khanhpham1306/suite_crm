# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Running the Application

```bash
# Start all services (PHP/Apache app + MariaDB)
docker compose up -d

# Stop services
docker compose down

# Rebuild after Dockerfile changes
docker compose up -d --build

# View app logs
docker compose logs -f app

# Open a shell inside the app container
docker compose exec app bash
```

App is served at **http://localhost:8080**. Default admin credentials: `admin` / `Admin1234!`

DB connection (from inside the container): host `db`, database `suitecrm`, user `suitecrm`, password `suitecrm`.

## After Any PHP/Schema Change

SuiteCRM caches metadata aggressively. After editing vardefs, language files, or view metadata, always run a Quick Repair & Rebuild in the UI:

> Admin → Repair → Quick Repair & Rebuild

Or trigger it via CLI inside the container:

```bash
docker compose exec app php -r "
  define('sugarEntry', true);
  chdir('/var/www/html');
  require_once('include/entryPoint.php');
  require_once('modules/Administration/QuickRepairAndRebuild.php');
  \$repair = new RepairAndClear();
  \$repair->repairAndClearAll(['rebuildExtensions','repairDatabase'], [translate('LBL_ALL_MODULES')], true, true);
"
```

## Running the SQL Schema Install

The `CANALI_GarmentOrders` table must exist before the module works. Apply it once:

```bash
docker compose exec db mysql -usuitecrm -psuitecrm suitecrm \
  < SuiteCRM/custom/modules/CANALI_GarmentOrders/install_canali_garment_orders.sql
```

## Playwright Smoke Test

```bash
# Install dependencies (first time)
npm install

# Run the login smoke test
node login.mjs
```

## Architecture

This is a **SuiteCRM** installation (PHP/Apache, MariaDB) customised for a luxury tailoring brand called **CANALI**. The SuiteCRM core lives in `SuiteCRM/` and is treated as read-only vendor code. All project-specific work is in two places:

### Custom Module: `CANALI_GarmentOrders`

A bespoke SuiteCRM module for tracking bespoke garment orders.

| Path | Purpose |
|------|---------|
| `SuiteCRM/modules/CANALI_GarmentOrders/CANALI_GarmentOrders.php` | Bean class (extends `Basic`) |
| `SuiteCRM/modules/CANALI_GarmentOrders/vardefs.php` | Field schema, relationships, indices |
| `SuiteCRM/modules/CANALI_GarmentOrders/metadata/` | Edit/detail/list view layouts |
| `SuiteCRM/custom/modules/CANALI_GarmentOrders/install_canali_garment_orders.sql` | DDL for `canali_garment_orders` table |
| `SuiteCRM/custom/Extension/application/Ext/Include/canali_garment_orders_module.php` | Registers module in `$beanList`, `$beanFiles`, `$moduleList` |

The module uses a **many-to-one** relationship to `Contacts` via `contact_id` (relationship name: `canali_garment_orders_contacts`). `balance_due` is a `non-db` computed field (total_price − deposit_paid).

### Contact (Client) Extensions

Custom fields are injected into the standard `Contacts` module via the Extension framework:

| Path | Purpose |
|------|---------|
| `SuiteCRM/custom/Extension/modules/Contacts/Ext/Vardefs/canali_client_fields.php` | ~30 custom fields across 3 groups: Body Measurements, Style Preferences, VIP Profile |
| `SuiteCRM/custom/Extension/modules/Contacts/Ext/Layoutdefs/canali_garment_orders_subpanel.php` | Adds Garment Orders subpanel to client detail view |
| `SuiteCRM/custom/modules/Contacts/metadata/` | Overridden edit/detail/list view layouts |

### Branding & Dropdowns

`SuiteCRM/custom/Extension/application/Ext/Language/en_us.canali_branding.php` renames UI labels (Contacts → Clients, Accounts → Ateliers), defines all custom dropdown lists (`canali_client_tier_list`, `canali_garment_type_list`, `canali_order_status_list`, `canali_fabric_category_list`, `canali_fit_style_list`), and sets the app title to CANALI.

### SuiteCRM Extension Framework Pattern

SuiteCRM merges files from `custom/Extension/` into `custom/Ext/` during Quick Repair & Rebuild. The flow is:

```
custom/Extension/modules/<Module>/Ext/<Type>/<file>.php
  --> merged by QR&R into -->
custom/modules/<Module>/Ext/<Type>.ext.php
```

Never edit the merged `.ext.php` files directly — edit the source files under `custom/Extension/`.
