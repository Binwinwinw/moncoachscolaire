<?php
/**
 * ENRICHISSEMENT DES EXERCICES SANS RÉPONSE AVEC IA
 *
 * Enrichit automatiquement les exercices sans réponse via :
 * 1. Extraction améliorée depuis Tips (359 exercices)
 * 2. Résolution automatique via IA pour les exercices sans Tips (112 exercices)
 *
 * Prérequis :
 * - OpenAI API key (pour résolution IA)
 * - Fichier exercises_without_answers.json
 *
 * Usage :
 *   php enrich_exercises_with_ai.php --mode=extract  (extraction Tips uniquement)
 *   php enrich_exercises_with_ai.php --mode=ai       (résolution IA uniquement)
 *   php enrich_exercises_with_ai.php --mode=full     (tout enrichir)
 *   php enrich_exercises_with_ai.php --dry-run       (simulation)
 */

// Configuration
define('PROJECT_ROOT', dirname(__DIR__, 3));
define('INPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_without_answers.json');
define('OUTPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_enriched.json');
define('REPORT_DIR', PROJECT_ROOT . '/dev/reports');

// Mode d'exécution
$mode = 'extract';  // Par défaut : extraction Tips seulement
$dryRun = in_array('--dry-run', $argv);

foreach ($argv as $arg) {
    if (strpos($arg, '--mode=') === 0) {
        $mode = substr($arg, 7);
    }
}

if (!in_array($mode, ['extract', 'ai', 'full'])) {
    die("❌ Mode invalide. Utilisez : extract, ai ou full\n");
}

if (!is_dir(REPORT_DIR)) {
    mkdir(REPORT_DIR, 0755, true);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  ENRICHISSEMENT DES EXERCICES SANS RÉPONSE\n";
echo "  Mode: $mode" . ($dryRun ? ' (DRY-RUN)' : '') . "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. CHARGEMENT
echo "📄 Chargement du fichier...\n";
if (!file_exists(INPUT_FILE)) {
    die("❌ ERREUR : Fichier introuvable : " . INPUT_FILE . "\n");
}

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
    'with_tips' => 0,
    'without_tips' => 0,
    'extracted_from_tips' => 0,
    'resolved_by_ai' => 0,
    'filtered' => 0,
    'failed' => 0,
    'skipped' => 0,
];

$examples = [
    'extracted' => [],
    'resolved' => [],
    'filtered' => [],
    'failed' => [],
];

// 3. FONCTION D'EXTRACTION AMÉLIORÉE
function extractAnswerFromTips(string $tips, array $exercise): ?string {
    // Nettoyage de base
    $tips = strip_tags($tips);
    $tips = html_entity_decode($tips, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $tips = trim(preg_replace('/\s+/', ' ', $tips));
    if (empty($tips)) return null;

    $answerType = strtolower($exercise['AnswerType'] ?? '');
    $content = isset($exercise['Content']) ? strip_tags($exercise['Content']) : '';

    // Patterns enrichis (réponse explicite, synonymes, formats variés)
    $patterns = [
        // x = valeur, y = valeur, z = valeur
        '/(?:x|y|z)\s*=\s*([^,.\n]{1,100})/i',
        // Réponse : valeur, Solution : valeur, Résultat : valeur
        '/(?:réponse|answer|solution|résultat|result|bonne réponse|bonne reponse|bonne réponse|bonne rep)\s*[:\s]\s*([^,.\n]{1,100})/i',
        // donc x = valeur
        '/donc\s+(?:x|y|z)\s*=\s*([^,.\n]{1,100})/i',
        // S'annule en x=valeur
        '/s.annule\s+en\s+(?:x|y|z)\s*=\s*([^,.\n]{1,100})/i',
        // Factorise : (x-2)(x-3)=0 donc x=2 ou x=3
        '/donc\s+(?:x|y|z)\s*=\s*([^,.\n]+(?:ou|or)\s+(?:x|y|z)\s*=\s*[^,.\n]+)/i',
        // = XX%
        '/=\s*(\d+(?:\.\d+)?%)/i',
        // = a/b
        '/=\s*(\d+\/\d+)/i',
        // = expression mathématique
        '/=\s*([a-z0-9\+\-\*\/\(\)\^\s]{3,50})(?:\.|,|\n|$)/i',
        // Réponse entre guillemets
        '/"([^"]{2,100})"/',
        // Réponse entre apostrophes
        '/\'([^\']{2,100})\'/',
        // Réponse implicite : “La bonne réponse est ...”
        '/la bonne réponse est\s*:?\s*([^,.\n]{1,100})/i',
        // Pour QCM : “C’est la réponse B”
        '/réponse\s*([a-d])/i',
        // Pour QCM : “La bonne est la C”
        '/la (?:bonne|bonne rep) est la ([a-d])/i',
        // Pour vrai/faux
        '/(vrai|faux|true|false)/i',
        // Pour nombre isolé
        '/\b(\d{1,4}(?:[\.,]\d{1,4})?)\b/',
        // Pour mot unique (texte court)
        '/\b([a-zA-Zéèêàâîôûçùëïüœ]{3,20})\b/u',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $tips, $matches)) {
            $answer = trim($matches[1]);
            $answer = str_replace(['e?', 'Oe'], ['→', 'Où'], $answer);
            $answer = preg_replace('/\s+/', ' ', $answer);
            if (strlen($answer) > 1 && strlen($answer) < 200) {
                return $answer;
            }
        }
    }

    // QCM : lettre dans Tips ou Content
    if (strpos($answerType, 'qcm') !== false) {
        if (preg_match('/\b([a-d])\b/i', $tips, $m)) {
            return strtoupper($m[1]);
        }
        if (preg_match('/\b([a-d])\b/i', $content, $m)) {
            return strtoupper($m[1]);
        }
    }

    // Texte court : première phrase du Tips ou Content
    if (strpos($answerType, 'texte') !== false) {
        if (preg_match('/^([^\.]{2,80})\./', $tips, $matches)) {
            $answer = trim($matches[1]);
            if (!preg_match('/(?:applique|utilise|calcule|méthode|formule|explique|démontre|justifie)/i', $answer)) {
                return $answer;
            }
        }
        if (preg_match('/^([^\.]{2,80})\./', $content, $matches)) {
            $answer = trim($matches[1]);
            if (!preg_match('/(?:applique|utilise|calcule|méthode|formule|explique|démontre|justifie)/i', $answer)) {
                return $answer;
            }
        }
    }

    // Fallback : mot unique ou nombre dans Content
    if (preg_match('/\b(\d{1,4}(?:[\.,]\d{1,4})?)\b/', $content, $matches)) {
        return $matches[1];
    }
    if (preg_match('/\b([a-zA-Zéèêàâîôûçùëïüœ]{3,20})\b/u', $content, $matches)) {
        return $matches[1];
    }

    return null;
}

// ---- Helpers: validation & heuristiques d'acceptation ----
function normalizeForCompare(string $s): string {
    $s = strip_tags($s);
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = mb_strtolower(trim(preg_replace('/\s+/', ' ', $s)), 'UTF-8');
    // garder lettres et chiffres pour la comparaison
    $s = preg_replace('/[^\p{L}\p{N}\s]/u', '', $s);
    return $s;
}

function answerAppearsInExercise(string $answer, array $exercise): bool {
    $needle = normalizeForCompare($answer);
    if ($needle === '') return false;

    $haystack = '';
    if (!empty($exercise['Content'])) $haystack .= ' ' . normalizeForCompare($exercise['Content']);
    if (!empty($exercise['Tips'])) $haystack .= ' ' . normalizeForCompare($exercise['Tips']);

    // Vérifier Choices (texte ou JSON)
    if (!empty($exercise['Choices'])) {
        $choices = $exercise['Choices'];
        // essayer de décoder JSON
        $decoded = json_decode($choices, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach ($decoded as $c) {
                if (normalizeForCompare((string)$c) === $needle) return true;
            }
        } else {
            $haystack .= ' ' . normalizeForCompare((string)$choices);
        }
    }

    return mb_strpos($haystack, $needle) !== false;
}

function isGenericAnswer(string $answer): bool {
    $a = normalizeForCompare($answer);
    if ($a === '') return true;

    // blacklist de réponses vagues / inutiles
    $blacklist = [
        'relis','rappelle','écoute','ecoute','bien','faut','voir','exemple','structure',
        'formation','à compléter','a completer','compléter','completer','réponse ici','reponse ici',
        'à revoir','a revoir','suivre','un verbe','le temps est comme un voleur'
    ];
    foreach ($blacklist as $w) {
        if ($a === $w) return true;
        if (mb_strpos($a, $w) !== false) return true;
    }

    // réponses ellipsées ou trop courtes
    if (preg_match('/\.\.\.|\.\.$/', $answer)) return true;
    $words = preg_split('/\s+/', $a);
    if (count($words) === 1 && mb_strlen($a) <= 2) return true;

    // finissant par une lettre isolée (ex: "pour t")
    $last = end($words);
    if (mb_strlen($last) <= 2 && count($words) > 1) {
        return true;
    }

    return false;
}

function isValidExtractedAnswer(string $answer, array $exercise): bool {
    $answer = trim($answer);
    if ($answer === '') return false;

    if (isGenericAnswer($answer)) return false;

    $type = strtolower($exercise['AnswerType'] ?? '');

    // QCM : accepter lettre A-D ou chiffre 1-4
    if (strpos($type, 'qcm') !== false) {
        if (preg_match('/^[a-d]$/i', $answer)) return true;
        if (preg_match('/^[1-4]$/', $answer)) return true;
        // sinon vérifier que la réponse figure parmi les choix
        if (answerAppearsInExercise($answer, $exercise)) return true;
        return false;
    }

    // vrai/faux
    if (strpos($type, 'vrai') !== false || strpos($type, 'faux') !== false || strpos($type, 'true') !== false) {
        if (preg_match('/^(vrai|faux|true|false)$/i', $answer)) return true;
    }

    // numérique
    if (preg_match('/^[\d\.,\-]+$/', $answer)) {
        // accepter si l'exercice contient un indice numérique
        if (answerAppearsInExercise($answer, $exercise) || preg_match('/\d/', $exercise['Content'] ?? '')) return true;
        return false;
    }

    // texte : accepter si apparition dans l'énoncé/tips ou si longueur raisonnable
    $wordCount = count(preg_split('/\s+/', $answer));
    if ($wordCount <= 2) {
        // mot unique ou petit groupe : n'accepter que s'il apparaît dans l'exo
        return answerAppearsInExercise($answer, $exercise);
    }

    // sinon accepter (phrase courte plausible)
    if ($wordCount >= 3 && mb_strlen($answer) >= 8) return true;

    return false;
}

// 4. FONCTION DE RÉSOLUTION IA (PLACEHOLDER)
function resolveExerciseWithAI(array $exercise): ?string {
    // TODO : Intégration OpenAI/Claude API
    // Pour l'instant, retourne null

    // Exemple d'implémentation future :
    /*
    $apiKey = getenv('OPENAI_API_KEY');
    if (!$apiKey) return null;

    $prompt = "Résous cet exercice de {$exercise['Subject']} niveau {$exercise['Level']}:\n\n";
    $prompt .= "Titre: {$exercise['Title']}\n";
    $prompt .= "Consigne: {$exercise['Content']}\n\n";
    $prompt .= "Donne uniquement la réponse finale, sans explication.";

    $response = callOpenAI($apiKey, $prompt);
    return $response['answer'] ?? null;
    */

    return null;
}

// 5. ENRICHISSEMENT
echo "🧠 Enrichissement en cours...\n";

foreach ($exercises as $index => &$ex) {
    $identifier = $ex['Identifier'] ?? "UNKNOWN-$index";
    $hasTips = !empty($ex['Tips']);

    if ($hasTips) {
        $stats['with_tips']++;
    } else {
        $stats['without_tips']++;
    }

    // Mode extract ou full : extraction depuis Tips
    if (($mode === 'extract' || $mode === 'full') && $hasTips) {
        $extracted = extractAnswerFromTips($ex['Tips'], $ex);

        if ($extracted !== null) {
            // validation sémantique/heuristique
            if (isValidExtractedAnswer($extracted, $ex)) {
                if (!$dryRun) {
                    $ex['Answer'] = $extracted;
                }
                $stats['extracted_from_tips']++;

                if (count($examples['extracted']) < 10) {
                    $examples['extracted'][] = [
                        'identifier' => $identifier,
                        'tips' => substr($ex['Tips'], 0, 120),
                        'answer' => $extracted,
                    ];
                }
            } else {
                // filtré : réponse générique / hors-contexte
                $stats['filtered']++;
                if (count($examples['filtered']) < 30) {
                    $examples['filtered'][] = [
                        'identifier' => $identifier,
                        'tips' => substr($ex['Tips'], 0, 120),
                        'extracted' => $extracted,
                        'reason' => 'Filtré: générique ou hors-contexte',
                    ];
                }
            }
        } else {
            $stats['failed']++;

            if (count($examples['failed']) < 10) {
                $examples['failed'][] = [
                    'identifier' => $identifier,
                    'tips' => substr($ex['Tips'], 0, 120),
                    'reason' => 'Impossible d\'extraire depuis Tips',
                ];
            }
        }
    }

    // Mode ai ou full : résolution IA
    if (($mode === 'ai' || $mode === 'full') && !$hasTips && is_null($ex['Answer'])) {
        $resolved = resolveExerciseWithAI($ex);

        if ($resolved !== null) {
            if (isValidExtractedAnswer($resolved, $ex)) {
                if (!$dryRun) {
                    $ex['Answer'] = $resolved;
                }
                $stats['resolved_by_ai']++;

                if (count($examples['resolved']) < 10) {
                    $examples['resolved'][] = [
                        'identifier' => $identifier,
                        'title' => substr($ex['Title'], 0, 60),
                        'answer' => $resolved,
                    ];
                }
            } else {
                $stats['filtered']++;
                if (count($examples['filtered']) < 30) {
                    $examples['filtered'][] = [
                        'identifier' => $identifier,
                        'title' => substr($ex['Title'], 0, 60),
                        'resolved' => $resolved,
                        'reason' => 'IA: réponse douteuse / filtrée',
                    ];
                }
            }
        } else {
            $stats['failed']++;
        }
    }

    // Progression
    if (($index + 1) % 50 === 0) {
        echo "   Traité: " . ($index + 1) . "/$total\n";
    }
}

echo "\n";

// 6. SAUVEGARDE
if (!$dryRun) {
    echo "💾 Sauvegarde du fichier enrichi...\n";
    $output = json_encode($exercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents(OUTPUT_FILE, $output);
    $fileSize = filesize(OUTPUT_FILE);
    echo "✅ Fichier créé : " . basename(OUTPUT_FILE) . " (" . round($fileSize / 1024, 2) . " KB)\n\n";
}

// 7. RAPPORT
echo "📊 Génération du rapport...\n";

$txt = "═══════════════════════════════════════════════════════════════\n";
$txt .= "  RAPPORT D'ENRICHISSEMENT\n";
$txt .= "═══════════════════════════════════════════════════════════════\n\n";
$txt .= "Date : " . date('Y-m-d H:i:s') . "\n";
$txt .= "Mode : $mode" . ($dryRun ? ' (DRY-RUN)' : '') . "\n";
$txt .= "Fichier entrée : " . basename(INPUT_FILE) . "\n";
$txt .= "Fichier sortie : " . basename(OUTPUT_FILE) . "\n\n";

$txt .= "STATISTIQUES\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("Total exercices:              %4d\n", $stats['total']);
$txt .= sprintf("Avec Tips:                    %4d\n", $stats['with_tips']);
$txt .= sprintf("Sans Tips:                    %4d\n\n", $stats['without_tips']);

$txt .= "RÉSULTATS\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("✅ Extraits depuis Tips:      %4d (%.1f%%)\n",
    $stats['extracted_from_tips'],
    $stats['with_tips'] > 0 ? ($stats['extracted_from_tips'] / $stats['with_tips']) * 100 : 0
);
$txt .= sprintf("🤖 Résolus par IA:            %4d (%.1f%%)\n",
    $stats['resolved_by_ai'],
    $stats['without_tips'] > 0 ? ($stats['resolved_by_ai'] / $stats['without_tips']) * 100 : 0
);
$txt .= sprintf("🔶 Filtrés (douteux):        %4d (%.1f%%)\n",
    $stats['filtered'],
    ($stats['filtered'] / $stats['total']) * 100
);
$txt .= sprintf("❌ Échecs:                    %4d (%.1f%%)\n\n",
    $stats['failed'],
    ($stats['failed'] / $stats['total']) * 100
);

// Exemples
if (count($examples['extracted']) > 0) {
    $txt .= "EXEMPLES : Extractions réussies (10 premiers)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($examples['extracted'] as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "    Tips: " . $ex['tips'] . "...\n";
        $txt .= "    Answer: " . $ex['answer'] . "\n\n";
    }
}

if (count($examples['filtered']) > 0) {
    $txt .= "EXEMPLES : Filtrés / Douteux (à vérifier manuellement)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($examples['filtered'] as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
        if (!empty($ex['tips'])) $txt .= "    Tips: " . $ex['tips'] . "...\n";
        if (!empty($ex['extracted'])) $txt .= "    Extracted: " . $ex['extracted'] . "\n";
        if (!empty($ex['resolved'])) $txt .= "    Resolved(IA): " . $ex['resolved'] . "\n";
        $txt .= "    Raison: " . ($ex['reason'] ?? 'douteux') . "\n\n";
    }
}

if (count($examples['failed']) > 0) {
    $txt .= "EXEMPLES : Échecs (10 premiers)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($examples['failed'] as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "    Tips: " . $ex['tips'] . "...\n";
        $txt .= "    Raison: " . $ex['reason'] . "\n\n";
    }
}

$txt .= "PROCHAINES ÉTAPES\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= "1. Vérifier les extractions dans exercises_enriched.json\n";
$txt .= "2. Pour les échecs, ajouter intégration OpenAI API\n";
$txt .= "3. Réimporter : php dev/tools/exercises/import_enriched_exercises.php\n\n";

file_put_contents(REPORT_DIR . '/enrichment_report.txt', $txt);
// Exporter les cas filtrés pour revue manuelle
if (!empty($examples['filtered'])) {
    file_put_contents(REPORT_DIR . '/enrichment_filtered.json', json_encode($examples['filtered'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

// 8. RÉSUMÉ
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ ENRICHISSEMENT TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total exercices:              %4d\n", $stats['total']);
echo sprintf("✅ Extraits depuis Tips:      %4d (%.1f%%)\n",
    $stats['extracted_from_tips'],
    $stats['with_tips'] > 0 ? ($stats['extracted_from_tips'] / $stats['with_tips']) * 100 : 0
);
echo sprintf("🤖 Résolus par IA:            %4d\n", $stats['resolved_by_ai']);
echo sprintf("🔶 Filtrés (douteux):        %4d (%.1f%%)\n",
    $stats['filtered'],
    ($stats['filtered'] / $stats['total']) * 100
);
echo sprintf("❌ Échecs:                    %4d (%.1f%%)\n",
    $stats['failed'],
    ($stats['failed'] / $stats['total']) * 100
);
echo "═══════════════════════════════════════════════════════════════\n\n";

if (!$dryRun) {
    echo "📄 Fichiers générés:\n";
    echo "   • " . OUTPUT_FILE . "\n";
    echo "   • " . REPORT_DIR . "/enrichment_report.txt\n";
    if (file_exists(REPORT_DIR . '/enrichment_filtered.json')) {
        echo "   • " . REPORT_DIR . "/enrichment_filtered.json\n";
    }
    echo "\n";
}

echo "📋 Prochaine étape:\n";
if ($dryRun) {
    echo "   Lancer sans --dry-run pour enrichir réellement\n";
} else {
    echo "   Vérifier exercises_enriched.json puis importer en BDD\n";
}
echo "\n";
