#!/usr/bin/env php
<?php
/**
 * Vérifier les subjects dans la BDD (table contents)
 */

require_once __DIR__ . '/../../../db/connection.php';

if (!isset($pdo) || !$pdo instanceof PDO) {
    die("ERROR: PDO connection not available\n");
}

try {
    $stmt = $pdo->query("
        SELECT DISTINCT subject, COUNT(*) as count
        FROM contents
        WHERE type='quiz'
        GROUP BY subject
        ORDER BY subject
    ");

    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== SUBJECTS IN DATABASE (contents table) ===\n";
    echo "Total distinct subjects: " . count($subjects) . "\n\n";

    foreach ($subjects as $row) {
        $subject = $row['subject'];
        $count = $row['count'];

        // Détecter les accents
        $hasAccent = (preg_match('/[àâäéèêëïîôùûüÿæœç]/ui', $subject) ? 'AVEC accent' : 'SANS accent');

        printf("%-25s | Count: %3d | %s\n", $subject, $count, $hasAccent);

        // Afficher l'encodage hex pour debug
        echo "   → HEX: " . bin2hex($subject) . "\n";
    }

    echo "\n";

} catch (Exception $e) {
    die("ERROR: " . $e->getMessage() . "\n");
}
