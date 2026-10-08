const { test, expect } = require('@playwright/test');

for (const css of ['bootstrap3', 'bootstrap4', 'bootstrap5', 'tailwind']) {
    test(`${css} renders activity and detail pages`, async ({ page }) => {
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.goto(`/activity?css=${css}`);
        await expect(page.getByText('Updated billing address', { exact: true })).toBeVisible();
        await page.goto(`/activity/log/1?css=${css}`);
        await expect(page.getByText('Updated billing address', { exact: true }).first()).toBeVisible();
        await page.goto(`/activity/cleared?css=${css}`);
        await expect(page.getByText('Cleared account visit', { exact: true })).toBeVisible();
        if (css === 'bootstrap5' || css === 'tailwind') {
            expect(errors).toEqual([]);
            await expect(page.locator('body')).not.toContainText('LaravelLogger::');
        }
    });
}

for (const css of ['bootstrap5', 'tailwind']) {
    test(`${css} supports accessible theme choices and follows system changes`, async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.emulateMedia({ colorScheme: 'light' });
        await page.goto(`/activity?css=${css}`);
        const dashboard = page.locator('.logger-dashboard');
        const toggle = page.locator('[data-theme-toggle]');
        await expect(toggle).toHaveCount(1);
        await expect(toggle).toHaveAccessibleName('Change theme: System theme');
        await expect(toggle.locator('svg:visible')).toHaveCount(1);
        await expect(toggle.locator('svg:visible')).toHaveAttribute('data-theme-icon', 'system');
        await expect(dashboard).toHaveAttribute('data-color-scheme', 'light');
        await toggle.click();
        await expect(toggle).toHaveAccessibleName('Change theme: Light theme');
        await toggle.focus();
        await expect(toggle).toBeFocused();
        await toggle.press('Enter');
        await expect(toggle).toHaveAccessibleName('Change theme: Dark theme');
        await expect(toggle.locator('svg:visible')).toHaveCount(1);
        await expect(toggle.locator('svg:visible')).toHaveAttribute('data-theme-icon', 'dark');
        await expect(dashboard).toHaveAttribute('data-color-scheme', 'dark');
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBeTruthy();
        const colors = await page.locator('.logger-filters').evaluate(el => ({
            ink: getComputedStyle(el).color,
            label: getComputedStyle(el.querySelector('label')).color,
            surface: getComputedStyle(el).backgroundColor,
            field: getComputedStyle(el.querySelector('input')).backgroundColor,
            scheme: getComputedStyle(el.querySelector('input')).colorScheme,
        }));
        expect(colors.ink).toBe('rgb(229, 234, 243)');
        expect(colors.label).toBe(colors.ink);
        expect(colors.surface).toBe('rgb(27, 37, 54)');
        expect(colors.field).toBe('rgb(19, 29, 45)');
        expect(colors.scheme).toBe('dark');
        await page.reload();
        await expect(toggle).toHaveAccessibleName('Change theme: Dark theme');
        await page.evaluate(() => {
            document.documentElement.classList.add('dark');
            document.documentElement.dataset.bsTheme = 'dark';
        });
        await toggle.click();
        await toggle.click();
        await expect(dashboard).toHaveAttribute('data-color-scheme', 'light');
        expect(await page.locator('.logger-filters').evaluate(el => getComputedStyle(el).color)).toBe('rgb(30, 41, 59)');
        await toggle.click();
        await toggle.click();
        await expect(dashboard).toHaveAttribute('data-theme', 'system');
        await expect(dashboard).toHaveAttribute('data-color-scheme', 'light');
        await page.emulateMedia({ colorScheme: 'dark' });
        await expect(dashboard).toHaveAttribute('data-color-scheme', 'dark');
        await page.emulateMedia({ colorScheme: 'light' });
        await expect(dashboard).toHaveAttribute('data-color-scheme', 'light');
        await page.reload();
        await expect(toggle).toHaveAccessibleName('Change theme: System theme');
        await page.goto(`/activity?css=${css}&theme=dark`);
        await expect(dashboard).toHaveAttribute('data-color-scheme', 'dark');
        await toggle.click();
        await toggle.click();
        await page.reload();
        await expect(dashboard).toHaveAttribute('data-color-scheme', 'dark');
    });

    test(`${css} can hide the theme toggle without disabling the configured theme`, async ({ page }) => {
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.goto(`/activity?css=${css}&theme=dark&toggle=off`);
        await expect(page.locator('[data-theme-toggle]')).toHaveCount(0);
        await expect(page.locator('.logger-dashboard')).toHaveAttribute('data-color-scheme', 'dark');
        expect(errors).toEqual([]);
    });

    test(`${css} keeps detail and cleared headings readable in dark mode`, async ({ page }) => {
        for (const path of ['/activity/log/1', '/activity/cleared', '/activity/cleared/log/8']) {
            await page.goto(`${path}?css=${css}&theme=dark`);
            await expect(page.locator('.logger-dashboard')).toHaveAttribute('data-color-scheme', 'dark');
            const headings = await page.locator('.logger-dashboard section h2').evaluateAll(elements => elements.map(el => getComputedStyle(el).color));
            expect(headings.length).toBeGreaterThan(0);
            expect(headings.every(color => color === 'rgb(229, 234, 243)')).toBeTruthy();
        }
    });

    test(`${css} filters activities and exports actual matching data`, async ({ page }) => {
        await page.goto(`/activity?css=${css}`);
        await page.getByRole('textbox', { name: 'Description', exact: true }).fill('billing');
        await page.getByRole('button', { name: 'Filter', exact: true }).click();
        await expect(page.getByText('Updated billing address', { exact: true })).toBeVisible();
        await expect(page.getByText('Changed notification settings', { exact: true })).toHaveCount(0);
        await expect(page.locator('.logger-dashboard')).toHaveAttribute('data-css', css);
        const download = page.waitForEvent('download');
        await page.getByRole('link', { name: 'Export JSON', exact: true }).click();
        const file = await download;
        expect(file.suggestedFilename()).toMatch(/\.json$/);
        const stream = await file.createReadStream();
        const chunks = [];
        for await (const chunk of stream) chunks.push(chunk);
        const data = JSON.parse(Buffer.concat(chunks).toString());
        expect(data).toHaveLength(1);
        expect(data[0].description).toBe('Updated billing address');
    });
}
