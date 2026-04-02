<?php
/**
 * Teste au moins 1 exercice pour chaque niveau scolaire
 * - Vérifie qu'il existe un exercice
 * - Détecte si des placeholders sont présents (après nettoyage)
 * - Affiche un résumé (titre, matière, cours liés)
 * Usage: php tools/test_exercises_levels.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';
require_once __DIR__ . '/../includes/course_markdown_loader.php';

$levels = ['6ème','5ème','4ème','3ème','Seconde','Première','Terminale'];

function isPlaceholderLine($line) {
    $trim = trim($line);
    $patterns = [
        '/^[a-dA-D][\)\.\-]\s*Réponse\s+correcte\s*$/u',
        '/^[a-dA-D][\)\.\-]\s*Une\s+autre\s+réponse\s+possible\s*$/u',
        '/^[a-dA-D][\)\.\-]\s*aucune\s+des\s+réponses\s*$/ui',
        '/^[a-dA-D][\)\.\-]\s*Réponse\s+alternative\s*$/u',
        '/^\-\s*Réponse\s+correcte\s*$/u',
        '/^\-\s*Une\s+autre\s+réponse\s+possible\s*$/u',
        '/^\-\s*aucune\s+des\s+réponses\s*$/ui',
        '/^\-\s*Réponse\s+alternative\s*$/u'
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $trim)) return true;
    }
    return false;
}

function hasPlaceholders($text) {
    if (!$text) return false;
    $lines = preg_split('/\r?\n/', $text);
    foreach ($lines as $line) {
        if (isPlaceholderLine($line)) return true;
    }
    return false;
}

if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "❌ Base de données non disponible\n");
    exit(1);
}

echo "🧪 TEST EXERCICES PAR NIVEAU\n";

echo str_repeat("=", 60) . "\n";
$totalOk = 0; $totalWarn = 0; $totalMissing = 0;

foreach ($levels as $level) {
    $stmt = $pdo->prepare("SELECT Id, Level, Subject, Title, Content, Answer FROM Exercises WHERE Level = ? ORDER BY Id ASC LIMIT 1");
    $stmt->execute([$level]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        echo "❌ $level: aucun exercice trouvé\n";
        $totalMissing++;
        continue;
    }
    
    $exerciseId = (int)$row['Id'];
    $placeholdersInContent = hasPlaceholders($row['Content']);
    $placeholdersInAnswer  = hasPlaceholders($row['Answer']);
    $linkedCourses = getCoursesForExercise($exerciseId);
    
    $status = 'OK';
    if ($placeholdersInContent || $placeholdersInAnswer) {
        $status = '⚠️ placeholders détectés';
        $totalWarn++;
    } else {
        $totalOk++;
    }
    
    echo "✅ Niveau: {$row['Level']} | Matière: {$row['Subject']} | Exercice #$exerciseId\n";
    echo "   Titre: {$row['Title']}\n";
    echo "   Placeholders: " . ($placeholdersInContent || $placeholdersInAnswer ? 'OUI' : 'NON') . "\n";
    echo "   Cours liés: " . count($linkedCourses) . "\n";
    if (!empty($linkedCourses)) {
        foreach ($linkedCourses as $c) {
            echo "     - {$c['Title']} ({$c['Subject']} {$c['Level']})\n";
        }
    }
    echo "   Statut: $status\n\n";
}

echo str_repeat("=", 60) . "\n";
echo "Résumé:\n";
echo "  OK: $totalOk\n";
echo "  Avertissements (placeholders): $totalWarn\n";
echo "  Niveaux sans exercice: $totalMissing\n";
echo "\n";
echo "🚀 Test terminé.\n";
