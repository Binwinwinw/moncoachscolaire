<?php
/**
 * Script d'analyse des classes CSS inutilisées
 * Extrait toutes les classes CSS et vérifie leur utilisation dans le code
 */

// Liste des fichiers CSS à analyser (hors backups)
$cssFiles = [
    'assets/css/style.css',
    'assets/css/colibri-mascot.css',
    'assets/css/pages/demo.css',
    'assets/css/pages/login.css',
    'assets/css/pages/landingpage.css',
    'assets/css/pages/dynamic-exercises.css',
    'assets/css/pages/dashboard.css',
    'assets/css/pages/quiz.css',
    'assets/css/pages/cours.css',
    'assets/css/pages/progression.css',
    'assets/css/pages/register.css',
];

// Extrait toutes les classes CSS d'un fichier
function extractCSSClasses($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    
    $content = file_get_contents($filePath);
    $classes = [];
    
    // Pattern pour trouver les classes CSS (commence par un point, suivi de caractères alphanumériques, tirets, underscores)
    preg_match_all('/\.([a-zA-Z][a-zA-Z0-9_-]*)/', $content, $matches);
    
    if (!empty($matches[1])) {
        $classes = array_unique($matches[1]);
        sort($classes);
    }
    
    return $classes;
}

// Vérifie si une classe est utilisée dans les fichiers PHP/HTML/JS
function isClassUsed($className, $baseDir = '.') {
    $searchPattern = $className;
    
    // Patterns de recherche dans différents contextes
    $patterns = [
        "class=['\"]" . preg_quote($className, '/'),
        "class=['\"][^'\"]*" . preg_quote($className, '/'),
        "className=['\"]" . preg_quote($className, '/'),
        "className=['\"][^'\"]*" . preg_quote($className, '/'),
        "addClass\('" . preg_quote($className, '/'),
        "addClass\('" . preg_quote($className, '/') . "'",
        "removeClass\('" . preg_quote($className, '/'),
        "toggleClass\('" . preg_quote($className, '/'),
        "hasClass\('" . preg_quote($className, '/'),
        "querySelector\('." . preg_quote($className, '/'),
        "querySelectorAll\('." . preg_quote($className, '/'),
        "getElementsByClassName\('" . preg_quote($className, '/'),
    ];
    
    // Fichiers à rechercher (PHP, HTML, JS)
    $extensions = ['php', 'html', 'js'];
    $found = false;
    
    foreach ($extensions as $ext) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            $path = $file->getRealPath();
            
            // Ignorer les backups, node_modules, vendor, etc.
            if (strpos($path, 'backup') !== false || 
                strpos($path, 'node_modules') !== false || 
                strpos($path, 'vendor') !== false ||
                strpos($path, 'tests') !== false ||
                strpos($path, 'tools') !== false) {
                continue;
            }
            
            if ($file->getExtension() === $ext) {
                $content = @file_get_contents($path);
                if ($content === false) continue;
                
                foreach ($patterns as $pattern) {
                    if (preg_match('/' . $pattern . '/i', $content)) {
                        $found = true;
                        break 2;
                    }
                }
            }
        }
        
        if ($found) break;
    }
    
    return $found;
}

// Analyse principale
echo "🔍 Analyse des classes CSS inutilisées...\n\n";

$allClasses = [];
$classesByFile = [];

// Première passe : extraction des classes
foreach ($cssFiles as $cssFile) {
    if (!file_exists($cssFile)) {
        echo "⚠️  Fichier non trouvé : $cssFile\n";
        continue;
    }
    
    $classes = extractCSSClasses($cssFile);
    $classesByFile[$cssFile] = $classes;
    $allClasses = array_merge($allClasses, $classes);
    
    echo "✅ $cssFile : " . count($classes) . " classes trouvées\n";
}

$allClasses = array_unique($allClasses);
echo "\n📊 Total de classes uniques : " . count($allClasses) . "\n\n";

// Deuxième passe : vérification de l'utilisation
echo "🔎 Vérification de l'utilisation des classes...\n\n";

$unusedClasses = [];
$usedClasses = [];

foreach ($allClasses as $class) {
    // Ignorer les classes système CSS courantes
    $systemClasses = ['html', 'body', 'header', 'footer', 'main', 'nav', 'section', 'article', 'aside', 'div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'button', 'input', 'form', 'label', 'img', 'ul', 'ol', 'li', 'table', 'tr', 'td', 'th'];
    
    if (in_array(strtolower($class), $systemClasses)) {
        $usedClasses[] = $class;
        continue;
    }
    
    $used = isClassUsed($class);
    
    if ($used) {
        $usedClasses[] = $class;
    } else {
        $unusedClasses[] = $class;
    }
    
    if (count($usedClasses) % 10 === 0) {
        echo "  Vérifié " . count($usedClasses) . " classes...\n";
    }
}

echo "\n📈 Résultats :\n";
echo "  ✅ Classes utilisées : " . count($usedClasses) . "\n";
echo "  ❌ Classes inutilisées : " . count($unusedClasses) . "\n\n";

if (!empty($unusedClasses)) {
    echo "🗑️  Classes potentiellement inutilisées :\n";
    foreach ($unusedClasses as $class) {
        echo "  - .$class\n";
    }
    
    // Sauvegarder les résultats
    file_put_contents('tools/unused_css_classes.txt', implode("\n", $unusedClasses));
    echo "\n💾 Résultats sauvegardés dans tools/unused_css_classes.txt\n";
} else {
    echo "✅ Aucune classe inutilisée détectée !\n";
}

echo "\n✅ Analyse terminée.\n";

