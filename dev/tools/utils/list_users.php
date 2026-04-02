<?php
require_once __DIR__ . '/../db/connection.php';

$stmt = $pdo->query('SELECT Id, Username, Email, Role FROM Users ORDER BY Id');
echo "=== USERS LIST ===\n";
foreach ($stmt as $u) {
    echo sprintf("ID: %s, User: %s, Email: %s, Role: %s\n", 
        $u['Id'], $u['Username'], $u['Email'], $u['Role']);
}
