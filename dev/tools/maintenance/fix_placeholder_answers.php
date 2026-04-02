#!/usr/bin/env php
<?php
/**
 * Script pour corriger les réponses problématiques avec placeholders
 */

require_once __DIR__ . '/../config.php';

echo "🔧 CORRECTION DES RÉPONSES PLACEHOLDER\n";
echo "============================================================\n\n";

// Exercices avec placeholders à corriger
$corrections = [
    82 => "Le passé simple et l'imparfait sont les temps principaux du récit. Le passé simple décrit les actions importantes et ponctuelles, tandis que l'imparfait décrit le contexte et les actions en cours.",
    83 => "La métaphore est une figure de style qui établit une comparaison implicite entre deux éléments sans utiliser de mot de comparaison (comme 'comme'). Exemple: 'Cet homme est un lion' (courage/force).",
    90 => "La Révolution française a commencé en 1789 avec la convocation des États généraux et la prise de la Bastille le 14 juillet 1789. Elle marque le début de profonds changements politiques et sociaux en France.",
    91 => "La machine à vapeur, inventée par James Watt, a été le moteur de l'industrialisation. Elle a permis de mécaniser la production dans les usines et de développer les transports (trains, bateaux à vapeur).",
    92 => "Le registre tragique exprime la fatalité et le destin. Le personnage tragique est confronté à des forces qui le dépassent et lutte contre une fatalité inéluctable, suscitant la pitié et la crainte.",
    99 => "Le Débarquement de Normandie (6 juin 1944) a marqué un tournant décisif de la Seconde Guerre mondiale. Cette opération militaire alliée a permis la libération progressive de l'Europe occidentale.",
    101 => "En France, le Président de la République est le chef de l'État. Il est élu pour 5 ans au suffrage universel direct et dispose de pouvoirs importants définis par la Constitution de la Ve République.",
    102 => "Les globules blancs (ou leucocytes) sont les cellules du système immunitaire. Ils défendent l'organisme contre les infections en détectant et en neutralisant les agents pathogènes comme les bactéries et les virus.",
    103 => "Le newton (N) est l'unité de mesure de la force dans le système international. Une force de 1 newton correspond à la force nécessaire pour accélérer une masse de 1 kg à 1 m/s².",
    110 => "Christophe Colomb (1451-1506) est un navigateur italien au service de l'Espagne. Il a découvert les Amériques en 1492 en cherchant une route vers les Indes par l'ouest, ouvrant l'ère des grandes découvertes.",
    111 => "La Russie est le plus grand pays d'Europe par sa superficie (partie européenne). Elle s'étend sur deux continents: l'Europe (à l'ouest de l'Oural) et l'Asie (à l'est de l'Oural).",
    112 => "L'ADN (acide désoxyribonucléique) a une structure en double hélice découverte par Watson et Crick en 1953. Les deux brins sont reliés par des liaisons hydrogène entre les bases azotées complémentaires.",
    113 => "Les mitochondries sont les organites responsables de la production d'énergie dans la cellule. Elles produisent l'ATP (adénosine triphosphate) par la respiration cellulaire, d'où leur surnom de 'centrales énergétiques'.",
    114 => "Le dioxyde de carbone (CO₂) est absorbé par les plantes lors de la photosynthèse. Combiné avec l'eau et l'énergie lumineuse, il permet la production de glucose et le rejet d'oxygène.",
    117 => "Dans l'équation équilibrée 2H₂ + O₂ → 2H₂O, il faut 2 molécules d'hydrogène pour former 2 molécules d'eau. Cette équation respecte la loi de conservation de la masse.",
    118 => "Pour f(x) = 3x² + 2x, la dérivée est f'(x) = 6x + 2. On applique la règle: la dérivée de ax^n est n·ax^(n-1), et la dérivée d'une somme est la somme des dérivées.",
    122 => "Le romantisme (XIXe siècle) est un mouvement littéraire qui valorise l'expression des sentiments, l'imagination et la nature. Il s'oppose au classicisme et au rationalisme des Lumières.",
    123 => "La Première Guerre mondiale a commencé en 1914 suite à l'assassinat de l'archiduc François-Ferdinand à Sarajevo. Le jeu des alliances a transformé ce conflit local en guerre mondiale (1914-1918).",
    124 => "L'OMC (Organisation mondiale du commerce) est une organisation internationale créée en 1995. Elle régule le commerce mondial, fixe les règles commerciales et arbitre les conflits entre pays membres.",
    125 => "L'être humain possède 46 chromosomes (23 paires) dans chaque cellule non sexuelle. Les gamètes (ovules et spermatozoïdes) en possèdent 23. Ces chromosomes portent l'information génétique (ADN).",
    126 => "La mitose produit 2 cellules filles génétiquement identiques à la cellule mère. C'est le processus de division cellulaire qui permet la croissance et la réparation des tissus chez les organismes multicellulaires.",
    127 => "La sélection naturelle, théorie développée par Charles Darwin, est le mécanisme principal de l'évolution. Les individus les mieux adaptés à leur environnement ont plus de chances de survivre et de se reproduire.",
    136 => "René Descartes (1596-1650) est un philosophe et mathématicien français. Il est l'auteur de la célèbre phrase 'Je pense, donc je suis' et est considéré comme le père de la philosophie moderne.",
    137 => "La traduction est la deuxième étape de l'expression génétique (après la transcription). L'ARN messager est décodé par les ribosomes pour synthétiser des protéines à partir d'acides aminés.",
    138 => "La méiose produit 4 cellules haploïdes (gamètes) à partir d'une cellule diploïde. C'est le processus de division cellulaire spécifique à la formation des cellules sexuelles (spermatozoïdes et ovules).",
];

$updated = 0;
$failed = 0;

foreach ($corrections as $id => $newAnswer) {
    try {
        $stmt = $pdo->prepare("UPDATE Exercises SET Answer = ? WHERE Id = ?");
        $result = $stmt->execute([$newAnswer, $id]);
        
        if ($result) {
            echo "✅ ID $id: Réponse corrigée\n";
            $updated++;
        } else {
            echo "❌ ID $id: Échec de la mise à jour\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "❌ ID $id: Erreur - " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\n============================================================\n";
echo "✅ Réponses corrigées: $updated\n";
echo "❌ Échecs: $failed\n";
echo "\n";
echo "ℹ️  Note: Les exercices avec réponses vides (IDs 154-216) nécessitent\n";
echo "   des réponses spécifiques basées sur leur contenu. Ils doivent être\n";
echo "   remplis manuellement ou via l'interface admin.\n";
