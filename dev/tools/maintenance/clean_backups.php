<?php
// tools/clean_backups.php
// Scans backups/ for PHP/HTML files, extracts inline <style> blocks and attributes
// into assets/css/pages/backups/<timestamp>/ and writes cleaned copies to backups/cleaned/<timestamp>/
// Usage: php clean_backups.php [--apply]

$opts = getopt('', ['apply']);
$apply = isset($opts['apply']);

$root = realpath(__DIR__ . '/..');
$backupsDir = $root . DIRECTORY_SEPARATOR . 'backups';
if (!is_dir($backupsDir)) {
    echo "No backups/ directory found. Nothing to do.\n";
    exit(0);
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($backupsDir));
$files = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    // skip already cleaned copies
    if (strpos($path, DIRECTORY_SEPARATOR . 'cleaned' . DIRECTORY_SEPARATOR) !== false) continue;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, ['php','html'])) continue;
    $files[] = $path;
}

if (count($files) === 0) {
    echo "No backup PHP/HTML files found to clean.\n";
    exit(0);
}

$timestamp = date('Ymd_His');
$outCssDir = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . $timestamp;
$outCleanDir = $root . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . 'cleaned' . DIRECTORY_SEPARATOR . $timestamp;

echo "Found " . count($files) . " files in backups/ to examine.\n";
if (!$apply) {
    echo "Dry-run mode — no files will be modified. Run with --apply to write cleaned copies and extracted CSS.\n";
}

$extractedMap = [];

foreach ($files as $file) {
    $rel = ltrim(str_replace($backupsDir, '', $file), DIRECTORY_SEPARATOR);
    $content = file_get_contents($file);
    $fileCssRules = [];

    // Extract <style> blocks
    if (preg_match_all('#<style[^>]*>(.*?)</style>#is', $content, $m)) {
        foreach ($m[1] as $block) {
            $fileCssRules[] = trim($block);
            // remove block
            $content = str_replace($block, '', $content);
            // also remove the wrapping tags (safer after)
        }
        // strip style tags now
        $content = preg_replace('#<style[^>]*>.*?</style>#is', '', $content);
    }

    // Extract occurrences
    if (preg_match_all('#style\s*=\s*(?:"([^"]*)"|\'([^\']*)\')#is', $content, $m2, PREG_SET_ORDER)) {
        foreach ($m2 as $match) {
            $styleStr = $match[1] !== '' ? $match[1] : $match[2];
            $styleStr = trim($styleStr);
            if ($styleStr === '') continue;
            // generate a deterministic class name based on style content
            $hash = substr(md5($styleStr), 0, 8);
            $className = 'bk-' . $hash;
            $rule = '.' . $className . ' { ' . $styleStr . ' }';
            $fileCssRules[] = $rule;

            // Replace with class insertion
            // Try to keep existing class attribute if present
            // strategy: replace "style="..." with "" then add class attribute if missing or append
            // We'll do a lightweight replacement using regex: find element starting before style attribute and inject class="..."

            // Attempt to replace occurrences of class="..." before style to append the class
            $pattern = '/(<[a-zA-Z0-9_-]+\b[^>]*?)class\s*=\s*("|\')([^\2]*?)\2([^>]*?)\s*style\s*=\s*("|\')(?:' . preg_quote($styleStr, '/') . ')\5/is';
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '\1class="\3 ' . $className . '"\4', $content, 1);
            } else {
                // fallback: replace after the tag opening, adding class attr
                $pattern2 = '/(<[a-zA-Z0-9_-]+\b)([^>]*?)\sstyle\s*=\s*("|\')' . preg_quote($styleStr, '/') . '\3/si';
                if (preg_match($pattern2, $content)) {
                    $content = preg_replace($pattern2, '\1 class="' . $className . '"\2', $content, 1);
                } else {
                    // last-resort: remove style attribute entirely
                    $content = preg_replace('/\sstyle\s*=\s*("|\')(?:' . preg_quote($styleStr, '/') . ')\1/i', '', $content, 1);
                }
            }
        }
    }

    if (!empty($fileCssRules)) {
        $cssPath = $outCssDir . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], '_', $rel) . '.css';
        $cssText = implode("\n\n", $fileCssRules) . "\n";
        $extractedMap[$rel] = ['css' => $cssPath, 'rules' => $fileCssRules];
        if ($apply) {
            @mkdir(dirname($cssPath), 0777, true);
            file_put_contents($cssPath, $cssText, FILE_APPEND);
            // save cleaned copy
            $dest = $outCleanDir . DIRECTORY_SEPARATOR . $rel;
            @mkdir(dirname($dest), 0777, true);
            file_put_contents($dest, $content);
            echo "Cleaned: $rel -> css: " . str_replace($root . DIRECTORY_SEPARATOR, '', $cssPath) . "\n";
        } else {
            echo "Would extract " . count($fileCssRules) . " CSS rule(s) from $rel -> $cssPath\n";
        }
    }
}

if ($apply) {
    echo "\nBackup cleaning complete. Cleaned copies in backups/cleaned/$timestamp and CSS in assets/css/pages/backups/$timestamp/\n";
} else {
    echo "\nDry-run complete. Re-run with --apply to create cleaned copies and extract CSS.\n";
}

exit(0);

