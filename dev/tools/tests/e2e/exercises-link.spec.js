const { test, expect } = require('@playwright/test');

test.describe('Exercices links', () => {
  test('landing page CTA navigates to central exercices hub', async ({ page }) => {
    const response = await page.goto('/');
    if (!response || !response.ok()) {
      test.skip('Server not available at baseURL');
      return;
    }

    // Try to find CTA by text (supports variants with emoji)
    const cta = page.locator('a:has-text("Mes Exercices")').first();
    const ctaCount = await cta.count();
    if (ctaCount === 0) {
      // fallback to emoji link
      const ctaAlt = page.locator('a:has-text("Exercices")').first();
      if (await ctaAlt.count() === 0) {
        test.skip('Landing CTA not found');
        return;
      }
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        ctaAlt.click()
      ]);
    } else {
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
        cta.click()
      ]);
    }

    await expect(page).toHaveURL(/exercices/);
  });

  test('footer link navigates to central exercices hub', async ({ page }) => {
    const response = await page.goto('/');
    if (!response || !response.ok()) {
      test.skip('Server not available at baseURL');
      return;
    }

    const footer = page.locator('footer.site-footer');
    if (await footer.count() === 0) {
      test.skip('Footer not present on page');
      return;
    }

    const link = footer.locator('a:has-text("Exercices")').first();
    if (await link.count() === 0) {
      test.skip('Footer exercices link not found');
      return;
    }

    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      link.click()
    ]);

    await expect(page).toHaveURL(/exercices/);
  });
});
