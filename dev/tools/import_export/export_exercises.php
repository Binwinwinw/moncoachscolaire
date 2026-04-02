<?php
/**
 * Export des exercices en CSV pour audit/backup
 */
require_once __DIR__ . '/../db/connection.php';

$format = $argv[1] ?? 'csv'; // csv, json, sql
$output_file = __DIR__ . "/../exercices/export_" . date('YmdHis') . ".{$format}";

echo "Export en cours vers $format...\n";

$stmt = $pdo->query(
    "SELECT Id, Subject, Level, Title, Content, Answer, is_active 
     FROM exercises 
     ORDER BY Level, Subject, Title"
);

$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'csv') {
    exportCsv($exercises, $output_file);
} elseif ($format === 'json') {
    exportJson($exercises, $output_file);
} elseif ($format === 'sql') {
    exportSql($exercises, $output_file);
}

echo "✓ Export terminé: $output_file\n";
echo "  Exercices exportés: " . count($exercises) . "\n";

function exportCsv($data, $file) {
    $fp = fopen($file, 'w');
    
    // Header
    $headers = array_keys($data[0]);
    fputcsv($fp, $headers, ',', '"', '\\');
    
    // Rows
    foreach ($data as $row) {
        fputcsv($fp, array_values($row), ',', '"', '\\');
    }
    
    fclose($fp);
}

function exportJson($data, $file) {
    file_put_contents(
        $file,
        json_encode(['exercises' => $data], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

function exportSql($data, $file) {
    $sql = "-- Export des exercices - " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- Total: " . count($data) . " exercices\n\n";
    
    foreach ($data as $ex) {
        $subject = addslashes($ex['Subject']);
        $level = addslashes($ex['Level']);
        $title = addslashes($ex['Title']);
        $content = addslashes($ex['Content']);
        $answer = addslashes($ex['Answer']);
        $active = $ex['is_active'];
        
        $sql .= "INSERT INTO exercises (Subject, Level, Title, Content, Answer, is_active) VALUES ('$subject', '$level', '$title', '$content', '$answer', $active);\n";
    }
    
    file_put_contents($file, $sql);
}
