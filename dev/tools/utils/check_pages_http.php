<?php
// tools/check_pages_http.php
// Check HTTP status and per-page CSS link for a list of routes.

$root = realpath(__DIR__ . '/..');
// Load config if available to get $baseUrl
$baseUrl = 'http://localhost/moncoachscolaire';
if (is_file($root . '/config.php')) {
    @include $root . '/config.php';
    if (isset($baseUrl) && $baseUrl) {
        // keep as provided by config.php
    }
}

$routes = [
    'college/college',
    'lycee/lycee',
    'bac/bac'
];

foreach ($routes as $route) {
    $full = rtrim($baseUrl, '/') . '/index.php?page=' . $route;
    echo "\n--- Testing route: $route\n";
    // get headers
    $headers = @get_headers($full, 1);
    if (!$headers) {
        echo "ERROR: no response for $full\n";
        continue;
    }
    // first header contains status
    $statusLine = $headers[0] ?? '';
    preg_match('#HTTP/\d+\.\d+\s+(\d+)#', $statusLine, $m);
    $status = $m[1] ?? 'unknown';
    echo "HTTP status: $status\n";

    if ((int)$status !== 200) {
        echo "  ⚠️ Page not returning 200 — stop checks for this route.\n";
        continue;
    }

    // fetch body
    $body = @file_get_contents($full);
    if ($body === false) {
        echo "ERROR: could not fetch response body for $full\n";
        continue;
    }

    // search for per-page CSS reference (assets/css/pages/...)
    if (preg_match('/<link[^>]+href=["\']([^"\']*assets\/css\/pages[^"\']*)["\']/i', $body, $m)) {
        $cssHref = $m[1];
        // make absolute if needed
        if (strpos($cssHref, 'http') !== 0) {
            // treat as relative path from baseUrl
            $cssUrl = rtrim($baseUrl, '/') . '/' . ltrim($cssHref, '/');
        } else {
            $cssUrl = $cssHref;
        }
        echo "Found per-page CSS link: $cssUrl\n";
        $cssHeaders = @get_headers($cssUrl, 1);
        if (!$cssHeaders) {
            echo "  ⚠️ CSS resource did not respond.\n";
            continue;
        }
        $s = $cssHeaders[0] ?? '';
        preg_match('#HTTP/\d+\.\d+\s+(\d+)#', $s, $mm);
        $cssStatus = $mm[1] ?? 'unknown';
        echo "  CSS HTTP status: $cssStatus\n";
        if ((int)$cssStatus !== 200) echo "  ⚠️ CSS returned non-200\n";
    } else {
        echo "  ⚠️ No per-page CSS link found in page HTML.\n";
    }
}

exit(0);
