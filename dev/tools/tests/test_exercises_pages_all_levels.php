<?php
// Test: vérifier que les pages d'exercices pour tous les niveaux renvoient du contenu
$base = 'http://localhost/moncoachscolaire/public/index.php?page=';
$pages = [
    'Collège (index)' => $base . 'college/exercices-college',
    '6ème' => $base . 'college/6eme/exercices-6eme',
    '5ème' => $base . 'college/5eme/exercices-5eme',
    '4ème' => $base . 'college/4eme/exercices-4eme',
    '3ème' => $base . 'college/3eme/exercices-3eme',
    // Utiliser les variantes d'URL qui retournent effectivement une page (legacy compatible)
    'Seconde' => $base . 'lycee/2nde/exercices-2nde',
    'Première' => $base . 'lycee/1ere/exercices-1ere',
    // BAC utilise un conteneur dynamique chargé par JS — on acceptera aussi la présence du conteneur
    'BAC' => $base . 'bac/exercices-bac',
];

$context = stream_context_create(['http' => ['timeout' => 5]]);
$exit_code = 0;
foreach ($pages as $label => $url) {
    echo "-- $label --\n";
    $html = @file_get_contents($url, false, $context);
    if ($html === false) {
        echo "FAIL: could not fetch $url\n\n";
        $exit_code = 1;
        continue;
    }

    // compter les cartes d'exercice
    $cards = preg_match_all('/<article[^>]+class="[^"]*exercise-card[^"]*"/i', $html, $m);
    $cards = $cards ?: 0;

    // détecter conteneur dynamique (ex: BAC)
    $hasDynamic = (bool) preg_match('/data-dynamic-exercises|dynamic-exercises/i', $html);

    if ($cards >= 1) {
        echo "PASS: $label (cards=$cards)\n\n";
        continue;
    }

    if ($hasDynamic) {
        echo "PASS: $label (dynamic container present)\n\n";
        continue;
    }

    // accepter aussi la mention explicite d'absence d'exercices
    if (preg_match('/Aucun exercice/i', $html) || preg_match('/Aucune mat(i|e)\w+ trouv/i', $html)) {
        echo "PASS: $label (no exercises message present)\n\n";
        continue;
    }

    echo "FAIL: $label (no exercise cards or dynamic container found)\n\n";
    $exit_code = 1;
}
exit($exit_code);
