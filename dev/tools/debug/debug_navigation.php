<?php
require_once __DIR__ . '/../site_boot.php';
require_once __DIR__ . '/../includes/level_navigation.php';

echo "🔍 Debug de la navigation pour 6ème\n\n";

$current_level = '6ème';
$college_levels = ['6ème', '5ème', '4ème', '3ème'];
$lycee_levels = ['Seconde', 'Première', 'Terminale'];

$current_index = array_search($current_level, $college_levels);
echo "Index de 6ème dans college_levels : $current_index\n";

if ($current_index !== false) {
    if ($current_index < count($college_levels) - 1) {
        $next_level = $college_levels[$current_index + 1];
        echo "Niveau suivant déterminé : $next_level\n";
        echo "Est dans college_levels ? " . (in_array($next_level, $college_levels) ? 'OUI' : 'NON') . "\n";
        echo "Est dans lycee_levels ? " . (in_array($next_level, $lycee_levels) ? 'OUI' : 'NON') . "\n";
    }
}

echo "\n🔍 Test de render_level_navigation\n";
$html = render_level_navigation('6ème', 'exercices');
preg_match_all('/href="([^"]+)"/', $html, $matches);
foreach ($matches[1] as $url) {
    echo "URL générée : $url\n";
    if (strpos($url, '5eme') !== false) {
        if (strpos($url, 'college') !== false) {
            echo "  ✅ CORRECT : 5ème est au collège\n";
        } else {
            echo "  ❌ ERREUR : 5ème devrait être au collège, pas au lycée !\n";
        }
    }
}

