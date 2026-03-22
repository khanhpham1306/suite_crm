# How to Activate the Haravan Sync on Your AWS Server

> **Who this guide is for:** Someone who has an AWS EC2 server already running
> but has little or no experience with terminals, commands, or servers.
> Every step is explained in plain language. Just follow them in order.

---

## What this integration does

Once set up, your SuiteCRM system will automatically pull data from your
Haravan online store every 30 minutes:

- **Customers** in Haravan → become **Clients** in SuiteCRM
- **Orders** in Haravan → become **Garment Orders** in SuiteCRM

You do not need to copy-paste data manually anymore.

---

## Before you start — things you need ready

Collect these four things before you begin. You will need them during the guide.

| What you need | Where to find it | Example |
|---|---|---|
| **Your EC2 server's IP address** | AWS Console → EC2 → Instances → Public IPv4 address | `54.123.45.67` |
| **Your SSH key file** (.pem file) | The `.pem` file you downloaded when you created the EC2 instance | `myserver-key.pem` |
| **Your Haravan shop domain** | The web address of your Haravan store | `myshop.haravan.com` |
| **Your Haravan access token** | Haravan Admin → Apps → Private Apps → the token shown on screen | `shpat_abc123...` |

> **What is an IP address?** It is the unique number address of your server on
> the internet, like a home address but for computers.

> **What is a .pem file?** It is a security key file that proves you are allowed
> to connect to your server. Keep it safe and never share it.

---

## Part A — Connect to your server

### Step 1 — Open a terminal on your computer

A **terminal** (also called a command prompt) is a text window where you type
instructions for your computer.

- **On Mac:** Press `Command + Space`, type `Terminal`, press Enter
- **On Windows:** Press the Windows key, type `PowerShell`, press Enter
- **On Linux:** Press `Ctrl + Alt + T`

You will see a blank window with a blinking cursor. That is normal.

---

### Step 2 — Protect your key file (Mac/Linux only)

> **Windows users:** Skip this step and go to Step 3.

Your key file needs special protection before SSH will allow you to use it.
Run this command, replacing `myserver-key.pem` with your actual file name:

```bash
chmod 400 ~/Downloads/myserver-key.pem
```

> **What does this do?** It sets the key file permissions so only you can read
> it. SSH refuses to work if the key file is too open.

You will see no output if it works. That is fine.

---

### Step 3 — Connect to your EC2 server

Type the following command into your terminal. Replace the two highlighted
parts with your real values:

```bash
ssh -i ~/Downloads/myserver-key.pem ec2-user@YOUR_EC2_IP_ADDRESS
```

**Example** (do not copy this exact line — use your own values):
```
ssh -i ~/Downloads/myserver-key.pem ec2-user@54.123.45.67
```

**The first time you connect**, you will see a message like:
```
The authenticity of host '54.123.45.67' can't be established.
Are you sure you want to continue connecting (yes/no)?
```

Type `yes` and press Enter. This is normal and only appears once.

**You are connected when you see** a prompt ending in `$` like this:
```
[ec2-user@ip-54-123-45-67 ~]$
```

> **Tip:** If you see `Permission denied`, your key file path or IP address
> may be wrong. Double-check both values.

---

## Part B — Get the latest code onto the server

### Step 4 — Go to the project folder

```bash
cd /home/ec2-user/suite_crm
```

> **What does this do?** `cd` means "change directory" — it moves you into
> the project folder where the SuiteCRM files live. If your folder has a
> different path, adjust accordingly.

---

### Step 5 — Download the latest code

Run these three commands one at a time. Press Enter after each one and wait
for it to finish before typing the next:

```bash
git fetch origin
```
```bash
git checkout claude/haravan-api-integration-NitOU
```
```bash
git pull origin claude/haravan-api-integration-NitOU
```

> **What does this do?** These commands connect to GitHub and download the
> Haravan integration code that was built for you.

**Check it worked** — run this:
```bash
ls SuiteCRM/custom/include/Haravan/
```

You should see exactly these five file names listed:
```
HaravanApiClient.php
HaravanCustomerMapper.php
HaravanHttpClient.php
HaravanOrderMapper.php
HaravanSyncEngine.php
```

If you see these files, the code download was successful.

---

## Part C — Start the application

### Step 6 — Start the application containers

```bash
docker compose up -d
```

> **What is Docker?** Docker is a tool that runs your application in an
> isolated box (called a "container"). This command starts the boxes for
> your web application and your database.

This command usually finishes in a few seconds. Wait **30 seconds** after it
completes for the database to finish starting up, then check that everything
is running:

```bash
docker compose ps
```

You should see two rows, both showing `Up` or `running` in the STATUS column:

```
NAME    STATUS          PORTS
app     Up 30 seconds   0.0.0.0:8080->80/tcp
db      Up 30 seconds   3306/tcp
```

> **If you see `Exit` instead of `Up`**, try:
> ```bash
> docker compose down
> docker compose up -d
> ```
> Then wait 30 seconds and run `docker compose ps` again.

---

### Step 7 — Set up the database table (first time only)

> **Important:** Only run this step if you have never set up the CANALI
> module before on this server. If the garment orders data already exists
> in the system, skip to Step 8.

```bash
docker compose exec db mysql -usuitecrm -psuitecrm suitecrm \
  < SuiteCRM/custom/modules/CANALI_GarmentOrders/install_canali_garment_orders.sql
```

> **What does this do?** Creates the database table that stores garment order
> records. You only need to do this once.

No output means it worked successfully.

---

## Part D — Apply the integration to the system

### Step 8 — Run the system update (very important)

This is the most important step. It tells SuiteCRM about the new Haravan
integration files and creates the new database fields it needs.

Copy the entire block below and paste it into your terminal, then press Enter:

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
  echo 'Update complete.' . PHP_EOL;
"
```

> **What does this do?** SuiteCRM keeps a cache of its settings. This command
> clears that cache, reloads everything, and adds two new columns to your
> database that the Haravan sync needs.

Wait up to **60 seconds**. When it finishes you will see:
```
Update complete.
```

---

### Step 9 — Confirm the new fields were created

Run these two commands to check that the database fields were created:

**Check 1 — for the customer field:**
```bash
docker compose exec db mysql -usuitecrm -psuitecrm suitecrm \
  -e "DESCRIBE contacts_cstm;" | grep haravan
```

You should see a line containing `haravan_customer_id_c`:
```
haravan_customer_id_c   varchar(64)   YES   ...
```

**Check 2 — for the order field:**
```bash
docker compose exec db mysql -usuitecrm -psuitecrm suitecrm \
  -e "DESCRIBE canali_garment_orders;" | grep haravan
```

You should see a line containing `haravan_order_id`:
```
haravan_order_id   varchar(64)   YES   ...
```

> **If you see nothing** (blank output), the update in Step 8 did not work
> properly. Run Step 8 again, then check again.

---

## Part E — Configure credentials in the browser

### Step 10 — Open SuiteCRM in your browser

Open any web browser (Chrome, Safari, Firefox) and go to this address.
Replace `YOUR_EC2_IP_ADDRESS` with your server's IP address:

```
http://YOUR_EC2_IP_ADDRESS:8080
```

**Example:** `http://54.123.45.67:8080`

You will see the SuiteCRM login page. Log in with:
- **Username:** `admin`
- **Password:** `Admin1234!`

> **If the page does not load**, your EC2 Security Group may be blocking
> port 8080. In AWS Console → EC2 → Security Groups, add an inbound rule
> for port 8080 from your IP address (or `0.0.0.0/0` for open access).

---

### Step 11 — Go to the Haravan settings page

1. After logging in, look for the **Admin** link — it is usually in the
   top-right corner of the page or in the navigation bar. Click it.

2. You will see the Administration panel with many sections. Scroll down
   until you find a section called **CANALI Integration**.

3. Click **Haravan API Settings**.

---

### Step 12 — Enter your Haravan API credentials

You are now on the Haravan settings form. Fill in each field:

**Shop Domain**
- Type your Haravan store domain here
- Example: `myshop.haravan.com`
- Do NOT include `https://` — just the domain name

**Access Token**
- Paste your Haravan private app access token here
- This is the long string of letters and numbers from your Haravan Private Apps page
- Example: `shpat_abc123def456...`

**Sync Enabled**
- Tick this checkbox to turn the sync on

Click the **Save Settings** button.

You should see a green message at the top: **"Haravan settings saved successfully."**

> **If you see an error**, read the message carefully. Usually it means a
> field was left empty or has an incorrect value.

---

## Part F — Set up automatic syncing

### Step 13 — Create the automatic sync schedule

This sets up an automatic timer so SuiteCRM pulls from Haravan every 30 minutes.

1. Go back to the **Admin** panel (click Admin in the top navigation)
2. Find the section called **Scheduler** or look in the menu for **Schedulers**
3. Click **Create Scheduler** (usually a button in the top-right area)
4. Fill in the form exactly like this:

   | Field | What to enter |
   |-------|---------------|
   | **Job Name** | Type anything — e.g. `Haravan Sync` |
   | **Job** | Click the dropdown and select `syncHaravanData` |
   | **Run Interval** | Type exactly: `*/30 * * * *` |
   | **Status** | Select `Active` |
   | Everything else | Leave as-is (default values) |

5. Click **Save**

> **What is `*/30 * * * *`?** This is a timer code that means "run every
> 30 minutes". You do not need to understand it — just type it exactly.

---

## Part G — Test that everything works

### Step 14 — Run a manual test right now

Instead of waiting 30 minutes for the scheduler, you can trigger the sync
immediately to make sure it is working. Go back to your terminal and paste
this whole block:

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

  echo 'Shop domain:  ' . (\$shopDomain  ?: '(not set — go back to Step 12)') . PHP_EOL;
  echo 'Access token: ' . (\$accessToken ? str_repeat('*', max(0, strlen(\$accessToken) - 4)) . substr(\$accessToken, -4) : '(not set — go back to Step 12)') . PHP_EOL;

  if (empty(\$shopDomain) || empty(\$accessToken)) { exit(1); }

  \$http   = new HaravanHttpClient(\$shopDomain, \$accessToken);
  \$api    = new HaravanApiClient(\$http);
  \$engine = new HaravanSyncEngine(\$api);
  \$result = \$engine->runFullSync();

  echo 'Sync result: ' . (\$result ? 'SUCCESS' : 'PARTIAL FAILURE — see troubleshooting section') . PHP_EOL;
"
```

**A successful result looks like this:**
```
Shop domain:  myshop.haravan.com
Access token: ************************abcd
Sync result: SUCCESS
```

If you see `SUCCESS`, the integration is fully working.

---

### Step 15 — Check the data appeared in SuiteCRM

Go back to your browser and check that the Haravan data is now in SuiteCRM:

**To check Clients (Customers):**
1. Click **Clients** in the top navigation bar
2. You should see your Haravan customers listed with their names, emails, and phone numbers

**To check Garment Orders:**
1. Click **CANALI Garment Orders** in the navigation bar
2. You should see orders from Haravan with order numbers, dates, and prices

If records appear — you are done! The integration is live.

---

### Step 16 — Watch it run in real time (optional)

To see the sync happening in real time, run this in your terminal:

```bash
docker compose exec app tail -f /var/www/html/suitecrm.log | grep -i haravan
```

You will see messages streaming like this every 30 minutes:
```
HaravanSyncEngine: starting full sync
HaravanSyncEngine: customers done — created=5 updated=2 errors=0
HaravanSyncEngine: orders done — created=3 updated=1 errors=0
HaravanSyncEngine: full sync complete, success=true
```

Press `Ctrl + C` to stop watching.

---

## Troubleshooting — when something goes wrong

### Problem: "Sync result: PARTIAL FAILURE" or errors in the log

**First, check what the error says** by running:
```bash
docker compose exec app tail -50 /var/www/html/suitecrm.log | grep -i "haravan\|error\|fatal"
```

Then find your error in the table below:

---

**Error says: "shop_domain or access_token not configured"**

Your credentials were not saved properly. Go back to **Step 12**, re-enter
both fields, and click Save again. Make sure you see the green success banner.

---

**Error says: "HTTP 401" or "HTTP 403"**

Your access token is wrong or has expired.

1. Log into your Haravan admin panel
2. Go to Apps → Private Apps
3. Find your app (named `claude_code` based on your setup)
4. Copy the access token again carefully
5. Go back to **Step 12** and paste it fresh

---

**Error says: "HTTP 404"**

Your shop domain is wrong.

- Correct format: `myshop.haravan.com`
- Wrong (remove these): `https://myshop.haravan.com` or `myshop.haravan.com/admin`

Go back to **Step 12** and fix the Shop Domain field.

---

**Problem: `syncHaravanData` does not appear in the scheduler dropdown**

The system update in Step 8 did not complete fully. Run Step 8 again.

---

**Problem: No data appeared in SuiteCRM after the test sync**

This usually means your Haravan store has no customers or orders yet, or
the `updated_at_min` filter is excluding them. Run this to do a full reset
of the sync timestamps:

```bash
docker compose exec db mysql -usuitecrm -psuitecrm suitecrm \
  -e "DELETE FROM config WHERE category='haravan' AND name LIKE '%last%sync%';"
```

Then run the manual test sync from **Step 14** again.

---

**Problem: The web page at port 8080 will not load**

1. Go to the **AWS Console** in your browser
2. Click **EC2** → **Instances** → click your instance name
3. Click the **Security** tab → click the Security Group link
4. Click **Edit inbound rules**
5. Add a rule:
   - Type: **Custom TCP**
   - Port range: **8080**
   - Source: **My IP** (or `0.0.0.0/0` if you want it open to everyone)
6. Click **Save rules**

Then try the URL again.

---

**Problem: Containers crashed or won't start**

```bash
docker compose down
docker compose up -d --build
```

Wait 30 seconds, then run:
```bash
docker compose ps
```

---

## Quick reference — useful commands

Keep this section handy for future use.

| What you want to do | Command to run |
|---------------------|----------------|
| Start the application | `docker compose up -d` |
| Stop the application | `docker compose down` |
| Run the sync right now | See the full command in Step 14 |
| Watch the live log | `docker compose exec app tail -f /var/www/html/suitecrm.log \| grep -i haravan` |
| Check containers are running | `docker compose ps` |
| See application errors | `docker compose logs -f app` |

---

## How often does the sync run?

Once the scheduler is active, the sync runs automatically every 30 minutes.
You do not need to do anything — it works in the background.

To change the frequency, go to Admin → Schedulers → click `Haravan Sync` →
edit the Run Interval field:

| Interval code | Meaning |
|---------------|---------|
| `*/30 * * * *` | Every 30 minutes |
| `0 * * * *` | Once every hour |
| `0 8 * * *` | Once every day at 8 AM |

---

## Done!

Your Haravan data will now flow automatically into SuiteCRM every 30 minutes.
Customers from Haravan appear as Clients, and orders appear as Garment Orders.

If you ever need to re-configure the credentials, go to:
**SuiteCRM Admin → CANALI Integration → Haravan API Settings**
