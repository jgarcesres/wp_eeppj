const { test, expect } = require('@playwright/test');
const path = require('path');

const SCREENSHOT_DIR = path.join(__dirname, 'screenshots');

const pages = [
  { path: '/', name: 'homepage' },
  { path: '/pqrrs/', name: 'pqrrs' },
  { path: '/transparencia/', name: 'transparencia' },
  { path: '/contactenos/', name: 'contactenos' },
  { path: '/blog/', name: 'blog' },
];

for (const pg of pages) {
  test(`screenshot: ${pg.name}`, async ({ page }) => {
    await page.goto(pg.path, { waitUntil: 'networkidle' });

    // Dismiss cookie consent if present
    const cookieBtn = page.locator('[data-cookie-accept]');
    if (await cookieBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
      await cookieBtn.click();
      await page.waitForTimeout(500);
    }

    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, `${pg.name}-desktop.png`),
      fullPage: true,
    });

    // Page should have meaningful content
    const bodyText = await page.locator('body').innerText();
    expect(bodyText.length).toBeGreaterThan(100);
  });
}

// Mobile screenshots
const mobilePages = [
  { path: '/', name: 'homepage' },
  { path: '/pqrrs/', name: 'pqrrs' },
];

for (const pg of mobilePages) {
  test(`screenshot mobile: ${pg.name}`, async ({ browser }) => {
    const context = await browser.newContext({
      viewport: { width: 375, height: 812 },
    });
    const page = await context.newPage();
    await page.goto(pg.path, { waitUntil: 'networkidle' });

    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, `${pg.name}-mobile.png`),
      fullPage: true,
    });

    await context.close();
  });
}
