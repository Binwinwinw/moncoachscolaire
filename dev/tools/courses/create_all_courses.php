<?php
require_once __DIR__ . '/src/database/connection.php';

echo "CRÉATION AUTOMATIQUE DES COURS MANQUANTS\n";
echo str_repeat("=", 70) . "\n\n";

// Charger la structure depuis le JSON
$courses_data = json_decode(file_get_contents('all_courses_structure.json'), true);

$created = 0;
$skipped = 0;
$errors = 0;

foreach ($courses_data as $course) {
    // Ignorer les cours qui existent déjà
    if (!$course['needs_creation']) {
        $skipped++;
        continue;
    }

    $subject = $course['subject'];
    $level = $course['level'];
    $competence = $course['competence'];

    echo "📚 Création: {$subject} | {$level} | {$competence}\n";

    // Générer le contenu pédagogique
    $explanation = generateExplanation($competence, $subject);
    $key_point = generateKeyPoint($competence, $subject);
    $example = generateExample($competence, $subject);
    $section = determinSection($competence, $subject);
    $slug = generateSlug($subject, $level, $competence);

    try {
        // Vérifier si le cours existe déjà
        $check = $pdo->prepare("
            SELECT id FROM courses
            WHERE UPPER(subject) = UPPER(:subject)
            AND UPPER(level) = UPPER(:level)
            AND UPPER(competence) = UPPER(:competence)
            LIMIT 1
        ");
        $check->execute([
            'subject' => $subject,
            'level' => $level,
            'competence' => $competence
        ]);

        if ($check->fetch()) {
            echo "   ⚠️  Cours déjà existant (skipped)\n";
            $skipped++;
            continue;
        }

        // Insérer le cours
        $stmt = $pdo->prepare("
            INSERT INTO courses (
                subject, level, competence,
                explanation, key_point, example, section, slug,
                is_active, created_at
            ) VALUES (
                :subject, :level, :competence,
                :explanation, :key_point, :example, :section, :slug,
                1, NOW()
            )
        ");

        $stmt->execute([
            'subject' => $subject,
            'level' => $level,
            'competence' => $competence,
            'explanation' => $explanation,
            'key_point' => $key_point,
            'example' => $example,
            'section' => $section,
            'slug' => $slug
        ]);

        $created++;
        echo "   ✅ Cours créé (ID: " . $pdo->lastInsertId() . ")\n";

    } catch (Exception $e) {
        $errors++;
        echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "RÉSUMÉ\n";
echo str_repeat("-", 70) . "\n";
echo "Cours créés: {$created}\n";
echo "Cours ignorés (déjà existants): {$skipped}\n";
echo "Erreurs: {$errors}\n";
echo "\n✅ Création terminée!\n";

// ============================================================================
// FONCTIONS DE GÉNÉRATION DE CONTENU
// ============================================================================

function generateExplanation($competence, $subject) {
    $comp_lower = mb_strtolower($competence);
    $subject_lower = mb_strtolower($subject);

    // Français
    if ($subject_lower === 'français') {
        if (strpos($comp_lower, 'commentaire') !== false) {
            return "Le commentaire littéraire analyse un texte en profondeur. Il faut dégager une problématique, construire un plan en 2-3 parties, analyser les procédés littéraires et leurs effets, utiliser des citations précises et rédiger une introduction et une conclusion complètes.";
        }
        if (strpos($comp_lower, 'dissertation') !== false) {
            return "La dissertation littéraire exige une problématique claire, un plan dialectique structuré, des arguments appuyés sur les œuvres au programme, des transitions logiques et une conclusion synthétique avec ouverture.";
        }
        if (strpos($comp_lower, 'linéaire') !== false || strpos($comp_lower, 'explication') !== false) {
            return "L'explication linéaire suit le fil du texte en analysant progressivement ses mouvements, ses procédés et ses effets. Elle demande rigueur et cohérence dans l'interprétation.";
        }
        if (strpos($comp_lower, 'contraction') !== false || strpos($comp_lower, 'contracter') !== false) {
            return "La contraction réduit un texte au quart de sa longueur en conservant sa structure logique, ses idées principales et son argumentation. Il faut reformuler sans déformer.";
        }
        if (strpos($comp_lower, 'essai') !== false) {
            return "L'essai argumentatif développe une réflexion personnelle structurée avec une thèse, des arguments nuancés et des exemples pertinents.";
        }
        if (strpos($comp_lower, 'figures') !== false) {
            return "Les figures de style (métaphore, comparaison, hyperbole, anaphore, antithèse, oxymore...) créent des effets de sens qu'il faut identifier et interpréter.";
        }
        if (strpos($comp_lower, 'registres') !== false) {
            return "Les registres littéraires (tragique, comique, lyrique, épique, satirique...) révèlent les intentions de l'auteur et les émotions suscitées chez le lecteur.";
        }
        if (strpos($comp_lower, 'stratégies') !== false) {
            return "Les stratégies argumentatives utilisent la persuasion (émotions), la conviction (raison) et divers procédés rhétoriques pour convaincre le lecteur.";
        }
        if (strpos($comp_lower, 'proposition') !== false || strpos($comp_lower, 'phrase') !== false) {
            return "L'analyse grammaticale identifie les propositions (principales, relatives, conjonctives), leur fonction et les liens logiques dans la phrase complexe.";
        }
        if (strpos($comp_lower, 'poème') !== false) {
            return "L'analyse poétique étudie la versification (mètres, rimes, strophes), les thèmes et les procédés poétiques pour dégager le sens et les effets du poème.";
        }
        if (strpos($comp_lower, 'théâtre') !== false) {
            return "L'analyse théâtrale prend en compte les didascalies, les répliques, les registres (comique/tragique), la double énonciation et la dimension scénique du texte.";
        }
    }

    // Mathématiques
    if ($subject_lower === 'mathématiques') {
        if (strpos($comp_lower, 'fonction') !== false && strpos($comp_lower, 'variations') !== false) {
            return "Pour étudier une fonction, on détermine son domaine de définition, on calcule sa dérivée, on étudie son signe pour trouver les variations, puis on construit le tableau de variations et la représentation graphique.";
        }
        if (strpos($comp_lower, 'exponentielle') !== false) {
            return "La fonction exponentielle exp(x) vérifie exp(a+b) = exp(a) × exp(b). Sa dérivée est elle-même. Elle est strictement croissante et tend vers 0 en moins l'infini et vers plus l'infini en plus l'infini.";
        }
        if (strpos($comp_lower, 'morceaux') !== false) {
            return "Une fonction définie par morceaux nécessite une étude séparée sur chaque intervalle. Il faut vérifier la continuité aux points de raccordement.";
        }
        if (strpos($comp_lower, 'composer') !== false) {
            return "La composée de deux fonctions f et g notée (f o g)(x) = f(g(x)) nécessite que l'image de g soit dans le domaine de f. L'ordre de composition compte.";
        }
        if (strpos($comp_lower, 'référence') !== false) {
            return "Les fonctions de référence (carré, cube, inverse, racine, valeur absolue, affine) ont des propriétés et représentations graphiques à connaître par cœur.";
        }
        if (strpos($comp_lower, 'inverse') !== false && strpos($comp_lower, 'valeur') !== false) {
            return "La fonction inverse 1/x est décroissante sur chaque intervalle de son domaine. La fonction valeur absolue |x| crée une symétrie par rapport à l'axe des ordonnées.";
        }
        if (strpos($comp_lower, 'équation') !== false) {
            return "La résolution d'équations utilise les techniques algébriques : factorisation, identités remarquables, changement de variable. Il faut toujours vérifier les solutions dans l'équation initiale.";
        }
        if (strpos($comp_lower, 'second degré') !== false) {
            return "Une équation du second degré ax² + bx + c = 0 se résout avec le discriminant Delta = b² - 4ac. Si Delta > 0 : deux solutions, Delta = 0 : une solution double, Delta < 0 : aucune solution réelle.";
        }
        if (strpos($comp_lower, 'factorisation') !== false) {
            return "Factoriser consiste à transformer une somme en produit. On utilise le facteur commun, les identités remarquables (a² - b², (a+b)², (a-b)²) ou la forme factorisée avec les racines.";
        }
        if (strpos($comp_lower, 'vecteur') !== false) {
            return "Les vecteurs ont des coordonnées, une norme et on peut les additionner ou les multiplier par un scalaire. Deux vecteurs sont colinéaires si leurs coordonnées sont proportionnelles.";
        }
        if (strpos($comp_lower, 'coordonnées') !== false) {
            return "Dans un repère, on peut calculer des distances avec la formule de Pythagore, des coordonnées de milieux, des équations de droites et résoudre des problèmes géométriques.";
        }
        if (strpos($comp_lower, 'statistiques') !== false || strpos($comp_lower, 'médiane') !== false) {
            return "Les indicateurs statistiques mesurent la tendance centrale (moyenne, médiane) et la dispersion (écart-type, écart interquartile). Les quartiles Q1 et Q3 délimitent 50 pour cent des données centrales.";
        }
        if (strpos($comp_lower, 'probabilité') !== false) {
            return "La probabilité conditionnelle P(A|B) mesure la probabilité de A sachant que B s'est produit. Formule : P(A|B) = P(A inter B) / P(B). On utilise souvent un arbre pondéré pour visualiser.";
        }
    }

    // Générique
    return "Ce cours développe la compétence : " . $competence . ". Il propose des explications claires, des méthodes structurées et des exemples concrets pour progresser efficacement.";
}

function generateKeyPoint($competence, $subject) {
    $comp_lower = mb_strtolower($competence);
    $subject_lower = mb_strtolower($subject);

    if ($subject_lower === 'français') {
        if (strpos($comp_lower, 'commentaire') !== false) {
            return "Introduction avec problématique | Plan en 2-3 parties | Analyse des procédés | Citations précises | Transitions | Conclusion avec ouverture";
        }
        if (strpos($comp_lower, 'dissertation') !== false) {
            return "Problématique claire | Plan dialectique (thèse/antithèse/synthèse) | Arguments et exemples | Références aux œuvres | Transitions | Conclusion synthétique";
        }
        if (strpos($comp_lower, 'linéaire') !== false) {
            return "Suivre le fil du texte | Mouvements du texte | Procédés littéraires | Interprétation progressive | Cohérence globale";
        }
        if (strpos($comp_lower, 'contraction') !== false) {
            return "Idées principales | Structure logique | Reformulation fidèle | Réduction au quart | Pas d'ajout personnel";
        }
        if (strpos($comp_lower, 'figures') !== false) {
            return "Métaphore | Comparaison | Hyperbole | Anaphore | Antithèse | Oxymore | Interpréter les effets";
        }
        if (strpos($comp_lower, 'registres') !== false) {
            return "Tragique | Comique | Lyrique | Épique | Satirique | Indices textuels | Effets sur le lecteur";
        }
        if (strpos($comp_lower, 'stratégies') !== false) {
            return "Persuasion (émotions) | Conviction (raison) | Arguments | Exemples | Procédés rhétoriques";
        }
        if (strpos($comp_lower, 'proposition') !== false || strpos($comp_lower, 'phrase') !== false) {
            return "Principale | Relative | Conjonctive | Coordination | Juxtaposition | Fonction dans la phrase";
        }
        if (strpos($comp_lower, 'poème') !== false) {
            return "Versification | Rimes | Strophes | Thèmes | Procédés poétiques | Interprétation";
        }
        if (strpos($comp_lower, 'théâtre') !== false) {
            return "Didascalies | Répliques | Comique/Tragique | Double énonciation | Dimension scénique";
        }
        return "Méthode | Analyse | Exemples | Interprétation";
    }

    if ($subject_lower === 'mathématiques') {
        if (strpos($comp_lower, 'fonction') !== false && strpos($comp_lower, 'variations') !== false) {
            return "Domaine de définition | Dérivée | Signe de la dérivée | Tableau de variations | Graphique";
        }
        if (strpos($comp_lower, 'exponentielle') !== false) {
            return "exp(a+b) = exp(a) × exp(b) | Dérivée = elle-même | Strictement croissante | Limites en plus ou moins l'infini";
        }
        if (strpos($comp_lower, 'second degré') !== false) {
            return "ax² + bx + c = 0 | Discriminant Delta = b² - 4ac | Formules des solutions | Factorisation";
        }
        if (strpos($comp_lower, 'vecteur') !== false) {
            return "Coordonnées | Norme | Addition | Produit par scalaire | Colinéarité | Déterminant";
        }
        if (strpos($comp_lower, 'probabilité') !== false) {
            return "P(A|B) = P(A inter B) / P(B) | Arbre pondéré | Formule des probabilités totales | Indépendance";
        }
        if (strpos($comp_lower, 'statistiques') !== false || strpos($comp_lower, 'médiane') !== false) {
            return "Moyenne | Médiane | Q1 et Q3 | Écart-type | Écart interquartile | Interprétation";
        }
        return "Formules | Méthodes | Calculs | Vérification";
    }

    return "Notions clés | Méthodes | Exemples | Applications";
}

function generateExample($competence, $subject) {
    $comp_lower = mb_strtolower($competence);

    if (strpos($comp_lower, 'second degré') !== false) {
        return "Résoudre x² - 5x + 6 = 0. Delta = 25 - 24 = 1. Solutions : x1 = (5+1)/2 = 3 et x2 = (5-1)/2 = 2.";
    }
    if (strpos($comp_lower, 'probabilité') !== false) {
        return "On tire une boule dans une urne. P(Rouge) = 0.4, P(Bleu|Rouge tiré) = 0.3. Calculer P(Rouge et Bleu).";
    }

    return "Des exemples détaillés seront fournis dans les exercices associés.";
}

function determinSection($competence, $subject) {
    $comp_lower = mb_strtolower($competence);
    $subject_lower = mb_strtolower($subject);

    if ($subject_lower === 'français') {
        if (strpos($comp_lower, 'commentaire') !== false || strpos($comp_lower, 'linéaire') !== false) {
            return "Méthodes du bac";
        }
        if (strpos($comp_lower, 'dissertation') !== false || strpos($comp_lower, 'essai') !== false) {
            return "Méthodes du bac";
        }
        if (strpos($comp_lower, 'contraction') !== false) {
            return "Méthodes du bac";
        }
        if (strpos($comp_lower, 'figures') !== false || strpos($comp_lower, 'registres') !== false) {
            return "Analyse littéraire";
        }
        if (strpos($comp_lower, 'proposition') !== false || strpos($comp_lower, 'phrase') !== false) {
            return "Grammaire";
        }
        if (strpos($comp_lower, 'poème') !== false) {
            return "Poésie";
        }
        if (strpos($comp_lower, 'théâtre') !== false) {
            return "Théâtre";
        }
        return "Français";
    }

    if ($subject_lower === 'mathématiques') {
        if (strpos($comp_lower, 'fonction') !== false) {
            return "Fonctions";
        }
        if (strpos($comp_lower, 'équation') !== false || strpos($comp_lower, 'factorisation') !== false) {
            return "Algèbre";
        }
        if (strpos($comp_lower, 'vecteur') !== false || strpos($comp_lower, 'coordonnées') !== false) {
            return "Géométrie";
        }
        if (strpos($comp_lower, 'probabilité') !== false || strpos($comp_lower, 'statistiques') !== false) {
            return "Statistiques et probabilités";
        }
        return "Mathématiques";
    }

    return $subject;
}

function generateSlug($subject, $level, $competence) {
    $text = $subject . ' ' . $level . ' ' . $competence;
    $text = mb_strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return substr($text, 0, 200);
}
