const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  const base = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8081';
  console.log('Using base:', base);
  const r = await page.goto(base, { waitUntil: 'domcontentloaded' });
  console.log('Response ok:', r && r.ok());
  const info = await page.evaluate(() => {
    const body = document.body;
    const classes = body.className;
    const before = window.getComputedStyle(body, '::before').getPropertyValue('background-image');
    const vars = {
      page_bg_image: getComputedStyle(body).getPropertyValue('--page-bg-image'),
      page_bg_overlay: getComputedStyle(body).getPropertyValue('--page-bg-overlay')
    };
    const linked = Array.from(document.querySelectorAll('link[rel=stylesheet]')).map(l => l.href);
    return { classes, before, vars, linked: linked.slice(0,20) };
  });
  console.log(info);
  await browser.close();
})();
