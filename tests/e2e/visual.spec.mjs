import { test } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';

const artifactsRoot = path.join(process.cwd(), 'artifacts', 'e2e-visual');

const ensureDir = async (dir) => {
    await fs.mkdir(dir, { recursive: true });
};

const saveShot = async (page, name, testInfo) => {
    const projectName = testInfo.project.name.replace(/[^a-z0-9_-]+/gi, '_');
    const dir = path.join(artifactsRoot, projectName);
    await ensureDir(dir);
    const filePath = path.join(dir, `${name}.png`);
    await page.screenshot({ path: filePath, fullPage: true });
};

const gotoAndShot = async (page, url, name, testInfo) => {
    await page.goto(url, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    await saveShot(page, name, testInfo);
};

test.describe('visual smoke', () => {
    test('landing', async ({ page }, testInfo) => {
        await gotoAndShot(page, '/', 'landing', testInfo);
    });

    test('onboarding scheduling_only', async ({ page }, testInfo) => {
        await gotoAndShot(page, '/onboarding?mode=scheduling_only', 'onboarding-scheduling-only', testInfo);
    });

    test('onboarding scheduling_with_payments', async ({ page }, testInfo) => {
        await gotoAndShot(page, '/onboarding?mode=scheduling_with_payments', 'onboarding-with-payments', testInfo);
    });

    test('login', async ({ page }, testInfo) => {
        await gotoAndShot(page, '/login', 'login', testInfo);
    });

    test.describe('authenticated modules', () => {
        test.skip(!process.env.E2E_ADMIN_EMAIL || !process.env.E2E_ADMIN_PASSWORD, 'Admin credentials not provided.');

        test('dashboard and admin modules', async ({ page }, testInfo) => {
            await page.goto('/login', { waitUntil: 'networkidle' });
            await page.fill('input[type=\"email\"]', process.env.E2E_ADMIN_EMAIL);
            await page.fill('input[type=\"password\"]', process.env.E2E_ADMIN_PASSWORD);
            await page.click('button[type=\"submit\"]');

            await page.waitForLoadState('networkidle');
            await page.waitForTimeout(1000);

            await gotoAndShot(page, '/dashboard', 'dashboard', testInfo);
            await gotoAndShot(page, '/dashboard/account', 'account', testInfo);
            await gotoAndShot(page, '/dashboard/master-admin', 'master-admin', testInfo);
        });
    });
});
