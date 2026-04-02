<?php
/**
 * Script pour trouver les fichiers API qui gèrent les exercices
 */

echo "🔍 Recherche des fichiers API pour les exercices...\n\n";

$rootDir = __DIR__;
$foundFiles = [];

// Fonction récursive pour chercher dans tous les fichiers PHP
function searchInDirectory($dir, &$results) {
    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $fullPath = $dir . DIRECTORY_SEPARATOR . $file;

        // Ignorer certains dossiers
        if (is_dir($fullPath)) {
            $dirname = basename($fullPath);
            if (in_array($dirname, ['vendor', 'node_modules', '.git', 'cache'])) {
                continue;
            }
            searchInDirectory($fullPath, $results);
        } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            // Lire le contenu du fichier
            $content = file_get_contents($fullPath);

            // Chercher les mots-clés liés aux exercices
            $keywords = [
                'handleGetExercises',
                'getExercises',
                'SELECT.*FROM.*exercises',
                'exercises.*WHERE',
                '/api/exercises',
                'route.*exerc'
            ];

            foreach ($keywords as $keyword) {
                if (preg_match('/' . str_replace('/', '\/', $keyword) . '/i', $content)) {
                    $results[$fullPath][] = $keyword;
                    break; // Un match suffit
                }
            }
        }
    }
}

searchInDirectory($rootDir, $foundFiles);

if (empty($foundFiles)) {
    echo "❌ Aucun fichier API trouvé.\n";
} else {
    echo "✅ Fichiers trouvés :\n\n";
    foreach ($foundFiles as $file => $keywords) {
        $relativePath = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $file);
        echo "📄 {$relativePath}\n";
        echo "   Mots-clés trouvés : " . implode(', ', $keywords) . "\n\n";
    }
}

echo "\n🔍 Fichiers contenant 'LinkedCourses' :\n\n";
searchInDirectory($rootDir, $linkedCoursesFiles = []);
foreach ($linkedCoursesFiles as $file => $kw) {
    if (stripos(file_get_contents($file), 'LinkedCourses') !== false) {
        echo "📌 " . str_replace($rootDir . DIRECTORY_SEPARATOR, '', $file) . "\n";
    }
}
if (empty($linkedCoursesFiles)) {
    echo "❌ Aucun fichier avec 'LinkedCourses' trouvé.\n";
}
