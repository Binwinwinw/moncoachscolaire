<?php
require_once __DIR__ . '/../db/connection.php';

$updates = [
    // 3ème
    692 => 'Réponse libre : rédiger un email simple (salutation, présentation, objet, conclusion)',
    688 => 'Production écrite libre : récit (situation, événements, résolution, chute)',

    // 4ème Anglais / Maths / Français
    676 => '1) I have maths lessons on Monday • 2) I love reading books • 3) I play football after school',
    677 => 'Production écrite libre : présenter un voyage (destination, transport, activités, impressions)',
    589 => 'Production écrite libre : présenter un voyage (destination, transport, activités, impressions)',
    664 => '1) rectangle • 2) rectangle • 3) non rectangle',
    665 => '1) 1/2 • 2) AC = 8 cm • 3) DE = 4 cm',
    583 => 'Exemples : frayeur, angoisse, terreur, inquiétude, crainte, panique',
    584 => 'Production écrite libre : court texte (introduction, idée principale, conclusion)',
    671 => 'Exemples : frayeur, angoisse, terreur, inquiétude, crainte, panique',
    672 => 'Production écrite libre : court texte (introduction, idée principale, conclusion)',

    // 5ème Anglais
    652 => "1) I wake up at 7 o'clock • 2) I have breakfast • 3) I go to school • 4) I do my homework",
    655 => "1) I wake up at 7 o'clock • 2) I have breakfast • 3) I go to school • 4) I do my homework",
    656 => '1) small • 2) short • 3) fast • 4) young',

    // 5ème Français
    648 => 'Exemple de champ lexical : mer, vague, sable, bateau, rivage',

    // 5ème Maths (assoc. aux corrections)
    634 => '1) équilatéral • 2) isocèle • 3) rectangle • 4) quelconque',
    635 => '1) parallèles / égaux • 2) coupent en leur milieu • 3) parallélogramme • 4) égaux',
    636 => 'Réponse géométrique : construction graphique du triangle selon les mesures données',
    637 => '1) 3500 m • 2) 4,5 km • 3) 2300 g • 4) 0,75 kg',
    638 => '1) 60 cm² • 2) 30 cm² • 3) 40 cm²',
    639 => 'Moyenne = 12,8 • ≥15 : 4 élèves • <10 : 2 élèves',

    // 6ème Anglais
    624 => '1) play • 2) plays • 3) play • 4) play',
    625 => '1) red • 2) blue • 3) green • 4) yellow • 5) black',
    628 => 'Exemple : My name is Anna. I am 12 years old. I come from Paris. I have one brother and one sister. I like music and football.',
    629 => 'Exemple : He is tall and thin. He has brown hair and blue eyes.',
    630 => '1) My name is John. 2) I am twelve. 3) I live in Lyon. 4) My favourite subject is maths. 5) Yes, I have a cat.',

    // 6ème Français
    613 => 'Identier verbes et sujets dans chaque phrase',
    616 => 'Être : suis, es, est, sommes, êtes, sont • Avoir : ai, as, a, avons, avez, ont • Aller : vais, vas, va, allons, allez, vont • Parler : parle, parles, parle, parlons, parlez, parlent',

    // 6ème Maths
    610 => 'Angles : aigu (<90°), droit (=90°), obtus (>90° et <180°), plat (=180°)'
];

$stmt = $pdo->prepare('UPDATE exercises SET Answer = ? WHERE Id = ?');
$total = 0;
foreach ($updates as $id => $ans) {
    $stmt->execute([$ans, $id]);
    $total += $stmt->rowCount();
    echo "Updated $id\n";
}

echo "Total updated: $total\n";
