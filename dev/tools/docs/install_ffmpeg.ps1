# Script PowerShell pour installer FFmpeg automatiquement sur Windows
# Usage: .\install_ffmpeg.ps1 [--path C:\ffmpeg] [--add-to-path]

param(
    [string]$installPath = "C:\ffmpeg",
    [switch]$addToPath = $false,
    [switch]$help = $false
)

if ($help) {
    Write-Host "Script d'installation automatique de FFmpeg pour Windows" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage:" -ForegroundColor Yellow
    Write-Host "  .\install_ffmpeg.ps1                    # Installation dans C:\ffmpeg"
    Write-Host "  .\install_ffmpeg.ps1 -installPath D:\ffmpeg  # Installation dans D:\ffmpeg"
    Write-Host "  .\install_ffmpeg.ps1 -addToPath         # Ajouter au PATH automatiquement"
    Write-Host ""
    exit 0
}

Write-Host "🎬 Installation de FFmpeg pour Windows" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si FFmpeg est déjà installé
try {
    $ffmpegVersion = & ffmpeg -version 2>&1 | Select-Object -First 1
    if ($ffmpegVersion -match "ffmpeg version") {
        Write-Host "✅ FFmpeg est déjà installé !" -ForegroundColor Green
        Write-Host "   $ffmpegVersion" -ForegroundColor Gray
        Write-Host ""
        Write-Host "Voulez-vous quand même continuer ? (O/N)" -ForegroundColor Yellow
        $response = Read-Host
        if ($response -ne "O" -and $response -ne "o" -and $response -ne "Y" -and $response -ne "y") {
            Write-Host "Installation annulée." -ForegroundColor Yellow
            exit 0
        }
    }
} catch {
    # FFmpeg n'est pas installé, continuer
}

Write-Host "📥 Téléchargement de FFmpeg..." -ForegroundColor Cyan
Write-Host ""

# URL de téléchargement (version essentials)
$downloadUrl = "https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip"
$zipFile = Join-Path $env:TEMP "ffmpeg-release-essentials.zip"
$extractPath = Join-Path $env:TEMP "ffmpeg-extract"

try {
    # Télécharger FFmpeg
    Write-Host "   Téléchargement depuis: $downloadUrl" -ForegroundColor Gray
    Write-Host "   Fichier temporaire: $zipFile" -ForegroundColor Gray
    Write-Host ""
    
    # Utiliser Invoke-WebRequest pour télécharger
    $ProgressPreference = 'SilentlyContinue'
    Invoke-WebRequest -Uri $downloadUrl -OutFile $zipFile -UseBasicParsing
    
    if (-not (Test-Path $zipFile)) {
        Write-Host "❌ Erreur lors du téléchargement" -ForegroundColor Red
        Write-Host "   Veuillez télécharger manuellement depuis:" -ForegroundColor Yellow
        Write-Host "   https://www.gyan.dev/ffmpeg/builds/" -ForegroundColor Yellow
        Write-Host "   Choisir: ffmpeg-release-essentials.zip" -ForegroundColor Yellow
        exit 1
    }
    
    $fileSize = (Get-Item $zipFile).Length / 1MB
    Write-Host "✅ Téléchargement réussi ($([math]::Round($fileSize, 2)) MB)" -ForegroundColor Green
    Write-Host ""
    
    # Extraire
    Write-Host "📦 Extraction de FFmpeg..." -ForegroundColor Cyan
    Write-Host "   Destination: $installPath" -ForegroundColor Gray
    
    if (Test-Path $extractPath) {
        Remove-Item $extractPath -Recurse -Force
    }
    New-Item -ItemType Directory -Path $extractPath -Force | Out-Null
    
    # Extraire le ZIP
    Expand-Archive -Path $zipFile -DestinationPath $extractPath -Force
    
    # Trouver le dossier bin dans l'extraction
    $binPath = Get-ChildItem -Path $extractPath -Recurse -Filter "ffmpeg.exe" | Select-Object -First 1
    
    if (-not $binPath) {
        Write-Host "❌ ffmpeg.exe non trouvé dans l'archive" -ForegroundColor Red
        exit 1
    }
    
    $sourceDir = $binPath.Directory.Parent.FullName
    
    # Créer le dossier d'installation
    if (Test-Path $installPath) {
        Write-Host "⚠️  Le dossier $installPath existe déjà" -ForegroundColor Yellow
        Write-Host "   Voulez-vous le remplacer ? (O/N)" -ForegroundColor Yellow
        $response = Read-Host
        if ($response -eq "O" -or $response -eq "o" -or $response -eq "Y" -or $response -eq "y") {
            Remove-Item $installPath -Recurse -Force
        } else {
            Write-Host "Installation annulée." -ForegroundColor Yellow
            exit 0
        }
    }
    
    # Copier les fichiers
    Write-Host "   Copie des fichiers..." -ForegroundColor Gray
    Copy-Item -Path $sourceDir -Destination $installPath -Recurse -Force
    
    Write-Host "✅ FFmpeg installé dans: $installPath" -ForegroundColor Green
    Write-Host ""
    
    # Vérifier l'installation
    $ffmpegExe = Join-Path $installPath "bin\ffmpeg.exe"
    if (Test-Path $ffmpegExe) {
        $version = & $ffmpegExe -version 2>&1 | Select-Object -First 1
        Write-Host "✅ Installation vérifiée:" -ForegroundColor Green
        Write-Host "   $version" -ForegroundColor Gray
        Write-Host ""
    } else {
        Write-Host "⚠️  ffmpeg.exe non trouvé dans $installPath\bin" -ForegroundColor Yellow
    }
    
    # Ajouter au PATH si demandé
    if ($addToPath) {
        Write-Host "🔧 Ajout de FFmpeg au PATH..." -ForegroundColor Cyan
        
        $binPath = Join-Path $installPath "bin"
        $currentPath = [Environment]::GetEnvironmentVariable("Path", "User")
        
        if ($currentPath -notlike "*$binPath*") {
            $newPath = $currentPath + ";$binPath"
            [Environment]::SetEnvironmentVariable("Path", $newPath, "User")
            Write-Host "✅ FFmpeg ajouté au PATH utilisateur" -ForegroundColor Green
            Write-Host "   Redémarrez PowerShell pour que les changements prennent effet" -ForegroundColor Yellow
        } else {
            Write-Host "✅ FFmpeg est déjà dans le PATH" -ForegroundColor Green
        }
    } else {
        Write-Host "💡 Pour ajouter FFmpeg au PATH, exécutez:" -ForegroundColor Yellow
        Write-Host "   .\install_ffmpeg.ps1 -addToPath" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "   OU utilisez le chemin complet dans les scripts:" -ForegroundColor Yellow
        Write-Host "   .\optimize_videos.ps1 -ffmpegPath `"$ffmpegExe`"" -ForegroundColor Cyan
    }
    
    # Nettoyer les fichiers temporaires
    Write-Host ""
    Write-Host "🧹 Nettoyage des fichiers temporaires..." -ForegroundColor Cyan
    Remove-Item $zipFile -Force -ErrorAction SilentlyContinue
    Remove-Item $extractPath -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "✅ Nettoyage terminé" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "🎉 Installation terminée avec succès !" -ForegroundColor Green
    Write-Host ""
    Write-Host "📝 Prochaines étapes:" -ForegroundColor Cyan
    Write-Host "   1. Redémarrer PowerShell (si ajouté au PATH)" -ForegroundColor White
    Write-Host "   2. Tester: ffmpeg -version" -ForegroundColor White
    Write-Host "   3. Optimiser les vidéos: .\optimize_videos.ps1" -ForegroundColor White
    
} catch {
    Write-Host "❌ Erreur lors de l'installation:" -ForegroundColor Red
    Write-Host "   $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "💡 Installation manuelle recommandée:" -ForegroundColor Yellow
    Write-Host "   1. Télécharger depuis: https://www.gyan.dev/ffmpeg/builds/" -ForegroundColor White
    Write-Host "   2. Choisir: ffmpeg-release-essentials.zip" -ForegroundColor White
    Write-Host "   3. Extraire dans: $installPath" -ForegroundColor White
    Write-Host "   4. Voir: tools\INSTALL-FFMPEG.md pour plus de détails" -ForegroundColor White
    exit 1
}
