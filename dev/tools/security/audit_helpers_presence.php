<?php
// Script CLI pour auditer la présence des helpers obligatoires et l'absence d'includes interdits dans les pages
// Usage : php dev/tools/audit_helpers_presence.php

$pagesDir = __DIR__ . '/../../src/pages';
$requiredHelpers = [
    'get_validated_param',
    'safe_redirect',
];
$forbiddenIncludes = [
    'require',
    'include',
];

function scanPhpFiles($dir) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($rii as $file) {
        if ($file->isDir()) continue;
        if (strtolower($file->getExtension()) === 'php') {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

$phpFiles = scanPhpFiles($pagesDir);
$results = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $missing = [];
    foreach ($requiredHelpers as $helper) {
        if (strpos($content, $helper) === false) {
            $missing[] = $helper;
        }
    }
    $forbidden = [];
    foreach ($forbiddenIncludes as $inc) {
        // Exclure les commentaires et les chaînes
        if (preg_match_all('/^[^\n#]*\b' . $inc . '\b[^;]*;/m', $content, $matches)) {
            foreach ($matches[0] as $line) {
                $forbidden[] = trim($line);
            }
        }
    }
    $results[$file] = [
        'missing_helpers' => $missing,
        'forbidden_includes' => $forbidden,
    ];
}

// Affichage CLI
$ok = true;
foreach ($results as $file => $info) {
    if ($info['missing_helpers'] || $info['forbidden_includes']) {
        $ok = false;
        echo "\n$file\n";
        if ($info['missing_helpers']) {
            echo "  Helpers manquants : ", implode(', ', $info['missing_helpers']), "\n";
        }
        if ($info['forbidden_includes']) {
            echo "  Includes interdits :\n";
            foreach ($info['forbidden_includes'] as $inc) {
                echo "    $inc\n";
            }
        }
    }
}
if ($ok) {
    echo "\nToutes les pages sont conformes (helpers présents, includes interdits absents).\n";
    exit(0);
} else {
    echo "\nDes corrections sont nécessaires.\n";
    exit(1);
}
