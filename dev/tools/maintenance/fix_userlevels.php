<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../db/connection.php';

echo "🔧 Correction des UserLevel (sans accents → avec accents)\n\n";

$updates = [
    '6eme' => '6ème',
    '5eme' => '5ème',
    '4eme' => '4ème',
    '3eme' => '3ème'
];

foreach ($updates as $oldLevel => $newLevel) {
    $stmt = $pdo->prepare("UPDATE Users SET UserLevel = ? WHERE UserLevel = ?");
    $stmt->execute([$newLevel, $oldLevel]);
    $count = $stmt->rowCount();
    if ($count > 0) {
        echo "✅ $oldLevel → $newLevel : $count utilisateur(s) mis à jour\n";
    }
}

echo "\n📊 État final de la table Users:\n";
$users = $pdo->query("SELECT Id, Username, UserLevel FROM Users ORDER BY Id")->fetchAll();
foreach ($users as $user) {
    echo "  - {$user['Username']} : {$user['UserLevel']}\n";
}

echo "\n✅ Correction terminée!\n";
?>
