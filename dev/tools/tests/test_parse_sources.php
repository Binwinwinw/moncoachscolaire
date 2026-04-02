<?php
/**
 * Script de test pour vérifier le parsing du fichier sources
 */

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/import_exercices_to_db.php';

$sourcesFile = __DIR__ . '/../docs/exercices-sources-par-niveau.md';

echo "🔍 Test du parsing du fichier sources\n\n";

if (!file_exists($sourcesFile)) {
    die("❌ Fichier non trouvé : $sourcesFile\n");
}

$content = file_get_contents($sourcesFile);
echo "📄 Taille du fichier : " . strlen($content) . " caractères\n";
echo "📄 Nombre de lignes : " . substr_count($content, "\n") . "\n\n";

// Test de détection des niveaux
preg_match_all('/^##\s*[🎓📚]*\s*(\d+[èmee]+|Seconde|Premi[èe]re|Terminale|BAC)\s*$/im', $content, $levelMatches);
echo "🎓 Niveaux détectés : " . count($levelMatches[1]) . "\n";
foreach ($levelMatches[1] as $level) {
    echo "   - $level\n";
}

echo "\n";

// Test de détection des exercices
preg_match_all('/^####\s*Exercice\s+\d+\s*:\s*(.+?)$/m', $content, $exerciseMatches);
echo "📝 Exercices détectés : " . count($exerciseMatches[1]) . "\n";
echo "   Premiers titres :\n";
foreach (array_slice($exerciseMatches[1], 0, 5) as $title) {
    echo "   - " . trim($title) . "\n";
}

echo "\n";

// Test avec la fonction parseSourcesFile
echo "🧪 Test avec parseSourcesFile() :\n";
$exercises = parseSourcesFile($sourcesFile, null);
echo "   Exercices extraits : " . count($exercises) . "\n";

if (count($exercises) > 0) {
    echo "\n   Premiers exercices :\n";
    foreach (array_slice($exercises, 0, 3) as $ex) {
        echo "   - {$ex['title']} ({$ex['level']}, {$ex['subject']})\n";
    }
    
    // Compter par niveau
    $byLevel = [];
    foreach ($exercises as $ex) {
        $level = $ex['level'] ?? 'Inconnu';
        if (!isset($byLevel[$level])) {
            $byLevel[$level] = 0;
        }
        $byLevel[$level]++;
    }
    
    echo "\n   Répartition par niveau :\n";
    foreach ($byLevel as $level => $count) {
        echo "   - $level : $count exercice(s)\n";
    }
} else {
    echo "   ❌ Aucun exercice extrait !\n";
}

