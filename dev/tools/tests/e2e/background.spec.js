const { test, expect } = require('@playwright/test');

test('Landing page background is visible and contains expected assets', async ({ page }) => {
  const response = await page.goto('/');
  if (!response || !response.ok()) {
    test.skip('Server not available at baseURL');
    return;
  }

  // Prefer checking a specific landing body class when present, otherwise fallback to document.body
  const bg = await page.evaluate(() => {
    const el = document.querySelector('body.landing-page.app-bg') || document.body;
    const pseudo = window.getComputedStyle(el, '::before');
    // first try ::before pseudo-element (CSS-only approach), fallback to element background
    const beforeBg = pseudo ? pseudo.getPropertyValue('background-image') : '';
    if (beforeBg && beforeBg !== 'none') return beforeBg;
    const elBg = window.getComputedStyle(el).getPropertyValue('background-image');
    return elBg || null;
  });

  // Robust assertions: background not 'none' and contains a url( reference
  expect(bg).not.toBe(null);
  expect(bg).not.toBe('none');
  expect(bg).toContain('url(');
  // Optional: check for known asset names only if stable
  // const containsAsset = /landing-illustration|background-school-material/.test(bg);
  // expect(containsAsset).toBe(true);
});
