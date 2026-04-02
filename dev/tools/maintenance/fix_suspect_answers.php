<?php
require __DIR__ . '/../db/connection.php';
if (!$pdo) {
    fwrite(STDERR, "DB indisponible\n");
    exit(1);
}

$updates = [
    433 => "1) 3,5 km = 3500 m ; 2) 4500 m = 4,5 km ; 3) 2,3 kg = 2300 g ; 4) 750 g = 0,75 kg.",
    434 => "1) Rectangle 12×5 -> 60 cm² ; 2) Triangle base 10 cm hauteur 6 cm -> 30 cm² ; 3) Parallélogramme base 8 cm hauteur 5 cm -> 40 cm².",
    442 => "1) Il a un chien et un chat. 2) Elle est partie à la bibliothèque. 3) Pierre et Marie jouent dans le jardin. 4) Il a trois livres à lire.",
    443 => "clair -> clarté ; chant -> chanteur ; rapide -> rapidement ; lire -> lecture.",
    102 => "Réponse : option b) (2e choix) = les globules blancs, cellules immunitaires qui détruisent les agents pathogènes.",
    122 => "Réponse : b) le romantisme (mouvement du XIXe siècle, Hugo 1802-1885, chef de file).",
    479 => "Pour x² - 5x + 6 = 0 : Δ = 25 - 24 = 1 ; racines x1 = 3 et x2 = 2 ; solutions x = 2 ou x = 3.",
    485 => "Réponse : b) (option 2) les mitochondries produisent l'ATP.",
    490 => "Analyse de l'extrait des Misérables : thème de la pauvreté imposée par la société, critique sociale d'Hugo (champ lexical des misérables, antithèses), plan en deux axes + conclusion synthèse.",
    491 => "Plan type : introduction avec problématique ; I) poésie qui dénonce (Hugo, Aragon, Desnos) ; II) force des images/rythmes pour toucher le lecteur ; III) limites et réception ; conclusion avec ouverture.",
    496 => "Plan dialectique : thèse liberté = faire ce qu'on veut ; antithèse : liberté = autonomie sous loi morale/droit ; synthèse : liberté = vouloir ce que l'on fait dans un cadre commun.",
    150 => "Croisement génétique : coder les allèles, tableau de Punnett ; ex Aa x Aa -> 1/4 AA, 1/2 Aa, 1/4 aa ; phénotypes 3/4 dominant, 1/4 récessif ; ségrégation en méiose + fécondation aléatoire.",
    136 => "Réponse : a) Descartes, auteur du Discours de la méthode (1637).",
];

$updateStmt = $pdo->prepare("UPDATE Exercises SET Answer = ? WHERE Id = ?");
foreach ($updates as $id => $answer) {
    $updateStmt->execute([$answer, $id]);
    echo "✅ Mis à jour #{$id}\n";
}

echo "Terminé.\n";
