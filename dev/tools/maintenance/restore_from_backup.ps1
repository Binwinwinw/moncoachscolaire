# Script pour restaurer un fichier depuis son backup
param(
    [Parameter(Mandatory=$true)]
    [string]$FilePath
)

$backupPath = $FilePath + ".backup"

if (-not (Test-Path $backupPath)) {
    Write-Host "[ERREUR] Backup introuvable: $backupPath" -ForegroundColor Red
    exit 1
}

Write-Host "Restauration depuis backup" -ForegroundColor Cyan
Write-Host "=========================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Fichier original: $FilePath" -ForegroundColor White
Write-Host "Backup: $backupPath" -ForegroundColor White
Write-Host ""

# Verifier que le backup est valide avec Python/PIL
$checkScript = @"
from PIL import Image
import sys
try:
    img = Image.open(r'$backupPath')
    print(f'OK: {img.size[0]}x{img.size[1]}, mode: {img.mode}')
    sys.exit(0)
except Exception as e:
    print(f'ERREUR: {str(e)}')
    sys.exit(1)
"@

$checkResult = python -c $checkScript 2>&1

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERREUR] Le backup est corrompu ou invalide" -ForegroundColor Red
    Write-Host "Details: $checkResult" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Solutions possibles:" -ForegroundColor Yellow
    Write-Host "1. Verifier si vous avez une autre copie du fichier" -ForegroundColor White
    Write-Host "2. Utiliser un outil de recuperation de fichiers" -ForegroundColor White
    Write-Host "3. Verifier l'historique Git si disponible" -ForegroundColor White
    exit 1
}

Write-Host "[OK] Backup valide: $checkResult" -ForegroundColor Green
Write-Host ""

# Creer un backup de l'actuel avant restauration
if (Test-Path $FilePath) {
    $currentBackup = $FilePath + ".before_restore"
    Copy-Item $FilePath $currentBackup -Force
    Write-Host "[OK] Backup de l'actuel cree: $currentBackup" -ForegroundColor Green
}

# Restaurer depuis le backup
try {
    Copy-Item $backupPath $FilePath -Force
    Write-Host "[OK] Fichier restaure avec succes!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Le fichier original a ete restaure depuis le backup." -ForegroundColor Green
} catch {
    Write-Host "[ERREUR] Impossible de restaurer: $_" -ForegroundColor Red
    exit 1
}
