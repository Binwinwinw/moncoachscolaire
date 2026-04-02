<?php
/**
 * Script pour tester toutes les URLs générées par render_level_navigation
 */

require_once __DIR__ . '/../site_boot.php';
require_once __DIR__ . '/../includes/level_navigation.php';

echo "🧪 Test des URLs générées par render_level_navigation\n\n";

$levels = ['6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale', 'BAC'];
$page_types = ['exercices', 'guide-remediation', 'cours'];

foreach ($levels as $level) {
    echo "📚 Niveau : $level\n";
    foreach ($page_types as $type) {
        echo "   Type : $type\n";
        $nav = render_level_navigation($level, $type);
        
        // Extraire les URLs du HTML
        if (preg_match_all('/href="([^"]+)"/', $nav, $matches)) {
            foreach ($matches[1] as $url) {
                echo "      ✅ URL : $url\n";
                
                // Vérifier si l'URL est valide
                $pageParam = parse_url($url, PHP_URL_QUERY);
                parse_str($pageParam, $params);
                $pagePath = $params['page'] ?? '';
                
                if ($pagePath) {
                    // Vérifier si le fichier existe
                    $filePath = __DIR__ . '/../pages/' . str_replace('/', '/', $pagePath) . '.php';
                    if (file_exists($filePath)) {
                        echo "         ✅ Fichier existe : $filePath\n";
                    } else {
                        echo "         ❌ Fichier manquant : $filePath\n";
                    }
                }
            }
        }
        echo "\n";
    }
    echo "\n";
}
