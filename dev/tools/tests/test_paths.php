<?php
header('Content-Type: text/plain');

$root = dirname(__DIR__);
echo "Root: $root\n";

$target = $root . '/src/api/admin/exercises_api.php';
echo "Target Raw: $target\n";
echo "Target Realpath: " . realpath($target) . "\n";
echo "Target Exists: " . (file_exists($target) ? 'YES' : 'NO') . "\n";

$targetQuality = $root . '/src/api/admin/exercise_quality.php';
echo "Quality Realpath: " . realpath($targetQuality) . "\n";

// Emulate Router Check
$realApiFile = realpath($target);
$realSrcApi = realpath($root . '/src/api');
echo "Src Realpath: " . $realSrcApi . "\n";

if ($realApiFile && $realSrcApi) {
    $normApi = str_replace('\\', '/', strtolower($realApiFile));
    $normSrc = str_replace('\\', '/', strtolower($realSrcApi));
    echo "Norm API: $normApi\n";
    echo "Norm Src: $normSrc\n";

    if (strpos($normApi, $normSrc) === 0) {
        echo "CHECK: PASS\n";
    } else {
        echo "CHECK: FAIL (strpos)\n";
    }
} else {
    echo "CHECK: FAIL (realpath returned false)\n";
}
