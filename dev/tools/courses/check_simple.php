<?php
require_once __DIR__ . '/../../../src/database/connection.php';
$stmt = $pdo->query("SELECT id, competence, explanation FROM courses WHERE explanation IS NULL OR explanation = '' LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo "Il reste au moins un cours vide : [" . $row['id'] . "] " . $row['competence'] . "\n";
    echo "Content len: " . strlen($row['explanation'] ?? '') . "\n";
} else {
    echo "Aucun cours vide trouvé !\n";
}
