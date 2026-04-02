<?php
// Simple smoke test: verify that given page slugs resolve to an existing candidate file
$root = dirname(__DIR__);
$pages = [
    'lycee/seconde/guide-remediation',
    'lycee/seconde/exercices-seconde',
    'lycee/premiere/guide-remediation',
    'lycee/premiere/exercices-premiere',
    'lycee/terminale/guide-remediation',
    'lycee/2nde/exercices-2nde', // legacy
    'lycee/1ere/exercices-1ere', // legacy
    'lycee/terminale/exercices-terminale',
    'college/exercices-college',
    'bac/exercices-bac'
];

function candidatesFor($root, $pageRaw) {
    $c = [];
    $c[] = $root . '/src/pages/' . $pageRaw . '.php';
    $c[] = $root . '/src/pages/' . $pageRaw . '/index.php';
    $c[] = $root . '/src/pages/' . $pageRaw . '.html';
    $c[] = $root . '/' . $pageRaw . '.php';
    $c[] = $root . '/' . $pageRaw . '/index.php';
    $c[] = $root . '/' . $pageRaw;
    $c[] = $root . '/' . $pageRaw . '.html';
    $c[] = $root . '/pages/' . $pageRaw . '.php';
    $c[] = $root . '/pages/' . $pageRaw . '/index.php';
    $c[] = $root . '/pages/' . $pageRaw . '.html';
    return $c;
}

foreach ($pages as $p) {
    $page = $p;
    // Apply normalization/mappings similar to public/index.php
    // Normalize accents/cases (basic replacements used in router)
    $replacements = [
        'Première' => 'premiere',
        'première' => 'premiere',
        'Seconde' => 'seconde',
        'Terminale' => 'terminale'
    ];
    foreach ($replacements as $from => $to) {
        if (stripos($page, $from) !== false) {
            $page = str_ireplace($from, $to, $page);
        }
    }
    // Map readable aliases to actual folder names
    if (preg_match('#^lycee/#', $page)) {
        // Remplacer d'abord les chemins d'exercices complets
        $page = preg_replace('#^lycee/seconde/exercices-seconde#', 'lycee/2nde/exercices-2nde', $page);
        $page = preg_replace('#^lycee/premiere/exercices-premiere#', 'lycee/1ere/exercices-1ere', $page);

        $page = preg_replace('#^lycee/seconde/#', 'lycee/2nde/', $page);
        $page = preg_replace('#^lycee/premiere/#', 'lycee/1ere/', $page);
        if (strpos($page, 'exercices-seconde') !== false) {
            $page = str_replace('exercices-seconde', 'exercices-2nde', $page);
        }
        if (strpos($page, 'exercices-premiere') !== false) {
            $page = str_replace('exercices-premiere', 'exercices-1ere', $page);
        }
        if ($page === 'lycee/seconde') $page = 'lycee/2nde';
        if ($page === 'lycee/premiere') $page = 'lycee/1ere';
    }

    $found = false;
    $cands = candidatesFor($root, $page);
    $existing = [];
    foreach ($cands as $cand) {
        if (file_exists($cand)) {
            $existing[] = $cand;
            $found = true;
        }
    }
    echo $p . ' -> ' . $page . ': ' . ($found ? "OK (" . count($existing) . " candidates)" : "MISSING") . PHP_EOL;
    if ($found) {
        foreach ($existing as $e) {
            echo "  - " . str_replace($root . '/', '', $e) . PHP_EOL;
        }
    }
}
