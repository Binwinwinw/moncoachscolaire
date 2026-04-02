<?php
// Scan project files for root-absolute links like href="/path" or src="/path"
// Usage: php scan_root_links.php [--json] [--ext=php,html,htm,inc,js,css]

$opts = getopt('', ['json', 'ext:']);
$asJson = isset($opts['json']);
$exts = isset($opts['ext']) ? explode(',', $opts['ext']) : ['php','html','htm','inc','js','css'];

$root = realpath(__DIR__ . '/..');
if (!$root) $root = __DIR__ . '/..';

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    // skip backups, vendor, node_modules
    if (preg_match('#\\b(backups|vendor|node_modules|.git)\\b#', $path)) continue;
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), $exts, true)) continue;
    $files[] = $path;
}

$report = ['files'=>[],'total_files'=>0,'total_occurrences'=>0];
foreach ($files as $f) {
    $content = @file_get_contents($f);
    if ($content === false) continue;
    // find href="/..." or href='/...' and src equivalents; ignore protocol-relative //
    if (preg_match_all('#(href|src)\s*=\s*([\"\'])(/[^/][^\"\']*)\2#i', $content, $m, PREG_SET_ORDER)) {
        $occ = count($m);
        $report['files'][] = ['file'=>$f,'occurrences'=>$occ,'matches'=>array_map(function($it){ return $it[0]; }, $m)];
        $report['total_files']++;
        $report['total_occurrences'] += $occ;
    }
}

if ($asJson) {
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
    echo "Scan root-absolute links report:\n";
    echo "Files with matches: {$report['total_files']}\n";
    echo "Total occurrences: {$report['total_occurrences']}\n\n";
    foreach ($report['files'] as $f) {
        echo "- {$f['file']} ({$f['occurrences']})\n";
        foreach ($f['matches'] as $m) {
            echo "    $m\n";
        }
    }
}

// Exit code 0 always; script is informational
exit(0);
