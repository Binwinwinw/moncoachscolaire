const fs = require('fs');
const path = require('path');

const htmlDir = path.join(__dirname, '..', '..', '..', 'public', 'assets', 'html');
const fragmentPath = path.join(htmlDir, 'footer-fragment.html');
const footerCssLink = '<link rel="stylesheet" href="assets/css/components/footer.css">';

const pageClassMap = {
  'rgpd.html': 'rgpd-page',
  'conditions-utilisation.html': 'conditions-page',
  'mentions-legales.html': 'mentions-page',
  'politique-cookies.html': 'cookies-page'
};

if (!fs.existsSync(fragmentPath)) {
  console.error('Footer fragment not found at', fragmentPath);
  process.exit(1);
}
const fragment = fs.readFileSync(fragmentPath, 'utf8');

const files = fs.readdirSync(htmlDir).filter(f => f.endsWith('.html') && f !== 'footer-fragment.html');

files.forEach(file => {
  const filePath = path.join(htmlDir, file);
  let content = fs.readFileSync(filePath, 'utf8');

  // Ensure footer CSS link is present in the head
  if (!/assets\/css\/components\/footer\.css/.test(content)) {
    content = content.replace(/<\/head>/i, `    ${footerCssLink}\n</head>`);
  }

  // Ensure body has the scoped class
  const cls = pageClassMap[file];
  if (cls) {
    content = content.replace(/<body(\s[^>]*)?>/i, (m) => {
      if (/class=/.test(m)) {
        // Add class to existing class attribute
        return m.replace(/class=(['"])([^'"]*)(['"])/i, (all, q1, val, q3) => {
          const classes = val.split(/\s+/).filter(Boolean);
          if (!classes.includes(cls)) classes.push(cls);
          return `class=${q1}${classes.join(' ')}${q3}`;
        });
      }
      return `<body class="${cls}">`;
    });
  }

  // Replace existing footer if present else append fragment before </body>
  if (/<footer[\s\S]*<\/footer>/i.test(content)) {
    content = content.replace(/<footer[\s\S]*<\/footer>/i, fragment);
  } else {
    content = content.replace(/<\/body>/i, `${fragment}\n</body>`);
  }

  fs.writeFileSync(filePath, content, 'utf8');
  console.log('Injected footer into', file);
});

console.log('Done.');
