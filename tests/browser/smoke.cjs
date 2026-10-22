const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const assert = require('assert/strict');

const base = process.env.APP_BASE_URL || 'http://127.0.0.1:8000';
const output = process.env.UI_OUTPUT_DIR || '/tmp/attendance-ui';
fs.mkdirSync(output, { recursive: true });
const failures = [];
const checks = [];
async function record(label, action) { await action(); checks.push(label); console.log('PASS: ' + label); }
async function noOverflow(page) {
  assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1), true, 'Page must not overflow viewport');
}
async function capture(page, name, fullPage = false) {
  const buffer = await page.screenshot({ path: path.join(output, name + '.jpg'), type: 'jpeg', quality: 55, fullPage });
  if (process.env.EMIT_QA_IMAGES === '1') {
    const encoded = buffer.toString('base64');
    const chunks = encoded.match(/.{1,4000}/g) || [];
    console.log('QA_IMAGE_BEGIN:' + name + ':' + chunks.length);
    chunks.forEach((chunk, index) => console.log('QA_IMAGE_CHUNK:' + name + ':' + index + ':' + chunk));
    console.log('QA_IMAGE_END:' + name);
  }
}
async function signIn(context, email, password) {
  const page = await context.newPage();
  page.on('pageerror', error => failures.push(error.message));
  const response = await page.goto(base + '/login');
  assert.equal(response.status(), 200);
  await page.getByLabel('Work email').fill(email);
  await page.getByLabel('Password', { exact: true }).fill(password);
  await page.getByRole('button', { name: 'Sign in to workspace' }).click();
  await page.waitForURL(base + '/');
  return page;
}
(async () => {
  const browser = await chromium.launch(process.env.CHROME_PATH
    ? { executablePath: process.env.CHROME_PATH, headless: true, args: ['--no-sandbox'] }
    : { channel: 'chrome', headless: true, args: ['--no-sandbox'] });
  try {
    const adminContext = await browser.newContext({ viewport: { width: 1440, height: 1100 } });
    const login = await adminContext.newPage();
    await login.goto(base + '/login');
    await noOverflow(login);
    await capture(login, 'login-desktop');
    assert.equal(await login.locator('link[href*="vendor/bootstrap"]').count(), 1);
    const css = await adminContext.request.get(base + '/vendor/bootstrap/bootstrap.min.css');
    assert.equal(css.status(), 200);
    await login.close();
    const admin = await signIn(adminContext, 'admin@example.test', 'DemoAdmin123!');
    await record('Admin dashboard and server-provided metrics', async () => {
      await admin.getByRole('heading', { name: 'A clear view of your team.' }).waitFor();
      await noOverflow(admin);
      await capture(admin, 'admin-desktop', true);
    });
    await record('All admin pages render on desktop and mobile', async () => {
      for (const width of [1440, 390]) {
        await admin.setViewportSize({ width, height: width === 390 ? 844 : 1100 });
        for (const route of ['/employees', '/departments', '/attendance', '/leaves', '/reports', '/access', '/account']) {
          const response = await admin.goto(base + route);
          assert.equal(response.status(), 200, route);
          await noOverflow(admin);
        }
      }
      await admin.getByRole('button', { name: 'Open navigation' }).click();
      assert.equal(await admin.getByRole('button', { name: 'Close navigation' }).getAttribute('aria-expanded'), 'true');
      await admin.keyboard.press('Escape');
      assert.equal(await admin.getByRole('button', { name: 'Open navigation' }).getAttribute('aria-expanded'), 'false');
      await admin.setViewportSize({ width: 1440, height: 1100 });
    });
    await record('CSRF protects write requests', async () => {
      const response = await adminContext.request.post(base + '/departments', { form: { name: 'No CSRF must fail' } });
      assert.equal(response.status(), 419);
    });
    await record('Admin creates department and employee through forms', async () => {
      await admin.goto(base + '/departments');
      await admin.getByLabel('New department').fill('QA Department');
      await admin.getByRole('button', { name: 'Add department', exact: true }).click();
      await admin.waitForLoadState();
      await admin.getByRole('cell', { name: 'QA Department', exact: true }).waitFor();
      await admin.goto(base + '/employees/create');
      await admin.getByLabel('Full name').fill('QA Employee');
      await admin.getByLabel('Employee code').fill('QA001');
      await admin.getByLabel('Email / login').fill('qa.browser@example.test');
      await admin.getByLabel('Department', { exact: true }).selectOption({ label: 'QA Department' });
      await admin.getByLabel('Password', { exact: true }).fill('DemoEmployee123!');
      await admin.getByLabel('Confirm password').fill('DemoEmployee123!');
      await admin.getByRole('button', { name: 'Save employee' }).click();
      await admin.waitForURL(base + '/employees');
      await admin.getByRole('cell', { name: 'QA Employee', exact: true }).waitFor();
    });
    const employeeContext = await browser.newContext({ viewport: { width: 1280, height: 1000 } });
    const employee = await signIn(employeeContext, 'qa.browser@example.test', 'DemoEmployee123!');
    await record('Employee check-in and checkout work through UI', async () => {
      const checkin = employee.getByRole('button', { name: 'Check in', exact: true });
      if (await checkin.count()) {
        await checkin.click();
        await employee.waitForLoadState();
        await employee.getByRole('button', { name: 'Check out', exact: true }).click();
        await employee.waitForLoadState();
        await employee.getByText('You are checked out for today.', { exact: false }).waitFor();
      } else {
        // The configured non-working-day state must never offer attendance buttons.
        await employee.getByText('Today is a non-working day.', { exact: false }).waitFor();
      }
    });
    await record('Employee cannot access administrator pages', async () => {
      for (const route of ['/employees', '/departments', '/reports/export', '/access']) {
        const response = await employeeContext.request.get(base + route);
        assert.equal(response.status(), 403);
      }
      assert.equal(await employee.locator('.side-nav').getByText('Team directory', { exact: true }).count(), 0);
    });
    await record('Employee submits leave and admin approves it through UI', async () => {
      await employee.goto(base + '/leaves');
      const minimum = await employee.getByLabel('From', { exact: true }).getAttribute('min');
      const next = new Date(minimum + 'T12:00:00Z');
      next.setUTCDate(next.getUTCDate() + 8);
      while ([0, 6].includes(next.getUTCDay())) next.setUTCDate(next.getUTCDate() + 1);
      const date = next.toISOString().slice(0, 10);
      await employee.getByLabel('From', { exact: true }).fill(date);
      await employee.getByLabel('To', { exact: true }).fill(date);
      await employee.getByLabel('Reason', { exact: true }).fill('QA sample leave request for browser verification');
      await employee.getByRole('button', { name: 'Submit request' }).click();
      await employee.waitForLoadState();
      await employee.getByRole('cell', { name: 'Pending', exact: true }).waitFor();
      await admin.goto(base + '/leaves?status=pending');
      const row = admin.getByRole('row').filter({ hasText: 'QA sample leave request for browser verification' });
      await row.locator('select[name="status"]').selectOption('approved');
      await row.locator('textarea[name="review_note"]').fill('Coverage arranged for this demo request');
      await row.getByRole('button', { name: 'Save decision' }).click();
      await admin.waitForLoadState();
      await employee.reload();
      await employee.getByRole('cell', { name: 'Approved', exact: true }).waitFor();
    });
    await record('Admin edits and deactivates/reactivates an employee', async () => {
      await admin.goto(base + '/employees?q=QA001');
      await admin.getByRole('link', { name: 'Edit', exact: true }).click();
      await admin.getByLabel('Full name').fill('QA Employee Updated');
      await admin.getByRole('button', { name: 'Save employee' }).click();
      await admin.waitForLoadState();
      await admin.getByRole('heading', { name: 'QA Employee Updated', exact: true }).waitFor();
      admin.once('dialog', dialog => dialog.accept());
      await admin.getByRole('button', { name: 'Deactivate', exact: true }).click();
      await admin.waitForLoadState();
      await employee.goto(base + '/');
      await employee.getByText('Your profile is inactive.', { exact: false }).waitFor();
      await admin.getByRole('button', { name: 'Reactivate', exact: true }).click();
      await admin.waitForLoadState();
      await employee.reload();
      assert.equal(await employee.getByText('Your profile is inactive.', { exact: false }).count(), 0);
    });
    await record('CSV download works', async () => {
      await admin.goto(base + '/reports');
      const pending = admin.waitForEvent('download');
      await admin.getByRole('link', { name: 'Export CSV', exact: true }).click();
      const download = await pending;
      const text = fs.readFileSync(await download.path(), 'utf8');
      assert.match(download.suggestedFilename(), /^attendance-/);
      assert.match(text.split('\n')[0], /Leave/);
      assert.match(text, /QA001/);
    });
    await record('Employee changes own password through account settings', async () => {
      await employee.goto(base + '/account');
      await employee.getByLabel('Current password', { exact: true }).fill('DemoEmployee123!');
      await employee.getByLabel('New password', { exact: true }).fill('BrowserVerified123!');
      await employee.getByLabel('Confirm new password', { exact: true }).fill('BrowserVerified123!');
      await employee.getByRole('button', { name: 'Update password', exact: true }).click();
      await employee.waitForLoadState();
      await employee.getByText('Your password has been updated.', { exact: true }).waitFor();
      await employee.getByRole('button', { name: 'Sign out', exact: true }).click();
      await employee.waitForURL(base + '/login');
      const second = await signIn(employeeContext, 'qa.browser@example.test', 'BrowserVerified123!');
      await second.getByRole('heading', { name: 'Your day, at a glance.' }).waitFor();
      await second.close();
    });
    await record('Seeded employee portal and responsive navigation', async () => {
      const context = await browser.newContext({ viewport: { width: 1440, height: 1100 } });
      const page = await signIn(context, 'ayesha@example.test', 'DemoEmployee123!');
      await capture(page, 'employee-desktop', true);
      for (const route of ['/my-profile', '/my-attendance', '/leaves', '/account']) {
        const response = await page.goto(base + route);
        assert.equal(response.status(), 200);
        assert.equal(await page.getByText('bilal@example.test', { exact: true }).count(), 0);
      }
      await page.setViewportSize({ width: 390, height: 844 });
      await page.goto(base + '/');
      await noOverflow(page);
      await capture(page, 'employee-mobile');
      await page.getByRole('button', { name: 'Open navigation' }).click();
      await page.locator('.side-nav').getByRole('link', { name: 'My attendance', exact: true }).click();
      await page.waitForURL(base + '/my-attendance');
      await noOverflow(page);
      await context.close();
    });
    assert.deepEqual(failures, [], 'No browser JavaScript errors');
    fs.writeFileSync(path.join(output, 'results.json'), JSON.stringify({ passed: checks, browserErrors: failures }, null, 2));
    console.log('BROWSER_QA_RESULT:' + JSON.stringify({ checks: checks.length, browserErrors: failures.length }));
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exit(1); });
