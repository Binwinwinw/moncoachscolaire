// visual-regression.spec.js — Playwright visual regression test
const { test, expect } = require('@playwright/test');

// Liste des pages critiques à tester (à adapter selon inventaire)
const pages = [
  '/',
  '/index.php?page=login',
  '/index.php?page=register',
  '/index.php?page=eleve/dashboard',
  '/index.php?page=dashboard_parent',
  '/index.php?page=dashboard_admin',
  // Ajouter d'autres pages clés ici
];

test.describe('Visual regression — pages critiques', () => {
  for (const pagePath of pages) {
    test(`Screenshot ${pagePath}`, async ({ page }) => {
      await page.goto(pagePath);
      // Attendre le chargement complet (adapter si besoin)
      await page.waitForLoadState('networkidle');
      // Screenshot de la page entière
      expect(await page.screenshot({ fullPage: true })).toMatchSnapshot(`${pagePath.replace(/[\/?=]/g, '_')}.png`, { threshold: 0.15 });
    });
  }
});
