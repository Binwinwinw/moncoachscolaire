<?php
/**
 * Script de nettoyage des classes CSS vraiment inutilisées
 * Deuxième passe : vérification manuelle et suppression
 */

// Liste des classes à GARDER (utilisées dynamiquement via JS ou PHP)
$classesToKeep = [
    // Colibri mascot (utilisées via JS)
    'colibri-neutre', 'colibri-heureux', 'colibri-encourageant', 
    'colibri-celebration', 'colibri-reflexion', 'colibri-speech-bubble',
    'size-small', 'size-medium', 'size-large', 'compact',
    'position-float', 'position-inline', 'position-center',
    'state-success', 'state-error', 'state-thinking', 'visible',
    
    // Classes utilitaires communes
    'hidden', 'floating', 'delay-1', 'delay-2',
    
    // QCM (utilisées via JS)
    'qcm-correct', 'qcm-wrong', 'qcm-option',
    
    // Demo (utilisées dans demo.php)
    'demo-locked', 'demo-quiz-lock', 'demo-signup-popup',
    
    // Landing page (utilisées dans landingpage.php)
    'landing-page', 'niveau-card', 'niveau-cards', 'niveau-college', 
    'niveau-lycee', 'niveau-bac', 'niveau-card-modern', 'niveaux-container',
    'niveaux-grid', 'niveaux-grid-single', 'niveaux-section',
    
    // Dashboard (utilisées dans dashboard.php)
    'card-header', 'card-body', 'card-badge',
    
    // Exercices dynamiques (utilisées via JS)
    'exercise-encouragement-message', 'exercise-success-message',
    'quiz-good', 'quiz-needs-work', 'quiz-success',
    
    // Features (utilisées dans footer.php JS)
    'feature-card',
    
    // CTA (utilisées dans plusieurs fichiers)
    'cta-actions',
    
    // Guide remediation (utilisées dans les guides)
    'guide-body',
    
    // Cours (utilisées dans cours.php)
    'cours-card-header', 'cours-card-body',
    
    // Quiz (utilisées dans quiz.php)
    'quiz-card-header', 'quiz-card-body',
];

// Classes probablement inutilisées à vérifier manuellement
$classesToCheck = [
    // Classes "modern" qui semblent être d'anciennes versions
    'container-modern', 'modern-landing', 'hero-modern', 'hero-wrapper',
    'hero-text', 'hero-title', 'hero-description', 'hero-cta', 'hero-note',
    'hero-visual', 'hero-card', 'hero-btn', 'hero-container',
    'features-modern', 'features-grid-modern', 'parcours-modern',
    'section-title-modern', 'section-subtitle-modern',
    
    // Classes de cartes non utilisées
    'card-feature', 'card-features', 'card-content', 'card-cover', 
    'card-emoji', 'card-footer', 'card-icon', 'card-level', 'card-link',
    'card-subtitle', 'card-tags', 'card-bac', 'card-college', 'card-lycee',
    
    // Classes de boutons non utilisées
    'btn-cta-alt', 'btn-cta-main', 'btn-primary-large', 'btn-secondary-large',
    'btn-print', 'cta-btn', 'cta-btn-primary', 'cta-btn-secondary',
    'cta-buttons', 'cta-buttons-group', 'cta-container', 'cta-icon-large',
    'cta-modern',
    
    // Classes de features non utilisées
    'feature-icon', 'feature-item', 'features-container', 'features-grid',
    'features-section', 'features-list',
    
    // Classes de layout non utilisées
    'content-wrapper', 'adventure-section', 'parents-container', 'parents-section',
    'logo-section', 'header-actions', 'header-container', 'header-sidebar-toggle',
    'section-header', 'reassurance-panel',
    
    // Classes utilitaires non utilisées
    'gap-sm', 'items-center', 'ml-auto', 'flex', 'shrink',
    'gradient-text', 'full-width', 'visited',
    
    // Classes de print non utilisées
    'print-actions',
    
    // Classes diverses non utilisées
    'bg-landing-5', 'wall-card', 'padlet-wall', 'game-mode-indicator',
    'db-unavailable', 'site-notice', 'skip-link', 'socials', 'user-theme',
    'with-sidebar', 'sidebar-hidden', 'nav-btn-ghost',
    'lock-icon-small', 'quiz-lock-content', 'level-select-label',
    'rgpd-link', 'security-badge',
    
    // Classes d'extension d'image (probablement erreurs)
    'css', 'jpg', 'png', 'avif',
];

// Fonction pour chercher une classe dans tous les fichiers
function searchClassInFiles($className, $baseDir = '.') {
    $found = false;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    $patterns = [
        "class=['\"]" . preg_quote($className, '/'),
        "class=['\"][^'\"]*" . preg_quote($className, '/'),
        "className=['\"]" . preg_quote($className, '/'),
        "className=['\"][^'\"]*" . preg_quote($className, '/'),
        "addClass\('" . preg_quote($className, '/'),
        "classList\.add\('" . preg_quote($className, '/'),
        "\.$className",
    ];
    
    foreach ($files as $file) {
        $path = $file->getRealPath();
        
        // Ignorer les backups, node_modules, vendor, tools, etc.
        if (strpos($path, 'backup') !== false || 
            strpos($path, 'node_modules') !== false || 
            strpos($path, 'vendor') !== false ||
            strpos($path, 'tools') !== false ||
            strpos($path, 'docs') !== false) {
            continue;
        }
        
        if (in_array($file->getExtension(), ['php', 'html', 'js'])) {
            $content = @file_get_contents($path);
            if ($content === false) continue;
            
            foreach ($patterns as $pattern) {
                if (preg_match('/' . $pattern . '/i', $content)) {
                    $found = true;
                    return ['found' => true, 'file' => $path];
                }
            }
        }
    }
    
    return ['found' => false];
}

echo "🔍 Vérification finale des classes CSS...\n\n";

$confirmedUnused = [];

foreach ($classesToCheck as $class) {
    if (in_array($class, $classesToKeep)) {
        continue;
    }
    
    $result = searchClassInFiles($class);
    if (!$result['found']) {
        $confirmedUnused[] = $class;
        echo "❌ .$class - NON UTILISÉE\n";
    } else {
        echo "✅ .$class - utilisée dans " . basename($result['file']) . "\n";
    }
}

echo "\n📊 Résumé :\n";
echo "  Classes confirmées inutilisées : " . count($confirmedUnused) . "\n\n";

if (!empty($confirmedUnused)) {
    file_put_contents('tools/confirmed_unused_css_classes.txt', implode("\n", $confirmedUnused));
    echo "💾 Liste sauvegardée dans tools/confirmed_unused_css_classes.txt\n";
    echo "\n🗑️  Classes à supprimer :\n";
    foreach ($confirmedUnused as $class) {
        echo "  - .$class\n";
    }
}

echo "\n✅ Vérification terminée.\n";

