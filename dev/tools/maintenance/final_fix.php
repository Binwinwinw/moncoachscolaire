<?php
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../db/connection.php';

echo "🔧 Correction finale des UserLevel avec UTF-8\n\n";

// Définir l'encodage correctement
$pdo->exec("SET NAMES utf8mb4");
$pdo->exec("SET CHARACTER_SET_CLIENT = utf8mb4");
$pdo->exec("SET CHARACTER_SET_RESULTS = utf8mb4");

// Corriger les utilisateurs
$users_to_fix = [
    1 => '6ème',  // demo
    3 => '6ème'   // admin
];

foreach ($users_to_fix as $id => $level) {
    $stmt = $pdo->prepare("UPDATE Users SET UserLevel = ? WHERE Id = ?");
    $stmt->execute([$level, $id]);
    echo "✅ Utilisateur $id : UserLevel = '$level'\n";
}

// Afficher l'état final
echo "\n📊 État final:\n";
$users = $pdo->query("SELECT Id, Username, UserLevel FROM Users")->fetchAll();
foreach ($users as $user) {
    echo "- {$user['Username']} ({$user['Id']}) : {$user['UserLevel']}\n";
}

// Test du in_array
$demo_level = $users[0]['UserLevel'] ?? null;
$college_levels = ['6ème', '5ème', '4ème', '3ème'];
$is_college = in_array($demo_level, $college_levels, true);

echo "\n🧪 Test in_array:\n";
echo "- UserLevel = '$demo_level'\n";
echo "- in_array(\$demo_level, \$college_levels) = " . ($is_college ? 'TRUE' : 'FALSE') . "\n";
echo "- Les routes doivent maintenant être college/6eme/exercices-6eme ✅\n";
?>
