<?php
// dev/tools/courses/analyze_content_status.php

$rootDir = dirname(dirname(dirname(__DIR__)));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

echo "📊 Analyse du contenu manquant des cours\n";
echo "========================================\n";

$total = $pdo->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetchColumn();
$emptyExpl = $pdo->query("SELECT COUNT(*) FROM courses WHERE is_active = 1 AND (explanation IS NULL OR explanation = '')")->fetchColumn();
$emptyKey = $pdo->query("SELECT COUNT(*) FROM courses WHERE is_active = 1 AND (key_point IS NULL OR key_point = '')")->fetchColumn();
$emptyEx = $pdo->query("SELECT COUNT(*) FROM courses WHERE is_active = 1 AND (example IS NULL OR example = '')")->fetchColumn();

// Check for "A completer" or "A definir" placeholders
$placeholderExpl = $pdo->query("SELECT COUNT(*) FROM courses WHERE explanation LIKE '%compléter%' OR explanation LIKE '%définir%'")->fetchColumn();


echo "Total cours actifs : $total\n\n";

echo "❌ Explanation manquante : $emptyExpl\n";
echo "⚠️  Explanation 'A compléter' : $placeholderExpl\n";
echo "❌ Key Point manquant : $emptyKey\n";
echo "❌ Example manquant : $emptyEx\n";

$percentDone = round((($total - $emptyExpl) / $total) * 100);
echo "\n📈 Remplissage global (Explanation) : $percentDone%\n";
