<?php
// dev/tools/courses/updates/update_full_batch_english.php
// Génération de contenu pour les cours d'Anglais

$rootDir = dirname(dirname(dirname(dirname(__DIR__))));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

echo "🚀 Génération Contenu Anglais\n";

$definitions = [
    // --- 6EME ---
    '45' => ['Daily', 'Daily Routine.', '<h3>1. Vocabulaire</h3><p>Wake up, get up, have breakfast, brush my teeth, go to school.</p>'],
    '46' => ['Decrire', 'Describing people.', '<h3>1. Be vs Have</h3><p>I am tall (Be). I have blue eyes (Have got).</p>'],
    '49' => ['Modals', 'Can / Can\'t.', '<h3>1. Capacité</h3><p>I can swim. I cannot (can\'t) fly.</p>'],
    '50' => ['Past', 'Introduction to Past Simple.', '<h3>1. Be</h3><p>I was, you were, he was...</p>'],
    '52' => ['Present', 'Present Simple vs Continuous.', '<h3>1. Simple</h3><p>Habitudes (often, usually). I play tennis.</p><h3>2. Continuous</h3><p>Action en cours (now). I am playing tennis.</p>'],
    '53' => ['Presenter', 'Introducing yourself.', '<h3>1. Basics</h3><p>My name is... I am ... years old. I live in...</p>'],

    // --- 5EME ---
    '44' => ['Adjectifs', 'Comparatives and Superlatives.', '<h3>1. Short adj</h3><p>Tall -> Taller -> The tallest.</p><h3>2. Long adj</h3><p>Expensive -> More expensive -> The most expensive.</p>'],
    '109' => ['Present', 'Present perfect (initiation).', '<h3>1. Construction</h3><p>Have/Has + Past Participle.</p>'],
    '111' => ['Pronoms', 'Possessive pronouns.', '<h3>1. Liste</h3><p>Mine, yours, his, hers, ours, theirs. (It\'s my car -> It\'s mine).</p>'],
    '112' => ['Questions', 'WH- Questions.', '<h3>1. Mots interrogatifs</h3><p>Who (qui), What (quoi), Where (où), When (quand), Why (pourquoi), How (comment).</p>'],

    // --- 3EME --- (Previously enriched examples only)
    '1' => ['Email', 'Writing an email.', '<h3>1. Structure</h3><p>Hi John, ... I\'m writing to tell you... See you soon, [Name].</p>'],
    '4' => ['Grammaire', 'Preterit simple vs BE-ing.', '<h3>1. Rupture</h3><p>I was sleeping (action longue) when the phone rang (action courte).</p>'],
    '5' => ['If', 'First Conditional.', '<h3>1. Structure</h3><p>If + Present, ... Will + Base verbale. "If it rains, I will stay home."</p>'],
    '6' => ['Passive', 'Passive Voice.', '<h3>1. Formation</h3><p>Subject + Be (conjugue) + Past Participle + (by agent).</p>'],
    '8' => ['Subjonctif', 'Expression du souhait.', '<h3>1. I want you to...</h3><p>I want you to clean your room. (Pas de "that").</p>'],

    // --- 2NDE / 1ERE ---
    '154' => ['Conditionnel', 'Second Conditional.', '<h3>1. Hypothèse irréelle</h3><p>If + Preterit, ... Would + Base verbale. "If I were rich, I would buy a boat."</p>'],
    '156' => ['Opinion', 'Giving your opinion.', '<h3>1. Expressions</h3><p>I think that, In my opinion, To my mind, I reckon, I agree/disagree.</p>'],
    '159' => ['Reported', 'Reported Speech.', '<h3>1. Concordance</h3><p>Present -> Past. Will -> Would. Can -> Could. Yesterday -> The day before.</p>'],
    '195' => ['Modals', 'Modals of probability.', '<h3>1. Degrés</h3><p>Must (90%), May/Might (50%), Can\'t (0%). "He must be at home."</p>'],

    // --- TERMINALE ---
    '233' => ['Essay', 'Writing an essay.', '<h3>1. Method</h3><p>Intro (Hook, Definition, Problem, Plan). Paragraphs with linking words. Conlusion.</p>'],
    '234' => ['Inversion', 'Inversion stylistique.', '<h3>1. Emphase</h3><p>Never have I seen such a thing (au lieu de I have never...).</p>'],
    '238' => ['Synthese', 'Synthèse de documents.', '<h3>1. Objectif</h3><p>Trouver les points communs et différences entre les documents sans donner son avis.</p>']
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
    echo "\n🎉 Succès : $count cours d'Anglais mis à jour.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
