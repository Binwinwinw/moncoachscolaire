<?php
/**
 * dev/tools/demo/view_multi_exercise.php
 * Affiche un exercice multi-parties du compte démo
 */

require_once __DIR__ . '/../../../src/database/connection.php';
require_once __DIR__ . '/../../../src/includes/exercice_loader.php';

// Charger l'exercice 2457 (multi-parties Maths 4ème)
$exercise = getExerciseById(2457);

if (!$exercise) {
    echo "❌ Exercice introuvable\n";
    exit(1);
}

echo "📝 EXERCICE MULTI-PARTIES\n";
echo str_repeat("=", 70) . "\n\n";

echo "ID          : {$exercise['Id']}\n";
echo "Titre       : {$exercise['Title']}\n";
echo "Matière     : {$exercise['Subject']}\n";
echo "Niveau      : {$exercise['Level']}\n";
echo "Structure   : {$exercise['structure_type']}\n";
echo "Pattern     : {$exercise['pattern_detected']}\n";
echo "\nConsigne :\n" . wordwrap($exercise['Instruction'], 70) . "\n";

if ($exercise['structure_type'] === 'multi-parties' && $exercise['sub_questions']) {
    $subQ = json_decode($exercise['sub_questions'], true);

    echo "\n📖 Introduction :\n";
    echo wordwrap($subQ['introduction'], 70) . "\n";

    echo "\n🔢 Sous-questions (" . count($subQ['questions']) . ") :\n\n";

    foreach ($subQ['questions'] as $q) {
        echo "  {$q['id']}) " . wordwrap($q['enonce'], 66, "\n     ") . "\n\n";
    }
}

echo "✅ Cet exercice est accessible au compte démo !\n";
