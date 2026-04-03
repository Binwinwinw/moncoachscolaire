const fs = require('fs');
const path = require('path');

function walk(dir, out = []) {
  for (const name of fs.readdirSync(dir)) {
    const p = path.join(dir, name);
    const st = fs.statSync(p);
    if (st.isDirectory()) walk(p, out);
    else if (st.isFile() && /exercices.*\.php$/i.test(name)) out.push(p);
  }
  return out;
}

const files = walk(path.join('src', 'pages', 'eleve'));
const rules = [
  {
    re: /<\?php if \(function_exists\('asset_url'\)\): \?>\s*<link rel="stylesheet" href="<\?php echo asset_url\('assets\/css\/pages\/dynamic-exercises\.css'\); \?>">\s*<\?php else: \?>\s*<link rel="stylesheet" href="\/assets\/css\/pages\/dynamic-exercises\.css">\s*<\?php endif; \?>/gms,
    to: `<link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/dynamic-exercises.css') : 'assets/css/pages/dynamic-exercises.css'; ?>">`
  },
  {
    re: /<\?php if \(function_exists\('asset_url'\)\): \?>\s*<script src="<\?php echo asset_url\('assets\/js\/interactive-exercises\.js'\); \?>"><\/script>\s*<\?php else: \?>\s*<script src="\/assets\/js\/interactive-exercises\.js"><\/script>\s*<\?php endif; \?>/gms,
    to: `<script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/interactive-exercises.js') : 'assets/js/interactive-exercises.js'; ?>"></script>`
  },
  {
    re: /<\?php if \(function_exists\('asset_url'\)\): \?>\s*<script src="<\?php echo asset_url\('assets\/js\/dynamic-exercises\.js'\); \?>"><\/script>\s*<\?php else: \?>\s*<script src="\/assets\/js\/dynamic-exercises\.js"><\/script>\s*<\?php endif; \?>/gms,
    to: `<script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/dynamic-exercises.js') : 'assets/js/dynamic-exercises.js'; ?>"></script>`
  },
  {
    re: /<\?php if \(function_exists\('asset_url'\)\): \?>\s*<script src="<\?php echo asset_url\('assets\/js\/exercises\.js'\); \?>"><\/script>\s*<\?php else: \?>\s*<script src="\/assets\/js\/exercises\.js"><\/script>\s*<\?php endif; \?>/gms,
    to: `<script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/exercises.js') : 'assets/js/exercises.js'; ?>"></script>`
  },
  {
    re: /<\?php if \(function_exists\('asset_url'\)\): \?>\s*<script src="<\?php echo asset_url\('assets\/js\/coach-webm\.js'\); \?>"><\/script>\s*<\?php else: \?>\s*<script src="\/assets\/js\/coach-webm\.js"><\/script>\s*<\?php endif; \?>/gms,
    to: `<script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/coach-webm.js') : 'assets/js/coach-webm.js'; ?>"></script>`
  }
];

const changed = [];
for (const f of files) {
  let c = fs.readFileSync(f, 'utf8');
  const orig = c;
  for (const r of rules) c = c.replace(r.re, r.to);
  if (c !== orig) {
    fs.writeFileSync(f, c, 'utf8');
    changed.push(f);
  }
}
console.log('CHANGED_FILES=' + changed.length);
for (const f of changed) console.log(f);
