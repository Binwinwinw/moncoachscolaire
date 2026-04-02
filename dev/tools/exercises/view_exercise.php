<?php
/**
 * dev/tools/exercises/view_exercise.php
 * Afficher un exercice parsé en détail
 */

require_once __DIR__ . '/../../../src/database/connection.php';

// ID de l'exercice à afficher
$exerciseId = $argv[1] ?? 2457;

echo "📝 DÉTAILS DE L'EXERCICE ID: $exerciseId\n";
echo str_repeat("=", 70) . "\n\n";


$stmt = $pdo->prepare("
    SELECT
        id,
        subject,
        level,
        title,
        identifier,
        structure_type,
        pattern_detected,
        instruction,
        content,
        sub_questions,
        answer,
        exam_prep
    FROM exercises
    WHERE id = ?
");

$stmt->execute([$exerciseId]);
$exercise = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exercise) {
    echo "❌ Exercice introuvable\n";
    exit(1);
}

// Affichage formaté
echo "📚 INFORMATIONS GÉNÉRALES\n";
echo str_repeat("-", 70) . "\n";
printf("Matière      : %s\n", $exercise['subject']);
printf("Niveau       : %s\n", $exercise['level']);
printf("Titre        : %s\n", $exercise['title']);
printf("Identifier   : %s\n", $exercise['identifier']);
if ($exercise['exam_prep']) {
    printf("Prépa        : %s\n", $exercise['exam_prep']);
}

echo "\n📋 STRUCTURE PARSÉE\n";
echo str_repeat("-", 70) . "\n";
printf("Type         : %s\n", $exercise['structure_type']);
printf("Pattern      : %s\n", $exercise['pattern_detected'] ?: 'N/A');

echo "\n📌 CONSIGNE\n";
echo str_repeat("-", 70) . "\n";
echo wordwrap($exercise['instruction'] ?: '(non détectée)', 70) . "\n";

if ($exercise['structure_type'] === 'multi-parties' && $exercise['sub_questions']) {
    $subQuestions = json_decode($exercise['sub_questions'], true);

    echo "\n📖 INTRODUCTION\n";
    echo str_repeat("-", 70) . "\n";
    if (!empty($subQuestions['introduction'])) {
        echo wordwrap($subQuestions['introduction'], 70) . "\n";
    } else {
        echo "(aucune)\n";
    }

    echo "\n🔢 SOUS-QUESTIONS (" . count($subQuestions['questions']) . ")\n";
    echo str_repeat("-", 70) . "\n";

    foreach ($subQuestions['questions'] as $sq) {
        echo "\n  {$sq['id']}) " . wordwrap($sq['enonce'], 66, "\n     ") . "\n";
    }
} else {
    echo "\n📄 CONTENU COMPLET\n";
    echo str_repeat("-", 70) . "\n";
    echo wordwrap(substr($exercise['content'], 0, 500), 70) . "\n";
    if (strlen($exercise['content']) > 500) {
        echo "\n... (tronqué)\n";
    }
}

if ($exercise['answer']) {
    echo "\n✅ RÉPONSE ATTENDUE\n";
    echo str_repeat("-", 70) . "\n";
    echo wordwrap(substr($exercise['answer'], 0, 200), 70) . "\n";
    if (strlen($exercise['answer']) > 200) {
        echo "... (tronqué)\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✅ Affichage terminé\n";
