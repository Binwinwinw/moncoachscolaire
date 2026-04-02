<?php
/**
 * Audit complet de génération interactive pour tous les exercices
 * - Parcourt tous les exercices actifs
 * - Détecte le type (QCM / Maths / Conjugaison / Classes de mots)
 * - Valide les données minimales attendues par le front (interactive-exercises.js)
 * - Sortie: résumé + liste des exercices à problème avec raison
 */

// Connexion DB
$root = dirname(__DIR__);
$loaded = false;
$paths = [
    $root . '/src/database/connection.php',
    $root . '/db/connection.php',
];
foreach ($paths as $p) {
    if (is_file($p)) { require_once $p; $loaded = true; break; }
}
if (!$loaded || empty($pdo)) {
    fwrite(STDERR, "Connexion DB introuvable\n");
    exit(2);
}

// Chargeurs et générateurs
require_once $root . '/src/includes/exercice_loader.php';
require_once $root . '/src/includes/exercice_card.php';

function summarize_issue($id, $level, $subject, $title, $reason) {
    return [
        'Id' => (int)$id,
        'Level' => (string)$level,
        'Subject' => (string)$subject,
        'Title' => (string)$title,
        'Issue' => (string)$reason,
    ];
}

$all = getAllExercises();
$total = count($all);
$stats = [
    'total' => $total,
    'types' => ['qcm'=>0,'math'=>0,'conjugation'=>0,'word-coloring'=>0,'other'=>0],
    'ok' => 0,
    'issues' => [],
];

foreach ($all as $ex) {
    $id = $ex['Id'];
    $level = $ex['Level'] ?? '';
    $subject = $ex['Subject'] ?? '';
    $title = $ex['Title'] ?? '';
    $content = $ex['Content'] ?? '';

    // Détecter type
    $type = detectExerciseType($subject, strip_tags($content), $title);
    if (!isset($stats['types'][$type])) { $stats['types'][$type] = 0; }
    $stats['types'][$type]++;

    // Extraire questions/données
    $ok = true;
    switch ($type) {
        case 'qcm':
            $questions = extractQuestionsFromContent($ex, 'qcm');
            if (empty($questions)) {
                $ok = false;
                $stats['issues'][] = summarize_issue($id,$level,$subject,$title,'QCM sans questions détectées');
                break;
            }
            foreach ($questions as $qIdx => $q) {
                $choices = $q['choices'] ?? [];
                $correct = $q['correct'] ?? '';
                if (count($choices) < 2) {
                    $ok = false;
                    $stats['issues'][] = summarize_issue($id,$level,$subject,$title,"QCM question #".($qIdx+1).": moins de 2 choix");
                    break;
                }
                if ($correct === '') {
                    $ok = false;
                    $stats['issues'][] = summarize_issue($id,$level,$subject,$title,"QCM question #".($qIdx+1).": réponse correcte absente");
                    break;
                }
            }
            break;
        case 'math':
            $questions = extractQuestionsFromContent($ex, 'math');
            if (empty($questions)) {
                $ok = false;
                $stats['issues'][] = summarize_issue($id,$level,$subject,$title,'Math: aucune question détectée');
            }
            break;
        case 'conjugation':
            $questions = extractQuestionsFromContent($ex, 'conjugation');
            if (empty($questions)) {
                $ok = false;
                $stats['issues'][] = summarize_issue($id,$level,$subject,$title,'Conjugaison: aucune question/phrase détectée');
            }
            break;
        case 'word-coloring':
            // Utiliser le parseur pour la phrase + mapping
            list($sentence,$mapping) = parseWordColoringData($content, $ex['Answer'] ?? '');
            if (empty($sentence) || empty($mapping)) {
                $ok = false;
                $stats['issues'][] = summarize_issue($id,$level,$subject,$title,'Classes de mots: phrase ou mapping absent');
            }
            break;
        default:
            $stats['types']['other']++;
            // fallback: vérifier que du contenu existe
            if (trim(strip_tags($content)) === '') {
                $ok = false;
                $stats['issues'][] = summarize_issue($id,$level,$subject,$title,'Autre: contenu vide');
            }
    }

    if ($ok) { $stats['ok']++; }
}

// Sortie lisible
echo "=== AUDIT GENERATION INTERACTIVE ===\n";
echo "Exercices scannés : {$stats['total']}\n";
foreach ($stats['types'] as $k=>$v) { echo " - $k : $v\n"; }
$nbIssues = count($stats['issues']);
$rate = $stats['total']>0 ? round(($stats['ok']/$stats['total'])*100,1) : 0;

echo "OK : {$stats['ok']} ({$rate}%)\n";

echo "\nProblemes detectés : {$nbIssues}\n";
if ($nbIssues>0) {
    $max = 100; $i = 0;
    foreach ($stats['issues'] as $iss) {
        printf("[%s][%s] #%d %s\n  - %s\n",
            $iss['Level'], $iss['Subject'], $iss['Id'], $iss['Title'], $iss['Issue']
        );
        $i++; if ($i >= $max) { echo "... (" . ($nbIssues-$max) . " autres)\n"; break; }
    }
}

// Code de sortie non-zero si des problèmes sont détectés
// Enregistrer un rapport JSON complet
$reportDir = $root . '/tools/reports';
if (!is_dir($reportDir)) { @mkdir($reportDir, 0777, true); }
$timestamp = date('Ymd_His');
@file_put_contents($reportDir . "/audit_interactive_generation_{$timestamp}.json", json_encode($stats, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
@file_put_contents($reportDir . "/audit_interactive_generation_latest.json", json_encode($stats, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

echo "\nRapport enregistré: tools/reports/audit_interactive_generation_latest.json\n";

exit($nbIssues>0 ? 1 : 0);
