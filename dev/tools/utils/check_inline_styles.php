#!/usr/bin/env php
<?php
// Usage: php tools/check_inline_styles.php [--include-backups]

$includeBackups = false;
foreach ($argv as $a) if ($a === '--include-backups') $includeBackups = true;

$root = __DIR__ . '/../';
$exclude = ['vendor', '.git', 'backups'];
if ($includeBackups) $exclude = ['vendor', '.git'];

$pattern = '/(<style\b[^>]*>.*?<\/style>)|style\s*=\s*"([^"]*)"/is';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$count = 0;
$found = [];

foreach ($files as $file) {
    if ($file->isDir()) continue;
    $path = str_replace('\\', '/', $file->getPathname());
    $rel = substr($path, strlen($root));

    // skip excludes
    $skip = false;
    foreach ($exclude as $e) { if (strpos($rel, $e . '/') === 0 || $rel === $e) { $skip = true; break; } }
    if ($skip) continue;

    // only search HTML/PHP/HTM files
    if (!preg_match('/\.(php|html|htm)$/i', $rel)) continue;

    $content = file_get_contents($path);
    if (preg_match_all($pattern, $content, $m, PREG_OFFSET_CAPTURE)) {
        foreach ($m[0] as $match) {
            $count++;
            $found[] = [ 'file' => $rel, 'match' => trim($match[0]) ];
        }
    }
}

if ($count === 0) {
    echo "OK — no inline <style> or style=\"...\" found (include-backups=" . ($includeBackups? 'true':'false') . ").\n";
    exit(0);
}

echo "$count inline style occurrences found (include-backups=" . ($includeBackups? 'true':'false') . "):\n";
foreach ($found as $f) {
    echo " - {$f['file']}: " . (strlen($f['match'])>80? substr($f['match'],0,80).'...': $f['match']) . "\n";
}

// exit with failure code so CI can block
exit(2);
