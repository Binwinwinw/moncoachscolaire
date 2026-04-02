<?php
/**
 * Script pour supprimer les classes CSS inutilisées des fichiers CSS
 */

$unusedClasses = file('tools/confirmed_unused_css_classes.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Fichiers CSS à nettoyer (hors backups)
$cssFiles = [
    'assets/css/style.css',
    'assets/css/pages/landingpage.css',
    'assets/css/pages/demo.css',
    'assets/css/pages/dashboard.css',
];

foreach ($cssFiles as $cssFile) {
    if (!file_exists($cssFile)) {
        echo "⚠️  Fichier non trouvé : $cssFile\n";
        continue;
    }
    
    echo "📝 Traitement de $cssFile...\n";
    $content = file_get_contents($cssFile);
    $originalLength = strlen($content);
    $removedCount = 0;
    
    foreach ($unusedClasses as $class) {
        // Pattern pour trouver les règles CSS avec cette classe
        // Format: .className { ... } ou .className.other { ... } ou .parent .className { ... }
        $patterns = [
            // Classe seule : .classname { ... }
            '/(\n|^)\s*\.' . preg_quote($class, '/') . '\s*\{[^}]*\}/m',
            // Classe combinée : .classname.other { ... }
            '/\.' . preg_quote($class, '/') . '\.[a-zA-Z][a-zA-Z0-9_-]*\s*\{[^}]*\}/m',
            // Classe dans sélecteur descendant : .parent .classname { ... }
            '/\.' . preg_quote($class, '/') . '\s*\{[^}]*\}/m',
            // Classe avec pseudo-classe : .classname:hover { ... }
            '/\.' . preg_quote($class, '/') . ':[a-zA-Z-]+\s*\{[^}]*\}/m',
        ];
        
        foreach ($patterns as $pattern) {
            $matches = [];
            if (preg_match_all($pattern, $content, $matches)) {
                foreach ($matches[0] as $match) {
                    $content = str_replace($match, '', $content);
                    $removedCount++;
                }
            }
        }
    }
    
    // Nettoyer les lignes vides multiples
    $content = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $content);
    
    // Sauvegarder
    file_put_contents($cssFile, $content);
    $newLength = strlen($content);
    $savedBytes = $originalLength - $newLength;
    
    echo "  ✅ $removedCount règles supprimées, " . round($savedBytes / 1024, 2) . " KB économisés\n";
}

echo "\n✅ Nettoyage terminé !\n";

