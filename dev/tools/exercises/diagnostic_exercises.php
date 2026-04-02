<?php
/**
 * diagnostic_exercises.php — MonCoachScolaire
 * Diagnostic complet de la table exercises :
 *   1. Vue d'ensemble
 *   2. Activité 24h
 *   3. Échantillon des 5 derniers modifiés
 * Usage : php diagnostic_exercises.php
 */

require_once __DIR__ . '/../../../db/connection.php';
if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "❌ Connexion à la base de données impossible.\n");
    exit(1);
}

// 1️⃣ VUE D'ENSEMBLE COMPLÈTE
$sql1 = "SELECT
    COUNT(*) AS Total,
    SUM(CASE WHEN Answer IS NOT NULL AND Answer != '' AND Answer != 'null' THEN 1 ELSE 0 END) AS Avec_reponse,
    SUM(CASE WHEN Answer IS NULL OR Answer = '' OR Answer = 'null' THEN 1 ELSE 0 END) AS Sans_reponse,
    ROUND(SUM(CASE WHEN Answer IS NOT NULL AND Answer != '' AND Answer != 'null' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) AS Pourcentage_complet,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS Actifs,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS Inactifs,
    SUM(CASE WHEN structure_type = 'multi-parties' THEN 1 ELSE 0 END) AS Multi_parties,
    SUM(CASE WHEN structure_type = 'simple' THEN 1 ELSE 0 END) AS Simples
FROM exercises";

// 2️⃣ ACTIVITÉ DERNIÈRES 24H
$sql2 = "SELECT
    COUNT(*) AS Total_modifie_24h,
    MIN(updated_at) AS Premiere_modif,
    MAX(updated_at) AS Derniere_modif
FROM exercises
WHERE updated_at >= NOW() - INTERVAL 24 HOUR";

// 3️⃣ ÉCHANTILLON : 5 derniers modifiés
$sql3 = "SELECT
    Id,
    Identifier,
    Subject,
    Level,
    structure_type,
    LEFT(Answer, 50) AS Answer_preview,
    is_active,
    created_at,
    updated_at
FROM exercises
ORDER BY updated_at DESC
LIMIT 5";

// Exécution et affichage
function printSection($title) {
    echo "\n\033[1m$title\033[0m\n";
    echo str_repeat('-', 60) . "\n";
}

// 1️⃣
printSection('1️⃣ VUE D\'ENSEMBLE COMPLÈTE');
$res1 = $pdo->query($sql1)->fetch(PDO::FETCH_ASSOC);
foreach ($res1 as $k => $v) {
    printf("%-20s : %s\n", $k, $v);
}

// 2️⃣
printSection('2️⃣ ACTIVITÉ DERNIÈRES 24H');
$res2 = $pdo->query($sql2)->fetch(PDO::FETCH_ASSOC);
foreach ($res2 as $k => $v) {
    printf("%-20s : %s\n", $k, $v);
}

// 3️⃣
printSection('3️⃣ ÉCHANTILLON : 5 derniers modifiés');
$res3 = $pdo->query($sql3)->fetchAll(PDO::FETCH_ASSOC);
if (count($res3)) {
    // En-tête
    $headers = array_keys($res3[0]);
    echo implode(" | ", $headers) . "\n";
    echo str_repeat('-', 120) . "\n";
    foreach ($res3 as $row) {
        echo implode(" | ", array_map(function($v) { return (string)$v; }, $row)) . "\n";
    }
} else {
    echo "Aucun exercice modifié récemment.\n";
}
