<?php
/**
 * Script pour vérifier que le contenu des exercices correspond bien à leur niveau
 * en analysant les titres et le contenu
 */

require_once __DIR__ . '/../db/connection.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

echo "🔍 Vérification de cohérence niveau ↔ contenu des exercices\n\n";

// Récupérer tous les exercices
$stmt = $pdo->query("SELECT Id, Level, Subject, Title, Content FROM Exercises ORDER BY Level, Subject, Id");
$exercises = $stmt->fetchAll();

$suspicious = [];

foreach ($exercises as $ex) {
    $level = $ex['Level'];
    $title = $ex['Title'] ?? '';
    $content = $ex['Content'] ?? '';
    
    // Chercher des indices de niveau dans le titre ou le contenu
    $level_indicators = [
        '6ème' => ['6ème', '6eme', 'sixième', 'sixieme', '6e'],
        '5ème' => ['5ème', '5eme', 'cinquième', 'cinquieme', '5e'],
        '4ème' => ['4ème', '4eme', 'quatrième', 'quatrieme', '4e'],
        '3ème' => ['3ème', '3eme', 'troisième', 'troisieme', '3e'],
        'Seconde' => ['seconde', '2nde', '2nde', '2nd'],
        'Première' => ['première', 'premiere', '1ère', '1ere', '1ere'],
        'Terminale' => ['terminale', 'term', 'bac']
    ];
    
    $text = strtolower($title . ' ' . $content);
    
    foreach ($level_indicators as $expected_level => $indicators) {
        if ($level !== $expected_level) {
            foreach ($indicators as $indicator) {
                if (stripos($text, $indicator) !== false) {
                    $suspicious[] = [
                        'id' => $ex['Id'],
                        'level' => $level,
                        'expected' => $expected_level,
                        'title' => $title,
                        'subject' => $ex['Subject'] ?? 'N/A',
                        'indicator' => $indicator
                    ];
                    break 2; // Sortir des deux boucles
                }
            }
        }
    }
}

if (empty($suspicious)) {
    echo "✅ Aucun exercice suspect trouvé. Les niveaux semblent cohérents avec le contenu.\n";
} else {
    echo "⚠️  Exercices suspects trouvés : " . count($suspicious) . "\n\n";
    foreach ($suspicious as $sus) {
        echo "  ❌ ID {$sus['id']} : Niveau actuel = '{$sus['level']}', mais contient des indices de '{$sus['expected']}'\n";
        echo "     Titre : {$sus['title']}\n";
        echo "     Matière : {$sus['subject']}\n";
        echo "     Indicateur trouvé : '{$sus['indicator']}'\n\n";
    }
}

echo "\n✅ Vérification terminée !\n";

