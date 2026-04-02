# Script pour supprimer les videos originales maintenant que les versions optimisees sont disponibles
# Usage: .\remove_original_videos.ps1

param(
    [switch]$Force = $false
)

$videoDir = Join-Path $PSScriptRoot "..\assets\img\coach"
$optimizedDir = Join-Path $videoDir "optimized"

Write-Host "Suppression des videos originales" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""

# Verifier que le dossier optimized existe
if (-not (Test-Path $optimizedDir)) {
    Write-Host "[ERREUR] Dossier optimized introuvable: $optimizedDir" -ForegroundColor Red
    exit 1
}

# Liste des videos a supprimer
$videosToRemove = @(
    "cartoondebutderxercice.mp4",
    "cartoonreponsecorrecte.mp4",
    "colibri-cartoon_volant.mp4",
    "colibri-realiste_volant.mp4",
    "colibricartoonvolantbackground.mp4",
    "realistecelebration.mp4",
    "realisteencourager.mp4",
    "realistefelicitations.mp4"
)

$totalSize = 0
$filesToDelete = @()

Write-Host "Verification des fichiers..." -ForegroundColor Yellow
Write-Host ""

foreach ($video in $videosToRemove) {
    $originalPath = Join-Path $videoDir $video
    $optimizedMP4 = Join-Path $optimizedDir $video
    $optimizedWebM = Join-Path $optimizedDir ($video -replace '\.mp4$', '.webm')
    
    if (Test-Path $originalPath) {
        # Verifier que les versions optimisees existent
        if (-not (Test-Path $optimizedMP4)) {
            Write-Host "[ATTENTION] Version optimisee MP4 manquante pour: $video" -ForegroundColor Yellow
            continue
        }
        
        if (-not (Test-Path $optimizedWebM)) {
            Write-Host "[ATTENTION] Version optimisee WebM manquante pour: $video" -ForegroundColor Yellow
            continue
        }
        
        $fileInfo = Get-Item $originalPath
        $sizeMB = [math]::Round($fileInfo.Length / 1MB, 2)
        $totalSize += $fileInfo.Length
        
        $filesToDelete += @{
            Path = $originalPath
            Name = $video
            Size = $sizeMB
        }
        
        Write-Host "[OK] $video - $sizeMB MB (versions optimisees disponibles)" -ForegroundColor Green
    } else {
        Write-Host "[SKIP] $video - Deja supprime ou introuvable" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "Resume" -ForegroundColor Cyan
Write-Host "======" -ForegroundColor Cyan
Write-Host "Fichiers a supprimer: $($filesToDelete.Count)" -ForegroundColor White
Write-Host "Espace a liberer: $([math]::Round($totalSize / 1MB, 2)) MB" -ForegroundColor White
Write-Host ""

if ($filesToDelete.Count -eq 0) {
    Write-Host "[INFO] Aucun fichier a supprimer" -ForegroundColor Yellow
    exit 0
}

# Demander confirmation
if (-not $Force) {
    $confirmation = Read-Host "Voulez-vous supprimer ces fichiers ? (O/N)"
    if ($confirmation -ne 'O' -and $confirmation -ne 'o' -and $confirmation -ne 'Y' -and $confirmation -ne 'y') {
        Write-Host "[ANNULATION] Suppression annulee" -ForegroundColor Yellow
        exit 0
    }
}

# Supprimer les fichiers
Write-Host ""
Write-Host "Suppression en cours..." -ForegroundColor Cyan
Write-Host ""

$deleted = 0
$errors = 0

foreach ($file in $filesToDelete) {
    try {
        Remove-Item $file.Path -Force -ErrorAction Stop
        Write-Host "[OK] Supprime: $($file.Name) ($($file.Size) MB)" -ForegroundColor Green
        $deleted++
    } catch {
        Write-Host "[ERREUR] Impossible de supprimer $($file.Name): $_" -ForegroundColor Red
        $errors++
    }
}

Write-Host ""
Write-Host "Resume final" -ForegroundColor Cyan
Write-Host "============" -ForegroundColor Cyan
Write-Host "Fichiers supprimes: $deleted" -ForegroundColor Green
if ($errors -gt 0) {
    Write-Host "Erreurs: $errors" -ForegroundColor Red
}
Write-Host "Espace libere: $([math]::Round($totalSize / 1MB, 2)) MB" -ForegroundColor Green
Write-Host ""
Write-Host "[OK] Operation terminee!" -ForegroundColor Green
