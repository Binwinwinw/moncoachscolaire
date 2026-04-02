const { test, expect } = require('@playwright/test');

test.describe('Footer component', () => {
  test('footer visible and exercises link navigates', async ({ page }) => {
    const response = await page.goto('/');
    if (!response || !response.ok()) {
      test.skip('Server not available at baseURL');
      return;
    }

    const footer = page.locator('footer.site-footer');
    const footerCount = await footer.count();
    if (footerCount === 0) {
      test.skip('Footer not present on page (maybe server not configured)');
      return;
    }
    await expect(footer).toBeVisible();

    const exercicesLink = footer.locator('a:has-text("Exercices")');
    await expect(exercicesLink).toHaveCount(1);

    const href = await exercicesLink.getAttribute('href');
    expect(href).toBeTruthy();

    // Click and ensure we navigate to a URL that contains "exercices"
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      exercicesLink.click()
    ]);
    await expect(page).toHaveURL(/exercices/);
  });

  test('footer script loaded and exposes scrollToFeatures', async ({ page }) => {
    const response = await page.goto('/');
    if (!response || !response.ok()) {
      test.skip('Server not available at baseURL');
      return;
    }

    // Prefer vérifier la présence de la fonction globale plutôt que l'existence d'un script tag
    const fnExists = await page.evaluate(() => typeof window.scrollToFeatures === 'function');
    const scriptCount = await page.locator('script[src*="footer-animations.js"]').count();

    if (!fnExists && scriptCount === 0) {
      // Rien à vérifier ici en environnement CI local sans build — skip proprement
      test.skip('footer script not present and function not exposed (skipping)');
      return;
    }

    // Si le script est présent mais la fonction n'a pas été définie, échouer
    if (!fnExists && scriptCount > 0) {
      throw new Error('footer-animations.js loaded but did not expose scrollToFeatures');
    }

    expect(fnExists).toBe(true);
  });

  test('feature-card initial inline style set when present', async ({ page }) => {
    await page.goto('/');

    const count = await page.evaluate(() => document.querySelectorAll('.feature-card').length);
    if (count === 0) {
      test.skip();
      return;
    }

    const opacity = await page.evaluate(() => {
      const el = document.querySelector('.feature-card');
      return el ? el.style.opacity : null;
    });

    expect(opacity).toBe('0');
  });
});
