<?php
/**
 * Script pour tester si le routeur trouve correctement les pages générées par la navigation
 */

$root = dirname(__DIR__);

// Pages à tester (générées par render_level_navigation)
$testPages = [
    'college/6eme/exercices-6eme',
    'college/5eme/exercices-5eme',
    'college/4eme/exercices-4eme',
    'college/3eme/exercices-3eme',
    'college/6eme/guide-remediation',
    'college/5eme/guide-remediation',
    'college/4eme/guide-remediation',
    'college/3eme/guide-remediation',
    'lycee/seconde/exercices-seconde',
    'lycee/premiere/exercices-premiere',
    'lycee/terminale/exercices-terminale',
    'lycee/seconde/guide-remediation',
    'lycee/premiere/guide-remediation',
    'lycee/terminale/guide-remediation',
    'bac/exercices-bac',
];

echo "🧪 Test des chemins du routeur\n\n";

// Simuler la logique du routeur
$candidates = [];
foreach ($testPages as $pageRaw) {
    $candidates = [
        $root . '/' . $pageRaw,
        $root . '/' . $pageRaw . '/index.php',
        $root . '/' . $pageRaw . '.php',
        $root . '/' . $pageRaw . '.html',
        $root . '/pages/' . $pageRaw . '/index.php',
        $root . '/pages/' . $pageRaw . '.php',
        $root . '/pages/' . $pageRaw . '.html',
    ];
    
    $found = null;
    foreach ($candidates as $cand) {
        if (is_file($cand)) {
            $found = $cand;
            break;
        }
    }
    
    if ($found) {
        echo "✅ {$pageRaw} -> " . str_replace($root . '/', '', $found) . "\n";
    } else {
        echo "❌ {$pageRaw} -> AUCUN FICHIER TROUVÉ\n";
        echo "   Candidats testés :\n";
        foreach ($candidates as $cand) {
            echo "     - " . str_replace($root . '/', '', $cand) . "\n";
        }
    }
}

echo "\n✅ Test terminé !\n";

