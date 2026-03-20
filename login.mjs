import { chromium } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  console.log('Navigating to SuiteCRM login page...');
  await page.goto(`${BASE_URL}/index.php?module=Users&action=Login`);

  await page.screenshot({ path: '/workspaces/suite_crm/screenshot.png' });
  console.log('Screenshot after navigation saved.');

  // Log the page HTML to inspect elements
  const html = await page.content();
  const inputMatches = [...html.matchAll(/id="([^"]*user|[^"]*pass|[^"]*login)[^"]*"/gi)].map(m => m[0]);
  console.log('Relevant input IDs:', inputMatches);

  console.log('Filling in credentials...');
  await page.fill('#user_name', 'admin');
  await page.fill('#username_password', 'Admin1234!');

  console.log('Clicking login button...');
  await page.click('#bigbutton');

  await page.waitForURL(/^(?!.*action=Login).*$/, { timeout: 30000 });

  const title = await page.title();
  const url = page.url();
  console.log(`Current URL: ${url}`);
  console.log(`Page title: ${title}`);

  const loggedIn = !url.includes('action=Login') && !title.toLowerCase().includes('login');
  console.log(loggedIn ? '✓ Successfully logged in!' : '✗ Login failed.');

  await page.screenshot({ path: '/workspaces/suite_crm/screenshot.png', fullPage: false });
  console.log('Screenshot saved to screenshot.png');

  await browser.close();
})();
