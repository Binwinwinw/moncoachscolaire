<?php
$pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🎯 STATUT MONCOACHSCOLAIRE - DIAGNOSTICS\n";
echo str_repeat("=", 50) . "\n\n";

$levels = ['6eme', '5eme', '4eme', '3eme'];

foreach ($levels as $level) {
    $count = $pdo->query("SELECT COUNT(*) FROM contents WHERE level='$level'")->fetchColumn();
    $quiz = $pdo->query("SELECT id,title,subject FROM contents WHERE level='$level' AND type='quiz' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

    echo "📚 $level : $count contenus\n";
    echo "   🎮 " . count($quiz) . " QUIZZES :\n";

    foreach ($quiz as $q) {
        echo "     ✅ ID {$q['id']} : {$q['title']} ({$q['subject']})\n";
    }
    echo "\n";
}

$total = $pdo->query("SELECT COUNT(*) FROM contents WHERE type='quiz'")->fetchColumn();
echo "🏆 TOTAL QUIZZES : $total\n";
echo "🎉 MVP PRÊT POUR TESTS !\n";
?>
