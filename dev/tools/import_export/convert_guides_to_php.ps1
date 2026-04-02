# Script pour convertir les guides HTML en PHP
$ErrorActionPreference = "Stop"

# Fichier Collège
$htmlFile1 = "pages\college\Guide complet de remédiation Collège 6eme a la 3eme _ Programmes 2025.html"
$phpFile1 = "pages\college\guide-remediation-college.php"

# Fichier Lycée
$htmlFile2 = "pages\lycee\Guide complet de remédiation lycee seconde a terminale Programmes 2025.html"
$phpFile2 = "pages\lycee\guide-remediation-lycee.php"

function Convert-HtmlToPhp {
    param(
        [string]$htmlFile,
        [string]$phpFile,
        [string]$pageTitle,
        [string]$cssFile
    )
    
    Write-Host "Conversion de $htmlFile vers $phpFile..."
    
    # Lire le contenu HTML
    $htmlContent = Get-Content $htmlFile -Raw -Encoding UTF8
    
    # Extraire le contenu entre <body> et </body>
    if ($htmlContent -match '<body[^>]*>(.*?)</body>') {
        $bodyContent = $matches[1]
        
        # Extraire le CSS entre <style> et </style>
        $cssContent = ""
        if ($htmlContent -match '<style>(.*?)</style>') {
            $cssContent = $matches[1]
        }
        
        # Créer le contenu PHP
        $phpContent = @"
<?php
`$page_title = '$pageTitle';
`$page_css = '$cssFile';
// Header and footer are provided by the router (index.php)
?>

<style>
$cssContent
</style>

<main class="guide-content">
$bodyContent
</main>
"@
        
        # Écrire le fichier PHP
        $phpContent | Out-File -FilePath $phpFile -Encoding UTF8 -NoNewline
        Write-Host "✓ Fichier créé : $phpFile"
    } else {
        Write-Host "✗ Erreur : Impossible d'extraire le contenu body de $htmlFile"
    }
}

# Convertir le guide Collège
Convert-HtmlToPhp -htmlFile $htmlFile1 -phpFile $phpFile1 `
    -pageTitle "Guide de Remédiation Collège - MonCoachScolaire" `
    -cssFile ""

# Convertir le guide Lycée
Convert-HtmlToPhp -htmlFile $htmlFile2 -phpFile $phpFile2 `
    -pageTitle "Guide de Remédiation Lycée - MonCoachScolaire" `
    -cssFile ""

Write-Host "`nConversion terminée !"
