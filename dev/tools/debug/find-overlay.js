const { chromium } = require('@playwright/test');
(async ()=>{
  const url = process.argv[2] || 'http://127.0.0.1:8080/public/index.php?page=bac%2Fbac-accueil';
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.goto(url);
  const overlays = await page.evaluate(()=>{
    const res = [];
    const els = Array.from(document.querySelectorAll('*'));
    els.forEach(el=>{
      const s = getComputedStyle(el);
      if (['fixed','absolute'].includes(s.position)) {
        const z = parseInt(s.zIndex) || 0;
        const bg = s.backgroundColor || s.background;
        const rect = el.getBoundingClientRect();
        const covers = rect.width >= window.innerWidth - 2 && rect.height >= window.innerHeight - 2;
        res.push({tag: el.tagName, id: el.id, classes: el.className, position: s.position, zIndex: z, bg: bg, covers: covers});
      }
    });
    return res.filter(r=>r.covers || r.zIndex>0);
  });
  console.log('found overlays count:', overlays.length);
  overlays.forEach(o=>console.log(JSON.stringify(o)));
  await browser.close();
})();
