const { test, expect } = require('@playwright/test');

test.describe('Lycée navigation & pages', () => {
  test('header and index link to lycee and pages load correctly', async ({ page }) => {
    // Aller sur la page d'accueil
    await page.goto('http://127.0.0.1/moncoachscolaire/');
    // Cliquer sur le lien Lycée+
    const lyceeLinks = page.locator('a[href*="lycee/index.php"]');
    // Il y a plusieurs liens Lycée+ sur la page d'accueil, on clique sur le premier visible
    await lyceeLinks.first().click();
    await expect(page).toHaveURL(/\/lycee\/index\.php/);
    await expect(page.locator('h1')).toContainText(/Lycée/i);
    // Vérifier les liens vers guide et exercices Seconde
    // Adapter aux liens réels présents sur la page lycée/index.php
    const coursSecondeLink = page.locator('a[href*="index.php?page=cours&niveau=seconde"]');
    await coursSecondeLink.first().click();
  });
});
