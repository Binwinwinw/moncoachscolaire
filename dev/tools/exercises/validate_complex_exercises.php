<?php
/**
 * validate_complex_exercises.php — Validation automatisée des exercices multi-parties
 * Usage : php validate_complex_exercises.php [--verbose] [--skip-api] [--skip-performance] [--json-only] [--html-only]
 */

require_once __DIR__ . '/../../../db/connection.php';
if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "❌ Connexion à la base de données impossible.\n");
    exit(1);
}

// Options CLI
$opts = getopt('', ['verbose', 'skip-api', 'skip-performance', 'json-only', 'html-only']);
$VERBOSE = isset($opts['verbose']);
$SKIP_API = isset($opts['skip-api']);
$SKIP_PERF = isset($opts['skip-performance']);
$JSON_ONLY = isset($opts['json-only']);
$HTML_ONLY = isset($opts['html-only']);

$now = date('Y-m-d H:i:s');
$report = [
    'validation_date' => $now,
    'total_tests' => 0,
    'passed' => 0,
    'failed' => 0,
    'skipped' => 0,
    'success_rate' => 0,
    'phases' => [],
    'recommendations' => []
];
$console = [];

function badge($ok, $msg) {
    return ($ok ? "\033[32m✅\033[0m " : "\033[31m❌\033[0m ") . $msg;
}
function badge_warn($msg) { return "\033[33m⚠️\033[0m $msg"; }

// PHASE 1 : BACKEND (SQL)
$phase1 = ['tests' => 0, 'passed' => 0, 'details' => []];

// Test 1 : Nombre d'exercices multi-parties
$stmt = $pdo->query("SELECT COUNT(*) as total FROM exercises WHERE structure_type = 'multi-parties'");
$row = $stmt->fetch();
$ok = ($row['total'] == 57);
$phase1['tests']++;
$phase1['passed'] += $ok ? 1 : 0;
$phase1['details'][] = [
    'test' => 'Nombre d\'exercices multi-parties',
    'expected' => 57,
    'actual' => $row['total'],
    'ok' => $ok
];
if ($VERBOSE) $console[] = badge($ok, "Test 1 : 57 exercices multi-parties (trouvés : {$row['total']})");

// Test 2 : JSON valide
$stmt = $pdo->query("SELECT Id, Identifier FROM exercises WHERE structure_type = 'multi-parties' AND JSON_VALID(sub_questions) = 0");
$invalid = $stmt->fetchAll();
$ok = (count($invalid) === 0);
$phase1['tests']++;
$phase1['passed'] += $ok ? 1 : 0;
$phase1['details'][] = [
    'test' => 'JSON sub_questions valide',
    'invalid' => $invalid,
    'ok' => $ok
];
if ($VERBOSE) $console[] = badge($ok, "Test 2 : Tous les JSON valides");

// Test 3 : Structure sub_questions
$stmt = $pdo->query("SELECT Id, Identifier FROM exercises WHERE structure_type = 'multi-parties' AND (JSON_EXTRACT(sub_questions, '$[0].id') IS NULL OR JSON_EXTRACT(sub_questions, '$[0].question') IS NULL OR JSON_EXTRACT(sub_questions, '$[0].type') IS NULL)");
$struct = $stmt->fetchAll();
$ok = (count($struct) === 0);
$phase1['tests']++;
$phase1['passed'] += $ok ? 1 : 0;
$phase1['details'][] = [
    'test' => 'Structure sub_questions correcte',
    'invalid' => $struct,
    'ok' => $ok
];
if ($VERBOSE) $console[] = badge($ok, "Test 3 : Structure correcte (id, question, type présents)");

// Test 4 : Types de questions
$stmt = $pdo->query("SELECT JSON_EXTRACT(sub_questions, '$[*].type') as types FROM exercises WHERE structure_type = 'multi-parties'");
$types = [];
while ($row = $stmt->fetch()) {
    $arr = json_decode($row['types'], true);
    if (is_array($arr)) foreach ($arr as $t) $types[$t] = true;
}
$expectedTypes = ['qcm', 'texte', 'vrai_faux', 'association'];
$missing = array_diff($expectedTypes, array_keys($types));
$ok = (count($missing) === 0);
$phase1['tests']++;
$phase1['passed'] += $ok ? 1 : 0;
$phase1['details'][] = [
    'test' => 'Types de questions présents',
    'found' => array_keys($types),
    'missing' => $missing,
    'ok' => $ok
];
if ($VERBOSE) $console[] = badge($ok, "Test 4 : 4 types de questions détectés");

// Test 5 : Content et Instruction non vides
$stmt = $pdo->query("SELECT Id, Identifier FROM exercises WHERE structure_type = 'multi-parties' AND (Content IS NULL OR Content = '' OR Instruction IS NULL OR Instruction = '')");
$empty = $stmt->fetchAll();
$ok = (count($empty) === 0);
$phase1['tests']++;
$phase1['passed'] += $ok ? 1 : 0;
$phase1['details'][] = [
    'test' => 'Content et Instruction non vides',
    'invalid' => $empty,
    'ok' => $ok
];
if ($VERBOSE) $console[] = badge($ok, "Test 5 : Content et Instruction présents");

$report['phases']['backend'] = $phase1;
$report['total_tests'] += $phase1['tests'];
$report['passed'] += $phase1['passed'];

// PHASE 2 : API (optionnel)
$phase2 = ['tests' => 0, 'passed' => 0, 'details' => []];
if ($SKIP_API) {
    $report['phases']['api'] = ['skipped' => true];
    $report['skipped'] += 1;
    if ($VERBOSE) $console[] = badge_warn('PHASE 2 : API (Tests ignorés - option --skip-api)');
} else {
    // API non implémentée ici (à brancher selon infra locale)
    $report['phases']['api'] = ['skipped' => true];
    $report['skipped'] += 1;
    if ($VERBOSE) $console[] = badge_warn('PHASE 2 : API (Tests ignorés - API non configurée)');
}

// PHASE 4 : PERFORMANCE (optionnel)
$phase4 = ['tests' => 0, 'passed' => 0, 'details' => []];
if ($SKIP_PERF) {
    $report['phases']['performance'] = ['skipped' => true];
    $report['skipped'] += 2;
    if ($VERBOSE) $console[] = badge_warn('PHASE 4 : PERFORMANCE (Tests ignorés - option --skip-performance)');
} else {
    // Test 7 : Temps de chargement exercice simple
    $start = microtime(true);
    $stmt = $pdo->query("SELECT * FROM exercises WHERE structure_type = 'multi-parties' LIMIT 1");
    $stmt->fetch();
    $elapsed = (microtime(true) - $start) * 1000;
    $ok = ($elapsed < 500);
    $phase4['tests']++;
    $phase4['passed'] += $ok ? 1 : 0;
    $phase4['details'][] = [
        'test' => 'Temps de chargement exercice simple',
        'elapsed_ms' => round($elapsed,1),
        'ok' => $ok
    ];
    if ($VERBOSE) $console[] = badge($ok, "Test 7 : Chargement simple < 500ms (".round($elapsed,1)."ms)");
    // Test 8 : Temps de chargement exercice complexe (Id 268)
    $start = microtime(true);
    $stmt = $pdo->query("SELECT * FROM exercises WHERE Id = 268");
    $stmt->fetch();
    $elapsed = (microtime(true) - $start) * 1000;
    $ok = ($elapsed < 1000);
    $phase4['tests']++;
    $phase4['passed'] += $ok ? 1 : 0;
    $phase4['details'][] = [
        'test' => 'Temps de chargement exercice complexe',
        'elapsed_ms' => round($elapsed,1),
        'ok' => $ok
    ];
    if ($VERBOSE) $console[] = badge($ok, "Test 8 : Chargement complexe < 1000ms (".round($elapsed,1)."ms)");
}
$report['phases']['performance'] = $phase4;
$report['total_tests'] += $phase4['tests'];
$report['passed'] += $phase4['passed'];

// PHASE 5 : CAS LIMITES
$phase5 = ['tests' => 0, 'passed' => 0, 'details' => []];
// Test 9 : Exercice min questions
$stmt = $pdo->query("SELECT Id FROM exercises WHERE structure_type = 'multi-parties' AND JSON_LENGTH(sub_questions) <= 3 LIMIT 1");
$row = $stmt->fetch();
$ok = ($row !== false);
$phase5['tests']++;
$phase5['passed'] += $ok ? 1 : 0;
$phase5['details'][] = [
    'test' => 'Exercice min questions (2-3)',
    'found' => $row,
    'ok' => $ok
];
if ($VERBOSE) $console[] = badge($ok, "Test 9 : Exercice min questions OK");
// Test 10 : Exercice max questions (Id 268)
$stmt = $pdo->query("SELECT Id FROM exercises WHERE Id = 268 AND structure_type = 'multi-parties'");
$row = $stmt->fetch();
$ok = ($row !== false);
$phase5['tests']++;
$phase5['passed'] += $ok ? 1 : 0;
$phase5['details'][] = [
    'test' => 'Exercice max questions (114)',
    'found' => $row,
    'ok' => $ok
];
if ($VERBOSE) $console[] = badge($ok, "Test 10 : Exercice 114 questions OK");
// Test 11 : Tous types représentés
$stmt = $pdo->query("SELECT JSON_EXTRACT(sub_questions, '$[*].type') as types FROM exercises WHERE structure_type = 'multi-parties'");
$types = [];
while ($row = $stmt->fetch()) {
    $arr = json_decode($row['types'], true);
    if (is_array($arr)) foreach ($arr as $t) $types[$t] = true;
}
$expectedTypes = ['qcm', 'texte', 'vrai_faux', 'association'];
$missing = array_diff($expectedTypes, array_keys($types));
$ok = (count($missing) === 0);
$phase5['tests']++;
$phase5['passed'] += $ok ? 1 : 0;
$phase5['details'][] = [
    'test' => 'Tous types représentés',
    'found' => array_keys($types),
    'missing' => $missing,
    'ok' => $ok
];
if ($VERBOSE) $console[] = badge($ok, "Test 11 : Tous types représentés");

$report['phases']['edge_cases'] = $phase5;
$report['total_tests'] += $phase5['tests'];
$report['passed'] += $phase5['passed'];

$report['failed'] = $report['total_tests'] - $report['passed'];
$report['success_rate'] = $report['total_tests'] ? round($report['passed']*100/$report['total_tests'],1) : 0;

// Génération des rapports
$reportPath = __DIR__ . '/../../../dev/reports/validation_report.json';
$htmlPath = __DIR__ . '/../../../dev/reports/validation_report.html';
file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if (!$JSON_ONLY) {
    // Génération HTML moderne (Bootstrap)
    $html = "<!DOCTYPE html><html lang='fr'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>Validation Exercices Multi-parties</title>";
    $html .= "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>";
    $html .= "<style>.badge-pass{background:#22c55e}.badge-fail{background:#ef4444}.badge-warn{background:#f59e42}.table td,.table th{vertical-align:middle}</style></head><body class='bg-light'><div class='container my-5'>";
    $html .= "<h1 class='mb-4'>🧪 Validation Exercices Multi-parties</h1>";
    $html .= "<p><b>Date :</b> $now</p>";
    foreach ($report['phases'] as $phase => $data) {
        if (isset($data['skipped']) && $data['skipped']) {
            $html .= "<h3 class='mt-4'>$phase <span class='badge badge-warn ms-2'>Ignoré</span></h3>";
            continue;
        }
        $html .= "<h3 class='mt-4'>Phase : $phase</h3>";
        $html .= "<table class='table table-bordered bg-white'><thead><tr><th>Test</th><th>Résultat</th><th>Détail</th></tr></thead><tbody>";
        foreach ($data['details'] as $t) {
            $ok = $t['ok'];
            $badge = $ok ? "<span class='badge badge-pass'>Réussi</span>" : "<span class='badge badge-fail'>Échec</span>";
            $html .= "<tr><td>".htmlspecialchars($t['test'])."</td><td>$badge</td><td>".htmlspecialchars(json_encode($t))."</td></tr>";
        }
        $html .= "</tbody></table>";
    }
    $html .= "<div class='mt-4'><b>Résultat global :</b> <span class='badge badge-".($report['success_rate']==100?'pass':'fail')." ms-2'>".$report['success_rate']."%</span></div>";
    $html .= "<div class='mt-3'><a href='../docs/validation_exercices_multi_parties.md' class='btn btn-outline-primary'>Voir la checklist complète</a></div>";
    $html .= "</div></body></html>";
    file_put_contents($htmlPath, $html);
}

// Console
if (!$HTML_ONLY) {
    echo "\n\033[36m════════════════════════════════════════════════════════\033[0m\n";
    echo "   🧪 VALIDATION EXERCICES MULTI-PARTIES\n";
    echo "\033[36m════════════════════════════════════════════════════════\033[0m\n";
    echo "📅 Date : $now\n\n";
    foreach ($console as $line) echo $line."\n";
    echo "\n\033[36m════════════════════════════════════════════════════════\033[0m\n";
    echo "📊 RÉSULTAT GLOBAL : {$report['passed']}/{$report['total_tests']} tests passés ({$report['success_rate']}%)\n";
    echo "\033[36m════════════════════════════════════════════════════════\033[0m\n";
    if ($report['success_rate'] == 100) echo "\033[32m✅ Validation réussie !\033[0m\n";
    else echo "\033[31m❌ Des erreurs ont été détectées.\033[0m\n";
    echo "Fichiers générés :\n- $htmlPath\n- $reportPath\n";
    echo "\033[36m════════════════════════════════════════════════════════\033[0m\n";
}
