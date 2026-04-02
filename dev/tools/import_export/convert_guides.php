<?php
/**
 * Script de conversion des guides HTML en PHP
 */

function convertHtmlToPhp($htmlFile, $phpFile, $pageTitle) {
    echo "Conversion de $htmlFile vers $phpFile...\n";
    
    // Lire le fichier HTML
    $htmlContent = file_get_contents($htmlFile);
    
    if ($htmlContent === false) {
        echo "ERREUR: Impossible de lire le fichier $htmlFile\n";
        return false;
    }
    
    // Extraire le CSS entre <style> et </style>
    preg_match('/<style>(.*?)<\/style>/s', $htmlContent, $styleMatches);
    $cssContent = isset($styleMatches[1]) ? $styleMatches[1] : '';
    
    // Extraire le contenu du body
    preg_match('/<body[^>]*>(.*?)<\/body>/s', $htmlContent, $bodyMatches);
    $bodyContent = isset($bodyMatches[1]) ? $bodyMatches[1] : '';
    
    if (empty($bodyContent)) {
        echo "ERREUR: Impossible d'extraire le contenu body de $htmlFile\n";
        return false;
    }
    
    // Créer le contenu PHP
    $phpContent = "<?php\n";
    $phpContent .= "\$page_title = '$pageTitle';\n";
    $phpContent .= "\$page_css = '';\n";
    $phpContent .= "// Header and footer are provided by the router (index.php)\n";
    $phpContent .= "?>\n\n";
    $phpContent .= "<style>\n";
    $phpContent .= $cssContent;
    $phpContent .= "\n</style>\n\n";
    $phpContent .= "<main class=\"guide-content\">\n";
    $phpContent .= $bodyContent;
    $phpContent .= "\n</main>\n";
    
    // Écrire le fichier PHP
    $result = file_put_contents($phpFile, $phpContent);
    
    if ($result !== false) {
        echo "✓ Fichier créé avec succès: $phpFile (" . number_format(strlen($phpContent)) . " octets)\n";
        return true;
    } else {
        echo "ERREUR: Impossible d'écrire le fichier $phpFile\n";
        return false;
    }
}

// Convertir le guide Collège
convertHtmlToPhp(
    __DIR__ . '/../pages/college/Guide complet de remédiation Collège 6eme a la 3eme _ Programmes 2025.html',
    __DIR__ . '/../pages/college/guide-remediation-college.php',
    'Guide de Remédiation Collège - MonCoachScolaire'
);

// Convertir le guide Lycée
convertHtmlToPhp(
    __DIR__ . '/../pages/lycee/Guide complet de remédiation lycee seconde a terminale Programmes 2025.html',
    __DIR__ . '/../pages/lycee/guide-remediation-lycee.php',
    'Guide de Remédiation Lycée - MonCoachScolaire'
);

echo "\nConversion terminée !\n";
?>
