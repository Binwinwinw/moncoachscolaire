<?php
/**
 * analyze_content_length.php — Analyse la longueur et les patterns du Content des exercices
 */
require_once __DIR__ . '/../../../db/connection.php';
if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "❌ Connexion à la base de données impossible.\n");
    exit(1);
}

$stmt = $pdo->prepare("SELECT Id, Identifier, Subject, Level, Content FROM exercises WHERE is_active = 1");
$stmt->execute();
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$lengths = [];
$tranches = [
    '<100' => 0,
    '100-300' => 0,
    '300-500' => 0,
    '500-1000' => 0,
    '>1000' => 0
];
$pattern1 = $pattern2 = $pattern3 = 0;
$longest = null;
$longestLen = 0;
$ex1 = $ex2 = $ex3 = null;

foreach ($exercises as $ex) {
    $len = mb_strlen($ex['Content']);
    $lengths[] = $len;
    // Tranches
    if ($len < 100) $tranches['<100']++;
    elseif ($len < 300) $tranches['100-300']++;
    elseif ($len < 500) $tranches['300-500']++;
    elseif ($len < 1000) $tranches['500-1000']++;
    else $tranches['>1000']++;
    // Plus long
    if ($len > $longestLen) {
        $longestLen = $len;
        $longest = $ex;
    }
    // Patterns
    if (!$ex2 && (strpos($ex['Content'], '1)') !== false && strpos($ex['Content'], '2)') !== false)) $ex2 = $ex;
    if (!$ex3 && (stripos($ex['Content'], 'Texte :') !== false || stripos($ex['Content'], 'Document') !== false)) $ex3 = $ex;
    if (preg_match('/(1\)|1\.|a\))/u', $ex['Content'])) $pattern1++;
    if (preg_match('/(Texte :|Lis|Document)/iu', $ex['Content'])) $pattern2++;
    if (preg_match_all('/A\).*?B\).*?C\).*?D\)/su', $ex['Content'], $m) && count($m[0]) >= 1) $pattern3++;
}
// Statistiques longueur
sort($lengths);
$total = count($lengths);
$min = $lengths[0] ?? 0;
$max = $lengths[$total-1] ?? 0;
$mean = $total ? round(array_sum($lengths)/$total,1) : 0;
$median = $total ? ($total%2==0 ? ($lengths[$total/2-1]+$lengths[$total/2])/2 : $lengths[floor($total/2)]) : 0;
$top10 = array_slice(array_reverse($lengths),0,10);

// Affichage console
printf("\n📊 Analyse longueur Content (exercices actifs)\n");
printf("Total : %d\n", $total);
printf("Min : %d | Max : %d | Moyenne : %.1f | Médiane : %.1f\n", $min, $max, $mean, $median);
printf("Tranches : <100=%d, 100-300=%d, 300-500=%d, 500-1000=%d, >1000=%d\n", $tranches['<100'], $tranches['100-300'], $tranches['300-500'], $tranches['500-1000'], $tranches['>1000']);
printf("Top 10 longueurs : %s\n", implode(', ', $top10));
printf("\n🔎 Patterns :\n");
printf("- Avec '1)', '1.' ou 'a)' : %d\n", $pattern1);
printf("- Avec 'Texte :', 'Lis', 'Document' : %d\n", $pattern2);
printf("- Avec blocs 'A) B) C) D)' : %d\n", $pattern3);

// Exemples
$txt = "ANALYSE CONTENT — ".date('Y-m-d H:i:s')."\n\n";
$txt .= "Exercice le plus long (Id: {$longest['Id']}, {$longest['Identifier']})\n";
$txt .= str_repeat('-',40)."\n".($longest['Content'] ?? '')."\n\n";
if ($ex2) {
    $txt .= "Exercice avec '1)' et '2)' (Id: {$ex2['Id']}, {$ex2['Identifier']})\n";
    $txt .= str_repeat('-',40)."\n".($ex2['Content'] ?? '')."\n\n";
}
if ($ex3) {
    $txt .= "Exercice avec 'Texte :' ou 'Document' (Id: {$ex3['Id']}, {$ex3['Identifier']})\n";
    $txt .= str_repeat('-',40)."\n".($ex3['Content'] ?? '')."\n\n";
}
file_put_contents(__DIR__ . '/../../../dev/reports/content_analysis.txt', $txt);
printf("\n📄 Exemples complets écrits dans dev/reports/content_analysis.txt\n");
