<?php
/**
 * Met à jour les réponses depuis un mapping manuel (extrait des pages corrections PDF).
 * Match: Level + Subject + Title LIKE 'Exercice X.Y%'.
 */

require_once __DIR__ . '/../db/connection.php';

$mapping = [
    '6ème' => [
        'Mathématiques' => [
            '1.1' => '1) 63,12 • 2) 48,33 • 3) 62 • 4) 13,15 • 5) 113,4',
            '1.2' => 'Placer sur la droite entre 0 et 3 en respectant les positions',
            '1.3' => '15 × 18 ÷ 6 = 45 yaourts',
            '2.1' => '1) 26 cm • 2) 24 cm • 3) 12 cm',
            '2.2' => '1) parallèles • 2) perpendiculaires • 3) équerre / règle',
            '3.1' => '1) 150 • 2) 3 • 3) 24 • 4) 2',
            '3.2' => '1) 60 cm² • 2) 25 cm² • 3) 16 cm²',
        ],
        'Français' => [
            '1.1' => 'Identier verbes et sujets dans chaque phrase',
            '1.2' => '1) article • 2) adverbe • 3) article • 4) adverbe',
            '1.3' => '1) le ballon • 2) un lm • 3) des fruits • 4) la leçon',
            '2.1' => 'Être : suis, es, est, sommes, êtes, sont • Avoir : ai, as, a, avons, avez, ont',
            '2.2' => '1) était, jouais • 2) habitions • 3) faisaient • 4) avais',
            '2.3' => '1) suis allé(e) • 2) a mangé • 3) avez vu • 4) ont joué',
            '3.1' => '1) heureux • 2) grande • 3) blondes • 4) intelligent',
            '3.2' => '1) jeu • 2) lecteur • 3) marche • 4) dormir/sommeil',
            '3.3' => 'Beau = magnique/laid • Rapide = vite/lent • Grand = immense/petit • Chaud = brûlant/froid',
        ],
        'Anglais' => [
            '1.1' => '1) I • 2) They • 3) She • 4) We • 5) He',
            '1.2' => '1) am • 2) are • 3) is • 4) are • 5) are',
            '1.3' => 'I play • He/She plays • They play • We play',
            '2.1' => '1) red • 2) blue • 3) green • 4) yellow • 5) black',
            '2.2' => '1) father • 2) brother • 3) grandfather • 4) daughter',
            '2.3' => '1) five • 2) twelve • 3) twenty • 4) one hundred • 5) three hundred sixty-five',
        ],
    ],
    '5ème' => [
        'Mathématiques' => [
            '1.1' => '1) • 2) • 3) • 4) • 5)',
            '1.2' => '1) • 2) • 3) • 4)',
            '1.3' => '1) 8 • 2) 25 • 3) 1000 • 4) 9 • 5) -8',
            '1.4' => '5 €, 10 €, 25 €',
            '2.1' => '1) équilatéral • 2) isocèle • 3) rectangle • 4) quelconque',
            '2.2' => '1) parallèles / égaux • 2) coupent en leur milieu • 3) parallélogramme • 4) égaux',
            '3.1' => '1) 3500 m • 2) 4,5 km • 3) 2300 g • 4) 0,75 kg',
            '3.2' => '1) 60 cm² • 2) 30 cm² • 3) 40 cm²',
            '3.3' => 'Moyenne = 12,8 • ≥15 : 4 élèves • <10 : 2 élèves',
        ],
        'Français' => [
            '1.1' => '1) S • 2) C • 3) C • 4) C',
            '1.2' => '1) relative • 2) circonstancielle • 3) relative',
            '1.3' => '1) sujet • 2) COD • 3) COI • 4) COD',
            '2.1' => '1) irons • 2) jouais • 3) fais • 4) seront',
            '2.2' => '1) avons ni • 2) avait vu • 3) a pris • 4) avaient mangé',
            '2.3' => '1) écrites • 2) arrivées • 3) chantée • 4) promenés',
            '3.1' => '1) a / et • 2) est / à • 3) et • 4) a / à',
            '3.2' => '1) clarté • 2) chanteur • 3) rapidement • 4) lecteur',
        ],
        'Anglais' => [
            '1.1' => '1) goes • 2) is raining • 3) play • 4) are doing',
            '1.2' => '1) went • 2) watched • 3) had • 4) studied',
            '1.3' => "1) Did he play football? / He didn't play football • 2) Did they go to London? / They didn't go to London",
            '2.1' => "1) I wake up at 7 o'clock • 2) I have breakfast • 3) I go to school • 4) I do my homework",
            '2.2' => '1) notebook • 2) pen • 3) ruler • 4) backpack',
            '2.3' => '1) small • 2) short • 3) fast • 4) young',
        ],
    ],
    '4ème' => [
        'Mathématiques' => [
            '1.1' => '1) 7 • 2) 10 • 3) -13 • 4) 24 • 5) -6',
            '1.2' => '1) 0,75 • 2) 1,4 • 3) 0,25 • 4) 1,5',
            '1.3' => '1) 16 • 2) -27 • 3) 28 • 4) 2',
            '2.1' => '1) • 2) • 3) • 4)',
            '2.2' => '1) • 2) • 3) • 4)',
            '2.3' => '1) x = 4 • 2) x = 3 • 3) x = 7 • 4) x = 4',
            '3.1' => 'BC = 10 cm',
            '3.2' => '1) rectangle • 2) rectangle • 3) non rectangle',
            '3.3' => '1) 1/2 • 2) AC = 8 cm • 3) DE = 4 cm',
        ],
        'Français' => [
            '1.1' => '1) conjonctive • 2) circonstancielle • 3) relative',
            '1.2' => '1) sujet • 2) COD + complément circonstanciel • 3) COI • 4) complément circonstanciel',
            '2.1' => '1) mangea • 2) jouions • 3) fais',
            '2.2' => '1) lirais • 2) aimerions • 3) pourrais • 4) voudraient',
            '3.1' => '1) portées • 2) dépêchées • 3) écrite',
            '3.2' => 'Exemples : frayeur, angoisse, terreur, inquiétude, crainte, panique',
        ],
        'Anglais' => [
            '1.1' => '1) saw • 2) has already nished • 3) went • 4) have just arrived',
            '1.2' => '1) for • 2) since • 3) for • 4) since',
            '2.1' => '1) taller • 2) more interesting • 3) better • 4) farther / further',
            '2.2' => '1) I have maths lessons on Monday • 2) I love reading books • 3) I play football after school',
        ],
    ],
    '3ème' => [
        'Mathématiques' => [
            '1.1' => '1) 6 • 2) 15 • 3) 7 • 4) 9',
            '1.2' => '1) x = 8 • 2) x = -2 • 3) x = 6',
            '1.3' => '1) pente = 2 • 2) f(x) = 2x + 2 • 3) f(5) = 12',
            '2.1' => '1) AD/AB = 1/3 • 2) AC = 12 cm',
            '2.2' => '1) BC = 13 cm • 2) cos B = 5/13',
            '3.1' => 'Fréquences : 0,2 ; 0,4 ; 0,3 ; 0,1 • Classe 1-2 h la plus fréquente',
            '3.2' => '1) 1/2 • 2) 1/3',
        ],
        'Français' => [
            '1.1' => '1) argumentatif • 2) descriptif • 3) narratif',
            '1.2' => "1) mais • 2) donc • 3) D'abord • 4) en eet",
            '2.1' => "1) qu'il viendra demain • 2) qu'elle avait terminé son travail • 3) qu'ils ne pouvaient pas venir",
        ],
        'Anglais' => [
            '1.1' => '1) English is spoken all over the world • 2) This bridge was built in 1990',
            '1.2' => '1) will stay • 2) would travel • 3) will pass',
            '2.1' => '1) In my opinion, the Internet is very useful • 2) I think that books are important • 3) I do not agree with you',
        ],
    ],
];

$totalUpdated = 0;
$perLevel = [];

$update = $pdo->prepare("UPDATE exercises SET Answer = ? WHERE Level = ? AND Subject = ? AND Title LIKE ?");

foreach ($mapping as $level => $subjects) {
    $perLevel[$level] = 0;
    foreach ($subjects as $subject => $exos) {
        foreach ($exos as $num => $answer) {
            $like = "Exercice $num%";
            $update->execute([$answer, $level, $subject, $like]);
            $count = $update->rowCount();
            $totalUpdated += $count;
            $perLevel[$level] += $count;
        }
    }
}

echo "✅ Mises à jour terminées\n";
echo "  Total réponses mises à jour: $totalUpdated\n";
foreach ($perLevel as $lvl => $cnt) {
    echo "  $lvl: $cnt\n";
}
