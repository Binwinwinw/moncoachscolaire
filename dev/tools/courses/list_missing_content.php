<?php
require_once __DIR__ . '/../../../src/config/config.php';
require_once __DIR__ . '/../../../src/database/connection.php';

try {
    // $pdo est défini dans connection.php
    if (!isset($pdo)) {
        throw new Exception("Erreur de connexion BDD");
    }

    // Récupérer les cours sans explication
    // Note: Utilisation des colonnes subject, level et competence (au lieu de Title)
    $stmt = $pdo->query("
        SELECT
            c.id,
            c.subject as subject_name,
            c.level as level_name,
            c.competence as title
        FROM courses c
        WHERE c.explanation IS NULL OR c.explanation = ''
        ORDER BY c.subject, c.level, c.competence
    ");

    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "=== COURS MANQUANTS PAR MATIÈRE ===\n\n";

    $bySubject = [];
    foreach ($courses as $c) {
        $bySubject[$c['subject_name']][] = $c;
    }

    foreach ($bySubject as $subject => $list) {
        echo "## $subject (" . count($list) . " cours manquants)\n";
        // Afficher les 5 premiers exemples
        for ($i = 0; $i < min(5, count($list)); $i++) {
            echo "   - [" . $list[$i]['level_name'] . "] " . $list[$i]['title'] . "\n";
        }
        if (count($list) > 5) {
            echo "   ... et " . (count($list) - 5) . " autres.\n";
        }
        echo "\n";
    }

    echo "Total manquant : " . count($courses) . "\n";

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
