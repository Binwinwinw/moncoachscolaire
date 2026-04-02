<?php
require_once __DIR__ . '/../db/connection.php';

// Réponses extraites manuellement du PDF (pages corrections 6-7, 13, 18, 22)
$updates = [
    // 6ème
    610 => 'Classe : Angle aigu (< 90°), Angle droit (= 90°), Angle obtus (> 90° et < 180°), Angle plat (= 180°)',
    613 => 'Identifier verbes et sujets dans chaque phrase',
    616 => 'Être : suis, es, est, sommes, êtes, sont • Avoir : ai, as, a, avons, avez, ont • Aller : vais, vas, va, allons, allez, vont • Parler : parle, parles, parle, parlons, parlez, parlent',
    624 => '1) play • 2) plays • 3) play • 4) play',
    625 => 'Colours: red (rouge), blue (bleu), green (vert), yellow (jaune), black (noir).',
    
    // 5ème
    634 => '1) équilatéral • 2) isocèle • 3) rectangle • 4) quelconque',
    635 => '1) parallèles / égaux • 2) coupent en leur milieu • 3) parallélogramme • 4) égaux',
    636 => '1) Tracer la base donnée 2) Reporter les longueurs des côtés 3) Relier les points pour former le triangle',
    637 => '1) 3500 m • 2) 4,5 km • 3) 2300 g • 4) 0,75 kg',
    638 => '1) 60 cm² • 2) 30 cm² • 3) 40 cm²',
    639 => 'Moyenne = 12,8 • ≥15 : 4 élèves • <10 : 2 élèves',
    648 => 'Exemple de champ lexical : mer, vague, sable, bateau, rivage',
    
    // 4ème
    664 => '1) rectangle • 2) rectangle • 3) non rectangle',
    665 => '1) 1/2 • 2) AC = 8 cm • 3) DE = 4 cm',
    671 => '1) frayeur 2) angoisse 3) terreur 4) inquiétude 5) crainte 6) panique',
    672 => 'Production écrite libre : court texte (introduction, idée principale, conclusion)',
    
    // 3ème
    688 => 'Production écrite libre : récit (situation, événements, résolution, chute)',
];

$stmt = $pdo->prepare('UPDATE exercises SET Answer = ? WHERE Id = ?');
$count = 0;
foreach ($updates as $id => $ans) {
    $stmt->execute([$ans, $id]);
    echo "Updated #$id\n";
    $count++;
}
echo "\nTotal: $count updates\n";
