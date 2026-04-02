<?php
// dev/tools/check_css.php
// Script de diagnostic local pour vérifier la présence et l’accessibilité des fichiers CSS du dashboard

$cssFiles = [
    '../../public/assets/css/style.css',
    '../../public/assets/css/pages/dashboard.css',
];

header('Content-Type: text/plain; charset=utf-8');
echo "--- Diagnostic CSS MonCoachScolaire (LOCAL) ---\n";
foreach ($cssFiles as $file) {
    $real = realpath(__DIR__ . '/' . $file);
    echo "Fichier: $file\n";
    if ($real && file_exists($real)) {
        echo "  ✔ Présent sur le disque : $real\n";
        echo "  Taille : " . filesize($real) . " octets\n";
        echo "  Permissions : " . substr(sprintf('%o', fileperms($real)), -4) . "\n";
        // Test lecture
        $handle = @fopen($real, 'r');
        if ($handle) {
            fclose($handle);
            echo "  ✔ Lecture OK\n";
        } else {
            echo "  ✖ Impossible de lire le fichier (droits ?)\n";
        }
    } else {
        echo "  ✖ Fichier absent ou chemin incorrect\n";
    }
    echo "\n";
}
echo "--- Fin diagnostic local ---\n";
