<?php
$urls = [
    'Seconde' => 'http://localhost/moncoachscolaire/public/index.php?page=lycee/exercices-lycee&niveau=2nde',
    'Première' => 'http://localhost/moncoachscolaire/public/index.php?page=lycee/exercices-lycee&niveau=1ere',
    'BAC' => 'http://localhost/moncoachscolaire/public/index.php?page=bac/exercices-bac',
];
$context = stream_context_create(['http'=>['timeout'=>5]]);
foreach ($urls as $label => $url) {
    echo "---- $label ----\nURL: $url\n";
    $html = @file_get_contents($url, false, $context);
    if ($html === false) {
        echo "Could not fetch page\n\n";
        continue;
    }
    $snippet = substr(trim(preg_replace('/\s+/', ' ', $html)), 0, 800);
    echo "Snippet:\n" . $snippet . "\n\n";
    $hasCards = (bool) preg_match('/<article[^>]+class="[^"]*exercise-card[^"]*"/i', $html);
    echo "Has exercise cards: " . ($hasCards ? 'yes' : 'no') . "\n";
    // detect common login prompts or access strings
    $signals = ['connectez-vous', 'créez un compte', 'identifiant', 'mot de passe', 'vous devez', 'Accès', 'bloqu', 'login', 'Connexion'];
    foreach ($signals as $s) {
        if (stripos($html, $s) !== false) {
            echo "Contains hint: '$s'\n";
        }
    }
    echo "\n";
}
