<?php
require_once __DIR__ . '/../db/connection.php';

$pdo->exec("UPDATE Users SET Role = 'admin' WHERE Username = 'admin'");
echo "✓ Rôle mis à jour: admin → admin\n";

// Vérifier
$stmt = $pdo->query("SELECT Username, Role FROM Users WHERE Username = 'admin'");
$user = $stmt->fetch();
echo "Vérification: {$user['Username']} = {$user['Role']}\n";
