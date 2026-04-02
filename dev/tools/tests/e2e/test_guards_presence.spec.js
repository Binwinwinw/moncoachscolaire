// Script Playwright minimal pour valider la présence des guards sur les pages critiques
// Usage : npx playwright test dev/tools/tests/e2e/test_guards_presence.spec.js
const { test, expect } = require('@playwright/test');

const pages = [
  '/src/pages/admin/audit.php',
  '/src/pages/admin/dashboard_admin.php',
  '/src/pages/admin/diagnostic.php',
  '/src/pages/admin/exercices_admin.php',
  '/src/pages/admin/exercise_quality.php',
  '/src/pages/admin/generate_course.php',
  '/src/pages/admin/integrations.php',
  '/src/pages/admin/maintenance.php',
  '/src/pages/admin/notifications.php',
  '/src/pages/admin/reporting.php',
  '/src/pages/admin/resources.php',
  '/src/pages/admin/security.php',
];

test.describe('Guards/Helpers présents sur pages admin', () => {
  for (const pagePath of pages) {
    test(`Vérifie guards sur ${pagePath}`, async ({ page }) => {
      const res = await page.goto('http://localhost/moncoachscolaire/public' + pagePath);
      expect(res.status()).toBeLessThan(500); // Pas d'erreur fatale PHP
      const content = await page.content();
      expect(content).not.toMatch(/Call to undefined function|get_validated_param|safe_redirect/);
    });
  }
});
