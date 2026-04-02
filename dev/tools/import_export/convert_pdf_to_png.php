<?php
/**
 * Script de conversion PDF vers PNG
 * 
 * Ce script convertit le fichier PDF en image PNG pour l'utiliser comme fond d'écran.
 * 
 * Méthodes supportées :
 * 1. Imagick (extension PHP) - Recommandé
 * 2. Ghostscript via exec() - Si disponible
 * 3. Fallback : Instructions manuelles
 */

$pdfPath = __DIR__ . '/../assets/img/background-school-material.pdf';
$outputPath = __DIR__ . '/../assets/img/background-school-material.png';

echo "=== Conversion PDF vers PNG ===\n\n";
echo "Fichier source : $pdfPath\n";
echo "Fichier de sortie : $outputPath\n\n";

// Vérifier que le PDF existe
if (!file_exists($pdfPath)) {
    die("❌ Erreur : Le fichier PDF n'existe pas : $pdfPath\n");
}

echo "✅ Fichier PDF trouvé\n\n";

// Méthode 1 : Imagick (extension PHP)
if (extension_loaded('imagick')) {
    echo "📦 Tentative avec Imagick...\n";
    try {
        $imagick = new Imagick();
        $imagick->setResolution(300, 300); // Haute résolution
        $imagick->readImage($pdfPath . '[0]'); // Première page seulement
        $imagick->setImageFormat('png');
        $imagick->setImageCompressionQuality(95);
        
        // Redimensionner si nécessaire (optionnel)
        // $imagick->resizeImage(1920, 1080, Imagick::FILTER_LANCZOS, 1, true);
        
        $imagick->writeImage($outputPath);
        $imagick->clear();
        $imagick->destroy();
        
        if (file_exists($outputPath)) {
            $size = filesize($outputPath);
            echo "✅ Conversion réussie avec Imagick !\n";
            echo "   Fichier créé : $outputPath\n";
            echo "   Taille : " . round($size / 1024, 2) . " KB\n";
            exit(0);
        }
    } catch (Exception $e) {
        echo "⚠️  Imagick disponible mais erreur : " . $e->getMessage() . "\n\n";
    }
} else {
    echo "⚠️  Extension Imagick non disponible\n\n";
}

// Méthode 2 : Ghostscript via exec()
echo "📦 Tentative avec Ghostscript...\n";
$gsCommands = [
    'gswin64c',  // Windows 64-bit
    'gswin32c',  // Windows 32-bit
    'gs',        // Linux/Mac
];

$gsFound = false;
foreach ($gsCommands as $gsCmd) {
    $output = [];
    $returnVar = 0;
    @exec("$gsCmd -v 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        $gsFound = true;
        echo "✅ Ghostscript trouvé : $gsCmd\n";
        
        // Commande de conversion
        $command = sprintf(
            '%s -dNOPAUSE -dBATCH -sDEVICE=png16m -r300 -dFirstPage=1 -dLastPage=1 -sOutputFile="%s" "%s"',
            escapeshellarg($gsCmd),
            escapeshellarg($outputPath),
            escapeshellarg($pdfPath)
        );
        
        echo "   Exécution de la commande...\n";
        exec($command, $output, $returnVar);
        
        if (file_exists($outputPath) && $returnVar === 0) {
            $size = filesize($outputPath);
            echo "✅ Conversion réussie avec Ghostscript !\n";
            echo "   Fichier créé : $outputPath\n";
            echo "   Taille : " . round($size / 1024, 2) . " KB\n";
            exit(0);
        } else {
            echo "⚠️  Ghostscript disponible mais conversion échouée\n";
            if (!empty($output)) {
                echo "   Erreur : " . implode("\n   ", $output) . "\n";
            }
        }
        break;
    }
}

if (!$gsFound) {
    echo "⚠️  Ghostscript non trouvé\n\n";
}

// Méthode 3 : Instructions manuelles
echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "❌ Aucun outil de conversion automatique disponible\n";
echo "═══════════════════════════════════════════════════════════\n\n";
echo "📋 SOLUTIONS ALTERNATIVES :\n\n";
echo "1️⃣  OUTILS EN LIGNE (Recommandé - Rapide) :\n";
echo "   • Canva : https://www.canva.com/fr_fr/outils/convertir-pdf-en-png/\n";
echo "   • HiPDF : https://www.hipdf.com/fr/pdf-en-png\n";
echo "   • Adobe Acrobat : https://www.adobe.com/fr/acrobat/hub/how-to-convert-pdf-to-png.html\n";
echo "   • Converter.app : https://converter.app/fr/pdf-en-png/\n\n";
echo "   Instructions :\n";
echo "   1. Ouvrez l'un de ces sites\n";
echo "   2. Téléchargez le fichier : $pdfPath\n";
echo "   3. Convertissez en PNG\n";
echo "   4. Enregistrez le PNG dans : $outputPath\n\n";
echo "2️⃣  INSTALLER ImageMagick (Windows) :\n";
echo "   • Télécharger : https://imagemagick.org/script/download.php#windows\n";
echo "   • Installer et ajouter au PATH\n";
echo "   • Installer l'extension PHP Imagick :\n";
echo "     - Télécharger depuis : https://pecl.php.net/package/imagick\n";
echo "     - Ou utiliser XAMPP avec Imagick pré-installé\n\n";
echo "3️⃣  INSTALLER Ghostscript (Windows) :\n";
echo "   • Télécharger : https://www.ghostscript.com/download/gsdnld.html\n";
echo "   • Installer et ajouter au PATH\n";
echo "   • Relancer ce script\n\n";
echo "═══════════════════════════════════════════════════════════\n";
exit(1);

