<?php
/**
 * dev/tools/courses/fill_missing_content_generic.php
 *
 * Remplit les cours manquants (explanation vide) avec un contenu générique
 * basé sur le titre, la matière et le niveau.
 * Stratégie "Bouche-trou" pour compléter les 190 cours restants.
 */

if (php_sapi_name() !== 'cli') {
    die('Ce script doit être exécuté en ligne de commande.');
}

require_once __DIR__ . '/../../../src/config/config.php';
require_once __DIR__ . '/../../../src/database/connection.php';

echo "🚀 Remplissage générique des cours manquants\n";
echo "=================================================\n\n";

try {
    // 1. Récupérer les cours vides
    $stmt = $pdo->query("
        SELECT id, subject, level, competence as title
        FROM courses
        WHERE explanation IS NULL OR explanation = ''
    ");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "INFO : " . count($courses) . " cours à traiter.\n";

    $updateStmt = $pdo->prepare("
        UPDATE courses
        SET explanation = :explanation,
            key_point = :key_point,
            updated_at = NOW()
        WHERE id = :id
    ");

    $updated = 0;

    foreach ($courses as $c) {
        $subject = htmlspecialchars($c['subject'] ?? 'Matière inconnue');
        $level = htmlspecialchars($c['level'] ?? 'Niveau inconnu');
        $title = htmlspecialchars($c['title'] ?? 'Chapitre');

        // Nettoyage basique des titres "bizarres" pour l'affichage
        $displayTitle = $title;
        if (strlen($title) < 3) {
             $displayTitle = "Chapitre général";
        }

        // Génération du contenu HTML générique
        $explanation = <<<HTML
<div class="course-theory generic-content">
    <h3>Introduction : {$displayTitle}</h3>
    <p>Ce module de <strong>{$subject}</strong> destiné au niveau <strong>{$level}</strong> se concentre sur : <em>{$title}</em>.</p>

    <div class="theory-block">
        <h4>Objectifs pédagogiques</h4>
        <p>Ce cours vise à fournir les bases essentielles pour maîtriser ce sujet. Il est conçu pour accompagner l'élève dans sa progression.</p>
        <ul>
            <li>Compréhension des concepts fondamentaux liés à "{$title}".</li>
            <li>Application pratique à travers des exemples concrets.</li>
            <li>Consolidation des acquis par la résolution de problèmes.</li>
        </ul>
    </div>

    <div class="theory-block">
        <h4>Méthodologie</h4>
        <p>Pour réussir ce module, il est conseillé de :</p>
        <ol>
            <li>Bien lire les définitions et le vocabulaire spécifique.</li>
            <li>S'entraîner régulièrement sur les concepts abordés.</li>
            <li>Revoir les notions précédentes si nécessaire.</li>
        </ol>
    </div>

    <p class="note-info"><em>Ce contenu est une base de travail générée automatiquement. Il sera enrichi ultérieurement avec des détails spécifiques.</em></p>
</div>
HTML;

        // Génération du point clé
        $keyPoint = "Synthèse et points clés sur : {$title}. Notion importante du programme de {$subject} ({$level}).";

        // Exécution de la mise à jour
        $updateStmt->execute([
            ':explanation' => $explanation,
            ':key_point' => $keyPoint,
            ':id' => $c['id']
        ]);

        $updated++;
        echo ".";
        if ($updated % 50 == 0) echo " $updated\n";
    }

    echo "\n\n✅ SUCCÈS : $updated cours mis à jour avec du contenu générique.\n";

} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
}
