// dashboard-js-errors.spec.js — Playwright test pour détecter erreurs JS et éléments manquants sur le dashboard élève
const { test, expect } = require('@playwright/test');

test.describe('Surveillance erreurs JS & éléments critiques — Dashboard élève', () => {
  test('Aucune erreur JS et présence des éléments clés', async ({ page }) => {
    const errors = [];
    page.on('pageerror', (err) => errors.push(err.message));
    await page.goto('/index.php?page=dashboard');
    await page.waitForLoadState('networkidle');

    // Vérifier l'absence d'erreurs JS
    expect(errors, 'Aucune erreur JS ne doit apparaître').toEqual([]);

    // Vérifier la présence des éléments critiques (cards, mascotte, progression...)
    await expect(page.locator('.dashboard-card')).toHaveCount(3); // 3 cards principales attendues
    await expect(page.locator('img.mascotte, .mascotte, .coach-avatar')).toHaveCount(1); // mascotte présente
    await expect(page.locator('text=Ma Progression')).toBeVisible();
    await expect(page.locator('text=Progression par Matière')).toBeVisible();
    await expect(page.locator('text=Activité Récente')).toBeVisible();
  });
});
