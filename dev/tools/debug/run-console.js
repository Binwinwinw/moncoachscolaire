const { chromium } = require('playwright');
(async () => {
  const url = process.argv[2] || 'http://127.0.0.1:8081/';
  const browser = await chromium.launch();
  const page = await browser.newPage();
  const res = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 10000 });
  console.log('Status:', res ? res.status() : 'no response');
  const out = await page.evaluate(() => {
    const lines = [];
    lines.push('1. Classe body: ' + document.body.className);
    lines.push('2. Variable CSS: ' + getComputedStyle(document.documentElement).getPropertyValue('--page-bg-image'));
    lines.push('3. body::before BG: ' + window.getComputedStyle(document.body, '::before').getPropertyValue('background-image'));
    lines.push('4. Règle app-bg active? (body background-image): ' + window.getComputedStyle(document.body).getPropertyValue('background-image'));
    return lines;
  });
  out.forEach(l => console.log(l));
  await browser.close();
})();
