<?php
/**
 * AUDIT & EXTRACTION — 1662 Quiz existants
 * Classifie par level/subject/notion et génère un rapport de "lots de compétences"
 *
 * Usage: php dev/tools/audit_quiz_stock.php
 */

$projectRoot = dirname(dirname(dirname(__FILE__)));
chdir($projectRoot);

echo "\n=== AUDIT STOCK QUIZ EXISTANT ===\n";
echo "(Classification par niveau/matière/notion)\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Dossiers source
$quizDir = $projectRoot . '/src/data/quiz';
$answersDir = $projectRoot . '/src/data/quiz_answers';

if (!is_dir($quizDir)) {
    echo "❌ Dossier $quizDir non trouvé\n";
    exit(1);
}

echo "[1/3] Scanning 1662 quiz...\n";

// Structure pour stocker l'inventory
$inventory = [
    'total' => 0,
    'by_level' => [],
    'by_subject' => [],
    'by_competence' => [],  // level + subject + notion = competence
    'errors' => [],
];

// Scan tous les quiz
$quizFiles = array_filter(scandir($quizDir), fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'json');
echo "   Trouvé " . count($quizFiles) . " fichiers JSON\n\n";

$processedCount = 0;
foreach ($quizFiles as $filename) {
    $processedCount++;

    // Progress toutes les 100
    if ($processedCount % 100 === 0) {
        echo "   ... traité $processedCount fichiers\n";
    }

    $filepath = $quizDir . '/' . $filename;
    $quizId = pathinfo($filename, PATHINFO_FILENAME);

    // Lire le quiz
    $content = file_get_contents($filepath);
    $quizData = json_decode($content, true);

    if ($quizData === null) {
        $inventory['errors'][] = "JSON invalide: $filename";
        continue;
    }

    // Extraire les métadonnées
    $level = $quizData['contents']['level'] ?? $quizData['quiz']['level'] ?? null;
    $subject = $quizData['contents']['subject'] ?? $quizData['quiz']['subject'] ?? null;
    $title = $quizData['contents']['title'] ?? $quizData['quiz']['title'] ?? 'N/A';

    // Extraire les notions
    $notions = [];
    if (isset($quizData['exercisenotion']) && is_array($quizData['exercisenotion'])) {
        foreach ($quizData['exercisenotion'] as $n) {
            if (isset($n['notion'])) {
                $notions[] = $n['notion'];
            }
        }
    }

    // Normaliser level et subject
    $level = $level ? trim($level) : 'UNKNOWN';
    $subject = $subject ? trim($subject) : 'UNKNOWN';
    $notions = array_filter($notions);

    // Accumuler
    if (!isset($inventory['by_level'][$level])) {
        $inventory['by_level'][$level] = 0;
    }
    $inventory['by_level'][$level]++;

    if (!isset($inventory['by_subject'][$subject])) {
        $inventory['by_subject'][$subject] = 0;
    }
    $inventory['by_subject'][$subject]++;

    // Pour chaque notion, créer une clé "competence"
    $competenceKey = $level . '|' . $subject;
    if (!isset($inventory['by_competence'][$competenceKey])) {
        $inventory['by_competence'][$competenceKey] = [
            'level' => $level,
            'subject' => $subject,
            'quizzes' => [],
            'notions' => [],
        ];
    }

    $inventory['by_competence'][$competenceKey]['quizzes'][] = [
        'id' => $quizId,
        'title' => $title,
        'notions' => $notions,
    ];

    // Ajouter les notions
    foreach ($notions as $notion) {
        if (!in_array($notion, $inventory['by_competence'][$competenceKey]['notions'])) {
            $inventory['by_competence'][$competenceKey]['notions'][] = $notion;
        }
    }

    $inventory['total']++;
}

echo "\n✅ Scan complet : $processedCount quiz traités\n\n";

// Affichage du rapport
echo "[2/3] Générant rapport...\n\n";

echo "=== SOMMAIRE ===\n";
echo "Total quiz: " . $inventory['total'] . "\n";
echo "Niveaux trouvés: " . count($inventory['by_level']) . "\n";
echo "Matières trouvées: " . count($inventory['by_subject']) . "\n";
echo "Combinaisons level+matière: " . count($inventory['by_competence']) . "\n";
if (count($inventory['errors']) > 0) {
    echo "Fichiers avec erreurs: " . count($inventory['errors']) . "\n";
}
echo "\n";

// Par niveau
echo "=== DISTRIBUTION PAR NIVEAU ===\n";
ksort($inventory['by_level']);
foreach ($inventory['by_level'] as $level => $count) {
    echo "  $level: $count quiz\n";
}
echo "\n";

// Par matière
echo "=== DISTRIBUTION PAR MATIÈRE ===\n";
arsort($inventory['by_subject']);
foreach ($inventory['by_subject'] as $subject => $count) {
    echo "  $subject: $count quiz\n";
}
echo "\n";

// Par competence (level + subject)
echo "=== LOTS DE COMPÉTENCES (Level + Matière) ===\n";
usort($inventory['by_competence'], fn($a, $b) =>
    strcmp($a['level'], $b['level']) ?: strcmp($a['subject'], $b['subject'])
);

foreach ($inventory['by_competence'] as $competenceKey => $competenceData) {
    $level = $competenceData['level'];
    $subject = $competenceData['subject'];
    $quizCount = count($competenceData['quizzes']);
    $notionCount = count($competenceData['notions']);

    echo "\n📚 [$level] $subject — $quizCount quiz, $notionCount notions\n";
    echo "   Notions couvertes:\n";

    foreach ($competenceData['notions'] as $notion) {
        echo "     • $notion\n";
    }

    // Top 3 quizzes pour ce lot
    echo "   Exemples de quiz:\n";
    for ($i = 0; $i < min(3, count($competenceData['quizzes'])); $i++) {
        $quiz = $competenceData['quizzes'][$i];
        echo "     - (ID " . $quiz['id'] . ") " . substr($quiz['title'], 0, 60) . "...\n";
    }
}

echo "\n";

// Sauvegarder le rapport
$reportDir = $projectRoot . '/dev/tmp/quiz_audit';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

// Rapport JSON complet
$reportFile = $reportDir . '/quiz_inventory_' . date('Ymd_His') . '.json';
file_put_contents($reportFile, json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Rapport CSV pour importation facile
$csvFile = $reportDir . '/quiz_competence_summary.csv';
$csvHandle = fopen($csvFile, 'w');
fputcsv($csvHandle, ['Level', 'Subject', 'Quiz Count', 'Notions', 'Quiz IDs Sample'], ';');

foreach ($inventory['by_competence'] as $competenceData) {
    $level = $competenceData['level'];
    $subject = $competenceData['subject'];
    $quizCount = count($competenceData['quizzes']);
    $notions = implode(' | ', array_slice($competenceData['notions'], 0, 5));
    $sampleIds = implode(', ', array_map(fn($q) => $q['id'], array_slice($competenceData['quizzes'], 0, 5)));

    fputcsv($csvHandle, [$level, $subject, $quizCount, $notions, $sampleIds], ';');
}
fclose($csvHandle);

echo "[3/3] Rapports sauvegardés :\n";
echo "  📊 JSON: $reportFile\n";
echo "  📋 CSV : $csvFile\n";
echo "\n";

echo "=== PROCHAINES ÉTAPES ===\n";
echo "1. Examiner le rapport CSV pour identifier les lots à consolider\n";
echo "2. Pour chaque lot (Level + Subject):\n";
echo "   - Fusionner les 1662 quiz par niveau/matière\n";
echo "   - Créer des listes de quiz par compétence/notion\n";
echo "   - Sauvegarder en src/data/competence_lots/\n";
echo "3. Consolider quiz + answers en une seule structure indexée\n";
echo "\n✅ Audit terminé!\n\n";
