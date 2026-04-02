<?php
/**
 * Script PHP pour optimiser les vidéos MP4 via FFmpeg
 * Usage: php optimize_videos.php [--ffmpeg-path=/chemin/vers/ffmpeg] [--no-webm] [--no-backup]
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$ffmpegPath = 'ffmpeg'; // Par défaut, chercher dans PATH
$inputDir = __DIR__ . '/../assets/img/coach';
$outputDir = __DIR__ . '/../assets/img/coach/optimized';
$createWebM = true;
$backupOriginal = true;

// Parser les arguments
$options = getopt('', ['ffmpeg-path:', 'no-webm', 'no-backup', 'help']);

if (isset($options['help'])) {
    echo "Usage: php optimize_videos.php [options]\n";
    echo "Options:\n";
    echo "  --ffmpeg-path=PATH   Chemin vers l'exécutable ffmpeg\n";
    echo "  --no-webm            Ne pas créer de version WebM\n";
    echo "  --no-backup          Ne pas créer de backup des originaux\n";
    echo "  --help               Afficher cette aide\n";
    exit(0);
}

if (isset($options['ffmpeg-path'])) {
    $ffmpegPath = $options['ffmpeg-path'];
}

if (isset($options['no-webm'])) {
    $createWebM = false;
}

if (isset($options['no-backup'])) {
    $backupOriginal = false;
}

echo "🎬 Optimisation des vidéos MP4\n";
echo "================================\n\n";

// Vérifier que FFmpeg est disponible
$testCommand = escapeshellarg($ffmpegPath) . ' -version 2>&1';
$output = [];
$returnVar = 0;
exec($testCommand, $output, $returnVar);

if ($returnVar !== 0) {
    echo "❌ FFmpeg non trouvé. Veuillez installer FFmpeg ou spécifier le chemin avec --ffmpeg-path\n";
    echo "   Exemple: php optimize_videos.php --ffmpeg-path='C:\\ffmpeg\\bin\\ffmpeg.exe'\n";
    exit(1);
}

echo "✅ FFmpeg trouvé: " . $output[0] . "\n\n";

// Créer le dossier de sortie
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "📁 Dossier de sortie créé: $outputDir\n";
}

// Créer le dossier de backup si nécessaire
$backupDir = __DIR__ . '/../backups/videos_original';
if ($backupOriginal && !is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Trouver tous les fichiers MP4
$videoFiles = glob($inputDir . '/*.mp4');

if (empty($videoFiles)) {
    echo "⚠️  Aucun fichier MP4 trouvé dans $inputDir\n";
    exit(0);
}

echo "📹 " . count($videoFiles) . " vidéo(s) trouvée(s)\n\n";

$totalOriginalSize = 0;
$totalOptimizedSize = 0;
$totalWebMSize = 0;
$processed = 0;
$skipped = 0;

foreach ($videoFiles as $video) {
    $processed++;
    $videoName = basename($video);
    $outputFile = $outputDir . '/' . $videoName;
    $webmFile = $outputDir . '/' . pathinfo($videoName, PATHINFO_FILENAME) . '.webm';
    
    echo "[$processed/" . count($videoFiles) . "] Traitement de: $videoName\n";
    
    $originalSize = filesize($video);
    $originalSizeMB = round($originalSize / 1048576, 2);
    echo "   Taille originale: {$originalSizeMB} MB\n";
    
    $totalOriginalSize += $originalSize;
    
    // Backup de l'original si demandé
    if ($backupOriginal) {
        $backupFile = $backupDir . '/' . $videoName;
        if (!file_exists($backupFile)) {
            copy($video, $backupFile);
            echo "   💾 Backup créé\n";
        }
    }
    
    // Vérifier si le fichier optimisé existe déjà
    if (file_exists($outputFile)) {
        $existingSize = filesize($outputFile);
        if ($existingSize < $originalSize) {
            echo "   ⏭️  Fichier optimisé existe déjà (plus petit)\n";
            $totalOptimizedSize += $existingSize;
            $skipped++;
            continue;
        }
    }
    
    // OPTIMISATION 1: MP4 compressé
    echo "   🔄 Compression MP4...\n";
    
    $videoEscaped = escapeshellarg($video);
    $outputEscaped = escapeshellarg($outputFile);
    $ffmpegEscaped = escapeshellarg($ffmpegPath);
    
    $mp4Command = "$ffmpegEscaped -i $videoEscaped " .
        "-c:v libx264 " .
        "-preset slow " .
        "-crf 28 " .
        "-vf \"scale='min(720,iw)':'min(720,ih)':force_original_aspect_ratio=decrease\" " .
        "-c:a aac " .
        "-b:a 64k " .
        "-movflags +faststart " .
        "-y " .
        "$outputEscaped 2>&1";
    
    exec($mp4Command, $mp4Output, $mp4ReturnVar);
    
    if ($mp4ReturnVar === 0 && file_exists($outputFile)) {
        $optimizedSize = filesize($outputFile);
        $optimizedSizeMB = round($optimizedSize / 1048576, 2);
        $reduction = round((1 - ($optimizedSize / $originalSize)) * 100, 1);
        echo "   ✅ MP4 optimisé: {$optimizedSizeMB} MB (-{$reduction}%)\n";
        $totalOptimizedSize += $optimizedSize;
    } else {
        echo "   ❌ Erreur lors de la compression MP4\n";
        if (!empty($mp4Output)) {
            echo "      " . implode("\n      ", array_slice($mp4Output, -3)) . "\n";
        }
        continue;
    }
    
    // OPTIMISATION 2: WebM (VP9)
    if ($createWebM) {
        echo "   🔄 Conversion WebM...\n";
        
        $webmEscaped = escapeshellarg($webmFile);
        
        $webmCommand = "$ffmpegEscaped -i $videoEscaped " .
            "-c:v libvpx-vp9 " .
            "-crf 30 " .
            "-b:v 0 " .
            "-vf \"scale='min(720,iw)':'min(720,ih)':force_original_aspect_ratio=decrease,format=yuva420p\" " .
            "-pix_fmt yuva420p " .
            "-metadata:s:v:0 alpha_mode=1 " .
            "-c:a libopus " .
            "-b:a 64k " .
            "-row-mt 1 " .
            "-y " .
            "$webmEscaped 2>&1";
        
        exec($webmCommand, $webmOutput, $webmReturnVar);
        
        if ($webmReturnVar === 0 && file_exists($webmFile)) {
            $webmSize = filesize($webmFile);
            $webmSizeMB = round($webmSize / 1048576, 2);
            $webmReduction = round((1 - ($webmSize / $originalSize)) * 100, 1);
            echo "   ✅ WebM créé: {$webmSizeMB} MB (-{$webmReduction}%)\n";
            $totalWebMSize += $webmSize;
        } else {
            echo "   ⚠️  Erreur lors de la conversion WebM (peut être normal si VP9 non disponible)\n";
        }
    }
    
    echo "\n";
}

// Résumé
echo "📊 Résumé de l'optimisation\n";
echo "===========================\n";
echo "Vidéos traitées: $processed\n";
echo "Vidéos ignorées (déjà optimisées): $skipped\n";
echo "\n";

$totalOriginalMB = round($totalOriginalSize / 1048576, 2);
$totalOptimizedMB = round($totalOptimizedSize / 1048576, 2);
echo "Taille totale originale: {$totalOriginalMB} MB\n";
echo "Taille totale MP4 optimisé: {$totalOptimizedMB} MB\n";

if ($totalWebMSize > 0) {
    $totalWebMMB = round($totalWebMSize / 1048576, 2);
    echo "Taille totale WebM: {$totalWebMMB} MB\n";
}

echo "\n";
$totalReduction = round((1 - ($totalOptimizedSize / $totalOriginalSize)) * 100, 1);
echo "💰 Réduction totale MP4: {$totalReduction}%\n";

if ($totalWebMSize > 0) {
    $webmReduction = round((1 - ($totalWebMSize / $totalOriginalSize)) * 100, 1);
    echo "💰 Réduction totale WebM: {$webmReduction}%\n";
}

echo "\n";
echo "✅ Optimisation terminée!\n";
echo "📁 Fichiers optimisés dans: $outputDir\n";
