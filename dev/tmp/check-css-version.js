const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.goto('http://localhost/moncoachscolaire/public/index.php?page=landingpage', { waitUntil: 'domcontentloaded' });
  const hrefs = await page.$$eval('link[rel="stylesheet"]', (els) => els.map((e) => e.getAttribute('href')));
  console.log(JSON.stringify(hrefs, null, 2));
  await browser.close();
})();
