<?php
// Simple accessibility smoke test for exercices_admin page
// It expects a running server at http://127.0.0.1:8080

$url = 'http://localhost/moncoachscolaire/public/index.php?page=exercices_admin';
$context = stream_context_create(['http' => ['timeout' => 5]]);
$html = @file_get_contents($url, false, $context);
if ($html === false) {
    echo "FAIL: unable to fetch $url (is the server running?)\n";
    exit(1);
}

// If the page requires login (admin), accept that and skip the test
if (preg_match('/login|connexion|mot de passe|name="username"/i', $html)) {
    echo "SKIP: page requires admin login; cannot fully assert accessibility without auth\n";
    exit(0);
}

// Check that at least one article with role=article exists
if (!preg_match('/<article[^>]+role="article"/i', $html)) {
    echo "FAIL: no accessible article elements found in page\n";
    exit(1);
}

// Check that toggles have aria-controls and aria-expanded
if (!preg_match('/class="exercise-toggle"[^>]*aria-controls="[^\"]+"/i', $html)) {
    echo "FAIL: toggles missing aria-controls\n";
    exit(1);
}
if (!preg_match('/class="exercise-toggle"[^>]*aria-expanded="[^"]+"/i', $html)) {
    echo "FAIL: toggles missing aria-expanded\n";
    exit(1);
}

// Check that collapsible contents have aria-hidden
if (!preg_match('/class="[^">]*collapsible[^">]*"[^>]*aria-hidden="[^"]+"/i', $html)) {
    echo "FAIL: collapsible sections missing aria-hidden\n";
    exit(1);
}

echo "PASS: basic accessibility attributes present\n";
exit(0);
