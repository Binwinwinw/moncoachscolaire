// college-accueil-visual.spec.js — Playwright visual regression test pour la page collège-accueil
const { test, expect } = require('@playwright/test');

test.describe('Visual regression — college-accueil', () => {
  test('Screenshot /index.php?page=eleve/college/college-accueil', async ({ page }) => {
    await page.goto('/index.php?page=eleve/college/college-accueil');
    await page.waitForLoadState('networkidle');
    expect(await page.screenshot({ fullPage: true })).toMatchSnapshot('college-accueil.png', { threshold: 0.15 });
  });
});
