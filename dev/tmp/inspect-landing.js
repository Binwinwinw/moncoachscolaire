const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1600, height: 900 } });
  await page.goto('http://localhost/moncoachscolaire/public/index.php?page=landingpage', { waitUntil: 'networkidle' });

  const data = await page.evaluate(() => {
    const b = document.body;
    const m = document.querySelector('main');
    const csb = getComputedStyle(b);
    const csm = m ? getComputedStyle(m) : null;
    const hero = document.querySelector('main > header[role="banner"]');
    return {
      bodyClass: b.className,
      bodyBgImage: csb.backgroundImage,
      bodyBgColor: csb.backgroundColor,
      bodyBgRepeat: csb.backgroundRepeat,
      bodyBgSize: csb.backgroundSize,
      bodyBgPos: csb.backgroundPosition,
      bodyBgAttachment: csb.backgroundAttachment,
      mainBgColor: csm ? csm.backgroundColor : null,
      mainBgImage: csm ? csm.backgroundImage : null,
      heroInlineStyle: hero ? hero.getAttribute('style') : null
    };
  });

  console.log(JSON.stringify(data, null, 2));
  await page.screenshot({ path: 'dev/tmp/landing-debug.png', fullPage: true });
  await browser.close();
})();
