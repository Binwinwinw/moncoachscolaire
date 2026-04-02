<?php
// dev/tools/courses/updates/update_full_batch_science_history.php
// Génération de contenu pour Sciences, SVT, Physique-Chimie et Histoire-Géo

$rootDir = dirname(dirname(dirname(dirname(__DIR__))));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

echo "🚀 Génération Contenu Sciences & Histoire\n";

$definitions = [
    // --- HISTOIRE-GEO ---
    '137' => ['Histoire', 'Les débuts de l\'humanité.', '<h3>1. Préhistoire</h3><p>Paléolithique (pierre taillée, nomades) et Néolithique (pierre polie, sédentarisation, agriculture).</p>'],
    '340' => ['Egypte', 'L\'Égypte antique.', '<h3>1. Pharaon</h3><p>Considéré comme un dieu vivant. Pyramides (tombeaux). Le Nil est vital pour l\'agriculture.</p>'],
    '79' => ['Moyen', 'Moyen Âge : Seigneurs et Paysans.', '<h3>1. Féodalité</h3><p>Relation suzerain/vassal. Les trois ordres : Clergé (prie), Noblesse (combat), Tiers-État (travaille).</p>'],
    '343' => ['Decolonisation', 'La décolonisation.', '<h3>1. Contexte</h3><p>Après 1945. Affaiblissement des puissances coloniales. Indépendance de l\'Inde (1947), Guerre d\'Algérie (1954-1962).</p>'],
    '344' => ['Guerre', 'Guerre Froide (1947-1991).', '<h3>1. Bloc de l\'Ouest vs Est</h3><p>USA (Capitalisme, OTAN) contre URSS (Communisme, Pacte de Varsovie). Équilibre de la terreur (nucléaire).</p>'],
    '360' => ['Mondialisation', 'La mondialisation.', '<h3>1. Définition</h3><p>Mise en relation des différentes parties du monde par l\'augmentation des flux (marchandises, humains, informations, capitaux).</p>'],

    // --- SVT ---
    '153' => ['Svt', 'Découverte de l\'environnement.', '<h3>1. Vivant vs Non-vivant</h3><p>Vivant : naît, grandit, se nourrit, se reproduit, meurt. Non-vivant : eau, air, roches.</p>'],
    '335' => ['Regime', 'La respiration et l\'effort.', '<h3>1. Adaptation</h3><p>Lors d\'un effort, le rythme cardiaque et le rythme respiratoire augmentent pour apporter plus d\'O2 et de glucose aux muscles.</p>'],
    '295' => ['Analyse', 'Génétique.', '<h3>1. ADN</h3><p>Molécule support de l\'hérédité. Gène : portion d\'ADN codant pour un caractère. Allèle : version d\'un gène.</p>'],

    // --- PHYSIQUE-CHIMIE ---
    '286' => ['Chimie', 'Transformations acide-base.', '<h3>1. pH</h3><p>0 (acide) < 7 (neutre) < 14 (basique). Échange de protons H+.</p>'],
    '287' => ['Electricite', 'Lois de l\'électricité.', '<h3>1. Loi d\'Ohm</h3><p>U = R x I. (Tension en Volts, Résistance en Ohms, Intensité en Ampères).</p>'],
    '363' => ['Electricite', 'Condensateurs et circuits RC.', '<h3>1. Charge</h3><p>q = C x u. Temps caractéristique tau = R x C.</p>'],

    // --- SCIENCES (Enseignement scientifique / Techno) ---
    '102' => ['Energies', 'Les formes d\'énergie.', '<h3>1. Conservation</h3><p>L\'énergie ne se perd pas, elle se transforme. Cinétique (vitesse), Potentielle (hauteur), Thermique (chaleur).</p>'],
    '220' => ['Atomes', 'Structure de la matière.', '<h3>1. Atome</h3><p>noyau (protons + neutrons) + électrons. L\'atome est électriquement neutre.</p>']
];

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE courses SET key_point = :key, explanation = :expl WHERE id = :id");

    $count = 0;
    foreach ($definitions as $id => $data) {
        $stmt->execute([
            ':key' => $data[1],
            ':expl' => $data[2],
            ':id' => $id
        ]);
        $count++;
    }

    $pdo->commit();
    echo "\n🎉 Succès : $count cours de Sciences/Hist-Géo mis à jour.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
