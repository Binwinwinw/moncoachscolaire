const { chromium } = require('@playwright/test');
(async ()=>{
  const url = process.argv[2] || 'http://127.0.0.1:8081/';
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.goto(url);
  const className = await page.evaluate(()=>document.body.className);
  const varVal = await page.evaluate(()=>{
    const doc = document.documentElement;
    return getComputedStyle(document.body).getPropertyValue('--page-bg-image') || getComputedStyle(doc).getPropertyValue('--page-bg-image');
  });
  const beforeBg = await page.evaluate(()=>{
    return window.getComputedStyle(document.body, '::before').getPropertyValue('background-image');
  });
  const styles = await page.evaluate(()=>Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map(l=>l.getAttribute('href')).join('\n'));
  console.log('body.className:', className);
  console.log('--page-bg-image:', varVal.trim() || '(empty)');
  console.log('::before background-image:', beforeBg);
  console.log('stylesheets:\n' + styles);
  await browser.close();
})();
