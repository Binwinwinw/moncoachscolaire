#!/usr/bin/env php
<?php
/**
 * RAPPORT COMPLET DES EXERCICES PAR MATIÈRE ET NIVEAU
 * Analyse tous les exercices pour identifier les problèmes
 */

require_once __DIR__ . '/../config.php';

echo "📊 RAPPORT COMPLET DES EXERCICES\n";
echo "============================================================\n\n";

// Récupérer toutes les statistiques par matière
$stmt = $pdo->query("
    SELECT 
        Subject,
        Level,
        COUNT(*) as total,
        SUM(CASE WHEN Answer IS NULL OR Answer = '' THEN 1 ELSE 0 END) as vides,
        SUM(CASE WHEN Answer LIKE 'a)%' OR Answer LIKE 'b)%' OR Answer LIKE 'c)%' OR Answer LIKE 'd)%' THEN 1 ELSE 0 END) as placeholders,
        SUM(CASE WHEN LENGTH(Answer) > 0 AND LENGTH(Answer) < 15 THEN 1 ELSE 0 END) as trop_courts
    FROM Exercises
    GROUP BY Subject, Level
    ORDER BY Subject, 
        CASE Level
            WHEN '6ème' THEN 1
            WHEN '5ème' THEN 2
            WHEN '4ème' THEN 3
            WHEN '3ème' THEN 4
            WHEN 'Seconde' THEN 5
            WHEN 'Première' THEN 6
            WHEN 'Terminale' THEN 7
            ELSE 8
        END
");

$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grouper par matière
$bySubject = [];
foreach ($stats as $row) {
    $subject = $row['Subject'];
    if (!isset($bySubject[$subject])) {
        $bySubject[$subject] = [
            'total' => 0,
            'vides' => 0,
            'placeholders' => 0,
            'trop_courts' => 0,
            'levels' => []
        ];
    }
    $bySubject[$subject]['total'] += $row['total'];
    $bySubject[$subject]['vides'] += $row['vides'];
    $bySubject[$subject]['placeholders'] += $row['placeholders'];
    $bySubject[$subject]['trop_courts'] += $row['trop_courts'];
    $bySubject[$subject]['levels'][] = $row;
}

// Afficher le rapport
echo "📋 RÉSUMÉ PAR MATIÈRE\n";
echo "============================================================\n\n";

$matieresPrioritaires = ['Mathématiques', 'Français', 'Histoire-Géographie', 'Histoire-Géo', 'SVT', 'Physique-Chimie'];
$matieresASupprimer = ['Anglais', 'Arts plastiques', 'Éducation musicale', 'Philosophie'];

foreach ($bySubject as $subject => $data) {
    $problematiques = $data['vides'] + $data['placeholders'] + $data['trop_courts'];
    $pourcentage = $data['total'] > 0 ? round(($problematiques / $data['total']) * 100, 1) : 0;
    
    // Icône selon priorité
    if (in_array($subject, $matieresASupprimer)) {
        $icon = '🔴';
        $action = 'À DÉSACTIVER';
    } elseif (in_array($subject, $matieresPrioritaires)) {
        $icon = '✅';
        $action = 'PRIORITAIRE';
    } else {
        $icon = '🟡';
        $action = 'À ÉVALUER';
    }
    
    echo "$icon $subject [$action]\n";
    echo "   Total: {$data['total']} exercices\n";
    echo "   Problématiques: $problematiques ({$pourcentage}%)\n";
    echo "     - Vides: {$data['vides']}\n";
    echo "     - Placeholders: {$data['placeholders']}\n";
    echo "     - Trop courts: {$data['trop_courts']}\n";
    echo "   Niveaux: ";
    foreach ($data['levels'] as $level) {
        echo "{$level['Level']}({$level['total']}) ";
    }
    echo "\n\n";
}

echo "\n============================================================\n";
echo "📊 STATISTIQUES GLOBALES\n";
echo "============================================================\n\n";

$totalExercises = array_sum(array_column($bySubject, 'total'));
$totalProblematiques = array_sum(array_column($bySubject, 'vides')) + 
                       array_sum(array_column($bySubject, 'placeholders')) + 
                       array_sum(array_column($bySubject, 'trop_courts'));

echo "Total exercices: $totalExercises\n";
echo "Total problématiques: $totalProblematiques\n";
echo "Pourcentage: " . round(($totalProblematiques / $totalExercises) * 100, 1) . "%\n\n";

// Matières à désactiver
echo "🔴 MATIÈRES À DÉSACTIVER (difficiles à automatiser):\n";
$toDisable = 0;
foreach ($bySubject as $subject => $data) {
    if (in_array($subject, $matieresASupprimer)) {
        echo "   - $subject: {$data['total']} exercices\n";
        $toDisable += $data['total'];
    }
}
echo "   TOTAL À DÉSACTIVER: $toDisable exercices\n\n";

// Matières prioritaires
echo "✅ MATIÈRES PRIORITAIRES (à conserver et améliorer):\n";
$toKeep = 0;
foreach ($bySubject as $subject => $data) {
    if (in_array($subject, $matieresPrioritaires)) {
        $problematiques = $data['vides'] + $data['placeholders'] + $data['trop_courts'];
        echo "   - $subject: {$data['total']} exercices ({$problematiques} à corriger)\n";
        $toKeep += $data['total'];
    }
}
echo "   TOTAL À CONSERVER: $toKeep exercices\n\n";

echo "============================================================\n";
echo "💡 RECOMMANDATIONS\n";
echo "============================================================\n\n";
echo "1. Désactiver immédiatement les exercices d'Anglais, Arts, Musique, Philo\n";
echo "2. Corriger les exercices des 4 matières prioritaires:\n";
echo "   - Mathématiques\n";
echo "   - Français\n";
echo "   - Histoire-Géographie\n";
echo "   - Sciences (SVT + Physique-Chimie)\n";
echo "3. Focus sur les exercices avec réponses claires et objectives\n";
echo "4. Éviter les exercices nécessitant de l'interprétation ou du contexte\n\n";
