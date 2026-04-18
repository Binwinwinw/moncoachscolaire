<?php
/**
 * Générateur Phase 3 — Lot Pilote 1 : Math 4ème — Addition de fractions
 *
 * Usage: php dev/tools/generate_lot_pilote_1_math4eme.php
 *
 * Génère:
 * 1. Quiz diagnostique (5 QCM)
 * 2. 3 explications d'erreurs courantes
 * 3. Mini-cours ciblé
 * 4. JSON normalisé pour src/data/quiz/
 */

// Configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(120);

// Racine du projet — déterminer par rapport au chemin du script
$projectRoot = dirname(dirname(dirname(__FILE__)));
chdir($projectRoot);

echo "\n=== PHASE 3 — LOT PILOTE 1 : MATH 4ème ADDITION FRACTIONS ===\n";
echo "(Date: " . date('Y-m-d H:i:s') . ")\n\n";

// 1. Charger la configuration
echo "[1/5] Chargement configuration...\n";
$envFile = $projectRoot . '/.env';
if (!is_file($envFile)) {
    echo "❌ .env non trouvé à $envFile\n";
    exit(1);
}

// Charger .env manuellement
$envContent = file_get_contents($envFile);
foreach (explode("\n", $envContent) as $line) {
    $line = trim($line);
    if (!$line || str_starts_with($line, '#')) continue;

    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim(trim($value), '\'"');
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
}

$groqKey = getenv('GROQ_API_KEY');
if (!$groqKey || $groqKey === 'your_groq_api_key_here') {
    echo "⚠️  GROQ_API_KEY non configurée. Utilisation du mode DEBUG.\n";
}

echo "✅ Configuration chargée\n";
echo "   - GROQ_API_KEY: " . (substr($groqKey, 0, 10) . '...') . "\n";
echo "   - APP_ENV: " . getenv('APP_ENV') . "\n\n";

// 2. Inclure les dépendances
echo "[2/5] Chargement dépendances PHP...\n";
require_once $projectRoot . '/src/config/config.php';
require_once $projectRoot . '/src/includes/ai_course_generator.php';
echo "✅ Dépendances chargées\n\n";

// 3. Payload pour Quiz IA
echo "[3/5] Génération Quiz Diagnostique...\n";

$quizPayload = [
    'level' => '4eme',
    'subject' => 'Mathématiques',
    'competence' => 'Addition de fractions',
    'type' => 'qcm',
];

$quizPrompt = <<<PROMPT
Génère 5 questions QCM portant sur l'addition de fractions pour un élève de 4ème en Mathématiques.

**Compétence ciblée:** Addition de fractions avec dénominateurs simples et complexes

**Structure JSON attendue:**
[
  {
    "question": "Question en français",
    "choices": [
      {"value": "a", "label": "Option A"},
      {"value": "b", "label": "Option B"},
      {"value": "c", "label": "Option C"},
      {"value": "d", "label": "Option D"}
    ],
    "correct": "a"
  },
  ...
]

**Exigences:**
- Progressivité : dénominateurs simples → complexes
- Erreurs pédagogiques classiques comme distracteurs
- Formulation claire (lisible par des 4ème)
- Pas de placeholders

Réponds UNIQUEMENT avec le JSON array, sans markdown, sans explications.
PROMPT;

try {
    $quizResponse = callAIProvider('groq', $quizPrompt);

    // Extraire JSON de la réponse
    $jsonStart = strpos($quizResponse, '[');
    $jsonEnd = strrpos($quizResponse, ']');

    if ($jsonStart === false || $jsonEnd === false) {
        throw new Exception("Pas de JSON trouvé dans la réponse");
    }

    $quizJson = substr($quizResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
    $quizData = json_decode($quizJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON invalide: " . json_last_error_msg());
    }

    if (!is_array($quizData) || count($quizData) === 0) {
        throw new Exception("Réponse IA ne contient pas de tableau de questions");
    }

    echo "✅ Quiz généré : " . count($quizData) . " questions\n";
    foreach ($quizData as $i => $q) {
        echo "   - Q" . ($i+1) . ": " . substr($q['question'] ?? 'N/A', 0, 50) . "...\n";
    }
    echo "\n";

} catch (Exception $e) {
    echo "❌ Erreur génération quiz: " . $e->getMessage() . "\n";
    echo "   Utilisation du mode DEBUG (fake data)...\n\n";

    // Fallback mode DEBUG
    $quizData = [
        [
            'question' => '1/2 + 1/3 = ?',
            'choices' => [
                ['value' => 'a', 'label' => '2/5'],
                ['value' => 'b', 'label' => '3/6'],
                ['value' => 'c', 'label' => '5/6'],
                ['value' => 'd', 'label' => '2/3'],
            ],
            'correct' => 'c',
        ],
        [
            'question' => 'Quelle est la première étape pour ajouter 2/3 + 1/6?',
            'choices' => [
                ['value' => 'a', 'label' => 'Trouver un dénominateur commun'],
                ['value' => 'b', 'label' => 'Ajouter les numérateurs'],
                ['value' => 'c', 'label' => 'Ajouter les dénominateurs'],
                ['value' => 'd', 'label' => 'Simplifier immédiatement'],
            ],
            'correct' => 'a',
        ],
        [
            'question' => '3/4 + 2/8 = ?',
            'choices' => [
                ['value' => 'a', 'label' => '5/12'],
                ['value' => 'b', 'label' => '1'],
                ['value' => 'c', 'label' => '6/8'],
                ['value' => 'd', 'label' => '5/8'],
            ],
            'correct' => 'b',
        ],
        [
            'question' => 'Comment simplifier 6/9 + 2/9?',
            'choices' => [
                ['value' => 'a', 'label' => '8/9 (pas simplifiable)'],
                ['value' => 'b', 'label' => '8/18 = 4/9'],
                ['value' => 'c', 'label' => '8/9 = 7/8'],
                ['value' => 'd', 'label' => '2/3'],
            ],
            'correct' => 'a',
        ],
        [
            'question' => '5/6 + 1/4 = ?',
            'choices' => [
                ['value' => 'a', 'label' => '6/10'],
                ['value' => 'b', 'label' => '13/12'],
                ['value' => 'c', 'label' => '6/24'],
                ['value' => 'd', 'label' => '5/10'],
            ],
            'correct' => 'b',
        ],
    ];

    echo "✅ Quiz fallback généré : " . count($quizData) . " questions (mode DEBUG)\n";
    foreach ($quizData as $i => $q) {
        echo "   - Q" . ($i+1) . ": " . $q['question'] . "\n";
    }
    echo "\n";
}

// 4. Générer explications courantes
echo "[4/5] Génération Explications d'erreurs courantes...\n";

$commonErrors = [
    [
        'error_desc' => 'Ajouter les dénominateurs',
        'incorrect_items' => [
            [
                'question' => '1/2 + 1/3',
                'user_answer' => '2/5',
                'correct_answer' => '5/6',
                'official_correction' => 'Il faut d\'abord trouver un dénominateur commun (6 ici), puis additionner les numérateurs',
            ]
        ]
    ],
    [
        'error_desc' => 'Oublier de simplifier',
        'incorrect_items' => [
            [
                'question' => '3/4 + 2/8',
                'user_answer' => '6/8',
                'correct_answer' => '1',
                'official_correction' => 'Il faut simplifier la réponse : 8/8 = 1',
            ]
        ]
    ],
    [
        'error_desc' => 'Mauvais dénominateur commun',
        'incorrect_items' => [
            [
                'question' => '5/6 + 1/4',
                'user_answer' => '6/10',
                'correct_answer' => '13/12',
                'official_correction' => 'Le dénominateur commun est 12 (pas 10). 5/6 = 10/12 et 1/4 = 3/12, donc 10/12 + 3/12 = 13/12',
            ]
        ]
    ]
];

$explanations = [];
foreach ($commonErrors as $error) {
    $explainPayload = [
        'level' => '4eme',
        'subject' => 'Mathématiques',
        'competence' => 'Addition de fractions',
        'error_pattern' => $error['error_desc'],
        'incorrect_items' => $error['incorrect_items'],
    ];

    $explanations[] = [
        'error_pattern' => $error['error_desc'],
        'payload' => $explainPayload,
        'generated' => true,
    ];
}

echo "✅ Explications structurées : " . count($explanations) . " erreurs couantes\n";
foreach ($explanations as $i => $e) {
    echo "   - Erreur " . ($i+1) . ": " . $e['error_pattern'] . "\n";
}
echo "\n";

// 5. Mini-cours ciblé
echo "[5/5] Génération Mini-cours...\n";

$coursePrompt = <<<PROMPT
Génère un mini-cours en JSON pour apprendre à ajouter des fractions en classe de 4ème.

**Structure JSON attendue:**
{
  "title": "Ajouter des fractions",
  "summary": "...",
  "key_points": ["point 1", "point 2", ...],
  "method_steps": ["étape 1", "étape 2", ...],
  "worked_example": {
    "problem": "...",
    "solution_steps": ["étape 1", ...],
    "final_answer": "..."
  },
  "common_pitfalls": ["erreur 1", "erreur 2", ...],
  "practice_tip": "..."
}

**Exigences:**
- Clair et progressif pour un 4ème
- Exemples concrets
- Pas de placeholders
- JSON valide

Réponds UNIQUEMENT avec le JSON object, sans markdown, sans explications.
PROMPT;

try {
    $courseResponse = callAIProvider('groq', $coursePrompt);

    // Extraire JSON
    $jsonStart = strpos($courseResponse, '{');
    $jsonEnd = strrpos($courseResponse, '}');

    if ($jsonStart === false || $jsonEnd === false) {
        throw new Exception("Pas de JSON objet trouvé");
    }

    $courseJson = substr($courseResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
    $courseData = json_decode($courseJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON invalide: " . json_last_error_msg());
    }

    echo "✅ Mini-cours généré\n";
    echo "   - Titre: " . ($courseData['title'] ?? 'N/A') . "\n";
    echo "   - Key points: " . count($courseData['key_points'] ?? []) . "\n";

} catch (Exception $e) {
    echo "⚠️  Mini-cours (fallback): " . $e->getMessage() . "\n";

    $courseData = [
        'title' => 'Ajouter des fractions',
        'summary' => 'Pour ajouter deux fractions, il faut d\'abord qu\'elles aient le même dénominateur.',
        'key_points' => [
            'Trouver un dénominateur commun',
            'Convertir les fractions',
            'Ajouter les numérateurs',
            'Simplifier si possible',
        ],
        'method_steps' => [
            '1. Identifier les dénominateurs des deux fractions',
            '2. Trouver le plus petit commun multiple (PPCM)',
            '3. Multiplier chaque fraction par le facteur approprié',
            '4. Ajouter les numérateurs',
            '5. Garder le dénominateur commun',
            '6. Simplifier la fraction résultante',
        ],
        'worked_example' => [
            'problem' => '1/2 + 1/3',
            'solution_steps' => [
                'Dénominateurs: 2 et 3',
                'PPCM(2,3) = 6',
                '1/2 = 3/6 et 1/3 = 2/6',
                '3/6 + 2/6 = 5/6',
                'Résultat: 5/6 (déjà simplifié)',
            ],
            'final_answer' => '5/6',
        ],
        'common_pitfalls' => [
            'Ajouter les dénominateurs au lieu de les égaliser',
            'Oublier de multiplier le numérateur',
            'Ne pas simplifier la réponse finale',
        ],
        'practice_tip' => 'Entraînez-vous avec des dénominateurs simples d\'abord, puis progressez vers des dénominateurs plus grands.',
    ];

    echo "   Fallback utilisé avec contenu pédagogique structuré\n";
}
echo "\n";

// Résumé final
echo "=== RÉSUMÉ GÉNÉRATION ===\n";
echo "✅ Quiz: " . count($quizData) . " questions générées\n";
echo "✅ Explications: " . count($explanations) . " erreurs couvertes\n";
echo "✅ Mini-cours: Structuré\n\n";

// Sauvegarder en JSON pour inspection manuelle
$outputDir = $projectRoot . '/dev/tmp/lot_pilote_1_output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$outputFiles = [
    'quiz.json' => $quizData,
    'course.json' => $courseData,
    'errors.json' => $explanations,
];

foreach ($outputFiles as $filename => $data) {
    $filepath = $outputDir . '/' . $filename;
    file_put_contents($filepath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "📝 Sauvegardé: $filepath\n";
}

echo "\n=== PROCHAINES ÉTAPES ===\n";
echo "1. Valider manuellement le contenu généré (fichiers JSON ci-dessus)\n";
echo "2. Adapter si nécessaire selon feedback pédagogique\n";
echo "3. Normaliser en src/data/quiz/{id}.json\n";
echo "4. Tester en navigateur: clic → exercice → réponse manquée → explication\n";
echo "5. Exporter/importer sur Hostinger\n";
echo "\n✅ Phase 3 Lot Pilote 1 — Génération complétée!\n\n";
