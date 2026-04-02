<?php
// tools/test_router_candidates.php
// Simulate index.php candidate resolution for a given page string.
$root = realpath(__DIR__ . '/..');

function resolve_candidate_for($pageRaw, $root) {
    $candidates = [];
    if (preg_match('/\.(php|html)$/i', $pageRaw)) {
        $candidates[] = $root . DIRECTORY_SEPARATOR . $pageRaw;
    } else {
        $candidates[] = $root . DIRECTORY_SEPARATOR . $pageRaw . DIRECTORY_SEPARATOR . 'index.php';
        $candidates[] = $root . DIRECTORY_SEPARATOR . $pageRaw . '.php';
        $candidates[] = $root . DIRECTORY_SEPARATOR . $pageRaw . '.html';
        $candidates[] = $root . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $pageRaw . DIRECTORY_SEPARATOR . 'index.php';
        $candidates[] = $root . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $pageRaw . '.php';
        $candidates[] = $root . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $pageRaw . '.html';
    }

    foreach ($candidates as $cand) {
        $real = realpath($cand);
        echo "Checking candidate: $cand => ";
        if ($real && is_file($real) && strpos($real, $root) === 0) {
            echo "FOUND: $real\n";
            return $real;
        }
        echo "not found\n";
    }
    echo "No candidate resolved for pageRaw='$pageRaw'\n";
    return null;
}

$tests = [
    'college+' ,
    'college/college+' ,
    'college/college%2B',
    'college%2Fcollege%2B',
    'college/college',
    'lycee/lycee%2B',
    'lycee/lycee',
    'bac/bac%2B',
    'bac/bac'
];

foreach ($tests as $t) {
    echo "\n=== Test: '$t' => decoded: '" . urldecode($t) . "' ===\n";
    resolve_candidate_for(urldecode($t), $root);
}

exit(0);
