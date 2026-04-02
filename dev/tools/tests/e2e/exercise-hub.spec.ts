import { test, expect } from '@playwright/test';

const levels = ['6eme', '5eme', '4eme', '3eme', 'college', 'lycee'];

test.describe('Exercise Hub Responsive + Pagination', () => {
    test('Desktop pagination + cache', async ({ page }) => {
        await page.goto('?page=college/6eme/exercices-6eme');
        await expect(page.locator('article.exercise-card, [data-exercise-id]')).toHaveCount(12);
        await page.click('button:has-text("Suivant")');
        await expect(page.locator('.exercise-card')).toHaveCount(24);
    });

    test('Mobile responsive', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 812 });
        await page.goto('?page=college/6eme/exercices-6eme');
        await expect(page.locator('.level-tab.active')).toBeVisible();
        await expect(page.locator('article.exercise-card, [data-exercise-id]')).toHaveCount(12);
    });

    levels.forEach(level => {
      test(`Level navigation: ${level}`, async ({ page }) => {
        let pagePath = '';
        if (level === 'college') pagePath = 'college/exercices-college';
        else if (level === 'lycee') pagePath = 'lycee/exercices-lycee';
        else pagePath = `college/${level}/exercices-${level}`;
        await page.goto(`?page=${pagePath}`);
        await expect(page.locator(`text=${level.toUpperCase()}`)).toHaveClass(/active/);
      });
    });
});
