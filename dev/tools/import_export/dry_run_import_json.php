<?php
// dry_run_import_json.php
// Simule l'import JSON et affiche un rapport sans toucher la base

$dir = __DIR__ . '/../../exercices';
$files = [];
foreach ($argv as $i => $arg) {
    if ($i === 0) continue;
    $files[] = $arg;
}
if (empty($files)) {
    foreach (glob($dir . DIRECTORY_SEPARATOR . '*.json') as $f) $files[] = $f;
}
if (empty($files)) {
    echo "[INFO] Aucun fichier JSON à analyser. Placez-les dans /exercices ou passez-les en argument.\n";
    exit(0);
}

$totalFiles = count($files);
$totalExercises = 0;
$invalid = 0;
$skipped = 0;

foreach ($files as $f) {
    echo "Analyse: $f\n";
    $json = @json_decode(file_get_contents($f), true);
    if (!$json) { echo "  - JSON invalide\n"; $invalid++; continue; }
    $level = $json['level'] ?? 'N/A';
    $subject = $json['subject'] ?? 'N/A';
    $exs = $json['exercises'] ?? [];
    echo "  - Niveau: $level, Matière: $subject, Exercices: " . count($exs) . "\n";
    foreach ($exs as $i => $ex) {
        $totalExercises++;
        // quick sanity checks
        if (empty($ex['title']) || empty($ex['content'])) {
            echo "    - Exercice #" . ($i+1) . " -> manquant titre ou contenu (skip)\n";
            $skipped++;
            continue;
        }
    }
}

echo "\n=== RAPPORT DRY-RUN ===\n";
echo "Fichiers analysés: $totalFiles\n";
echo "Exercices trouvés: $totalExercises\n";
echo "Exercices sautés (manquants): $skipped\n";
echo "Fichiers JSON invalides: $invalid\n";
exit($invalid || !$totalFiles ? 1 : 0);
