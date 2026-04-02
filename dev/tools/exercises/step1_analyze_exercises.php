<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  ÉTAPE 1 V2 : ANALYSE COMPLÈTE DES EXERCICES
 * ═══════════════════════════════════════════════════════════════
 *
 * Détection exhaustive de TOUS les problèmes :
 *
 * 🔴 CRITIQUE (bloque l'utilisation)
 *   - Champs obligatoires vides/manquants
 *   - Valeur "Array" non parsée
 *   - QCM sans Choices
 *   - Incohérences Subject/AnswerType/Domain
 *
 * 🟠 IMPORTANT (dégrade l'expérience)
 *   - Answer manquant
 *   - Références .md obsolètes
 *   - Encodage cassé
 *   - Tips manquants
 *
 * 🟡 MOYEN (amélioration UX)
 *   - Symboles Markdown dans texte
 *   - XP_Points = 0
 *   - Difficulty non standard
 *   - Structure mixte (plusieurs sections)
 *
 * Output:
 *   - dev/reports/analysis_report_v2.json (détaillé)
 *   - dev/reports/analysis_report_v2.txt (lisible)
 *   - dev/reports/analysis_summary.txt (résumé exécutif)
 */

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('INPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_final_deduplicated.json');
define('OUTPUT_JSON', PROJECT_ROOT . '/dev/reports/analysis_report_v2.json');
define('OUTPUT_TXT', PROJECT_ROOT . '/dev/reports/analysis_report_v2.txt');
define('OUTPUT_SUMMARY', PROJECT_ROOT . '/dev/reports/analysis_summary.txt');

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ÉTAPE 1 V2 : ANALYSE COMPLÈTE DES EXERCICES\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// ═══════════════════════════════════════════════════════════════
// DÉTECTIONS - NIVEAU CRITIQUE 🔴
// ═══════════════════════════════════════════════════════════════

function detectCriticalIssues($exercise) {
    $issues = [];

    // Champs obligatoires vides
    $requiredFields = ['Identifier', 'Subject', 'Level', 'Title', 'Content'];
    foreach ($requiredFields as $field) {
        if (!isset($exercise[$field]) || trim($exercise[$field]) === '') {
            $issues[] = "missing_required_field:$field";
        }
    }

    // Valeur "Array" non parsée
    $fieldsToCheck = ['Content', 'Answer', 'Tips'];
    foreach ($fieldsToCheck as $field) {
        if (isset($exercise[$field]) && $exercise[$field] === 'Array') {
            $issues[] = "unparsed_array:$field";
        }
    }

    // QCM sans Choices
    $answerType = strtolower($exercise['AnswerType'] ?? '');
    if (in_array($answerType, ['qcm', 'qcm_multiple', 'case_a_cocher'])) {
        $choices = $exercise['Choices'] ?? null;
        if (empty($choices) || $choices === 'null' || $choices === '[]') {
            $issues[] = 'qcm_without_choices';
        }
    }

    // Incohérences Subject/Domain
    $subjectDomainMap = [
        'Mathématiques' => ['Mathématiques', 'Sciences', 'Géométrie', 'Algèbre', 'Probabilités', 'Statistiques'],
        'Français' => ['Français', 'Langue vivante', 'Littérature', 'Grammaire', 'Orthographe'],
        'Anglais' => ['Langue vivante étrangère', 'Anglais', 'Langues'],
        'Histoire-Géographie' => ['Histoire', 'Géographie', 'Histoire-Géographie'],
        'SVT' => ['Sciences', 'Sciences de la vie', 'SVT'],
        'Physique-Chimie' => ['Sciences', 'Physique', 'Chimie']
    ];

    $subject = $exercise['Subject'] ?? '';
    $domain = $exercise['Domain'] ?? '';

    if (!empty($subject) && !empty($domain)) {
        $validDomains = $subjectDomainMap[$subject] ?? [];
        $isValid = false;
        foreach ($validDomains as $validDomain) {
            if (stripos($domain, $validDomain) !== false) {
                $isValid = true;
                break;
            }
        }
        if (!$isValid && $domain !== 'Sciences' && $domain !== 'Compétence scientifique générale.') {
            $issues[] = "subject_domain_mismatch:$subject/$domain";
        }
    }

    return $issues;
}

// ═══════════════════════════════════════════════════════════════
// DÉTECTIONS - NIVEAU IMPORTANT 🟠
// ═══════════════════════════════════════════════════════════════

function detectImportantIssues($exercise) {
    $issues = [];

    // Answer manquant (sauf pour essay)
    $answerType = $exercise['AnswerType'] ?? '';
    if (!in_array(strtolower($answerType), ['essay', 'texte']) && empty($exercise['Answer'])) {
        $issues[] = 'missing_answer';
    }

    // Tips manquants
    if (empty($exercise['Tips'])) {
        $issues[] = 'missing_tips';
    }

    // Références .md
    $fieldsToCheck = ['Content', 'Answer', 'Tips'];
    foreach ($fieldsToCheck as $field) {
        $text = $exercise[$field] ?? '';
        if (is_string($text) && preg_match('/\.md\b/i', $text)) {
            $issues[] = "md_reference_in:$field";
        }
    }

    // Encodage cassé
    $fieldsToCheck = ['Title', 'Content', 'Answer', 'Tips'];
    foreach ($fieldsToCheck as $field) {
        $text = $exercise[$field] ?? '';
        if (!is_string($text)) continue;

        $encodingPatterns = [
            '/e\?/' => 'arrow_encoding',
            '/Oe\s/' => 'ou_accent',
            '/&lt;|&gt;/' => 'html_entities',
            '/&#\d+;/' => 'numeric_entities',
            '/&[a-z]+;/i' => 'named_entities'
        ];

        foreach ($encodingPatterns as $pattern => $type) {
            if (preg_match($pattern, $text)) {
                $issues[] = "encoding:$type:$field";
                break;
            }
        }
    }

    return $issues;
}

// ═══════════════════════════════════════════════════════════════
// DÉTECTIONS - NIVEAU MOYEN 🟡
// ═══════════════════════════════════════════════════════════════

function detectModerateIssues($exercise) {
    $issues = [];

    // Markdown dans texte
    $fieldsToCheck = ['Content', 'Answer', 'Tips'];
    foreach ($fieldsToCheck as $field) {
        $text = $exercise[$field] ?? '';
        if (!is_string($text)) continue;

        $markdownPatterns = [
            '/^#{1,6}\s+/m' => 'markdown_headers',
            '/\*\*[^*]+\*\*/' => 'markdown_bold',
            '/^\d+\.\s+/m' => 'numbered_list',
            '/^[a-z]\)\s+/m' => 'letter_list'
        ];

        foreach ($markdownPatterns as $pattern => $type) {
            if (preg_match($pattern, $text)) {
                $issues[] = "markdown:$type:$field";
                break;
            }
        }
    }

    // XP_Points = 0
    if (isset($exercise['XP_Points']) && (int)$exercise['XP_Points'] === 0) {
        $issues[] = 'xp_points_zero';
    }

    // Difficulty non standard
    $validDifficulties = ['facile', 'moyen', 'difficile'];
    $difficulty = strtolower($exercise['Difficulty'] ?? '');
    if (!empty($difficulty) && !in_array($difficulty, $validDifficulties)) {
        $issues[] = "non_standard_difficulty:$difficulty";
    }

    // AnswerType typo/non standard
    $validAnswerTypes = ['texte', 'qcm', 'calcul', 'vrai_faux', 'qcm_multiple', 'case_a_cocher'];
    $answerType = strtolower($exercise['AnswerType'] ?? '');
    if (!empty($answerType) && !in_array($answerType, $validAnswerTypes)) {
        $issues[] = "non_standard_answer_type:$answerType";
    }

    // Structure mixte (plusieurs sections dans Content)
    $content = $exercise['Content'] ?? '';
    if (is_string($content)) {
        $sectionCount = preg_match_all('/^##\s+/m', $content);
        if ($sectionCount > 1) {
            $issues[] = "multiple_sections:$sectionCount";
        }

        // Contenu très long (probablement mal parsé)
        if (strlen($content) > 5000) {
            $issues[] = 'very_long_content:' . strlen($content);
        }
    }

    return $issues;
}

// ═══════════════════════════════════════════════════════════════
// ANALYSE COMPLÈTE
// ═══════════════════════════════════════════════════════════════

function analyzeExerciseComplete($exercise) {
    return [
        'critical' => detectCriticalIssues($exercise),
        'important' => detectImportantIssues($exercise),
        'moderate' => detectModerateIssues($exercise)
    ];
}

function getSeverity($analysis) {
    if (!empty($analysis['critical'])) return 'critical';
    if (!empty($analysis['important'])) return 'important';
    if (!empty($analysis['moderate'])) return 'moderate';
    return 'clean';
}

// ═══════════════════════════════════════════════════════════════
// SCRIPT PRINCIPAL
// ═══════════════════════════════════════════════════════════════

echo "📄 Chargement du fichier...\n";
if (!file_exists(INPUT_FILE)) {
    die("❌ Fichier introuvable: " . INPUT_FILE . "\n");
}

$json = file_get_contents(INPUT_FILE);
$exercises = json_decode($json, true);

if (!is_array($exercises)) {
    die("❌ JSON invalide\n");
}

echo sprintf("✅ %d exercices chargés\n\n", count($exercises));

echo "🔍 Analyse complète en cours...\n";

$stats = [
    'total' => count($exercises),
    'by_severity' => [
        'clean' => 0,
        'moderate' => 0,
        'important' => 0,
        'critical' => 0
    ],
    'issue_counts' => [
        'critical' => [],
        'important' => [],
        'moderate' => []
    ]
];

$categorizedExercises = [
    'clean' => [],
    'moderate' => [],
    'important' => [],
    'critical' => []
];

foreach ($exercises as $index => $exercise) {
    $identifier = $exercise['Identifier'] ?? 'NO_ID_' . $index;

    $analysis = analyzeExerciseComplete($exercise);
    $severity = getSeverity($analysis);

    $stats['by_severity'][$severity]++;

    // Compter types de problèmes
    foreach (['critical', 'important', 'moderate'] as $level) {
        foreach ($analysis[$level] as $issue) {
            if (!isset($stats['issue_counts'][$level][$issue])) {
                $stats['issue_counts'][$level][$issue] = 0;
            }
            $stats['issue_counts'][$level][$issue]++;
        }
    }

    if ($severity !== 'clean') {
        $categorizedExercises[$severity][] = [
            'identifier' => $identifier,
            'title' => $exercise['Title'] ?? '',
            'subject' => $exercise['Subject'] ?? '',
            'level' => $exercise['Level'] ?? '',
            'severity' => $severity,
            'issues' => $analysis
        ];
    }

    // Progress
    if (($index + 1) % 100 === 0) {
        echo sprintf("   Analysé: %d/%d\r", $index + 1, $stats['total']);
    }
}

echo "\n\n";

// Trier par gravité
foreach ($stats['issue_counts'] as $level => &$counts) {
    arsort($counts);
}
unset($counts);

// ═══════════════════════════════════════════════════════════════
// GÉNÉRATION DES RAPPORTS
// ═══════════════════════════════════════════════════════════════

echo "📊 Génération des rapports...\n";

// JSON détaillé
$reportData = [
    'analysis_date' => date('Y-m-d H:i:s'),
    'input_file' => basename(INPUT_FILE),
    'statistics' => $stats,
    'exercises_by_severity' => $categorizedExercises
];

$reportDir = dirname(OUTPUT_JSON);
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

file_put_contents(
    OUTPUT_JSON,
    json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

// Rapport texte détaillé
$txtReport = [];
$txtReport[] = "═══════════════════════════════════════════════════════════════";
$txtReport[] = "  RAPPORT D'ANALYSE COMPLÈTE DES EXERCICES V2";
$txtReport[] = "═══════════════════════════════════════════════════════════════";
$txtReport[] = "";
$txtReport[] = "Date: " . date('Y-m-d H:i:s');
$txtReport[] = "";
$txtReport[] = "📊 STATISTIQUES PAR GRAVITÉ";
$txtReport[] = "───────────────────────────────────────────────────────────────";
$txtReport[] = sprintf("Total exercices:           %4d", $stats['total']);
$txtReport[] = sprintf("✅ Propres:                %4d (%.1f%%)",
    $stats['by_severity']['clean'],
    ($stats['by_severity']['clean'] / $stats['total']) * 100);
$txtReport[] = sprintf("🟡 Problèmes modérés:      %4d (%.1f%%)",
    $stats['by_severity']['moderate'],
    ($stats['by_severity']['moderate'] / $stats['total']) * 100);
$txtReport[] = sprintf("🟠 Problèmes importants:   %4d (%.1f%%)",
    $stats['by_severity']['important'],
    ($stats['by_severity']['important'] / $stats['total']) * 100);
$txtReport[] = sprintf("🔴 Problèmes critiques:    %4d (%.1f%%)",
    $stats['by_severity']['critical'],
    ($stats['by_severity']['critical'] / $stats['total']) * 100);
$txtReport[] = "";

// Détail par niveau
foreach (['critical' => '🔴 CRITIQUES', 'important' => '🟠 IMPORTANTS', 'moderate' => '🟡 MODÉRÉS'] as $level => $label) {
    if (!empty($stats['issue_counts'][$level])) {
        $txtReport[] = "📋 PROBLÈMES $label";
        $txtReport[] = "───────────────────────────────────────────────────────────────";
        foreach ($stats['issue_counts'][$level] as $issue => $count) {
            $txtReport[] = sprintf("  %-50s %4d", $issue, $count);
        }
        $txtReport[] = "";
    }
}

// Exemples par gravité
foreach (['critical', 'important', 'moderate'] as $level) {
    $label = ['critical' => '🔴 CRITIQUES', 'important' => '🟠 IMPORTANTS', 'moderate' => '🟡 MODÉRÉS'][$level];
    $examples = array_slice($categorizedExercises[$level], 0, 10);

    if (!empty($examples)) {
        $txtReport[] = "📝 EXEMPLES $label (10 premiers)";
        $txtReport[] = "═══════════════════════════════════════════════════════════════";
        $txtReport[] = "";

        foreach ($examples as $idx => $ex) {
            $txtReport[] = sprintf("━━━ EXERCICE %d ━━━", $idx + 1);
            $txtReport[] = sprintf("ID:      %s", $ex['identifier']);
            $txtReport[] = sprintf("Titre:   %s", substr($ex['title'], 0, 60));
            $txtReport[] = sprintf("Matière: %s | Niveau: %s", $ex['subject'], $ex['level']);
            $txtReport[] = "";

            foreach (['critical', 'important', 'moderate'] as $issueLevel) {
                if (!empty($ex['issues'][$issueLevel])) {
                    $txtReport[] = sprintf("  %s:", strtoupper($issueLevel));
                    foreach ($ex['issues'][$issueLevel] as $issue) {
                        $txtReport[] = "    • $issue";
                    }
                }
            }
            $txtReport[] = "";
        }
    }
}

$txtReport[] = "═══════════════════════════════════════════════════════════════";

file_put_contents(OUTPUT_TXT, implode("\n", $txtReport));

// Résumé exécutif
$summary = [];
$summary[] = "═══════════════════════════════════════════════════════════════";
$summary[] = "  RÉSUMÉ EXÉCUTIF - ANALYSE EXERCICES";
$summary[] = "═══════════════════════════════════════════════════════════════";
$summary[] = "";
$summary[] = sprintf("Total: %d exercices analysés", $stats['total']);
$summary[] = "";
$summary[] = sprintf("✅ %d exercices propres (%.1f%%)",
    $stats['by_severity']['clean'],
    ($stats['by_severity']['clean'] / $stats['total']) * 100);
$summary[] = "";
$summary[] = "PROBLÈMES DÉTECTÉS:";
$summary[] = sprintf("🔴 %d exercices CRITIQUES (bloquants)", $stats['by_severity']['critical']);
$summary[] = sprintf("🟠 %d exercices IMPORTANTS (dégradent UX)", $stats['by_severity']['important']);
$summary[] = sprintf("🟡 %d exercices MODÉRÉS (améliorables)", $stats['by_severity']['moderate']);
$summary[] = "";
$summary[] = "TOP 5 PROBLÈMES CRITIQUES:";
$topCritical = array_slice($stats['issue_counts']['critical'], 0, 5, true);
foreach ($topCritical as $issue => $count) {
    $summary[] = sprintf("  • %s: %d", $issue, $count);
}
$summary[] = "";
$summary[] = "TOP 5 PROBLÈMES IMPORTANTS:";
$topImportant = array_slice($stats['issue_counts']['important'], 0, 5, true);
foreach ($topImportant as $issue => $count) {
    $summary[] = sprintf("  • %s: %d", $issue, $count);
}
$summary[] = "";
$summary[] = "PRIORITÉS:";
$summary[] = "1. Corriger les " . $stats['by_severity']['critical'] . " exercices CRITIQUES (bloquants)";
$summary[] = "2. Corriger les " . $stats['by_severity']['important'] . " exercices IMPORTANTS (encodage, réponses)";
$summary[] = "3. Améliorer les " . $stats['by_severity']['moderate'] . " exercices MODÉRÉS (cosmétique)";
$summary[] = "";
$summary[] = "═══════════════════════════════════════════════════════════════";

file_put_contents(OUTPUT_SUMMARY, implode("\n", $summary));

// ═══════════════════════════════════════════════════════════════
// RÉSUMÉ CONSOLE
// ═══════════════════════════════════════════════════════════════

echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ ANALYSE COMPLÈTE TERMINÉE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total:                    %4d\n", $stats['total']);
echo sprintf("✅ Propres:               %4d (%.1f%%)\n",
    $stats['by_severity']['clean'],
    ($stats['by_severity']['clean'] / $stats['total']) * 100);
echo sprintf("🟡 Modérés:               %4d (%.1f%%)\n",
    $stats['by_severity']['moderate'],
    ($stats['by_severity']['moderate'] / $stats['total']) * 100);
echo sprintf("🟠 Importants:            %4d (%.1f%%)\n",
    $stats['by_severity']['important'],
    ($stats['by_severity']['important'] / $stats['total']) * 100);
echo sprintf("🔴 Critiques:             %4d (%.1f%%)\n",
    $stats['by_severity']['critical'],
    ($stats['by_severity']['critical'] / $stats['total']) * 100);
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "📄 Rapports générés:\n";
echo "   • " . OUTPUT_SUMMARY . " (résumé)\n";
echo "   • " . OUTPUT_JSON . " (données)\n";
echo "   • " . OUTPUT_TXT . " (détaillé)\n";
echo "\n";
echo "📋 Prochaine étape:\n";
echo "   1. Consulter " . basename(OUTPUT_SUMMARY) . "\n";
echo "   2. Lancer le script 2 (nettoyage)\n";
echo "\n";
