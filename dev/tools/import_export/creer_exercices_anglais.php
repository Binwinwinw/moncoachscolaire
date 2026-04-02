#!/usr/bin/env php
<?php
/**
 * CRÉATION D'EXERCICES D'ANGLAIS ORIGINAUX
 * Niveaux: 6ème à Terminale (A1 → B2)
 * 100% original, cohérent, objectif
 */

require_once __DIR__ . '/../config.php';

echo "🇬🇧 CRÉATION EXERCICES D'ANGLAIS ORIGINAUX\n";
echo "============================================================\n\n";

// Exercices par niveau
$exercises = [
    // 6ème - Niveau A1
    [
        'level' => '6ème',
        'subject' => 'Anglais',
        'title' => 'Le verbe TO BE au présent',
        'content' => "Complétez la phrase avec la forme correcte de 'to be':\n\n**I ___ a student.**",
        'answer' => "La réponse correcte est **am**. La conjugaison du verbe 'to be' au présent: I am, you are, he/she/it is, we are, you are, they are."
    ],
    [
        'level' => '6ème',
        'subject' => 'Anglais',
        'title' => 'Les nombres de 1 à 10',
        'content' => "Quel est le mot anglais pour le nombre 7?\n\na) six\nb) seven\nc) eight\nd) nine",
        'answer' => "La réponse correcte est **b) seven**. Les nombres en anglais: 1=one, 2=two, 3=three, 4=four, 5=five, 6=six, 7=seven, 8=eight, 9=nine, 10=ten."
    ],
    [
        'level' => '6ème',
        'subject' => 'Anglais',
        'title' => 'Les couleurs - Colors',
        'content' => "Quelle est la traduction de 'rouge' en anglais?\n\na) blue\nb) red\nc) green\nd) yellow",
        'answer' => "La réponse correcte est **b) red**. Les couleurs principales: red (rouge), blue (bleu), green (vert), yellow (jaune), black (noir), white (blanc)."
    ],
    [
        'level' => '6ème',
        'subject' => 'Anglais',
        'title' => 'Présent simple - Questions',
        'content' => "Comment dit-on 'Quel âge as-tu?' en anglais?\n\na) How are you?\nb) How old are you?\nc) What is your name?\nd) Where are you?",
        'answer' => "La réponse correcte est **b) How old are you?**. 'How old' signifie 'quel âge'. Pour demander l'âge en anglais, on utilise 'How old are you?'"
    ],
    [
        'level' => '6ème',
        'subject' => 'Anglais',
        'title' => 'La famille - Family',
        'content' => "Quel est le mot anglais pour 'mère'?\n\na) father\nb) mother\nc) sister\nd) brother",
        'answer' => "La réponse correcte est **b) mother**. La famille: father (père), mother (mère), brother (frère), sister (sœur), son (fils), daughter (fille)."
    ],
    
    // 5ème - Niveau A1/A2
    [
        'level' => '5ème',
        'subject' => 'Anglais',
        'title' => 'Present simple - Forme affirmative',
        'content' => "Choisissez la forme correcte:\n\n**She ___ to school every day.**\n\na) go\nb) goes\nc) going\nd) to go",
        'answer' => "La réponse correcte est **b) goes**. Au présent simple, avec he/she/it, on ajoute un 's' au verbe: I go, you go, he/she/it goes, we go, they go."
    ],
    [
        'level' => '5ème',
        'subject' => 'Anglais',
        'title' => 'Can - Capacité',
        'content' => "Complétez avec 'can' ou 'can\\'t':\n\n**Birds ___ fly.**\n\na) can\nb) can't\nc) are\nd) is",
        'answer' => "La réponse correcte est **a) can**. 'Can' exprime la capacité ou la possibilité. Les oiseaux peuvent voler = Birds can fly. 'Can't' est la forme négative."
    ],
    [
        'level' => '5ème',
        'subject' => 'Anglais',
        'title' => 'There is / There are',
        'content' => "Quelle est la forme correcte?\n\n**___ three books on the table.**\n\na) There is\nb) There are\nc) It is\nd) They are",
        'answer' => "La réponse correcte est **b) There are**. On utilise 'There is' pour le singulier et 'There are' pour le pluriel. Ici 'three books' est pluriel donc 'There are'."
    ],
    [
        'level' => '5ème',
        'subject' => 'Anglais',
        'title' => 'Adjectifs possessifs',
        'content' => "Complétez:\n\n**This is ___ book.** (le livre de Marie)\n\na) her\nb) his\nc) their\nd) your",
        'answer' => "La réponse correcte est **a) her**. Les adjectifs possessifs: my (mon/ma), your (ton/ta), his (son/sa masculin), her (son/sa féminin), its (son/sa neutre), our (notre), their (leur)."
    ],
    [
        'level' => '5ème',
        'subject' => 'Anglais',
        'title' => 'Heure - Telling the time',
        'content' => "Comment dit-on '3 heures' en anglais?\n\na) three hour\nb) three o'clock\nc) three hours\nd) the three",
        'answer' => "La réponse correcte est **b) three o'clock**. Pour donner l'heure pile, on utilise le format: [nombre] o'clock. Exemple: 5 o'clock = 5 heures."
    ],
    
    // 4ème - Niveau A2
    [
        'level' => '4ème',
        'subject' => 'Anglais',
        'title' => 'Preterit simple - Verbes réguliers',
        'content' => "Mettez le verbe au preterit:\n\n**Yesterday, I ___ (play) football.**\n\na) play\nb) plays\nc) played\nd) playing",
        'answer' => "La réponse correcte est **c) played**. Le preterit des verbes réguliers se forme en ajoutant -ed au verbe: play → played, walk → walked, work → worked."
    ],
    [
        'level' => '4ème',
        'subject' => 'Anglais',
        'title' => 'Comparatifs',
        'content' => "Complétez avec le comparatif:\n\n**My car is ___ than yours.** (rapide)\n\na) fast\nb) faster\nc) fastest\nd) more fast",
        'answer' => "La réponse correcte est **b) faster**. Pour les adjectifs courts (1-2 syllabes), le comparatif se forme avec -er: fast → faster, tall → taller, big → bigger."
    ],
    [
        'level' => '4ème',
        'subject' => 'Anglais',
        'title' => 'Present continuous',
        'content' => "Quelle est la forme correcte au présent progressif?\n\n**She ___ a book right now.**\n\na) read\nb) reads\nc) is reading\nd) reading",
        'answer' => "La réponse correcte est **c) is reading**. Le present continuous se forme avec: be (am/is/are) + verbe-ing. Il exprime une action en cours au moment où on parle."
    ],
    [
        'level' => '4ème',
        'subject' => 'Anglais',
        'title' => 'Question words - Mots interrogatifs',
        'content' => "Quel mot interrogatif pour demander 'Où'?\n\na) What\nb) When\nc) Where\nd) Who",
        'answer' => "La réponse correcte est **c) Where**. Les mots interrogatifs: What (Quoi/Quel), When (Quand), Where (Où), Who (Qui), Why (Pourquoi), How (Comment)."
    ],
    [
        'level' => '4ème',
        'subject' => 'Anglais',
        'title' => 'Quantifieurs - Much/Many',
        'content' => "Complétez:\n\n**How ___ money do you have?**\n\na) many\nb) much\nc) lot\nd) some",
        'answer' => "La réponse correcte est **b) much**. On utilise 'much' avec les noms indénombrables (money, water, sugar) et 'many' avec les noms dénombrables (books, cars, people)."
    ],
    
    // 3ème - Niveau A2/B1
    [
        'level' => '3ème',
        'subject' => 'Anglais',
        'title' => 'Present perfect',
        'content' => "Choisissez la forme correcte:\n\n**I ___ Paris three times.**\n\na) visited\nb) have visited\nc) am visiting\nd) visit",
        'answer' => "La réponse correcte est **b) have visited**. Le present perfect se forme avec: have/has + participe passé. Il relie le passé au présent et indique une expérience de vie."
    ],
    [
        'level' => '3ème',
        'subject' => 'Anglais',
        'title' => 'Conditionnel - If clauses (type 1)',
        'content' => "Complétez la phrase conditionnelle:\n\n**If it ___ tomorrow, we will stay home.**\n\na) rain\nb) rains\nc) will rain\nd) rained",
        'answer' => "La réponse correcte est **b) rains**. Dans les conditionnelles de type 1 (probable): If + présent simple, will + verbe. Exemple: If it rains, we will stay home."
    ],
    [
        'level' => '3ème',
        'subject' => 'Anglais',
        'title' => 'Superlatifs',
        'content' => "Quel est le superlatif de 'good'?\n\na) gooder\nb) goodest\nc) better\nd) best",
        'answer' => "La réponse correcte est **d) best**. Les superlatifs irréguliers: good → best, bad → worst, far → farthest. Le superlatif exprime le degré maximum: the best (le meilleur)."
    ],
    [
        'level' => '3ème',
        'subject' => 'Anglais',
        'title' => 'Voix passive - Présent',
        'content' => "Transformez à la voix passive:\n\n**They speak English here.**\n\na) English is spoken here.\nb) English speaks here.\nc) English was spoken here.\nd) English is speaking here.",
        'answer' => "La réponse correcte est **a) English is spoken here.** La voix passive au présent se forme avec: be (am/is/are) + participe passé. L'objet devient sujet."
    ],
    [
        'level' => '3ème',
        'subject' => 'Anglais',
        'title' => 'Modaux - Must / Have to',
        'content' => "Quelle est la différence?\n\n**You ___ wear a uniform at school.** (obligation externe)\n\na) must\nb) have to\nc) should\nd) can",
        'answer' => "La réponse correcte est **b) have to**. 'Have to' exprime une obligation externe (règlement), tandis que 'must' exprime une obligation interne (conviction personnelle)."
    ],
    
    // Lycée - Niveau B1/B2
    [
        'level' => 'Seconde',
        'subject' => 'Anglais',
        'title' => 'Past continuous',
        'content' => "Complétez au past continuous:\n\n**While I ___ (study), my phone rang.**\n\na) studied\nb) was studying\nc) have studied\nd) am studying",
        'answer' => "La réponse correcte est **b) was studying**. Le past continuous se forme avec: was/were + verbe-ing. Il exprime une action en cours dans le passé, souvent interrompue par une autre action."
    ],
    [
        'level' => 'Seconde',
        'subject' => 'Anglais',
        'title' => 'Reported speech - Discours indirect',
        'content' => "Transformez au discours indirect:\n\nDirect: \"I am tired,\" she said.\nIndirect: She said that she ___ tired.\n\na) is\nb) was\nc) has been\nd) will be",
        'answer' => "La réponse correcte est **b) was**. Au discours indirect, le temps change généralement: présent → passé. 'I am' devient 'she was'. On applique la concordance des temps."
    ],
    [
        'level' => 'Première',
        'subject' => 'Anglais',
        'title' => 'Conditionnel type 2 (irréel du présent)',
        'content' => "Complétez la conditionnelle de type 2:\n\n**If I ___ rich, I would travel the world.**\n\na) am\nb) was\nc) were\nd) will be",
        'answer' => "La réponse correcte est **c) were**. Conditionnel type 2 (irréel du présent): If + preterit, would + verbe. Avec 'I', on utilise 'were' (et non 'was') dans le style soutenu."
    ],
    [
        'level' => 'Première',
        'subject' => 'Anglais',
        'title' => 'Phrasal verbs - Look',
        'content' => "Quel phrasal verb signifie 'chercher'?\n\na) look at\nb) look for\nc) look after\nd) look up",
        'answer' => "La réponse correcte est **b) look for** (chercher). Look at = regarder, look after = s'occuper de, look up = consulter (dictionnaire), look for = chercher."
    ],
    [
        'level' => 'Terminale',
        'subject' => 'Anglais',
        'title' => 'Present perfect continuous',
        'content' => "Quelle est la forme correcte?\n\n**I ___ for two hours.**\n\na) am waiting\nb) have waited\nc) have been waiting\nd) waited",
        'answer' => "La réponse correcte est **c) have been waiting**. Le present perfect continuous (have/has been + verbe-ing) insiste sur la durée d'une action commencée dans le passé et qui continue."
    ],
];

echo "📝 Préparation de " . count($exercises) . " exercices d'anglais...\n\n";

$inserted = 0;
$errors = 0;

foreach ($exercises as $ex) {
    try {
        // Vérifier si existe déjà
        $stmt = $pdo->prepare("SELECT Id FROM Exercises WHERE Title = ? AND Level = ? AND Subject = ?");
        $stmt->execute([$ex['title'], $ex['level'], $ex['subject']]);
        
        if ($stmt->fetch()) {
            echo "⏭️  {$ex['level']} - {$ex['title']} (déjà existant)\n";
            continue;
        }
        
        // Insérer le nouvel exercice
        $stmt = $pdo->prepare("
            INSERT INTO Exercises (Level, Subject, Title, Content, Answer, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        
        $result = $stmt->execute([
            $ex['level'],
            $ex['subject'],
            $ex['title'],
            $ex['content'],
            $ex['answer']
        ]);
        
        if ($result) {
            echo "✅ {$ex['level']} - {$ex['title']}\n";
            $inserted++;
        } else {
            echo "❌ {$ex['level']} - {$ex['title']} (échec insertion)\n";
            $errors++;
        }
        
    } catch (Exception $e) {
        echo "❌ {$ex['level']} - {$ex['title']} - Erreur: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n============================================================\n";
echo "📊 RÉSULTAT\n";
echo "============================================================\n\n";
echo "✅ Exercices créés: $inserted\n";
echo "❌ Erreurs: $errors\n";
echo "⏭️  Déjà existants: " . (count($exercises) - $inserted - $errors) . "\n\n";

if ($inserted > 0) {
    echo "🎉 Les exercices d'anglais sont maintenant en base!\n";
    echo "💡 Pensez à les activer: UPDATE Exercises SET is_active = 1 WHERE Subject = 'Anglais'\n";
}
