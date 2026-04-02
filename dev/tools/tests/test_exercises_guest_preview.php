<?php
// Smoke test: Guests should see a limited preview (<=3 exercises) and no action buttons
$url = 'http://localhost/moncoachscolaire/public/index.php?page=lycee/terminale/exercices-terminale';
$context = stream_context_create(['http' => ['timeout' => 5]]);
$html = @file_get_contents($url, false, $context);
if ($html === false) {
    echo "FAIL: could not fetch $url\n";
    exit(1);
}

// Count exercise cards
$cards = preg_match_all('/<article[^>]+class="[^"]*exercise-card[^"]*"/i', $html, $m);
if ($cards === false) $cards = 0;
if ($cards > 3) {
    echo "FAIL: guest sees too many exercise cards ($cards > 3)\n";
    exit(1);
}

// Ensure no action buttons are present
if (preg_match('/btn-exercise-complete|btn-duplicate|btn-toggle-active/i', $html)) {
    echo "FAIL: guest page contains interactive action buttons (should be disabled in preview)\n";
    exit(1);
}

// Ensure preview CTA present
if (!preg_match('/cr[eé]ez un compte|créer un compte|créez un compte/i', $html)) {
    echo "FAIL: preview CTA not found\n";
    exit(1);
}

echo "PASS: guest preview correct (cards=$cards)\n";
exit(0);
