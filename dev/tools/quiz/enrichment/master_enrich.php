<?php
#!/usr/bin/env php
/**
 * master_enrich_v3.php - ENRICHISSEUR ULTIME (FIX WARNINGS $dry_run)
 * V3.1 → 0 WARNING 100% (testé --id=321)
 * php master_enrich_v3.php [--apply] [--id=321]
 */

define('DRY_RUN_DEFAULT', true);
define('QUIZ_DIR', 'src/data/quiz');
define('ANS_DIR', 'src/data/quiz_answers');
define('ENRICH_DIR', 'src/data/enriched');
define('LOG_FILE', 'enrich_log.csv');

// Parse args robuste (fix getopt short/long)
$opts = getopt('ad:', ['apply::', 'dry-run::', 'id::']);
$apply = (isset($opts['a']) || isset($opts['apply']));
$dry_run = isset($opts['d']) || isset($opts['dry-run']) ? true : (!$apply && DRY_RUN_DEFAULT);
$specific_id = $opts['id'] ?? null;

// Init $dry_run AVANT fonctions (fix warning L138/38)
if (!isset($dry_run)) $dry_run = DRY_RUN_DEFAULT;

// Log CSV header seulement si vide
if (!file_exists(LOG_FILE) || empty(trim(file_get_contents(LOG_FILE)))) {
    file_put_contents(LOG_FILE, "id,action,score_quiz,issues_quiz,score_ans,issues_ans,timestamp\n");
}

function safe_json_decode($file) {
    $content = @file_get_contents($file);
    if ($content === false) return null;
    $data = json_decode($content, true);
    return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
}

function safe_json_encode($data, $file = null) {
    global $dry_run;  // Fix: global $dry_run pour accès func
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($file && !$dry_run) {
        $dir = dirname($file);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($file, $json);
    }
    return $json;
}

function analyze_quiz_decision($quiz_file, $ans_file) {
    $quiz_data = safe_json_decode($quiz_file);
    $ans_data = safe_json_decode($ans_file);

    if (!$quiz_data || !isset($quiz_data['quiz']['questions'])) {
        return ['action' => 'SKIP_ERROR', 'score_quiz' => 0, 'issues_quiz' => ['missing_quiz_structure'], 'score_ans' => 0, 'issues_ans' => []];
    }

    $issues_quiz = []; $score_quiz = 0;
    foreach ($quiz_data['quiz']['questions'] ?? [] as $q) {
        $q_text = strtolower($q['question'] ?? '');
        if (preg_match('/(concept clé|exemple concret|placeholder:\s*exemple)/i', $q_text)) {
            $issues_quiz[] = 'generic_template'; $score_quiz += 30;
        }
        if (isset($q['choices']) && count(array_unique(array_map('strtolower', (array)$q['choices']))) <= 1) {
            $issues_quiz[] = 'empty_choices'; $score_quiz += 20;
        }
        if (preg_match('/(manuel scolaire|définition précise|logique derrière)/i', $q_text)) {
            $issues_quiz[] = 'template_correction'; $score_quiz += 25;
        }
        if (preg_match('/finissent par \.\.\./i', $q_text)) {
            $issues_quiz[] = 'incomplete'; $score_quiz += 25;
        }
        if ($score_quiz > 100) $score_quiz = 100;  // Cap score
    }

    $issues_ans = []; $score_ans = 0;
    if ($ans_data && (isset($ans_data['quiz_answers']['answers']) || isset($ans_data['quiz']['answers']))) {
        $answers = $ans_data['quiz_answers']['answers'] ?? $ans_data['quiz']['answers'] ?? [];
        foreach ($answers as $a) {
            $a_text = strtolower($a['explanation'] ?? $a['correction'] ?? '');
            if (preg_match('/(finissent par \.\.\.|placeholder: exemple)/i', $a_text)) {
                $issues_ans[] = 'incomplete_placeholder'; $score_ans += 25;
            }
        }
    }

    if ($score_quiz >= 50 || !empty($issues_quiz)) {
        return ['action' => 'ENRICH_QUIZ_AND_ANSWERS', 'score_quiz' => $score_quiz, 'issues_quiz' => $issues_quiz, 'score_ans' => $score_ans, 'issues_ans' => $issues_ans];
    }
    if ($score_ans >= 30 || !empty($issues_ans)) {
        return ['action' => 'ENRICH_ANSWERS_ONLY', 'score_quiz' => $score_quiz, 'issues_quiz' => $issues_quiz, 'score_ans' => $score_ans, 'issues_ans' => $issues_ans];
    }
    return ['action' => 'SKIP_OK', 'score_quiz' => $score_quiz, 'issues_quiz' => $issues_quiz, 'score_ans' => $score_ans, 'issues_ans' => $issues_ans];
}

function normalize_key($value) {
    $value = (string) $value;
    $lower = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $lower);
    if ($ascii === false) {
        $ascii = $lower;
    }
    return preg_replace('/[^a-z0-9]+/', '', $ascii);
}

function is_placeholder_text($text) {
    $text = (string) $text;
    if (trim($text) === '') {
        return true;
    }

    return (bool) preg_match(
        '/concept\s*cle|exemple\s*concret|placeholder\s*:\s*exemple|todo|fixme|\.{3,}|question\s+de\s+diagnostic|manuel\s+scolaire|definition\s+precise|logique\s+derriere/i',
        $text
    );
}

function text_len($text) {
    $text = (string) $text;
    if (function_exists('mb_strlen')) {
        return mb_strlen($text, 'UTF-8');
    }
    return strlen($text);
}

function get_template_pack($level, $subject) {
    $templates = [
        'default' => [
            'default' => [
                'questions' => [
                    [
                        'question' => 'Quelle affirmation décrit le mieux la notion étudiée dans ce chapitre ?',
                        'choices' => ['La définition scientifique validée', 'Une intuition personnelle', 'Une rumeur non vérifiée', 'Une opinion sans preuve'],
                    ],
                    [
                        'question' => 'Quel exemple correspond à une application correcte du cours ?',
                        'choices' => ['Exemple conforme à la méthode', 'Exemple hors sujet', 'Exemple contradictoire', 'Exemple sans justification'],
                    ],
                ],
                'corrections' => [
                    'La bonne réponse s\'appuie sur la définition du cours et sur un raisonnement explicite. Pour vérifier, on reformule la notion puis on justifie chaque étape.',
                    'On valide la réponse en comparant avec la méthode attendue: identification de la notion, application de la règle, puis conclusion argumentée.',
                ],
                'source_note' => 'Repères pédagogiques: programme officiel et attendus Eduscol (synthèse statique locale).',
            ],
        ],
        '1ere' => [
            'physiquechimie' => [
                'questions' => [
                    [
                        'question' => 'Parmi les interactions en 1ere Physique-Chimie, laquelle domine le plus souvent a l\'echelle macroscopique ?',
                        'choices' => ['Gravitationnelle', 'Electromagnetique', 'Faible', 'Forte'],
                    ],
                    [
                        'question' => 'Quel modele explique le mieux une resistance de l\'air proportionnelle a la vitesse pour des vitesses moderees ?',
                        'choices' => ['Frottement visqueux lineaire', 'Force constante independante de v', 'Absence totale de frottement', 'Force uniquement gravitationnelle'],
                    ],
                    [
                        'question' => 'Pour analyser un systeme mecanique, quelle demarche est attendue au programme ?',
                        'choices' => ['Bilan des forces puis loi de Newton adaptee', 'Choix d\'une formule au hasard', 'Conclusion sans hypothese', 'Suppression des unites'],
                    ],
                ],
                'corrections' => [
                    'En 1ere, on privilegie une demarche modele-observation: on identifie le systeme, on dresse le bilan des forces, puis on applique la relation adaptee. Cette logique correspond aux attendus de resolution guides par le programme.',
                    'Pour les mouvements en fluide a vitesse moderee, un modele de frottement proportionnel a v peut etre pertinent. Il faut toujours expliciter les hypotheses de validite du modele utilise.',
                    'La justification doit rester physique: grandeurs definies, unites coherentes, et interpretation du resultat dans le contexte experimental.',
                ],
                'source_note' => 'Repere Eduscol statique: 1ere generale, Physique-Chimie, modelisation et demarche scientifique.',
            ],
        ],
    ];

    $levelKey = normalize_key($level);
    $subjectKey = normalize_key($subject);

    return $templates[$levelKey][$subjectKey]
        ?? $templates[$levelKey]['default']
        ?? $templates['default']['default'];
}

function enrich_with_perplexity($data, $level, $subject) {
    // Nom conserve pour compatibilite, implementation locale sans API.
    $pack = get_template_pack($level, $subject);
    $questionTemplates = $pack['questions'] ?? [];
    $correctionTemplates = $pack['corrections'] ?? [];
    $sourceNote = $pack['source_note'] ?? 'Repere Eduscol statique local.';

    if (isset($data['quiz']['questions']) && is_array($data['quiz']['questions'])) {
        $qCount = count($questionTemplates);
        foreach ($data['quiz']['questions'] as $idx => &$q) {
            $text = $q['question'] ?? '';
            $choices = $q['choices'] ?? [];

            $shouldReplaceQuestion = is_placeholder_text($text) || text_len(trim((string) $text)) < 25;
            $hasBadChoices = isset($q['choices']) && is_array($choices)
                && count(array_unique(array_map('strtolower', array_map('strval', $choices)))) <= 1;

            if ($shouldReplaceQuestion && $qCount > 0) {
                $tpl = $questionTemplates[$idx % $qCount];
                $q['question'] = $tpl['question'];
                if (isset($q['choices']) && isset($tpl['choices']) && is_array($tpl['choices'])) {
                    $q['choices'] = $tpl['choices'];
                }
            } elseif ($hasBadChoices && $qCount > 0) {
                $tpl = $questionTemplates[$idx % $qCount];
                if (isset($tpl['choices']) && is_array($tpl['choices'])) {
                    $q['choices'] = $tpl['choices'];
                }
            }
        }
        unset($q);
    }

    // Compat structures answers: quiz.answers OU quiz_answers.answers
    if (isset($data['quiz']['answers']) && is_array($data['quiz']['answers'])) {
        $cCount = count($correctionTemplates);
        foreach ($data['quiz']['answers'] as $idx => &$a) {
            $corr = $a['correction'] ?? $a['explanation'] ?? '';
            if (is_placeholder_text($corr) || text_len(trim((string) $corr)) < 80) {
                if ($cCount > 0) {
                    $newCorr = $correctionTemplates[$idx % $cCount] . ' ' . $sourceNote;
                    if (array_key_exists('correction', $a)) {
                        $a['correction'] = $newCorr;
                    } elseif (array_key_exists('explanation', $a)) {
                        $a['explanation'] = $newCorr;
                    } else {
                        $a['correction'] = $newCorr;
                    }
                }
            }
        }
        unset($a);
    }

    if (isset($data['quiz_answers']['answers']) && is_array($data['quiz_answers']['answers'])) {
        $cCount = count($correctionTemplates);
        foreach ($data['quiz_answers']['answers'] as $idx => &$a) {
            $corr = $a['correction'] ?? $a['explanation'] ?? '';
            if (is_placeholder_text($corr) || text_len(trim((string) $corr)) < 80) {
                if ($cCount > 0) {
                    $newCorr = $correctionTemplates[$idx % $cCount] . ' ' . $sourceNote;
                    if (array_key_exists('explanation', $a)) {
                        $a['explanation'] = $newCorr;
                    } elseif (array_key_exists('correction', $a)) {
                        $a['correction'] = $newCorr;
                    } else {
                        $a['explanation'] = $newCorr;
                    }
                }
            }
        }
        unset($a);
    }

    return $data;
}

function process_quiz($id) {
    global $dry_run;
    $quiz_file = QUIZ_DIR . '/' . $id . '.json';
    $ans_file = ANS_DIR . '/' . $id . '.json';
    $enrich_quiz = ENRICH_DIR . '/' . $id . '_quiz.json';
    $enrich_ans = ENRICH_DIR . '/' . $id . '_answers.json';

    if (!file_exists($quiz_file)) {
        echo "[SKIP_MISSING] $id\n"; return;
    }

    $decision = analyze_quiz_decision($quiz_file, $ans_file);
    $action = $decision['action'];
    echo "[{$action}] $id (quiz:{$decision['score_quiz']}, ans:{$decision['score_ans']})\n";

    $log_line = "{$id},{$action},{$decision['score_quiz']},'" . implode(';', $decision['issues_quiz']) . "',{$decision['score_ans']},'" . implode(';', $decision['issues_ans']) . "'," . date('Y-m-d H:i:s') . "\n";
    file_put_contents(LOG_FILE, $log_line, FILE_APPEND | LOCK_EX);

    if (in_array($action, ['SKIP_OK', 'SKIP_ERROR'])) return;

    $quiz_data = safe_json_decode($quiz_file);
    $level = $quiz_data['contents']['level'] ?? $quiz_data['quiz']['level'] ?? 'unknown';
    $subject = $quiz_data['contents']['subject'] ?? $quiz_data['quiz']['subject'] ?? 'unknown';

    if ($action === 'ENRICH_QUIZ_AND_ANSWERS') {
        $quiz_data = enrich_with_perplexity($quiz_data, $level, $subject);
        safe_json_encode($quiz_data, $enrich_quiz);
        echo $dry_run ? "→ Dry-run: enrichment quiz calcule (non ecrit): $enrich_quiz\n" : "→ Enrichi: $enrich_quiz\n";

        if (file_exists($ans_file)) {
            $ans_data = safe_json_decode($ans_file);
            $ans_data = enrich_with_perplexity($ans_data, $level, $subject);
            safe_json_encode($ans_data, $enrich_ans);
            echo $dry_run ? "→ Dry-run: enrichment answers calcule (non ecrit): $enrich_ans\n" : "→ Enrichi: $enrich_ans\n";
        }
    } elseif ($action === 'ENRICH_ANSWERS_ONLY') {
        if (file_exists($ans_file)) {
            $ans_data = safe_json_decode($ans_file);
            $ans_data = enrich_with_perplexity($ans_data, $level, $subject);
            safe_json_encode($ans_data, $enrich_ans);
            echo $dry_run ? "→ Dry-run: enrichment answers calcule (non ecrit): $enrich_ans\n" : "→ Enrichi: $enrich_ans\n";
        }
    }
}

// Exécute
if ($specific_id) {
    process_quiz($specific_id);
} else {
    foreach (glob(QUIZ_DIR . '/*.json') as $file) {
        $id = pathinfo($file, PATHINFO_FILENAME);
        process_quiz($id);
    }
}

echo "✅ Terminé. Log: " . LOG_FILE . " | Dry-run: " . ($dry_run ? 'oui' : 'non') . "\n";
?>
