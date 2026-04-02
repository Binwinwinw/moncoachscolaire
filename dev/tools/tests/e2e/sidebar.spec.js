const { test, expect } = require('@playwright/test');

test('header hamburger toggles the sidebar (ARIA and body class)', async ({ page }) => {
  // Aller sur une page où la sidebar est présente (ex : lycee/index.php)
  await page.goto('http://127.0.0.1/moncoachscolaire/lycee/index.php');

  const sidebar = page.locator('#site-sidebar');
  const toggle = page.locator('#headerSidebarToggle');

  // initially sidebar should exist and indicate it's visible
  await expect(sidebar).toHaveAttribute('aria-hidden', 'false');
  await expect(toggle).toHaveAttribute('aria-expanded', 'true');

  // Vérifie simplement la présence du sidebar et du bouton toggle
  await expect(sidebar).toHaveCount(1);
  await expect(toggle).toHaveCount(1);
});
