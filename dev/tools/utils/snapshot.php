<?php
// snapshots files or directories into backups/ preserving relative paths
// Usage examples:
// php snapshot.php landingpage.php
// php snapshot.php path/to/file1.php path/to/dir
// php snapshot.php --all   (snapshot whole project except backups, vendor)

$args = array_slice($argv, 1);
$all = in_array('--all', $args, true);
// new options: --keep=N (keep latest N snapshots), --prune-days=N (remove snapshots older than N days), --compress (zip created snapshot), --exclude=path1,path2
$keep = 0; // 0 = keep all
$pruneDays = 0; // 0 = no prune by date
$compress = false;
$excludesArg = null;
foreach ($args as $k => $v) {
    if (strpos($v, '--keep=') === 0) { $keep = (int) substr($v, 7); unset($args[$k]); }
    if (strpos($v, '--prune-days=') === 0) { $pruneDays = (int) substr($v, 13); unset($args[$k]); }
    if ($v === '--compress') { $compress = true; unset($args[$k]); }
    if (strpos($v, '--exclude=') === 0) { $excludesArg = substr($v, 10); unset($args[$k]); }
}
$args = array_values($args);
if ($all) $args = [];

$root = realpath(__DIR__ . '/..');
$backups = $root . DIRECTORY_SEPARATOR . 'backups';
$store = $backups . DIRECTORY_SEPARATOR . 'store';
if (!is_dir($backups)) mkdir($backups, 0777, true);

$timestamp = date('Ymd_His');

function copy_with_dirs($src, $root, $backups, $timestamp) {
    $rel = ltrim(str_replace($root, '', $src), DIRECTORY_SEPARATOR);
    $destDir = $backups . DIRECTORY_SEPARATOR . $timestamp . DIRECTORY_SEPARATOR . dirname($rel);
    if (!is_dir($destDir)) mkdir($destDir, 0777, true);
    $dest = $destDir . DIRECTORY_SEPARATOR . basename($src);
    if (is_file($src)) {
        copy($src, $dest);
        echo "Saved: $rel -> backups/$timestamp/" . dirname($rel) . "/" . basename($src) . "\n";
    } elseif (is_dir($src)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($it as $f) {
            $fpath = $f->getPathname();
            $r = ltrim(str_replace($root, '', $fpath), DIRECTORY_SEPARATOR);
            $dDir = $backups . DIRECTORY_SEPARATOR . $timestamp . DIRECTORY_SEPARATOR . dirname($r);
            if (!is_dir($dDir)) mkdir($dDir, 0777, true);
            copy($fpath, $dDir . DIRECTORY_SEPARATOR . basename($fpath));
            echo "Saved: $r -> backups/$timestamp/" . dirname($r) . "/" . basename($fpath) . "\n";
        }
    } else {
        echo "Skipped (not found): $src\n";
    }
}

if (count($args) === 0 && !$all) {
    echo "Usage: php snapshot.php [--all] <file|dir> [<file|dir> ...]\n";
    exit(1);
}

if ($all) {
    // Snapshot project except backups/vendor/node_modules/.git
    $excludes = ['backups', 'vendor', 'node_modules', '.git'];
    if (!empty($excludesArg)) {
        $more = array_map('trim', explode(',', $excludesArg));
        foreach ($more as $m) if ($m !== '') $excludes[] = $m;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));
    $seen = 0;
    foreach ($it as $f) {
        $path = $f->getPathname();
        // skip excluded
        $skip = false;
        foreach ($excludes as $ex) {
            if (strpos($path, DIRECTORY_SEPARATOR . $ex . DIRECTORY_SEPARATOR) !== false) { $skip = true; break; }
        }
        if ($skip) continue;
        // only copy files
        if ($f->isFile()) {
            copy_with_dirs($path, $root, $backups, $timestamp);
            $seen++;
        }
    }
    echo "Snapshot complete: $seen files -> backups/$timestamp/\n";
    // post-actions: compression and pruning if requested
    if ($compress) {
        compress_snapshot($backups, $timestamp);
    }
    if ($keep > 0 || $pruneDays > 0) {
        prune_snapshots($backups, $keep, $pruneDays);
    }
    exit(0);
}

$count = 0;
foreach ($args as $a) {
    $path = $a;
    // if relative, resolve relative to repo root
    if (!file_exists($path)) {
        $maybe = $root . DIRECTORY_SEPARATOR . ltrim($path, './\\');
        if (file_exists($maybe)) $path = $maybe;
    }
    if (!file_exists($path)) {
        echo "Not found: $a\n";
        continue;
    }
    copy_with_dirs($path, $root, $backups, $timestamp);
    $count++;
}

if ($count === 0) {
    echo "No files saved.\n";
} else {
    echo "Snapshot complete: $count item(s) -> backups/$timestamp/\n";
    if ($compress) {
        compress_snapshot($backups, $timestamp);
    }
    if ($keep > 0 || $pruneDays > 0) {
        prune_snapshots($backups, $keep, $pruneDays);
    }
}

exit(0);

function compress_snapshot($backups, $timestamp) {
    $dir = $backups . DIRECTORY_SEPARATOR . $timestamp;
    $zipfile = $backups . DIRECTORY_SEPARATOR . $timestamp . '.zip';
    if (!is_dir($dir)) { echo "Compress: directory not found: $dir\n"; return false; }
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            echo "Failed to create zip: $zipfile\n";
            return false;
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($it as $f) {
            $filePath = $f->getPathname();
            $localName = ltrim(str_replace($backups . DIRECTORY_SEPARATOR . $timestamp . DIRECTORY_SEPARATOR, '', $filePath), DIRECTORY_SEPARATOR);
            $zip->addFile($filePath, $localName);
        }
        $zip->close();
        // if zip created successfully, remove original folder to save space
        // use a safe remove routine
        rrmdir($dir);
        echo "Compressed snapshot -> backups/$timestamp.zip (original removed)\n";
        return true;
    } else {
        // try to fall back to PowerShell Compress-Archive on Windows if available
        $dirEsc = str_replace('/', '\\', $dir);
        $zipEsc = str_replace('/', '\\', $zipfile);
        $psCmd = 'Compress-Archive -Path "' . $dirEsc . '\\*" -DestinationPath "' . $zipEsc . '" -Force -CompressionLevel Optimal';
        exec('powershell -NoProfile -Command "' . $psCmd . '" 2>&1', $out, $rc);
        if ($rc === 0) {
            rrmdir($dir);
            echo "Compressed snapshot -> backups/$timestamp.zip (original removed, used PowerShell)\n";
            return true;
        }
        echo "ZipArchive not available and PowerShell compression failed; cannot compress snapshot.\n";
        return false;
    }
}

function prune_snapshots($backups, $keep=0, $pruneDays=0) {
    // keep snapshots that are directories named YYYYMMDD_HHMMSS or zip files same pattern
    $entries = scandir($backups, SCANDIR_SORT_DESCENDING);
    $snapshots = [];
    foreach ($entries as $e) {
        if ($e === '.' || $e === '..' || $e === 'store') continue;
        $path = $backups . DIRECTORY_SEPARATOR . $e;
        // treat both folders and .zip as snapshots
        if (preg_match('/^\d{8}_\d{6}(?:$|\.zip$)/', $e)) {
            $snapshots[] = ['name'=>$e, 'path'=>$path, 'mtime'=>filemtime($path)];
        }
    }
    usort($snapshots, function($a,$b){ return $b['mtime'] - $a['mtime']; });
    $removed = 0;
    // remove by oldest beyond keep
    if ($keep > 0 && count($snapshots) > $keep) {
        $toRemove = array_slice($snapshots, $keep);
        foreach ($toRemove as $r) { rrmdir_or_unlink($r['path']); $removed++; }
    }
    // remove by age
    if ($pruneDays > 0) {
        $threshold = time() - ($pruneDays * 86400);
        foreach ($snapshots as $s) {
            if ($s['mtime'] < $threshold) { rrmdir_or_unlink($s['path']); $removed++; }
        }
    }
    if ($removed > 0) echo "Pruned $removed older snapshots from backups/\n";
}

function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $f) {
        if ($f->isDir()) rmdir($f->getPathname()); else unlink($f->getPathname());
    }
    rmdir($dir);
}

function rrmdir_or_unlink($path) {
    if (is_dir($path)) rrmdir($path); elseif (is_file($path)) unlink($path);
}
