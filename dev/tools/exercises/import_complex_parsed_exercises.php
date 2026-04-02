<?php
/**
 * import_complex_parsed_exercises.php — MonCoachScolaire
 * Importe les exercices complexes parsés dans la BDD
 * Usage : php import_complex_parsed_exercises.php [--dry-run]
 */

require_once __DIR__ . '/../../../db/connection.php';
if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "❌ Connexion à la base de données impossible.\n");
    exit(1);
}

$dryRun = in_array('--dry-run', $argv);
$inputPath = __DIR__ . '/../../../dev/db/json/schema/exercices/complex_exercises_parsed.json';
$summaryPath = __DIR__ . '/../../../dev/reports/import_complex_summary.txt';
$detailsPath = __DIR__ . '/../../../dev/reports/import_complex_details.json';
$partialLogPath = __DIR__ . '/../../../dev/reports/import_complex_partials.json';

if (!is_file($inputPath)) {
    fwrite(STDERR, "❌ Fichier d'entrée introuvable : $inputPath\n");
    exit(1);
}

$data = json_decode(file_get_contents($inputPath), true);
if (!$data || !isset($data['exercises'])) {
    fwrite(STDERR, "❌ Fichier JSON d'entrée invalide.\n");
    exit(1);
}
$exercises = $data['exercises'];
$total = count($exercises);
$updated = $partials = $errors = 0;
$details = [];
$partialsLog = [];

// Statistiques avant
$simpleBefore = $multiBefore = 0;
$stmt = $pdo->query("SELECT COUNT(*) as nb, structure_type FROM exercises GROUP BY structure_type");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['structure_type'] === 'multi-parties') $multiBefore = $row['nb'];
    else $simpleBefore += $row['nb'];
}

foreach ($exercises as $ex) {
    $id = $ex['id'];
    $status = $ex['parsing_status'];
    $support = trim($ex['support_text'] ?? '');
    $instruction = trim($ex['instruction'] ?? '');
    $sub_questions = $ex['sub_questions'] ?? [];
    $pattern = '';
    // Détection du pattern principal (par la première question)
    if (isset($sub_questions[0]['question'])) {
        if (preg_match('/^Question ?\d+/', $sub_questions[0]['question'])) $pattern = 'Question X :';
        elseif (preg_match('/^\d+\)/', $sub_questions[0]['question'])) $pattern = 'X)';
        elseif (preg_match('/^\d+\./', $sub_questions[0]['question'])) $pattern = 'X.';
        elseif (preg_match('/^[a-z]\)/i', $sub_questions[0]['question'])) $pattern = 'a)';
        else $pattern = 'autre';
    }
    if ($status === 'success') {
        // Validation
        $valid = true;
        if (empty($support) || count($sub_questions) < 2) $valid = false;
        $subqJson = json_encode($sub_questions, JSON_UNESCAPED_UNICODE);
        if ($subqJson === false) $valid = false;
        if (!$valid) {
            $errors++;
            $details[] = [ 'id' => $id, 'error' => 'Validation échouée', 'support' => mb_substr($support,0,100) ];
            continue;
        }
        if (!$dryRun) {
            $sql = "UPDATE exercises SET structure_type = 'multi-parties', pattern_detected = ?, Content = ?, Instruction = ?, sub_questions = ?, Answer = NULL, Choices = NULL, updated_at = NOW() WHERE Id = ?";
            $stmt = $pdo->prepare($sql);
            $ok = $stmt->execute([$pattern, $support, $instruction, $subqJson, $id]);
            if (!$ok) {
                $errors++;
                $details[] = [ 'id' => $id, 'error' => 'Échec UPDATE', 'pdo_error' => $stmt->errorInfo() ];
                continue;
            }
        }
        $updated++;
        $details[] = [ 'id' => $id, 'status' => 'updated', 'pattern' => $pattern, 'support_excerpt' => mb_substr($support,0,80) ];
    } elseif ($status === 'partial') {
        // Marquer processed=1 pour révision
        if (!$dryRun) {
            $sql = "UPDATE exercises SET processed = 1, updated_at = NOW() WHERE Id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
        }
        $partials++;
        $partialsLog[] = [ 'id' => $id, 'reason' => 'Parsing partiel', 'support_excerpt' => mb_substr($support,0,80) ];
    }
}

// Statistiques après
$simpleAfter = $multiAfter = 0;
$stmt = $pdo->query("SELECT COUNT(*) as nb, structure_type FROM exercises GROUP BY structure_type");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['structure_type'] === 'multi-parties') $multiAfter = $row['nb'];
    else $simpleAfter += $row['nb'];
}

// Rapport TXT
$txt = "Import exercices complexes — ".date('Y-m-d H:i:s')."\n";
$txt .= ($dryRun ? "[MODE DRY-RUN]\n" : "");
$txt .= "Total à traiter : $total\n";
$txt .= "Mis à jour avec succès : $updated\n";
$txt .= "Marqués pour révision (partiels) : $partials\n";
$txt .= "Erreurs : $errors\n\n";
$txt .= "Avant : Exercices simples : $simpleBefore | multi-parties : $multiBefore\n";
$txt .= "Après : Exercices simples : $simpleAfter | multi-parties : $multiAfter\n";
file_put_contents($summaryPath, $txt);

// Détails JSON
file_put_contents($detailsPath, json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents($partialLogPath, json_encode($partialsLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Console
printf("✅ Exercices traités : %d\n", $total);
printf("✅ Mis à jour avec succès : %d\n", $updated);
printf("⚠️ Marqués pour révision (partiels) : %d\n", $partials);
printf("❌ Erreurs : %d\n", $errors);
printf("Avant/Après :\n- Exercices simples : %d → %d\n- Exercices multi-parties : %d → %d\n", $simpleBefore, $simpleAfter, $multiBefore, $multiAfter);
printf("\nFichiers générés :\n- %s\n- %s\n- %s\n", $summaryPath, $detailsPath, $partialLogPath);
