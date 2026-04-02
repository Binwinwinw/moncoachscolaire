<?php
/**
 * ÉTAPE 2 : NETTOYAGE DES EXERCICES (VERSION 2)
 *
 * Corrections appliquées :
 * 1. Answer = "Array" (379) → Récupération depuis Tips ou null
 * 2. Encodage e? → → (325)
 * 3. Subject/Domain incohérents (21 vraies erreurs)
 * 4. XP_Points = 0 → 10 (649)
 * 5. Markdown, .md, HTML entities
 *
 * Entrée  : exercises_final_deduplicated.json (1245 exercices)
 * Sortie  : exercises_stage2_cleaned.json (1245 exercices nettoyés)
 * Rapport : cleaning_report_v2.txt + cleaning_report_v2.json
 */

// Configuration
define('PROJECT_ROOT', dirname(__DIR__, 3));
define('INPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_final_deduplicated.json');
define('OUTPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_stage2_cleaned.json');
define('REPORT_DIR', PROJECT_ROOT . '/dev/reports');

if (!is_dir(REPORT_DIR)) {
    mkdir(REPORT_DIR, 0755, true);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  ÉTAPE 2 : NETTOYAGE COMPLET DES EXERCICES\n";
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

// 2. STATISTIQUES
$stats = [
    'total' => $total,
    'cleaned' => 0,
    'unchanged' => 0,
    'array_answer_fixed' => 0,
    'array_answer_nullified' => 0,
    'encoding_fixed' => 0,
    'subject_domain_fixed' => 0,
    'xp_points_fixed' => 0,
    'markdown_cleaned' => 0,
    'md_refs_removed' => 0,
];

$examples = [
    'array_answer' => [],
    'encoding' => [],
    'subject_domain' => [],
];

// 3. FONCTIONS DE NETTOYAGE

/**
 * Nettoie l'encodage cassé (5 passes HTML + caractères spéciaux)
 */
function cleanEncoding(?string $text): ?string {
    if (is_null($text) || trim($text) === '') return null;

    // 5 passes de décodage HTML
    for ($i = 0; $i < 5; $i++) {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // Caractères spéciaux cassés
    $replacements = [
        'e?' => '→',
        'Oe' => 'Où',
        'oe' => 'œ',
        'OE' => 'Œ',
        'a?' => 'à',
        'e`' => 'è',
        'e^' => 'ê',
        'c?' => 'ç',
        '\u00b1' => '±',
        '&nbsp;' => ' ',
    ];

    foreach ($replacements as $search => $replace) {
        $text = str_replace($search, $replace, $text);
    }

    // Nettoyer espaces multiples
    $text = preg_replace('/\s+/', ' ', $text);

    return trim($text) !== '' ? trim($text) : null;
}

/**
 * Nettoie le Markdown
 */
function cleanMarkdown(?string $text): ?string {
    if (is_null($text) || trim($text) === '') return null;

    // Titres ##
    $text = preg_replace('/^##\s+[^\n]+\n?/m', '', $text);

    // Gras **texte**
    $text = preg_replace('/\*\*([^*]+)\*\*/', '$1', $text);

    // Italique *texte*
    $text = preg_replace('/\*([^*]+)\*/', '$1', $text);

    // Listes
    $text = preg_replace('/^([a-z])\)\s+/m', '$1. ', $text);

    // HTML
    $text = preg_replace('/<\/?p>/', '', $text);
    $text = str_replace(['<br />', '<br/>'], "\n", $text);

    return trim($text) !== '' ? trim($text) : null;
}

/**
 * Supprime références .md
 */
function removeMdReferences(?string $text): ?string {
    if (is_null($text) || trim($text) === '') return null;

    $patterns = [
        '/Voir le fichier\s+[a-z0-9\-]+\.md/i',
        '/exercice-\d+-[a-z\-]+\.md/i',
        '/corrige-\d+-[a-z\-]+\.md/i',
        '/cours-\d+-[a-z\-]+\.md/i',
        '/## Ressources\n[^\n]*\.md[^\n]*/i',
    ];

    foreach ($patterns as $pattern) {
        $text = preg_replace($pattern, '', $text);
    }

    return trim($text) !== '' ? trim($text) : null;
}

/**
 * Récupère la réponse depuis Tips si Answer = "Array"
 */
function extractAnswerFromTips(?string $tips): ?string {
    if (is_null($tips)) return null;

    // Pattern : "x = valeur" ou "réponse : valeur"
    $patterns = [
        '/(?:x|y|z)\s*=\s*([^,\.\n]+)/i',
        '/(?:réponse|answer|solution)\s*[:\s]\s*([^,\.\n]+)/i',
        '/(?:résultat|result)\s*[:\s]\s*([^,\.\n]+)/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $tips, $matches)) {
            return trim($matches[1]);
        }
    }

    return null;
}

/**
 * Valide la cohérence Subject/Domain
 * Retourne true si INCOHÉRENT, false si OK
 */
function isSubjectDomainIncoherent(string $subject, ?string $domain): bool {
    if (is_null($domain) || trim($domain) === '') return false;

    // Liste des VRAIES incohérences (détectées manuellement)
    $incoherent = [
        'Français' => [
            'Geometrie', 'Trigonometrie', 'Algebre', 'Fonctions',
            'Probabilites', 'Statistiques', 'Calcul litteral',
            'Nombres et calculs', 'Grandeurs et mesures', 'Espace et geometrie'
        ],
        'Mathématiques' => [
            'Argumentation', 'Méthodologie', 'Conjugaison', 'Vocabulaire',
            'Lecture', 'Expression ecrite', 'Comprehension', 'Dissertation',
            'Commentaire', 'Analyse litteraire', 'Poesie', 'Theatre', 'Roman'
        ],
        'Anglais' => [
            'Chimie', 'Physique', 'Biologie', 'Histoire', 'Géographie'
        ],
        'Histoire-Géographie' => [
            'Chimie', 'Physique', 'Biologie', 'Argumentation', 'Dissertation'
        ],
        'Sciences' => [
            'Français', 'Mathématiques', 'Anglais', 'Histoire', 'Géographie'
        ],
    ];

    if (!isset($incoherent[$subject])) return false;

    foreach ($incoherent[$subject] as $invalidDomain) {
        if (stripos($domain, $invalidDomain) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Corrige Subject ou Domain incohérent
 */
function fixSubjectDomain(string &$subject, ?string &$domain): bool {
    if (is_null($domain)) return false;

    // Détection automatique du vrai Subject depuis Domain
    $domainLower = strtolower($domain);

    // Math dans Domain → Subject doit être Mathématiques
    $mathTerms = ['geometrie', 'algebre', 'fonctions', 'probabilite', 'trigonometrie',
                  'calcul', 'equation', 'nombre', 'statistique'];
    foreach ($mathTerms as $term) {
        if (stripos($domainLower, $term) !== false && $subject !== 'Mathématiques') {
            $subject = 'Mathématiques';
            return true;
        }
    }

    // Français dans Domain → Subject doit être Français
    $frenchTerms = ['conjugaison', 'vocabulaire', 'litteraire', 'dissertation',
                    'commentaire', 'poesie', 'theatre', 'roman', 'argumentation'];
    foreach ($frenchTerms as $term) {
        if (stripos($domainLower, $term) !== false && $subject !== 'Français') {
            $subject = 'Français';
            return true;
        }
    }

    return false;
}

// 4. NETTOYAGE
echo "🧹 Nettoyage en cours...\n";

foreach ($exercises as $index => &$ex) {
    $identifier = $ex['Identifier'] ?? "UNKNOWN-$index";
    $hasChanges = false;

    // 4.1. Correction Answer = "Array"
    if (isset($ex['Answer']) && $ex['Answer'] === 'Array') {
        $extracted = extractAnswerFromTips($ex['Tips'] ?? null);

        if ($extracted !== null) {
            $ex['Answer'] = $extracted;
            $stats['array_answer_fixed']++;

            if (count($examples['array_answer']) < 10) {
                $examples['array_answer'][] = [
                    'identifier' => $identifier,
                    'tips' => substr($ex['Tips'] ?? '', 0, 100),
                    'extracted_answer' => $extracted,
                ];
            }
        } else {
            $ex['Answer'] = null;
            $stats['array_answer_nullified']++;
        }

        $hasChanges = true;
    }

    // 4.2. Encodage
    $fieldsToClean = ['Title', 'Content', 'Answer', 'Tips', 'Domain', 'Competence', 'Instruction'];
    foreach ($fieldsToClean as $field) {
        if (!isset($ex[$field])) continue;

        $original = $ex[$field];
        $cleaned = cleanEncoding($original);
        $cleaned = cleanMarkdown($cleaned);
        $cleaned = removeMdReferences($cleaned);

        if ($original !== $cleaned) {
            $ex[$field] = $cleaned;
            $hasChanges = true;

            if (strpos($original ?? '', 'e?') !== false || strpos($original ?? '', '&') !== false) {
                $stats['encoding_fixed']++;

                if (count($examples['encoding']) < 10) {
                    $examples['encoding'][] = [
                        'identifier' => $identifier,
                        'field' => $field,
                        'before' => substr($original ?? '', 0, 80),
                        'after' => substr($cleaned ?? '', 0, 80),
                    ];
                }
            }

            if (strpos($original ?? '', '.md') !== false) {
                $stats['md_refs_removed']++;
            }

            if (strpos($original ?? '', '**') !== false || strpos($original ?? '', '##') !== false) {
                $stats['markdown_cleaned']++;
            }
        }
    }

    // 4.3. Subject/Domain incohérent
    if (isSubjectDomainIncoherent($ex['Subject'], $ex['Domain'] ?? null)) {
        $oldSubject = $ex['Subject'];
        $oldDomain = $ex['Domain'];

        if (fixSubjectDomain($ex['Subject'], $ex['Domain'])) {
            $stats['subject_domain_fixed']++;
            $hasChanges = true;

            if (count($examples['subject_domain']) < 10) {
                $examples['subject_domain'][] = [
                    'identifier' => $identifier,
                    'before' => "$oldSubject / $oldDomain",
                    'after' => "{$ex['Subject']} / {$ex['Domain']}",
                ];
            }
        }
    }

    // 4.4. XP_Points = 0 → 10
    if (isset($ex['XP_Points']) && $ex['XP_Points'] == 0) {
        $ex['XP_Points'] = 10;
        $stats['xp_points_fixed']++;
        $hasChanges = true;
    }

    if ($hasChanges) {
        $stats['cleaned']++;
    } else {
        $stats['unchanged']++;
    }

    // Progression
    if (($index + 1) % 100 === 0) {
        echo "   Traité: " . ($index + 1) . "/$total\n";
    }
}

echo "\n";

// 5. SAUVEGARDE
echo "💾 Sauvegarde...\n";
$output = json_encode($exercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(OUTPUT_FILE, $output);

// 6. RAPPORTS
echo "📊 Génération des rapports...\n";

$reportJson = [
    'timestamp' => date('Y-m-d H:i:s'),
    'input_file' => basename(INPUT_FILE),
    'output_file' => basename(OUTPUT_FILE),
    'statistics' => $stats,
    'examples' => $examples,
];

file_put_contents(
    REPORT_DIR . '/cleaning_report_v2.json',
    json_encode($reportJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// Rapport TXT
$txt = "═══════════════════════════════════════════════════════════════\n";
$txt .= "  RAPPORT DE NETTOYAGE COMPLET V2\n";
$txt .= "═══════════════════════════════════════════════════════════════\n\n";
$txt .= "Date : " . date('Y-m-d H:i:s') . "\n\n";

$txt .= "STATISTIQUES GLOBALES\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("Total exercices:           %4d\n", $stats['total']);
$txt .= sprintf("Exercices nettoyés:        %4d (%.1f%%)\n",
    $stats['cleaned'], ($stats['cleaned'] / $stats['total']) * 100);
$txt .= sprintf("Exercices inchangés:       %4d (%.1f%%)\n\n",
    $stats['unchanged'], ($stats['unchanged'] / $stats['total']) * 100);

$txt .= "CORRECTIONS PAR TYPE\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("Answer 'Array' corrigé:    %4d (récupéré depuis Tips)\n",
    $stats['array_answer_fixed']);
$txt .= sprintf("Answer 'Array' → null:     %4d (impossible à récupérer)\n",
    $stats['array_answer_nullified']);
$txt .= sprintf("Encodage corrigé (e?→→):   %4d\n", $stats['encoding_fixed']);
$txt .= sprintf("Subject/Domain corrigé:    %4d\n", $stats['subject_domain_fixed']);
$txt .= sprintf("XP_Points 0→10:            %4d\n", $stats['xp_points_fixed']);
$txt .= sprintf("Markdown nettoyé:          %4d\n", $stats['markdown_cleaned']);
$txt .= sprintf("Références .md:            %4d\n\n", $stats['md_refs_removed']);

// Exemples
if (count($examples['array_answer']) > 0) {
    $txt .= "EXEMPLES : Answer 'Array' récupéré\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($examples['array_answer'] as $i => $ex) {
        $txt .= sprintf("%d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "   Tips: " . $ex['tips'] . "...\n";
        $txt .= "   Answer extrait: " . $ex['extracted_answer'] . "\n\n";
    }
}

if (count($examples['encoding']) > 0) {
    $txt .= "EXEMPLES : Encodage corrigé\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($examples['encoding'] as $i => $ex) {
        $txt .= sprintf("%d. %s (%s)\n", $i + 1, $ex['identifier'], $ex['field']);
        $txt .= "   Avant: " . $ex['before'] . "\n";
        $txt .= "   Après: " . $ex['after'] . "\n\n";
    }
}

if (count($examples['subject_domain']) > 0) {
    $txt .= "EXEMPLES : Subject/Domain corrigé\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($examples['subject_domain'] as $i => $ex) {
        $txt .= sprintf("%d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "   Avant: " . $ex['before'] . "\n";
        $txt .= "   Après: " . $ex['after'] . "\n\n";
    }
}

file_put_contents(REPORT_DIR . '/cleaning_report_v2.txt', $txt);

// 7. RÉSUMÉ
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ NETTOYAGE TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total exercices:           %4d\n", $stats['total']);
echo sprintf("Exercices nettoyés:        %4d (%.1f%%)\n",
    $stats['cleaned'], ($stats['cleaned'] / $stats['total']) * 100);
echo sprintf("Exercices inchangés:       %4d (%.1f%%)\n\n",
    $stats['unchanged'], ($stats['unchanged'] / $stats['total']) * 100);

echo "CORRECTIONS APPLIQUÉES\n";
echo "───────────────────────────────────────────────────────────────\n";
echo sprintf("🔴 Answer 'Array' corrigé:  %4d\n", $stats['array_answer_fixed']);
echo sprintf("⚪ Answer 'Array' → null:   %4d\n", $stats['array_answer_nullified']);
echo sprintf("🟠 Encodage (e?→→):         %4d\n", $stats['encoding_fixed']);
echo sprintf("🟡 Subject/Domain:          %4d\n", $stats['subject_domain_fixed']);
echo sprintf("🟢 XP_Points 0→10:          %4d\n", $stats['xp_points_fixed']);
echo sprintf("🔵 Markdown nettoyé:        %4d\n", $stats['markdown_cleaned']);
echo sprintf("⚫ Références .md:          %4d\n", $stats['md_refs_removed']);
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📄 Fichier nettoyé: " . basename(OUTPUT_FILE) . "\n";
echo "📄 Rapport: cleaning_report_v2.txt\n\n";

echo "📋 Prochaine étape:\n";
echo "   1. Vérifier le rapport\n";
echo "   2. Importer dans la BDD si OK\n\n";
