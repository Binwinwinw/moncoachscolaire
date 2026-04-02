<?php
// dev/tools/fix_css_permissions.php
// Script pour corriger les droits sur les fichiers CSS du dashboard

$cssFiles = [
    '../../public/assets/css/style.css',
    '../../public/assets/css/pages/dashboard.css',
];

$cssDirs = [
    '../../public/assets/css',
    '../../public/assets/css/pages',
];

header('Content-Type: text/plain; charset=utf-8');
echo "--- Correction des permissions CSS ---\n";
foreach ($cssFiles as $file) {
    $real = realpath(__DIR__ . '/' . $file);
    echo "Fichier: $file\n";
    if ($real && file_exists($real)) {
        if (chmod($real, 0644)) {
            echo "  ✔ Permissions corrigées (644)\n";
        } else {
            echo "  ✖ Impossible de corriger les permissions\n";
        }
    } else {
        echo "  ✖ Fichier absent\n";
    }
}
foreach ($cssDirs as $dir) {
    $real = realpath(__DIR__ . '/' . $dir);
    echo "Dossier: $dir\n";
    if ($real && is_dir($real)) {
        if (chmod($real, 0755)) {
            echo "  ✔ Permissions dossier corrigées (755)\n";
        } else {
            echo "  ✖ Impossible de corriger les permissions du dossier\n";
        }
    } else {
        echo "  ✖ Dossier absent\n";
    }
}
echo "--- Fin correction ---\n";
