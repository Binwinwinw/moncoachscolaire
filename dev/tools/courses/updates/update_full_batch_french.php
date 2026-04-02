<?php
// dev/tools/courses/updates/update_full_batch_french.php
// Génération de contenu pour les cours de Français

$rootDir = dirname(dirname(dirname(dirname(__DIR__))));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

echo "🚀 Génération Contenu Français\n";

$definitions = [
    // --- 6EME ---
    '57' => ['Accord', 'Accord sujet-verbe.', '<h3>1. Règle de base</h3><p>Le verbe s\'accorde toujours en nombre et en personne avec son sujet.</p>'],
    '58' => ['Champs', 'Champ lexical.', '<h3>1. Définition</h3><p>Ensemble des mots qui se rapportent à une même idée ou un même thème.</p>'],
    '59' => ['Conjugaison', 'Les groupes de verbes.', '<h3>1. 1er groupe</h3><p>Verbes en -ER (sauf aller). 2e groupe : -IR (finissant). 3e groupe : les autres.</p>'],
    '65' => ['Grammaire', 'Classes grammaticales.', '<h3>1. Nature des mots</h3><p>Nom, déterminant, adjectif, verbe, pronom, adverbe, préposition...</p>'],
    '66' => ['Homophones', 'a/à, et/est, on/ont.', '<h3>1. Astuce</h3><p>Si on peut remplacer par "avait", c\'est "a". Sinon c\'est "à".</p>'],
    '69' => ['Orthographe', 'Pluriel des noms.', '<h3>1. Règle générale</h3><p>On ajoute un "s" au singulier. Exceptions : bois, nez... (invariables), cheval/chevaux...</p>'],
    '70' => ['Passe', 'Passé simple (3e personne).', '<h3>1. Terminaisons</h3><p>1er gr : -a, -èrent. 2e gr : -it, -irent.</p>'],
    '71' => ['Phrase', 'Types et formes de phrases.', '<h3>1. Types</h3><p>Déclarative, interrogative, exclamative, impérative.</p>'],

    // --- 5EME ---
    '115' => ['Accords', 'Accord du participe passé.', '<h3>1. Avec être</h3><p>S\'accorde avec le sujet. Elle est partie.</p><h3>2. Avec avoir</h3><p>S\'accorde avec le COD si placé avant. La pomme que j\'ai mangée.</p>'],
    '116' => ['Complements', 'COD et COI.', '<h3>1. Identification</h3><p>Sujet + Verbe + Quoi/Qui ? = COD. Sujet + Verbe + À qui/De quoi ? = COI.</p>'],
    '117' => ['Conjugaison', 'Temps composés.', '<h3>1. Formation</h3><p>Auxiliaire (être ou avoir) conjugué + Participe passé.</p>'],
    '120' => ['Expression', 'Écrire une lettre.', '<h3>1. Structure</h3><p>Lieu, date, formule d\'appel, corps de la lettre, formule de politesse, signature.</p>'],
    '124' => ['Imparfait', 'Valeurs de l\'imparfait.', '<h3>1. Utilisation</h3><p>Description, action qui dure, habitude dans le passé.</p>'],
    '125' => ['Lecture', 'Le roman de chevalerie.', '<h3>1. Thèmes</h3><p>Prouesses, quête, amour courtois, merveilleux.</p>'],
    '127' => ['Nature', 'Adjectifs qualificatifs.', '<h3>1. Fonction</h3><p>Épithète (collé au nom) ou Attribut (séparé par un verbe d\'état).</p>'],

    // --- 4EME ---
    // Attention aux erreurs de classification (Maths dans Français), on ne traite que les vrais sujets FR
    '161' => ['Analyse', 'Analyse logique.', '<h3>1. Propositions</h3><p>Indépendante, principale, subordonnée.</p>'],
    '163' => ['Essai', 'La nouvelle réaliste.', '<h3>1. Caractéristiques</h3><p>Récit court, chute inattendue, cadre réaliste.</p>'],
    '166' => ['Grammaire', 'Les paroles rapportées.', '<h3>1. Discours direct</h3><p>Guillemets, tirets. "Je pars demain".</p><h3>2. Indirect</h3><p>Il a dit qu\'il partirait le lendemain.</p>'],
    '170' => ['Procedes', 'Figures de style (1).', '<h3>1. Comparaison/Métaphore</h3><p>Rapprochement de deux éléments. La métaphore n\'a pas d\'outil de comparaison.</p>'],

    // --- 2NDE ---
    '201' => ['Analyse', 'L\'objet d\'étude.', '<h3>1. Genres</h3><p>Roman, Théâtre, Poésie, Littérature d\'idées.</p>'],
    '202' => ['Courants', 'Réalisme et Naturalisme.', '<h3>1. Auteurs clés</h3><p>Balzac, Flaubert, Zola, Maupassant.</p>'],
    '203' => ['Figures', 'Figures de style (2).', '<h3>1. Opposition</h3><p>Antithèse, Oxymore.</p><h3>2. Amplification</h3><p>Hyperbole, Anaphore, Gradation.</p>'],
    '205' => ['Types', 'Registres littéraires.', '<h3>1. Liste</h3><p>Tragique, comique, pathétique, lyrique, épique...</p>'],

    // --- 1ERE ---
    '240' => ['Argumentation', 'Convaincre et Persuader.', '<h3>1. Différence</h3><p>Convaincre fait appel à la raison (arguments logiques). Persuader fait appel aux sentiments.</p>'],
    '244' => ['Narratologie', 'Focalisation (Point de vue).', '<h3>1. Types</h3><p>Zéro (Omniscient), Interne (à travers un personnage), Externe (caméra objective).</p>'],
    '246' => ['Versification', 'Règles de poésie.', '<h3>1. Vers</h3><p>Alexandrin (12), Décasyllabe (10). "e" muet.</p><h3>2. Rimes</h3><p>Plates (AABB), Croisées (ABAB), Embrassées (ABBA).</p>'],

    // --- TERMINALE ---
    '247' => ['Analyse', 'L\'inconscient (Philo/Litt).', '<h3>1. Freud</h3><p>Le ça, le moi et le surmoi.</p>'],
    '249' => ['Commentaire', 'Méthode du commentaire composé.', '<h3>1. Plan</h3><p>Introduction, 2 ou 3 grandes parties (axes), Conclusion. Ne jamais paraphraser.</p>'],
    '251' => ['Dissertation', 'Méthode de la dissertation.', '<h3>1. Plan dialectique</h3><p>Thèse, Antithèse, Synthèse (Dépassement).</p>'],
    '253' => ['Philosophie', 'La conscience.', '<h3>1. Descartes</h3><p>"Cogito, ergo sum" (Je pense, donc je suis).</p>']
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
    echo "\n🎉 Succès : $count cours de Français (+ Philo) mis à jour.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
