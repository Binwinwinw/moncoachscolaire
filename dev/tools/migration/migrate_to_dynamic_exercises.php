<?php
/**
 * Script de migration automatique des pages d'exercices vers le nouveau système dynamique
 * Usage : php migrate_to_dynamic_exercises.php
 * Parcourt toutes les pages d'exercices et remplace l'ancien affichage par le conteneur dynamique
 * Ajoute le niveau détecté (Userlevel) si présent
 */

$pagesDir = __DIR__ . '/../../src/pages';
$pattern = '/exercices.*\.php$/i';

function detectUserLevel($fileContent) {
    // Détection simple du niveau dans le fichier (body, variable, etc.)
    if (preg_match('/userlevel\s*=\s*[\'\"]?([a-zA-Z0-9èéêîôûàâäëïöüç\-]+)[\'\"]?/i', $fileContent, $matches)) {
        return $matches[1];
    }
    if (preg_match('/data-level\s*=\s*[\'\"]?([a-zA-Z0-9èéêîôûàâäëïöüç\-]+)[\'\"]?/i', $fileContent, $matches)) {
        return $matches[1];
    }
    return null;
}

function migratePage($filePath) {
    $content = file_get_contents($filePath);
    $userLevel = detectUserLevel($content);
    if (!$userLevel) {
        // Par défaut, ne migre que si le niveau est détecté
        return false;
    }
    // Supprimer l'ancien affichage d'exercices (section, includes, boucles PHP)
    $content = preg_replace('/<section[^>]*id=["\']?dynamic-exercises-section["\']?[^>]*>[\s\S]*?<\/section>/i', '', $content);
    $content = preg_replace('/<div[^>]*class=["\']?exercices-navigation["\']?[^>]*>[\s\S]*?<\/div>/i', '', $content);
    $content = preg_replace('/<\?php[\s\S]*?\?>/i', '', $content); // Supprime les blocs PHP liés aux exercices
    // Ajoute le conteneur dynamique
    $dynamicDiv = "<div class=\"dynamic-exercises-container\" data-dynamic-exercises=\"true\" data-level=\"$userLevel\"></div>\n";
    // Ajoute le script JS si absent
    if (strpos($content, 'dynamic-exercises.js') === false) {
        $scriptTag = '<script src="/moncoachscolaire/public/assets/js/dynamic-exercises.js"></script>';
        $content = preg_replace('/(<\/body>)/i', "$dynamicDiv\n$scriptTag\n$1", $content, 1);
    } else {
        $content = preg_replace('/(<\/body>)/i', "$dynamicDiv\n$1", $content, 1);
    }
    // Sauvegarde le fichier modifié
    file_put_contents($filePath, $content);
    return true;
}

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pagesDir));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    if (preg_match($pattern, $file->getFilename())) {
        $ok = migratePage($file->getPathname());
        if ($ok) {
            echo "Migré : " . $file->getPathname() . "\n";
        }
    }
}
echo "Migration terminée.\n";
