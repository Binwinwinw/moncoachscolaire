<?php
// dev/tools/courses/updates/update_maths_3eme.php
// Script de mise à jour de contenu (Lot 1)

$rootDir = dirname(dirname(dirname(dirname(__DIR__))));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

echo "🚀 Mise à jour contenu Mathématiques 3ème...\n";

$updates = [
    17 => [
        'key' => 'Maîtriser les priorités opératoires et le développement/factorisation.',
        'expl' => '<h3>1. Les priorités opératoires</h3><p>Dans un calcul sans parenthèses, la multiplication et la division sont prioritaires sur l\'addition et la soustraction.</p><h3>2. Calcul littéral</h3><p>Développer, c\'est transformer un produit en somme : k(a+b) = ka + kb.<br>Factoriser, c\'est transformer une somme en produit : ka + kb = k(a+b).</p>'
    ],
    18 => [
        'key' => 'Une équation est une égalité comportant une inconnue x.',
        'expl' => '<h3>1. Résoudre une équation</h3><p>Résoudre une équation d\'inconnue x, c\'est trouver toutes les valeurs de x pour lesquelles l\'égalité est vraie.</p><h3>2. Équation produit-nul</h3><p>Un produit de facteurs est nul si et seulement si l\'un au moins des facteurs est nul. <br>Exemple : (x - 3)(x + 2) = 0 signifie x - 3 = 0 ou x + 2 = 0.</p>'
    ],
    20 => [
        'key' => 'Une fonction associe à tout nombre x un unique nombre f(x).',
        'expl' => '<h3>1. Notion de fonction</h3><p>On note f: x ↦ f(x). x est l\'antécédent, f(x) est l\'image.</p><h3>2. Représentation graphique</h3><p>Dans un repère, la courbe représentative d\'une fonction f est l\'ensemble des points de coordonnées (x ; f(x)).</p><h3>3. Fonctions linéaires et affines</h3><p>Fonction linéaire : f(x) = ax (droite passant par l\'origine).<br>Fonction affine : f(x) = ax + b (droite ne passant pas forcément par l\'origine).</p>'
    ],
    21 => [
        'key' => 'La fréquence d\'une valeur est le quotient de son effectif par l\'effectif total.',
        'expl' => '<h3>1. Calcul de fréquence</h3><p>Fréquence = Effectif de la valeur / Effectif total. Elle est souvent exprimée en pourcentage.</p><h3>2. Moyenne pondérée</h3><p>Pour calculer la moyenne d\'une série statistique, on additionne les produits des valeurs par leurs effectifs, puis on divise par l\'effectif total.</p>'
    ],
    22 => [
        'key' => 'Révision des solides, volumes et repérage dans l\'espace.',
        'expl' => '<h3>1. Les solides usuels</h3><p>Il faut connaître les formules de volume pour : le cube, le pavé droit, le cylindre, la pyramide, le cône et la sphère.</p><h3>2. Section de solides</h3><p>La section d\'un pavé, cylindre ou sphère par un plan parallèle aux axes donne des figures géométriques simples (rectangle, disque...).</p>'
    ],
    24 => [
        'key' => 'Le PGCD est le Plus Grand Commun Diviseur de deux nombres entiers.',
        'expl' => '<h3>1. Décomposition en facteurs premiers</h3><p>Tout nombre entier supérieur à 1 peut s\'écrire de manière unique sous la forme d\'un produit de nombres premiers.</p><h3>2. Fractions irréductibles</h3><p>Une fraction est irréductible lorsque le PGCD de son numérateur et de son dénominateur est égal à 1.</p>'
    ],
    25 => [
        'key' => 'La probabilité mesure la chance qu\'un événement se produise (entre 0 et 1).',
        'expl' => '<h3>1. Notions de base</h3><p>Une expérience est aléatoire si on ne peut pas prévoir son résultat avec certitude. L\'ensemble des résultats possibles est l\'univers.</p><h3>2. Calcul de probabilité</h3><p>Dans une situation d\'équiprobabilité : P(A) = Nombre d\'issues favorables / Nombre d\'issues total.</p>'
    ],
    26 => [
        'key' => 'Le théorème de Thalès permet de calculer des longueurs dans des triangles semblables.',
        'expl' => '<h3>1. Théorème de Thalès</h3><p>Si (BM) et (CN) sont sécantes en A et si (MN) // (BC), alors : AM/AB = AN/AC = MN/BC.</p><h3>2. Réciproque de Thalès</h3><p>Elle permet de démontrer que deux droites sont parallèles.</p>'
    ],
    27 => [
        'key' => 'Cosinus, Sinus et Tangente dans le triangle rectangle.',
        'expl' => '<h3>1. Formules (SOH CAH TOA)</h3><p>Sinus = Opposé / Hypoténuse<br>Cosinus = Adjacent / Hypoténuse<br>Tangente = Opposé / Adjacent.</p><h3>2. Utilisation</h3><p>La trigonométrie permet de calculer une longueur manquante ou la mesure d\'un angle dans un triangle rectangle.</p>'
    ]
];

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE courses SET key_point = :key, explanation = :expl WHERE id = :id");

    foreach ($updates as $id => $data) {
        $stmt->execute([
            ':key' => $data['key'],
            ':expl' => $data['expl'],
            ':id' => $id
        ]);
        echo "✅ Cours #$id mis à jour.\n";
    }

    $pdo->commit();
    echo "\n🎉 Succès : " . count($updates) . " cours mis à jour.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
