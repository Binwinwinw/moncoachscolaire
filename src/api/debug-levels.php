<?php

// debug-levels.php - Vérifie les niveaux disponibles dans la BDD
header('Content-Type: application/json; charset=utf-8');

// Charger config et PDO
if (!isset($pdo)) {
    if (is_file(dirname(__DIR__, 2) . '/src/config/config.php')) {
        require_once dirname(__DIR__, 2) . '/src/config/config.php';
    }
    if (is_file(dirname(__DIR__, 2) . '/src/database/connection.php')) {
        require_once dirname(__DIR__, 2) . '/src/database/connection.php';
    }
}

try {
    // Récupérer tous les niveaux uniques et le count de quiz par niveau
    // Note: columns are lowercase in the schema (level, type, status)
    $stmt = $pdo->prepare("
        SELECT `level`, COUNT(*) as count
        FROM contents
        WHERE `type`='quiz' AND `status`='published'
        GROUP BY `level`
        ORDER BY `level`
    ");
    $stmt->execute();
    $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer aussi les niveaux listés dans la table users
    $stmt2 = $pdo->prepare("
        SELECT DISTINCT `level`
        FROM users
        WHERE `level` IS NOT NULL AND `level` != ''
        LIMIT 10
    ");
    $stmt2->execute();
    $userLevels = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'levels_in_contents' => $levels,
        'sample_levels_in_users' => $userLevels,
        'message' => 'Données de debug pour verifier les niveaux en BDD',
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
