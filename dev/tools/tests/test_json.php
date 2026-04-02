<?php
$data = json_decode(file_get_contents(__DIR__ . '/pdf_data.json'), true);

echo "Keys: " . implode(', ', array_keys($data)) . "\n";
echo "Data keys: " . implode(', ', array_keys($data['data'])) . "\n";

foreach ($data['data'] as $level => $subjects) {
    echo "\n$level:\n";
    foreach ($subjects as $subject => $exercises) {
        echo "  $subject: " . count($exercises) . " exercices\n";
        if (count($exercises) > 0) {
            $first = $exercises[0];
            echo "    Sample: " . $first['numero'] . " - " . substr($first['titre'], 0, 40) . "\n";
            echo "    Reponse: " . substr($first['reponse'], 0, 50) . "\n";
        }
    }
}
