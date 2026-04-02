<?php
/**
 * ANALYSE DES EXERCICES SANS RÉPONSE
 *
 * Compare :
 * 1. Base de données actuelle (combien ont Answer = NULL ou "")
 * 2. Fichier avant nettoyage (exercises_final_deduplicated.json)
 * 3. Fichier après nettoyage (exercises_stage2_cleaned.json)
 *
 * Identifie :
 * - Les 127 exercices de différence
 * - Les causes (Answer = "Array", Answer = "", Answer déjà NULL)
 * - Les exercices qui avaient une réponse valide mais ont été mis à NULL
 */

// Configuration
define('PROJECT_ROOT', dirname(__DIR__, 3));
define('FILE_BEFORE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_final_deduplicated.json');
define('FILE_AFTER', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_stage2_cleaned.json');
define('REPORT_DIR', PROJECT_ROOT . '/dev/reports');

require_once PROJECT_ROOT . '/src/database/connection.php';

if (!is_dir(REPORT_DIR)) {
    mkdir(REPORT_DIR, 0755, true);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  ANALYSE DES EXERCICES SANS RÉPONSE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. CONNEXION BDD
echo "🔌 Connexion à la base de données...\n";

try {
    global $pdo;

    if (!isset($pdo)) {
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'moncoachscolaire';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';

        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    echo "✅ Connecté\n\n";
} catch (PDOException $e) {
    die("❌ ERREUR BDD : " . $e->getMessage() . "\n");
}

// 2. CHARGEMENT DES FICHIERS
echo "📄 Chargement des fichiers...\n";

$beforeJson = file_get_contents(FILE_BEFORE);
$beforeExercises = json_decode($beforeJson, true);

$afterJson = file_get_contents(FILE_AFTER);
$afterExercises = json_decode($afterJson, true);

echo "✅ Avant nettoyage: " . count($beforeExercises) . " exercices\n";
echo "✅ Après nettoyage: " . count($afterExercises) . " exercices\n\n";

// 3. ANALYSE BDD
echo "📊 Analyse de la base de données...\n";

$dbStats = [
    'total' => 0,
    'answer_null' => 0,
    'answer_empty' => 0,
    'answer_array' => 0,
    'answer_valid' => 0,
];

$stmt = $pdo->query("SELECT Identifier, Answer FROM exercises");
$dbExercises = [];

while ($row = $stmt->fetch()) {
    $dbExercises[$row['Identifier']] = $row['Answer'];
    $dbStats['total']++;

    if (is_null($row['Answer'])) {
        $dbStats['answer_null']++;
    } elseif (trim($row['Answer']) === '') {
        $dbStats['answer_empty']++;
    } elseif ($row['Answer'] === 'Array') {
        $dbStats['answer_array']++;
    } else {
        $dbStats['answer_valid']++;
    }
}

echo "✅ Base de données:\n";
echo "   Total:             " . $dbStats['total'] . "\n";
echo "   Answer = NULL:     " . $dbStats['answer_null'] . "\n";
echo "   Answer = '':       " . $dbStats['answer_empty'] . "\n";
echo "   Answer = 'Array':  " . $dbStats['answer_array'] . "\n";
echo "   Answer valide:     " . $dbStats['answer_valid'] . "\n\n";

// 4. INDEXATION DES FICHIERS
echo "📊 Indexation des fichiers...\n";

$beforeIndex = [];
foreach ($beforeExercises as $ex) {
    $beforeIndex[$ex['Identifier']] = $ex['Answer'] ?? null;
}

$afterIndex = [];
foreach ($afterExercises as $ex) {
    $afterIndex[$ex['Identifier']] = $ex['Answer'] ?? null;
}

echo "✅ Fichiers indexés\n\n";

// 5. COMPARAISON
echo "🔍 Comparaison en cours...\n";

$categories = [
    'already_null_in_db' => [],        // Déjà NULL dans la BDD
    'already_null_before' => [],       // Déjà NULL avant nettoyage
    'array_to_null' => [],              // "Array" → NULL
    'empty_to_null' => [],              // "" → NULL
    'valid_to_null' => [],              // Valide → NULL (PROBLÈME!)
    'not_in_files' => [],               // Dans BDD mais pas dans les fichiers
];

$stats = [
    'total_null_after' => 0,
    'already_null_in_db' => 0,
    'already_null_before' => 0,
    'array_to_null' => 0,
    'empty_to_null' => 0,
    'valid_to_null' => 0,
    'not_in_files' => 0,
];

// Parcourir le fichier après nettoyage
foreach ($afterExercises as $ex) {
    $identifier = $ex['Identifier'];
    $answerAfter = $ex['Answer'] ?? null;

    if (is_null($answerAfter)) {
        $stats['total_null_after']++;

        $answerBefore = $beforeIndex[$identifier] ?? 'NOT_FOUND';
        $answerDb = $dbExercises[$identifier] ?? 'NOT_FOUND';

        // Catégoriser
        if ($answerDb === null) {
            $stats['already_null_in_db']++;
            $categories['already_null_in_db'][] = [
                'identifier' => $identifier,
                'before' => $answerBefore,
                'db' => 'NULL',
                'after' => 'NULL',
            ];
        } elseif ($answerBefore === null) {
            $stats['already_null_before']++;
            $categories['already_null_before'][] = [
                'identifier' => $identifier,
                'before' => 'NULL',
                'db' => $answerDb,
                'after' => 'NULL',
            ];
        } elseif ($answerBefore === 'Array') {
            $stats['array_to_null']++;
            $categories['array_to_null'][] = [
                'identifier' => $identifier,
                'before' => 'Array',
                'db' => $answerDb,
                'after' => 'NULL',
            ];
        } elseif (is_string($answerBefore) && trim($answerBefore) === '') {
            $stats['empty_to_null']++;
            $categories['empty_to_null'][] = [
                'identifier' => $identifier,
                'before' => '""',
                'db' => $answerDb,
                'after' => 'NULL',
            ];
        } elseif (is_string($answerBefore) && strlen($answerBefore) > 0) {
            $stats['valid_to_null']++;
            $categories['valid_to_null'][] = [
                'identifier' => $identifier,
                'before' => substr($answerBefore, 0, 50),
                'db' => is_string($answerDb) ? substr($answerDb, 0, 50) : $answerDb,
                'after' => 'NULL',
            ];
        } else {
            $stats['not_in_files']++;
            $categories['not_in_files'][] = [
                'identifier' => $identifier,
                'before' => $answerBefore,
                'db' => $answerDb,
                'after' => 'NULL',
            ];
        }
    }
}

echo "✅ Comparaison terminée\n\n";

// 6. GÉNÉRATION DU RAPPORT
echo "📊 Génération du rapport...\n";

$txt = "═══════════════════════════════════════════════════════════════\n";
$txt .= "  ANALYSE DES EXERCICES SANS RÉPONSE\n";
$txt .= "═══════════════════════════════════════════════════════════════\n\n";
$txt .= "Date : " . date('Y-m-d H:i:s') . "\n\n";

$txt .= "RÉSUMÉ\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("Exercices sans réponse (après nettoyage): %4d (%.1f%%)\n\n",
    $stats['total_null_after'],
    ($stats['total_null_after'] / count($afterExercises)) * 100
);

$txt .= "RÉPARTITION PAR CAUSE\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("1. Déjà NULL dans la BDD:        %4d (%.1f%%)\n",
    $stats['already_null_in_db'],
    ($stats['already_null_in_db'] / $stats['total_null_after']) * 100
);
$txt .= sprintf("2. Déjà NULL avant nettoyage:    %4d (%.1f%%)\n",
    $stats['already_null_before'],
    ($stats['already_null_before'] / $stats['total_null_after']) * 100
);
$txt .= sprintf("3. 'Array' → NULL:               %4d (%.1f%%)\n",
    $stats['array_to_null'],
    ($stats['array_to_null'] / $stats['total_null_after']) * 100
);
$txt .= sprintf("4. Chaîne vide → NULL:           %4d (%.1f%%)\n",
    $stats['empty_to_null'],
    ($stats['empty_to_null'] / $stats['total_null_after']) * 100
);
$txt .= sprintf("5. 🔴 VALIDE → NULL (ERREUR):    %4d (%.1f%%)\n",
    $stats['valid_to_null'],
    ($stats['valid_to_null'] / $stats['total_null_after']) * 100
);
$txt .= sprintf("6. Autre/Non trouvé:             %4d (%.1f%%)\n\n",
    $stats['not_in_files'],
    ($stats['not_in_files'] / $stats['total_null_after']) * 100
);

$txt .= "EXPLICATION DES 483 EXERCICES\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= "356 attendus (Answer = 'Array' non récupérables)\n";
$txt .= "+ " . ($stats['already_null_in_db'] + $stats['already_null_before']) . " déjà NULL\n";
$txt .= "+ " . $stats['empty_to_null'] . " chaînes vides\n";
$txt .= "+ " . $stats['valid_to_null'] . " réponses valides perdues (à investiguer)\n";
$txt .= "+ " . $stats['not_in_files'] . " autres\n";
$txt .= "= " . $stats['total_null_after'] . " total\n\n";

// Exemples par catégorie
if (count($categories['valid_to_null']) > 0) {
    $txt .= "🔴 PROBLÈME : RÉPONSES VALIDES PERDUES (" . count($categories['valid_to_null']) . " cas)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach (array_slice($categories['valid_to_null'], 0, 20) as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "    AVANT:  " . $ex['before'] . "\n";
        $txt .= "    BDD:    " . $ex['db'] . "\n";
        $txt .= "    APRÈS:  NULL\n\n";
    }
}

if (count($categories['array_to_null']) > 0) {
    $txt .= "EXEMPLES : 'Array' → NULL (" . count($categories['array_to_null']) . " cas)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach (array_slice($categories['array_to_null'], 0, 10) as $i => $ex) {
        $txt .= sprintf("%2d. %s (Answer = 'Array' non récupérable)\n", $i + 1, $ex['identifier']);
    }
    $txt .= "\n";
}

if (count($categories['already_null_in_db']) > 0) {
    $txt .= "EXEMPLES : Déjà NULL dans la BDD (" . count($categories['already_null_in_db']) . " cas)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach (array_slice($categories['already_null_in_db'], 0, 10) as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
    }
    $txt .= "\n";
}

if (count($categories['empty_to_null']) > 0) {
    $txt .= "EXEMPLES : Chaîne vide → NULL (" . count($categories['empty_to_null']) . " cas)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach (array_slice($categories['empty_to_null'], 0, 10) as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
    }
    $txt .= "\n";
}

file_put_contents(REPORT_DIR . '/answer_null_analysis.txt', $txt);

// JSON détaillé
$reportJson = [
    'timestamp' => date('Y-m-d H:i:s'),
    'statistics' => $stats,
    'categories' => [
        'already_null_in_db' => count($categories['already_null_in_db']),
        'already_null_before' => count($categories['already_null_before']),
        'array_to_null' => count($categories['array_to_null']),
        'empty_to_null' => count($categories['empty_to_null']),
        'valid_to_null' => count($categories['valid_to_null']),
        'not_in_files' => count($categories['not_in_files']),
    ],
    'details' => $categories,
];

file_put_contents(
    REPORT_DIR . '/answer_null_analysis.json',
    json_encode($reportJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// 7. RÉSUMÉ CONSOLE
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ ANALYSE TERMINÉE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total sans réponse:          %4d (%.1f%%)\n\n",
    $stats['total_null_after'],
    ($stats['total_null_after'] / count($afterExercises)) * 100
);

echo "RÉPARTITION PAR CAUSE\n";
echo "───────────────────────────────────────────────────────────────\n";
echo sprintf("1. Déjà NULL dans BDD:       %4d\n", $stats['already_null_in_db']);
echo sprintf("2. Déjà NULL avant:          %4d\n", $stats['already_null_before']);
echo sprintf("3. 'Array' → NULL:           %4d\n", $stats['array_to_null']);
echo sprintf("4. Chaîne vide → NULL:       %4d\n", $stats['empty_to_null']);
echo sprintf("5. 🔴 VALIDE → NULL:         %4d ← PROBLÈME!\n", $stats['valid_to_null']);
echo sprintf("6. Autre:                    %4d\n", $stats['not_in_files']);
echo "═══════════════════════════════════════════════════════════════\n\n";

if ($stats['valid_to_null'] > 0) {
    echo "🔴 ATTENTION : " . $stats['valid_to_null'] . " réponses valides ont été perdues!\n";
    echo "   Consulte le rapport pour les détails.\n\n";
}

echo "📄 Rapports générés:\n";
echo "   • " . REPORT_DIR . "/answer_null_analysis.txt\n";
echo "   • " . REPORT_DIR . "/answer_null_analysis.json\n\n";
