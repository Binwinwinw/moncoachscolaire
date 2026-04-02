#!/usr/bin/env node
/**
 * build-context-bundle.js
 * Concatène un ensemble de fichiers essentiels dans CONTEXT_BUNDLE.md
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const root = path.resolve(__dirname, '../../../');
const outFile = path.join(root, 'CONTEXT_BUNDLE.md');

const files = [
  '.github/copilot-instructions.md',
  'CONTEXT_INDEX.md',
  'README.md',
  'DOCUMENTATION.md',
  '.github/PROJECT_CONTEXT.md'
];

let content = `# CONTEXT_BUNDLE.md\n\n_Généré le ${new Date().toISOString()}_\n\n`;

files.forEach(rel => {
  const filePath = path.join(root, rel);
  content += `---\n\n## Fichier : ${rel}\n\n`;
  if (fs.existsSync(filePath)) {
    const fileData = fs.readFileSync(filePath, 'utf8');
    content += '```file\n' + fileData + '\n```\n\n';
  } else {
    content += `> **MANQUANT** : ${rel} (fichier introuvable)\n\n`;
  }
});

// Optionnel : ajouter git log récent
try {
  const gitLog = execSync('git log -n 20 --pretty=format:"%h %ad %s (%an)" --date=short', { cwd: root, encoding: 'utf8' });
  content += '---\n\n## Git log (20 derniers commits)\n\n' + '```\\n' + gitLog + '\\n```\\n\\n';
} catch (e) {
  content += '> **Git log non disponible (pas de git ou commande échouée)**\n\n';
}

// Footer with quick usage
content += `---\n\n## Usage rapide\n\n- Regénérer : \`npm run context\`\n- Fichier de sortie : \`CONTEXT_BUNDLE.md\`\n\n`;

fs.writeFileSync(outFile, content, 'utf8');
console.log('CONTEXT_BUNDLE.md généré avec succès.');
