const http = require('http');
const url = 'http://127.0.0.1:8080/public/index.php?page=lycee%2Flycee-accueil';
http.get(url, (res) => {
  let data = '';
  res.on('data', chunk => data += chunk);
  res.on('end', () => {
    const lines = data.split('\n');
    lines.forEach((line, i) => {
      if (line.includes('Espace <strong>Administrateur')) {
        console.log(`${i+1}: ${line.trim()}`);
      }
    });
    console.log('\n-- summary --');
    const matches = lines.filter(l => l.includes('Espace <strong>Administrateur'));
    console.log('count:', matches.length);
  });
}).on('error', err => console.error(err));
