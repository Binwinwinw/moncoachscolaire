<?php
// dev/tools/courses/updates/update_full_batch_maths.php
// Génération de contenu pour TOUS les cours de Mathématiques (sauf 3ème déjà fait)

$rootDir = dirname(dirname(dirname(dirname(__DIR__))));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

echo "🚀 Génération Contenu Mathématiques (6eme -> Terminale)\n";

$definitions = [
    // --- 6EME ---
    '138' => ['Angles', 'Un angle est formé par deux demi-droites de même origine.', '<h3>1. Mesure</h3><p>L\'unité de mesure est le degré. Un angle droit mesure 90°, un angle plat 180°.</p><h3>2. Bissectrice</h3><p>La bissectrice est la demi-droite qui partage un angle en deux angles égaux.</p>'],
    '139' => ['Calcul', 'Addition, soustraction et multiplication des nombres décimaux.', '<h3>1. Poser une opération</h3><p>Il est important d\'aligner les chiffres des unités (et la virgule) pour les additions et soustractions.</p>'],
    '140' => ['Conversions', 'Convertir des longueurs, masses et durées.', '<h3>1. Tableau de conversion</h3><p>kilo, hecto, deca, unité, deci, centi, milli.</p>'],
    '141' => ['Droites', 'Droites parallèles et perpendiculaires.', '<h3>1. Propriétés</h3><p>Si deux droites sont perpendiculaires à une même troisième, alors elles sont parallèles entre elles.</p>'],
    '142' => ['Espace', 'Le pavé droit (parallélépipède rectangle).', '<h3>1. Patron</h3><p>Savoir dessiner le patron d\'un pavé droit.</p><h3>2. Volume</h3><p>V = L x l x h.</p>'],
    '143' => ['Fractions', 'Une fraction représente un partage.', '<h3>1. Vocabulaire</h3><p>Dans a/b, a est le numérateur et b le dénominateur.</p>'],
    '144' => ['Geometrie', 'Figures usuelles : carré, rectangle, losange, cercle.', '<h3>1. Cercle</h3><p>Ensemble des points situés à égale distance du centre. Périmètre = 2 x pi x R.</p>'],
    '145' => ['Gestion', 'Lire et interpréter des tableaux de données.', '<h3>1. Organisation</h3><p>Savoir regrouper des données dans un tableau à double entrée.</p>'],
    '146' => ['Grandeurs', 'Périmètres et aires usuelles.', '<h3>1. Formules</h3><p>Carré : c x c. Rectangle : L x l.</p>'],
    '147' => ['Nombres', 'Nombres entiers et décimaux.', '<h3>1. Écriture</h3><p>Connaître la valeur des chiffres selon leur position (dizaine, centième...).</p>'],
    '148' => ['Operations', 'Les 4 opérations de base.', '<h3>1. Division euclidienne</h3><p>Dividende = Diviseur x Quotient + Reste (avec Reste < Diviseur).</p>'],
    '149' => ['Organisation', 'Proportionnalité et tableaux.', '<h3>1. Reconnaitre</h3><p>Un tableau est de proportionnalité si on passe d\'une ligne à l\'autre en multipliant par un même nombre.</p>'],
    '150' => ['Probleme', 'Méthodologie de résolution de problèmes.', '<h3>1. Étapes</h3><p>Lire l\'énoncé, repérer les données, choisir l\'opération, calculer, conclure.</p>'],
    '151' => ['Proportionnalite', 'Situations de proportionnalité simple.', '<h3>1. Passage à l\'unité</h3><p>Calculer le prix d\'un objet pour trouver le prix de plusieurs.</p>'],
    '152' => ['Puissances', 'Initiation aux carrés et cubes.', '<h3>1. Définition</h3><p>3² = 3 x 3 = 9.</p>'],

    // --- 5EME ---
    '80' => ['Aires', 'Aire du triangle et du parallélogramme.', '<h3>1. Triangle</h3><p>(Base x Hauteur) / 2.</p><h3>2. Parallélogramme</h3><p>Base x Hauteur.</p>'],
    '81' => ['Calcul', 'Priorités et nombres relatifs (initiation).', '<h3>1. Relatifs</h3><p>Somme de deux nombres de signes contraires.</p>'],
    '82' => ['Calculs', 'Expressions avec parenthèses.', '<h3>1. Règles</h3><p>On effectue d\'abord les calculs entre parenthèses.</p>'],
    '83' => ['Construction', 'Construction de triangles.', '<h3>1. Inégalité triangulaire</h3><p>Pour construire un triangle, la plus grande longueur doit être inférieure à la somme des deux autres.</p>'],
    '84' => ['Conversions', 'Unités d\'aire et de volume.', '<h3>1. Attention</h3><p>Pour les aires, il y a 2 colonnes par unité. Pour les volumes, 3 colonnes.</p>'],
    '85' => ['Diagramme', 'Statistiques : diagrammes en bâtons et circulaires.', '<h3>1. Angles</h3><p>Dans un diagramme circulaire, la mesure de l\'angle est proportionnelle à l\'effectif.</p>'],
    '86' => ['Exercice', 'Exercices de synthèse 5ème.', '<h3>1. Entraînement</h3><p>Mélange de géométrie et calcul numérique.</p>'],
    '87' => ['Fractions', 'Comparaison et addition (dénominateurs égaux).', '<h3>1. Égalité</h3><p>On ne change pas une fraction en multipliant numérateur et dénominateur par un même nombre.</p>'],
    '88' => ['Geometrie', 'Symétrie centrale.', '<h3>1. Propriétés</h3><p>La symétrie centrale conserve les longueurs, les angles, le parallélisme et les aires.</p>'],
    '89' => ['Grandeurs', 'Conversions temps et vitesse.', '<h3>1. Vitesse</h3><p>v = d / t. Attention aux conversions d\'heures en minutes.</p>'],
    '90' => ['Nombres', 'Nombres relatifs : repérage et comparaison.', '<h3>1. Droite graduée</h3><p>Savoir placer des points d\'abscisse négative.</p>'],
    '91' => ['Organisation', 'Gestion de données.', '<h3>1. Effectifs</h3><p>Calculer des fréquences simples.</p>'],
    '92' => ['Parallelogrammes', 'Propriétés du parallélogramme.', '<h3>1. Diagonales</h3><p>Si un quadrilatère a ses diagonales qui se coupent en leur milieu, c\'est un parallélogramme.</p>'],
    '93' => ['Pourcentages', 'Appliquer un pourcentage.', '<h3>1. Calcul</h3><p>Prendre t% d\'un nombre, c\'est le multiplier par t/100.</p>'],
    '94' => ['Probleme', 'Problèmes de proportionnalité.', '<h3>1. Échelle</h3><p>Distance réelle = Distance carte x Échelle.</p>'],
    '96' => ['Proportions', 'Égalité des produits en croix.', '<h3>1. Règle</h3><p>a/b = c/d équivaut à ad = bc.</p>'],
    '97' => ['Proprietes', 'Angles alternes-internes.', '<h3>1. Parallélisme</h3><p>Si deux droites coupées par une sécante forment des angles alternes-internes égaux, alors elles sont parallèles.</p>'],
    '98' => ['Puissance', 'Puissances de 10.', '<h3>1. Notation</h3><p>10^n = 1 suivi de n zéros.</p>'],

    // --- 4EME ---
    '38' => ['Calculs', 'Opérations sur les relatifs.', '<h3>1. Règle des signes</h3><p>Moins par moins donne plus. Plus par moins donne moins.</p>'],
    '39' => ['Equations', 'Résolution ax + b = c.', '<h3>1. Méthode</h3><p>Isoler x en effectuant les mêmes opérations des deux côtés de l\'égalité.</p>'],
    '40' => ['Fractions', 'Opérations complexes.', '<h3>1. Multiplication</h3><p>On multiplie les numérateurs entre eux et les dénominateurs entre eux.</p>'],
    '41' => ['Priorites', 'Calculs avec puissances et relatifs.', '<h3>1. Ordre</h3><p>Parenthèses > Puissances > Multiplications/Divisions > Additions/Soustractions.</p>'],
    '42' => ['Proportion', 'Vitesse moyenne.', '<h3>1. Formule</h3><p>Vitesse = Distance / Temps.</p>'],
    '43' => ['Puissances', 'Puissances d\'exposant entier relatif.', '<h3>1. Formules</h3><p>a^n x a^m = a^(n+m). (a^n)^m = a^(n*m).</p>'],

    // --- 2NDE ---
    '210' => ['Calcul', 'Ensembles de nombres et intervalles.', '<h3>1. Ensembles</h3><p>N (entiers naturels) ⊂ Z (entiers relatifs) ⊂ D (décimaux) ⊂ Q (rationnels) ⊂ R (réels).</p>'],
    '211' => ['Equations', 'Équations produit et quotient.', '<h3>1. Interdit</h3><p>Pour A(x)/B(x) = 0, il faut que A(x) = 0 ET B(x) ≠ 0 (valeur interdite).</p>'],
    '212' => ['Fonction', 'Généralités sur les fonctions.', '<h3>1. Variations</h3><p>Tableau de variations, croissant, décroissant, maximum, minimum.</p>'],
    '213' => ['Fonctions', 'Fonctions de référence.', '<h3>1. Liste</h3><p>Fonction carré (parabole), fonction inverse (hyperbole).</p>'],
    '214' => ['Geometrie', 'Coordonnées dans le plan.', '<h3>1. Milieu et Distance</h3><p>Milieu K : ((xA+xB)/2 ; (yA+yB)/2).<br>Distance AB : sqrt((xB-xA)² + (yB-yA)²).</p>'],
    '215' => ['Paraboles', 'Fonction Carré.', '<h3>1. Propriété</h3><p>La fonction x² est paire (symétrie axe ordonnées) et décroissante sur ]-inf, 0], croissante sur [0, +inf[.</p>'],
    '216' => ['Probabilites', 'Union et Intersection.', '<h3>1. Formule</h3><p>P(A u B) = P(A) + P(B) - P(A n B).</p>'],
    '217' => ['Statistiques', 'Moyenne, Médiane, Écart-type.', '<h3>1. Médiane</h3><p>Valeur qui partage la série ordonnée en deux groupes de même effectif.</p>'],
    '218' => ['Vecteurs', 'Calcul vectoriel.', '<h3>1. Colinéarité</h3><p>Deux vecteurs u(x,y) et v(x\',y\') sont colinéaires si xy\' - x\'y = 0.</p>'],

    // --- 1ERE ---
    '177' => ['Algebre', 'Second degré', '<h3>1. Discriminant</h3><p>Delta = b² - 4ac. Si > 0, deux racines. Si = 0, une racine. Si < 0, pas de racine réelle.</p>'],
    '178' => ['Algorithmique', 'Boucles et conditions.', '<h3>1. Python</h3><p>Utilisation de for, while et if pour résoudre des problèmes mathématiques.</p>'],
    '179' => ['Analyse', 'Dérivation.', '<h3>1. Nombre dérivé</h3><p>Limite du taux d\'accroissement. Correspond au coefficient directeur de la tangente.</p>'],
    '180' => ['Derivees', 'Fonctions dérivées usuelles.', '<h3>1. Formules</h3><p>(x^n)\' = n*x^(n-1). (1/x)\' = -1/x².</p>'],
    '181' => ['Fonctions', 'Étude de variations.', '<h3>1. Lien dérivée/variation</h3><p>Si f\'(x) > 0, f est croissante. Si f\'(x) < 0, f est décroissante.</p>'],
    '182' => ['Geometrie', 'Produit scalaire.', '<h3>1. Définition</h3><p>u.v = ||u|| x ||v|| x cos(u,v) = xx\' + yy\'.</p>'],
    '183' => ['Limites', 'Suites arithmétiques et géométriques.', '<h3>1. Terme général</h3><p>Arithmétique : un = u0 + nr. Géométrique : un = u0 x q^n.</p>'],
    '184' => ['Probabilites', 'Probabilités conditionnelles.', '<h3>1. Formule</h3><p>P_A(B) = P(A n B) / P(A).</p>'],
    '186' => ['Suite', 'Sens de variation des suites.', '<h3>1. Méthode</h3><p>Étudier le signe de u(n+1) - un.</p>'],
    '188' => ['Trigonometrie', 'Cercle trigonométrique.', '<h3>1. Valeurs remarquables</h3><p>Connaître cos et sin de pi/6, pi/4, pi/3.</p>'],

    // --- TERMINALE ---
    '274' => ['Analyse', 'Fonction exponentielle.', '<h3>1. Propriétés</h3><p>Exp(x) est toujours positive. (e^x)\' = e^x. e^(a+b) = e^a x e^b.</p>'],
    '275' => ['Bac', 'Sujets type Bac.', '<h3>1. Synthèse</h3><p>Problèmes transversaux mêlant analyse, géométrie et probabilités.</p>'],
    '277' => ['Integrales', 'Calcul d\'aires.', '<h3>1. Primitive</h3><p>L\'intégrale de a à b de f(x) est F(b) - F(a) où F est une primitive de f.</p>'],
    '278' => ['Limites', 'Limites de fonctions et continuité.', '<h3>1. Théorème des valeurs intermédiaires</h3><p>Si f est continue et strictement monotone...</p>'],
    '279' => ['Logarithmes', 'Fonction ln.', '<h3>1. Lien avec exp</h3><p>ln(x) est la réciproque de exp(x). définie sur ]0, +inf[. ln(ab) = ln(a) + ln(b).</p>'],
    '280' => ['Loi', 'Lois à densité.', '<h3>1. Loi Normale</h3><p>Utilisation de la calculatrice pour déterminer P(X < k).</p>'],
    '281' => ['Probabilites', 'Schéma de Bernoulli et loi binomiale.', '<h3>1. Paramètres</h3><p>B(n, p). Espérance E(X) = np.</p>'],
    '283' => ['Thermodynamique', 'Application mathématique.', '<h3>1. Équations différentielles</h3><p>y\' = ay + b. Solutions de la forme Ce^(ax) - b/a.</p>']
];

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE courses SET key_point = :key, explanation = :expl WHERE id = :id");

    $count = 0;
    foreach ($definitions as $id => $data) {
        // Vérifier si le cours existe et n'a pas de contenu (optionnel, l'update ne fera rien de mal)
        $stmt->execute([
            ':key' => $data[1],
            ':expl' => $data[2],
            ':id' => $id
        ]);
        $count++;
    }

    $pdo->commit();
    echo "\n🎉 Succès : $count cours de Mathématiques mis à jour.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
