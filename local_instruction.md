# Haravan API Integration — Activation Guide (AWS EC2)

Step-by-step instructions to deploy and activate the Haravan → SuiteCRM sync
on your AWS EC2 server running Docker.

---

## Prerequisites

Before starting, confirm the following on your EC2 instance:

- Docker and Docker Compose are installed (`docker --version`, `docker compose version`)
- The repository is already cloned somewhere on the server (e.g. `/home/ec2-user/suite_crm`)
- Port `8080` is open in the EC2 Security Group (or whichever port maps to the app)
- You have SSH access to the EC2 instance

---

## Step 1 — SSH into your EC2 instance

```bash
ssh -i /path/to/your-key.pem ec2-user@<YOUR_EC2_PUBLIC_IP>
```

Replace `/path/to/your-key.pem` and `<YOUR_EC2_PUBLIC_IP>` with your actual values.

---

## Step 2 — Navigate to the project directory

```bash
cd /home/ec2-user/suite_crm   # adjust path if different
```

---

## Step 3 — Pull the latest code from the integration branch

```bash
git fetch origin
git checkout claude/haravan-api-integration-NitOU
git pull origin claude/haravan-api-integration-NitOU
```

Verify the Haravan files are present:

```bash
ls SuiteCRM/custom/include/Haravan/
# Expected output:
# HaravanApiClient.php  HaravanCustomerMapper.php  HaravanHttpClient.php
# HaravanOrderMapper.php  HaravanSyncEngine.php
```

---

## Step 4 — Start the Docker containers

```bash
docker compose up -d
```

Wait about 30 seconds for MariaDB to finish initializing, then confirm both
containers are healthy:

```bash
docker compose ps
# Both 'app' and 'db' should show status "Up" or "running"
```

---

## Step 5 — Install the database table (first time only)

> **Skip this step if the `canali_garment_orders` table already exists.**

```bash
docker compose exec db mysql -usuitecrm -psuitecrm suitecrm \
  < SuiteCRM/custom/modules/CANALI_GarmentOrders/install_canali_garment_orders.sql
```

---

## Step 6 — Run Quick Repair & Rebuild

This step:
- Merges all Extension files into SuiteCRM's cache
- Creates the two new database columns (`haravan_customer_id_c` and `haravan_order_id`)
- Registers the scheduler job and admin panel entry

```bash
docker compose exec app php -r "
  define('sugarEntry', true);
  chdir('/var/www/html');
  require_once('include/entryPoint.php');
  require_once('modules/Administration/QuickRepairAndRebuild.php');
  \$repair = new RepairAndClear();
  \$repair->repairAndClearAll(
    ['rebuildExtensions', 'repairDatabase'],
    [translate('LBL_ALL_MODULES')],
    true,
    true
  );
  echo 'Quick Repair & Rebuild complete.' . PHP_EOL;
"
```

Wait for the script to finish (may take 20–60 seconds).

---

## Step 7 — Verify the new database columns exist

```bash
docker compose exec db mysql -usuitecrm -psuitecrm suitecrm \
  -e "DESCRIBE contacts_cstm;" | grep haravan

# Expected output:
# haravan_customer_id_c  varchar(64)  YES  ...
```

```bash
docker compose exec db mysql -usuitecrm -psuitecrm suitecrm \
  -e "DESCRIBE canali_garment_orders;" | grep haravan

# Expected output:
# haravan_order_id  varchar(64)  YES  ...
```

If either column is missing, re-run Step 6.

---

## Step 8 — Open SuiteCRM in your browser

Navigate to:

```
http://<YOUR_EC2_PUBLIC_IP>:8080
```

Log in with admin credentials (default: `admin` / `Admin1234!`).

---

## Step 9 — Enter your Haravan API credentials

1. Click **Admin** (top-right menu or navigation bar)
2. Scroll down to the **CANALI Integration** section
3. Click **Haravan API Settings**
4. Fill in the form:

   | Field | Value |
   |-------|-------|
   | **Shop Domain** | Your Haravan store domain, e.g. `myshop.haravan.com` |
   | **Access Token** | The access token from your Haravan private app (visible in the screenshot you shared) |
   | **Sync Enabled** | ✅ Check this box |

5. Click **Save Settings**

You should see a green "Haravan settings saved successfully." confirmation banner.

---

## Step 10 — Create the Sync Scheduler

1. In the Admin panel, click **Schedulers** (under the "Jobs" or "Advanced" section)
2. Click **Create Scheduler** (top-right button)
3. Fill in the fields:

   | Field | Value |
   |-------|-------|
   | **Job Name** | `Haravan Data Sync` (any label) |
   | **Job** | Select `syncHaravanData` from the dropdown |
   | **Run Interval** | `*/30 * * * *` — every 30 minutes |
   | **Status** | `Active` |
   | **Date/Time Range** | Leave as default (always active) |

4. Click **Save**

---

## Step 11 — Run a manual test sync

Trigger the sync immediately from the terminal to confirm it works before
waiting for the scheduler:

```bash
docker compose exec app php -r "
  define('sugarEntry', true);
  chdir('/var/www/html');
  require_once('include/entryPoint.php');
  require_once('custom/include/Haravan/HaravanHttpClient.php');
  require_once('custom/include/Haravan/HaravanApiClient.php');
  require_once('custom/include/Haravan/HaravanCustomerMapper.php');
  require_once('custom/include/Haravan/HaravanOrderMapper.php');
  require_once('custom/include/Haravan/HaravanSyncEngine.php');

  \$admin = BeanFactory::newBean('Administration');
  \$admin->retrieveSettings('haravan');
  \$shopDomain  = trim(\$admin->settings['haravan_haravan_shop_domain']  ?? '');
  \$accessToken = trim(\$admin->settings['haravan_haravan_access_token'] ?? '');

  echo 'Shop domain:  ' . (\$shopDomain  ?: '(not set)') . PHP_EOL;
  echo 'Access token: ' . (\$accessToken ? str_repeat('*', strlen(\$accessToken) - 4) . substr(\$accessToken, -4) : '(not set)') . PHP_EOL;

  if (empty(\$shopDomain) || empty(\$accessToken)) {
    echo 'ERROR: credentials not saved. Repeat Step 9.' . PHP_EOL;
    exit(1);
  }

  \$http   = new HaravanHttpClient(\$shopDomain, \$accessToken);
  \$api    = new HaravanApiClient(\$http);
  \$engine = new HaravanSyncEngine(\$api);
  \$result = \$engine->runFullSync();

  echo 'Sync result: ' . (\$result ? 'SUCCESS' : 'PARTIAL FAILURE — check logs') . PHP_EOL;
"
```

A successful run prints:
```
Shop domain:  myshop.haravan.com
Access token: ****<last4>
Sync result: SUCCESS
```

---

## Step 12 — Verify data in SuiteCRM

**Check synced clients:**
1. Go to **Clients** (Contacts) in the top navigation
2. You should see contacts with Haravan customer data (name, email, phone)

**Check synced orders:**
1. Go to **CANALI Garment Orders** module
2. Orders from Haravan should appear with order number, date, total price, and status

**Check a specific contact's Haravan ID** (in the database):
```bash
docker compose exec db mysql -usuitecrm -psuitecrm suitecrm \
  -e "SELECT id, haravan_customer_id_c FROM contacts_cstm
      WHERE haravan_customer_id_c IS NOT NULL
      LIMIT 5;"
```

---

## Step 13 — Monitor sync logs

Tail the SuiteCRM log to watch the scheduler run in real-time:

```bash
docker compose exec app tail -f /var/www/html/suitecrm.log | grep -i haravan
```

You will see lines like:
```
HaravanSyncEngine: starting full sync
HaravanApiClient: fetching customers.json params={"updated_at_min":"...","limit":250}
HaravanSyncEngine: customers done — created=12 updated=3 errors=0
HaravanSyncEngine: orders done — created=8 updated=2 errors=0
HaravanSyncEngine: full sync complete, success=true
```

Press `Ctrl+C` to stop tailing.

---

## Troubleshooting

### "shop_domain or access_token not configured"
- Go back to Step 9 and re-enter the credentials, then save.
- Re-run the manual test sync (Step 11) to confirm.

### "HTTP 401" or "HTTP 403" in the log
- Your access token is wrong or expired.
- In Haravan Admin → Apps → Private Apps, regenerate the token and update it in Step 9.

### "HTTP 404" in the log
- Your shop domain is wrong. It should be the raw domain without `https://`.
- Correct: `myshop.haravan.com`
- Wrong: `https://myshop.haravan.com` or `myshop.myharavan.com`

### `syncHaravanData` not appearing in the Scheduler dropdown
- Quick Repair & Rebuild was not run or did not complete successfully.
- Repeat Step 6 and check for PHP errors in the output.

### `haravan_customer_id_c` column missing after QR&R
- Run the repair database step explicitly:
  ```bash
  docker compose exec app php -r "
    define('sugarEntry', true);
    chdir('/var/www/html');
    require_once('include/entryPoint.php');
    require_once('modules/Administration/QuickRepairAndRebuild.php');
    \$repair = new RepairAndClear();
    \$repair->repairAndClearAll(['repairDatabase'], [translate('LBL_ALL_MODULES')], true, true);
    echo 'Done' . PHP_EOL;
  "
  ```

### Containers not starting
```bash
docker compose down
docker compose up -d --build
docker compose logs -f app
```

---

## Sync schedule reference

| Interval string | Frequency |
|-----------------|-----------|
| `*/30 * * * *`  | Every 30 minutes (recommended) |
| `0 * * * *`     | Every hour |
| `0 2 * * *`     | Once daily at 2 AM |

To change the interval later: Admin → Schedulers → click the scheduler name → edit → Save.

---

## Security notes

- The Haravan access token is stored in the SuiteCRM `config` database table.
  Ensure your EC2 security group restricts MariaDB port 3306 to localhost only.
- Use HTTPS for the SuiteCRM URL in production (add a reverse proxy such as
  nginx with an SSL certificate from Let's Encrypt).
- Rotate your Haravan access token periodically and update it in Step 9.
