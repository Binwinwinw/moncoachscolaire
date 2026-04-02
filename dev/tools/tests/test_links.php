<?php
// tests/test_links.php
// Usage: php tests/test_links.php

// Resolve repository root (dev/tests -> project root)
$root = realpath(__DIR__ . '/../../');
require_once $root . '/config.php'; // sets $baseUrl

// Read landing page PHP source directly and look for router links in it.
$cmd = 'php ' . escapeshellarg($root . '/tools/render_page_cli.php') . ' landingpage';
$landingSrc = shell_exec($cmd);


$expected = [
    'college' => rtrim($baseUrl, '/') . '/index.php?page=college/college',
    'lycee' => rtrim($baseUrl, '/') . '/index.php?page=lycee/lycee',
    'bac' => rtrim($baseUrl, '/') . '/index.php?page=bac/bac',
];

function checkHrefInHtml($html, $href) {
    return strpos($html, htmlspecialchars($href)) !== false || strpos($html, $href) !== false;
}

$errors = [];
foreach ($expected as $k => $url) {
    if (!checkHrefInHtml($landingSrc, $url)) {
        $errors[] = "Landing: lien attendu manquant pour $k -> $url";
    }
}

if (count($errors) === 0) {
    echo "✅ Tous les liens attendus sur l'accueil sont corrects.\n";
    exit(0);
}

foreach ($errors as $err) {
    echo "❌ $err\n";
}
exit(1);
