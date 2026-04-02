<?php
/**
 * Import des données JSON extraites du PDF
 */

require_once __DIR__ . '/../db/connection.php';

$json_file = __DIR__ . '/pdf_data.json';
echo "JSON file check: " . (file_exists($json_file) ? "OK" : "MISSING") . "\n";

if (!file_exists($json_file)) {
    die("❌ Fichier JSON non trouvé: $json_file\n");
}

$data = json_decode(file_get_contents($json_file), true);
echo "JSON parsed: " . (is_array($data) ? "OK (" . count($data) . " keys)" : "FAILED") . "\n";

if (!$data) {
    die("❌ Erreur lors du parsing JSON\n");
}

try {
    $inserted = 0;
    $errors = [];
    
    foreach ($data['data'] as $level => $subjects) {
        foreach ($subjects as $subject => $exercises) {
            foreach ($exercises as $ex) {
                // Vérifier si exercice existe déjà (duplication)
                $check = $pdo->prepare(
                    "SELECT Id FROM exercises WHERE Level = ? AND Subject = ? AND Title = ?"
                );
                $check->execute([$level, $subject, $ex['titre']]);
                
                if ($check->rowCount() > 0) {
                    // Mise à jour SEULEMENT si réponse vide en base ET réponse fournie dans PDF
                    if ($ex['reponse'] !== 'Non fournie') {
                        $update = $pdo->prepare(
                            "UPDATE exercises SET Answer = ? 
                             WHERE Level = ? AND Subject = ? AND Title = ? AND (Answer IS NULL OR Answer = '')"
                        );
                        $update->execute([
                            $ex['reponse'],
                            $level,
                            $subject,
                            $ex['titre']
                        ]);
                    }
                    // Mise à jour du contenu toujours (s'il est fourni)
                    if (!empty($ex['contenu'])) {
                        $updateContent = $pdo->prepare(
                            "UPDATE exercises SET Content = ? 
                             WHERE Level = ? AND Subject = ? AND Title = ?"
                        );
                        $updateContent->execute([
                            $ex['contenu'],
                            $level,
                            $subject,
                            $ex['titre']
                        ]);
                    }
                } else {
                    // Insertion
                    $insert = $pdo->prepare(
                        "INSERT INTO exercises (Title, Level, Subject, Content, Answer)
                         VALUES (?, ?, ?, ?, ?)"
                    );
                    $insert->execute([
                        $ex['titre'],
                        $level,
                        $subject,
                        $ex['contenu'],
                        $ex['reponse']
                    ]);
                    $inserted++;
                }
            }
        }
    }
    
    echo "✅ Import réussi!\n";
    echo "  - Exercices insérés: $inserted\n";
    echo "  - Détail par niveau:\n";
    
    foreach ($data['data'] as $level => $subjects) {
        $math = count($subjects['Mathématiques']);
        $fr = count($subjects['Français']);
        $en = count($subjects['Anglais']);
        echo "    $level: Math=$math, Français=$fr, Anglais=$en\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
