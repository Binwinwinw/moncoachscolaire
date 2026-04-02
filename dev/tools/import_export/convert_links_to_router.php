<?php
// Scan files and convert internal links to .php/.html into router links index.php?page=...
// Usage: php convert_links_to_router.php [--apply] [--ext=php,html] [--include=path1,path2]

$opts = getopt('', ['apply','ext:','include:']);
$apply = isset($opts['apply']);
$exts = isset($opts['ext']) ? explode(',', $opts['ext']) : ['php','html'];
$include = isset($opts['include']) ? explode(',', $opts['include']) : [];

$root = realpath(__DIR__ . '/..');
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

$skipPatterns = ['backups', 'vendor', 'node_modules', '.git'];

$candidates = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    // skip certain directories
    $skip = false;
    foreach ($skipPatterns as $p) { if (stripos($path, $p) !== false) { $skip = true; break; } }
    if ($skip) continue;
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), $exts, true)) continue;
    if (!empty($include)) {
        $matchInclude = false;
        foreach ($include as $inc) { if (strpos($path, trim($inc)) !== false) { $matchInclude = true; break; } }
        if (!$matchInclude) continue;
    }
    $candidates[] = $path;
}

function normalize_path($baseFile, $link) {
    // ignore absolute root style '/something' (we consider internal without leading slash)
    if (strlen($link) === 0) return null;
    // remove query/hash for path normalization
    $linkNoQS = preg_replace('/[?#].*$/', '', $link);
    if (preg_match('#^(https?:)?//#', $linkNoQS)) return null; // protocol or protocol-relative
    if (preg_match('#^(mailto:|tel:|javascript:)#i', $linkNoQS)) return null;
    if (strpos($linkNoQS, 'index.php?page=') === 0) return null; // already routed

    // allow links that are plain filenames or relative paths
    // if link starts with '/', treat as root-relative (strip leading /)
    if ($linkNoQS[0] === '/') {
        $normalized = ltrim($linkNoQS, '/');
        return $normalized ?: null;
    }

    // Otherwise build absolute path from base file dir
    $baseDir = dirname($baseFile);
    $combined = $baseDir . DIRECTORY_SEPARATOR . $linkNoQS;
    // If target file exists, prefer realpath to get a canonical absolute path
    $real = realpath($combined);
    if ($real !== false) {
        // produce project-relative path
        $root = realpath(__DIR__ . '/..');
        $rootNorm = rtrim(str_replace(['\\','/'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR);
        if (strpos($real, $rootNorm) === 0) {
            $rel = ltrim(substr($real, strlen($rootNorm)), DIRECTORY_SEPARATOR);
            // normalize to forward slashes
            $rel = str_replace('\\', '/', $rel);
            // strip trailing index.php for canonical folder targets
            if (preg_match('#/index\.php$#i', $rel)) $rel = preg_replace('#/index\.php$#i', '', $rel);
            // strip file extension for cleaner route values
            $rel = preg_replace('#\.(php|html)$#i', '', $rel);
            if ($rel === '') return 'index';
            return $rel;
        }
    }
    // resolve .. and . parts
    $parts = preg_split('#[\\/]+#', $combined);
    $resolved = [];
    foreach ($parts as $part) {
        if ($part === '' || $part === '.') continue;
        if ($part === '..') { array_pop($resolved); continue; }
        $resolved[] = $part;
    }
    $abs = implode(DIRECTORY_SEPARATOR, $resolved);
    // find position of project root in abs and return a project-relative path
    $root = realpath(__DIR__ . '/..');
    $rootNorm = str_replace(['\\','/'], DIRECTORY_SEPARATOR, $root);
    if (strpos($abs, $rootNorm) === 0) {
        $rel = ltrim(substr($abs, strlen($rootNorm)), DIRECTORY_SEPARATOR);
    } else {
        // fallback: try to locate the project folder name inside the path
        $projName = basename($rootNorm);
        $pos = strpos($abs, $projName);
        if ($pos !== false) {
            $rel = substr($abs, $pos + strlen($projName) + 1);
        } else {
            $rel = $linkNoQS; // give up and return the original link
        }
    }
    // normalize separators to forward slash for the router
    $rel = str_replace('\\', '/', $rel);
    // canonicalize: remove redundant segments and trim
    $rel = preg_replace('#/+#', '/', $rel);
    $rel = trim($rel, "/ ");
    // if this path ends with /index.php remove the trailing index.php to produce cleaner route values
    if (preg_match('#/index\.php$#i', $rel)) {
        $rel = preg_replace('#/index\.php$#i', '', $rel);
    }
    // remove trailing .php or .html extension for cleaner routes
    $rel = preg_replace('#\.(php|html)$#i', '', $rel);
    // keep root index as 'index' rather than empty string
    if ($rel === '') return 'index';
    return $rel;
}

$changes = [];
foreach ($candidates as $f) {
    $content = @file_get_contents($f);
    if ($content === false) continue;
    // find anchor href only (not image src) that ends with .php or .html
    // regex will match href="index.php?page=scripts/something" or href="index.php?page=scripts/something"
    // capture just the target value (group 1) inside quotes
    if (!preg_match_all('#href\s*=\s*(?:"|\')([^"\']+\.(?:php|html))(?:"|\')#i', $content, $m, PREG_SET_ORDER)) continue;
    $fileChanges = [];
    foreach ($m as $match) {
        // old regex captured only the value as group 1
        $target = $match[1];
        $quote = (strpos($match[0], "'") !== false) ? "'" : '"';
        $orig = 'href=' . $quote . $target . $quote;
        // skip urls with protocol or index router already
        if (preg_match('#^(https?:)?//#i', $target) || strpos($target, 'index.php?page=') === 0) continue;
        // skip targets that look like anchor or javascript
        if (preg_match('#^(mailto:|tel:|javascript:)#i', $target)) continue;
        // normalize target path
        $norm = normalize_path($f, $target);
        if (!$norm) continue;
        $router = "index.php?page=" . $norm;
        $replacement = "href={$quote}{$router}{$quote}";
        $fileChanges[] = ['from'=>$orig, 'to'=>$replacement, 'target'=>$target, 'norm'=>$norm];
    }
    if ($fileChanges) $changes[$f] = $fileChanges;
}

// Report summary
echo "Scanned " . count($candidates) . " files. Found " . count($changes) . " files with link candidates.\n\n";
foreach ($changes as $file => $list) {
    echo "File: $file\n";
    foreach ($list as $c) echo "  - {$c['from']}  ->  href=\"index.php?page={$c['norm']}\"\n";
    echo "\n";
}

if ($apply && count($changes) > 0) {
    echo "Applying changes...\n";
    // create snapshot of affected files
    $timestamp = date('Ymd_His');
    $backups = $root . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . $timestamp;
    if (!is_dir($backups)) mkdir($backups, 0777, true);
    foreach ($changes as $file => $list) {
        $rel = ltrim(str_replace($root, '', $file), DIRECTORY_SEPARATOR);
        $destDir = dirname($backups . DIRECTORY_SEPARATOR . $rel);
        if (!is_dir($destDir)) mkdir($destDir, 0777, true);
        copy($file, $backups . DIRECTORY_SEPARATOR . $rel);
        $content = file_get_contents($file);
        foreach ($list as $c) {
            $content = str_replace($c['from'], "href=\"index.php?page={$c['norm']}\"", $content);
        }
        file_put_contents($file, $content);
        echo "Updated: $file (backup -> backups/$timestamp/$rel)\n";
    }
    echo "Done.\n";
}

if (!$apply) echo "Run with --apply to modify files in-place (a backup will be stored under backups/YYYYMMDD_HHMMSS/).\n";

exit(0);
