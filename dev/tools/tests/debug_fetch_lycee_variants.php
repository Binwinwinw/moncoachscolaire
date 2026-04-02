<?php
$tests = [
    'Seconde variants' => [
        'http://localhost/moncoachscolaire/public/index.php?page=lycee/2nde/exercices-lycee&niveau=2nde',
        'http://localhost/moncoachscolaire/public/index.php?page=lycee/2nde/exercices-2nde',
        'http://localhost/moncoachscolaire/public/index.php?page=lycee/seconde/exercices-seconde',
        'http://localhost/moncoachscolaire/public/index.php?page=lycee/exercices-lycee&niveau=2nde'
    ],
    'Première variants' => [
        'http://localhost/moncoachscolaire/public/index.php?page=lycee/1ere/exercices-lycee&niveau=1ere',
        'http://localhost/moncoachscolaire/public/index.php?page=lycee/1ere/exercices-1ere',
        'http://localhost/moncoachscolaire/public/index.php?page=lycee/premiere/exercices-premiere',
        'http://localhost/moncoachscolaire/public/index.php?page=lycee/exercices-lycee&niveau=1ere'
    ],
    'BAC variants' => [
        'http://localhost/moncoachscolaire/public/index.php?page=bac/exercices-bac',
        'http://localhost/moncoachscolaire/public/index.php?page=bac/exercices-bac&subject=Mathematiques'
    ]
];
$context = stream_context_create(['http'=>['timeout'=>5]]);
foreach ($tests as $label => $urls) {
    echo "== $label ==\n";
    foreach ($urls as $url) {
        echo "URL: $url\n";
        $html = @file_get_contents($url, false, $context);
        if ($html === false) { echo "  -> could not fetch\n"; continue; }
        $len = strlen($html);
        echo "  -> length: $len\n";
        $hasCards = (bool) preg_match('/<article[^>]+class="[^"]*exercise-card[^"]*"/i', $html);
        $hasDynamic = (bool) preg_match('/data-dynamic-exercises|dynamic-exercises/i', $html);
        echo "  -> has cards: " . ($hasCards ? 'yes' : 'no') . "; has dynamic: " . ($hasDynamic ? 'yes' : 'no') . "\n";
        // print small snippet
        $snippet = substr(trim(preg_replace('/\s+/', ' ', $html)), 0, 400);
        echo "  -> snippet: " . preg_replace('/\s+/', ' ', $snippet) . "\n";
    }
    echo "\n";
}
