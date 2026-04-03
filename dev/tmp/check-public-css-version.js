const { chromium } = require('playwright');

async function check(url) {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  await page.goto(url, { waitUntil: 'domcontentloaded' });
  const hrefs = await page.$$eval('link[rel="stylesheet"]', els => els.map(e => e.getAttribute('href')));
  console.log('\nURL=' + url);
  console.log(JSON.stringify(hrefs, null, 2));
  await browser.close();
}

(async () => {
  await check('http://localhost/moncoachscolaire/public/index.php?page=login');
  await check('http://localhost/moncoachscolaire/public/index.php?page=register');
})();
