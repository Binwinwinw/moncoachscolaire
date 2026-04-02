<?php
/**
 * Script pour extraire les 5 poses du sprite colibricartoon.jpg en 5 fichiers PNG séparés
 * 
 * Usage: php tools/extract_colibri_sprites.php
 */

$spriteFile = __DIR__ . '/../assets/img/coach/colibricartoon.jpg';
$outputDir = __DIR__ . '/../assets/img/coach/colibri-sprites/';

// Créer le dossier de sortie s'il n'existe pas
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Vérifier que le fichier sprite existe
if (!file_exists($spriteFile)) {
    die("Erreur: Le fichier sprite '$spriteFile' n'existe pas.\n");
}

echo "🖼️  Extraction des poses du sprite Colibri...\n\n";

// Vérifier quelle bibliothèque est disponible
$useImagick = extension_loaded('imagick');
$useGD = extension_loaded('gd');

if (!$useImagick && !$useGD) {
    die("❌ Erreur: Aucune bibliothèque d'image disponible (GD ou Imagick requis).\n");
}

echo "📚 Bibliothèque disponible: " . ($useImagick ? "Imagick" : "GD") . "\n\n";

// Lire l'image
if ($useImagick) {
    // Utiliser Imagick (meilleure qualité)
    try {
        $image = new Imagick($spriteFile);
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        
        // Le sprite contient 5 poses côte à côte
        $spriteWidth = $width / 5;
        
        echo "📐 Dimensions du sprite: {$width}x{$height}\n";
        echo "📐 Dimensions par pose: {$spriteWidth}x{$height}\n\n";
        
        // Définir les noms des poses
        $poses = [
            0 => 'neutre',
            1 => 'heureux',
            2 => 'encourageant',
            3 => 'celebration',
            4 => 'reflexion'
        ];
        
        // Extraire chaque pose
        foreach ($poses as $index => $poseName) {
            $x = $index * $spriteWidth;
            
            // Créer une nouvelle image pour cette pose
            $poseImage = clone $image;
            $poseImage->cropImage($spriteWidth, $height, $x, 0);
            
            // Convertir en PNG et enregistrer
            $outputFile = $outputDir . "colibri-{$poseName}.png";
            $poseImage->setImageFormat('png');
            $poseImage->writeImage($outputFile);
            
            echo "✅ Pose {$poseName} extraite → {$outputFile}\n";
            
            $poseImage->destroy();
        }
        
        $image->destroy();
        
    } catch (Exception $e) {
        die("❌ Erreur Imagick: " . $e->getMessage() . "\n");
    }
    
} else {
    // Utiliser GD (fallback)
    $image = imagecreatefromjpeg($spriteFile);
    if (!$image) {
        die("❌ Erreur: Impossible de charger l'image avec GD.\n");
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Le sprite contient 5 poses côte à côte
    $spriteWidth = intval($width / 5);
    
    echo "📐 Dimensions du sprite: {$width}x{$height}\n";
    echo "📐 Dimensions par pose: {$spriteWidth}x{$height}\n\n";
    
    // Définir les noms des poses
    $poses = [
        0 => 'neutre',
        1 => 'heureux',
        2 => 'encourageant',
        3 => 'celebration',
        4 => 'reflexion'
    ];
    
    // Extraire chaque pose
    foreach ($poses as $index => $poseName) {
        $x = $index * $spriteWidth;
        
        // Créer une nouvelle image pour cette pose
        $poseImage = imagecreatetruecolor($spriteWidth, $height);
        
        // Copier la partie correspondante du sprite
        imagecopy($poseImage, $image, 0, 0, $x, 0, $spriteWidth, $height);
        
        // Enregistrer en PNG
        $outputFile = $outputDir . "colibri-{$poseName}.png";
        imagepng($poseImage, $outputFile, 9); // Qualité maximale
        
        echo "✅ Pose {$poseName} extraite → {$outputFile}\n";
        
        imagedestroy($poseImage);
    }
    
    imagedestroy($image);
}

echo "\n✨ Extraction terminée avec succès !\n";
echo "📁 Fichiers créés dans: {$outputDir}\n\n";

// Lister les fichiers créés
$files = glob($outputDir . "colibri-*.png");
foreach ($files as $file) {
    $size = filesize($file);
    $sizeKB = round($size / 1024, 2);
    echo "   📄 " . basename($file) . " ({$sizeKB} KB)\n";
}

?>

