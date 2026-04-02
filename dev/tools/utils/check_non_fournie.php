<?php
$data = json_decode(file_get_contents(__DIR__ . '/pdf_data.json'), true);
$empty = 0;
echo "Exercices sans réponse (Non fournie):\n";
foreach ($data['data'] as $level => $subjects) {
    foreach ($subjects as $subject => $exercises) {
        foreach ($exercises as $ex) {
            if ($ex['reponse'] === 'Non fournie') {
                $empty++;
                echo "[$level][$subject] #{$ex['numero']} {$ex['titre']}\n";
            }
        }
    }
}
echo "\nTotal: $empty\n";
