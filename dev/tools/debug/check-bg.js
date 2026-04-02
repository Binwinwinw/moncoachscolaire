const { chromium } = require('playwright');
(async () => {
  const ports = ['http://127.0.0.1:8080', 'http://127.0.0.1:8081'];
  const browser = await chromium.launch();
  for (const base of ports) {
    try {
      const page = await browser.newPage();
      const r = await page.goto(base, { waitUntil: 'domcontentloaded', timeout: 10000 });
      console.log('Base:', base, 'status:', r ? r.status() : 'no response');
      const info = await page.evaluate(() => {
        return {
          bodyClass: document.body.className,
          rootVar: getComputedStyle(document.documentElement).getPropertyValue('--page-bg-image').trim(),
          beforeBg: window.getComputedStyle(document.body, '::before').getPropertyValue('background-image'),
          bodyBg: window.getComputedStyle(document.body).getPropertyValue('background-image'),
          links: Array.from(document.querySelectorAll('link[rel=stylesheet]')).map(l => l.href)
        };
      });
      console.log(JSON.stringify(info, null, 2));
      await page.close();
    } catch (e) {
      console.log('Base:', base, 'error:', e.message);
    }
  }
  await browser.close();
})();
