<?php
/**
 * AUDIT DES EXERCICES ENRICHIS
 *
 * Analyse la qualité des réponses extraites et détecte les problèmes :
 * - Answer toujours NULL
 * - Answer trop court (< 3 caractères)
 * - Answer trop long (> 500 caractères)
 * - Answer suspect (contient "##", "Correction", etc.)
 * - Fichiers corrigés (-COR, -CORRIGE)
 *
 * Usage :
 *   php audit_enriched_exercises.php
 */

// Configuration
define('PROJECT_ROOT', dirname(__DIR__, 3));
define('INPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_enriched.json');
define('REPORT_DIR', PROJECT_ROOT . '/dev/reports');

if (!is_dir(REPORT_DIR)) {
    mkdir(REPORT_DIR, 0755, true);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  AUDIT DES EXERCICES ENRICHIS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. CHARGEMENT
echo "📄 Chargement du fichier...\n";
$json = file_get_contents(INPUT_FILE);
$exercises = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ ERREUR JSON : " . json_last_error_msg() . "\n");
}

$total = count($exercises);
echo "✅ $total exercices chargés\n\n";

// 2. CATÉGORISATION
echo "🔍 Analyse de la qualité...\n";

$categories = [
    'valid' => [],           // Answer OK
    'still_null' => [],      // Answer = null
    'too_short' => [],       // Answer < 3 caractères
    'too_long' => [],        // Answer > 500 caractères
    'suspect' => [],         // Contient "##", "Correction", etc.
    'corriges' => [],        // Fichiers corrigés (-COR)
    'empty_string' => [],    // Answer = ""
];

$stats = [
    'total' => $total,
    'valid' => 0,
    'problematic' => 0,
];

foreach ($exercises as $ex) {
    $identifier = $ex['Identifier'];
    $answer = $ex['Answer'];
    $tips = $ex['Tips'];
    $content = $ex['Content'];

    // Fichiers corrigés
    if (stripos($identifier, '-COR') !== false ||
        stripos($identifier, 'CORRIGE') !== false ||
        stripos($content ?? '', '## 🔑 Correction') !== false) {
        $categories['corriges'][] = [
            'identifier' => $identifier,
            'title' => $ex['Title'] ?? '',
        ];
        continue;
    }

    // Answer NULL
    if (is_null($answer)) {
        $categories['still_null'][] = [
            'identifier' => $identifier,
            'title' => $ex['Title'] ?? '',
            'has_tips' => !empty($tips),
            'tips_preview' => substr($tips ?? '', 0, 80),
        ];
        $stats['problematic']++;
        continue;
    }

    // Answer chaîne vide
    if (trim($answer) === '') {
        $categories['empty_string'][] = [
            'identifier' => $identifier,
            'title' => $ex['Title'] ?? '',
        ];
        $stats['problematic']++;
        continue;
    }

    $answerLen = strlen($answer);

    // Answer trop court
    if ($answerLen < 3) {
        $categories['too_short'][] = [
            'identifier' => $identifier,
            'title' => $ex['Title'] ?? '',
            'answer' => $answer,
            'length' => $answerLen,
        ];
        $stats['problematic']++;
        continue;
    }

    // Answer trop long (probablement tout le Content)
    if ($answerLen > 500) {
        $categories['too_long'][] = [
            'identifier' => $identifier,
            'title' => $ex['Title'] ?? '',
            'answer_preview' => substr($answer, 0, 100),
            'length' => $answerLen,
        ];
        $stats['problematic']++;
        continue;
    }

    // Answer suspect (contient du markdown, "Correction", etc.)
    if (preg_match('/##|Correction|Réponse :|### |Justification|Méthode de/i', $answer)) {
        $categories['suspect'][] = [
            'identifier' => $identifier,
            'title' => $ex['Title'] ?? '',
            'answer_preview' => substr($answer, 0, 100),
        ];
        $stats['problematic']++;
        continue;
    }

    // Answer valide
    $categories['valid'][] = [
        'identifier' => $identifier,
        'answer_preview' => substr($answer, 0, 60),
    ];
    $stats['valid']++;
}

echo "✅ Analyse terminée\n\n";

// 3. RAPPORT
echo "📊 Génération du rapport...\n";

$txt = "═══════════════════════════════════════════════════════════════\n";
$txt .= "  AUDIT DES EXERCICES ENRICHIS\n";
$txt .= "═══════════════════════════════════════════════════════════════\n\n";
$txt .= "Date : " . date('Y-m-d H:i:s') . "\n\n";

$txt .= "RÉSUMÉ\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("Total exercices analysés:     %4d\n\n", $stats['total']);

$txt .= sprintf("✅ VALIDES:                   %4d (%.1f%%)\n",
    count($categories['valid']),
    (count($categories['valid']) / $stats['total']) * 100
);

$txt .= sprintf("❌ PROBLÉMATIQUES:            %4d (%.1f%%)\n",
    $stats['problematic'],
    ($stats['problematic'] / $stats['total']) * 100
);

$txt .= sprintf("📄 Fichiers corrigés (exclus): %4d\n\n", count($categories['corriges']));

$txt .= "DÉTAIL DES PROBLÈMES\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("⚪ Answer toujours NULL:      %4d\n", count($categories['still_null']));
$txt .= sprintf("🔹 Answer chaîne vide:        %4d\n", count($categories['empty_string']));
$txt .= sprintf("🔸 Answer trop court (<3):    %4d\n", count($categories['too_short']));
$txt .= sprintf("🔶 Answer trop long (>500):   %4d\n", count($categories['too_long']));
$txt .= sprintf("🔴 Answer suspect (markdown): %4d\n\n", count($categories['suspect']));

// Exemples par catégorie
if (count($categories['still_null']) > 0) {
    $txt .= "❌ EXERCICES AVEC ANSWER NULL (" . count($categories['still_null']) . " cas)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach (array_slice($categories['still_null'], 0, 20) as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "    Titre: " . substr($ex['title'], 0, 60) . "\n";
        $txt .= "    Tips: " . ($ex['has_tips'] ? '✅ ' . $ex['tips_preview'] . '...' : '❌ Non') . "\n\n";
    }
    $txt .= "\n";
}

if (count($categories['too_long']) > 0) {
    $txt .= "🔶 ANSWER TROP LONG (>500 caractères, " . count($categories['too_long']) . " cas)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach (array_slice($categories['too_long'], 0, 10) as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "    Titre: " . substr($ex['title'], 0, 60) . "\n";
        $txt .= "    Longueur: " . $ex['length'] . " caractères\n";
        $txt .= "    Preview: " . $ex['answer_preview'] . "...\n\n";
    }
    $txt .= "\n";
}

if (count($categories['suspect']) > 0) {
    $txt .= "🔴 ANSWER SUSPECT (contient markdown/correction, " . count($categories['suspect']) . " cas)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach (array_slice($categories['suspect'], 0, 10) as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "    Titre: " . substr($ex['title'], 0, 60) . "\n";
        $txt .= "    Preview: " . $ex['answer_preview'] . "...\n\n";
    }
    $txt .= "\n";
}

if (count($categories['too_short']) > 0) {
    $txt .= "🔸 ANSWER TROP COURT (<3 caractères, " . count($categories['too_short']) . " cas)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach (array_slice($categories['too_short'], 0, 10) as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "    Answer: '" . $ex['answer'] . "'\n\n";
    }
    $txt .= "\n";
}

$txt .= "EXEMPLES D'EXERCICES VALIDES (" . count($categories['valid']) . " cas)\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
foreach (array_slice($categories['valid'], 0, 10) as $i => $ex) {
    $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
    $txt .= "    Answer: " . $ex['answer_preview'] . "...\n\n";
}

file_put_contents(REPORT_DIR . '/audit_enriched_report.txt', $txt);

// Sauvegarder les identifiants problématiques
$problematicIds = [
    'still_null' => array_column($categories['still_null'], 'identifier'),
    'too_short' => array_column($categories['too_short'], 'identifier'),
    'too_long' => array_column($categories['too_long'], 'identifier'),
    'suspect' => array_column($categories['suspect'], 'identifier'),
    'empty_string' => array_column($categories['empty_string'], 'identifier'),
];

file_put_contents(
    REPORT_DIR . '/problematic_exercises.json',
    json_encode($problematicIds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// 4. RÉSUMÉ CONSOLE
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ AUDIT TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total exercices:              %4d\n\n", $stats['total']);

echo sprintf("✅ VALIDES:                   %4d (%.1f%%)\n",
    count($categories['valid']),
    (count($categories['valid']) / $stats['total']) * 100
);

echo sprintf("❌ PROBLÉMATIQUES:            %4d (%.1f%%)\n",
    $stats['problematic'],
    ($stats['problematic'] / $stats['total']) * 100
);

echo sprintf("📄 Fichiers corrigés:         %4d\n\n", count($categories['corriges']));

echo "DÉTAIL DES PROBLÈMES\n";
echo "───────────────────────────────────────────────────────────────\n";
echo sprintf("⚪ Answer NULL:               %4d\n", count($categories['still_null']));
echo sprintf("🔹 Answer vide:               %4d\n", count($categories['empty_string']));
echo sprintf("🔸 Answer trop court:         %4d\n", count($categories['too_short']));
echo sprintf("🔶 Answer trop long:          %4d\n", count($categories['too_long']));
echo sprintf("🔴 Answer suspect:            %4d\n", count($categories['suspect']));
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📄 Fichiers générés:\n";
echo "   • " . REPORT_DIR . "/audit_enriched_report.txt\n";
echo "   • " . REPORT_DIR . "/problematic_exercises.json\n\n";

echo "📋 Prochaine étape:\n";
echo "   1. Consulter le rapport détaillé\n";
echo "   2. Corriger les exercices problématiques\n";
echo "   3. Relancer l'enrichissement sur les échecs\n\n";
