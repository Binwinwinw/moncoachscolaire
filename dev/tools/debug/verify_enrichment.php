<?php
// dev/tools/debug/verify_enrichment.php

if (php_sapi_name() !== 'cli') {
    die('CLI required');
}

$rootDir = dirname(dirname(dirname(__DIR__)));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

echo "🔍 Vérification de 3 cours enrichis au hasard...\n\n";

$stmt = $pdo->query("SELECT id, subject, competence, example FROM courses WHERE example IS NOT NULL AND example != '' ORDER BY RAND() LIMIT 3");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($courses as $c) {
    echo "---------------------------------------------------\n";
    echo "📚 Cours #{$c['id']} : {$c['subject']} - {$c['competence']}\n";
    echo "---------------------------------------------------\n";
    echo strip_tags(substr($c['example'], 0, 300)) . "...\n";
    echo "[Structure HTML " . (strpos($c['example'], '<div class="example-card') !== false ? 'OK' : 'FAIL') . "]\n\n";
}
