# Script rapide pour optimiser une seule vidéo (test)
# Usage: .\quick_optimize_video.ps1 -inputFile "chemin\vers\video.mp4"

param(
    [Parameter(Mandatory=$true)]
    [string]$inputFile,
    
    [string]$ffmpegPath = "ffmpeg",
    [string]$outputFile = ""
)

if (-not (Test-Path $inputFile)) {
    Write-Host "❌ Fichier non trouvé: $inputFile" -ForegroundColor Red
    exit 1
}

if ([string]::IsNullOrEmpty($outputFile)) {
    $outputFile = $inputFile -replace '\.mp4$', '_optimized.mp4'
}

$originalSize = (Get-Item $inputFile).Length
$originalSizeMB = [math]::Round($originalSize / 1MB, 2)

Write-Host "🎬 Optimisation de: $(Split-Path $inputFile -Leaf)" -ForegroundColor Cyan
Write-Host "   Taille originale: $originalSizeMB MB" -ForegroundColor Gray

# Compression MP4
$inputEscaped = "`"$inputFile`""
$outputEscaped = "`"$outputFile`""
$ffmpegEscaped = escapeshellarg($ffmpegPath)

$args = @(
    "-i", $inputEscaped
    "-c:v", "libx264"
    "-preset", "slow"
    "-crf", "28"
    "-vf", "scale='min(720,iw)':'min(720,ih)':force_original_aspect_ratio=decrease"
    "-c:a", "aac"
    "-b:a", "64k"
    "-movflags", "+faststart"
    "-y"
    $outputEscaped
)

$process = Start-Process -FilePath $ffmpegPath -ArgumentList $args -Wait -NoNewWindow -PassThru

if ($process.ExitCode -eq 0 -and (Test-Path $outputFile)) {
    $optimizedSize = (Get-Item $outputFile).Length
    $optimizedSizeMB = [math]::Round($optimizedSize / 1MB, 2)
    $reduction = [math]::Round((1 - ($optimizedSize / $originalSize)) * 100, 1)
    
    Write-Host "✅ Optimisation réussie!" -ForegroundColor Green
    Write-Host "   Taille optimisée: $optimizedSizeMB MB" -ForegroundColor Green
    Write-Host "   Réduction: $reduction%" -ForegroundColor Green
    Write-Host "   Fichier: $outputFile" -ForegroundColor Cyan
} else {
    Write-Host "❌ Erreur lors de l'optimisation" -ForegroundColor Red
    exit 1
}
