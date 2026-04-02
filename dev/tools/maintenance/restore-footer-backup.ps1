# Script pour restaurer le footer à son état précédent
# Usage: pwsh tools/restore-footer-backup.ps1

Write-Host "Restauration du footer a l'etat precedent..." -ForegroundColor Yellow

$backupFile = "assets/css/style.css.backup-footer"
$targetFile = "assets/css/style.css"

if (Test-Path $backupFile) {
    Copy-Item -Path $backupFile -Destination $targetFile -Force
    Write-Host "✅ Footer restaure avec succes!" -ForegroundColor Green
    Write-Host "Le fichier de sauvegarde est conserve: $backupFile" -ForegroundColor Cyan
} else {
    Write-Host "❌ Fichier de sauvegarde introuvable: $backupFile" -ForegroundColor Red
    Write-Host "La restauration ne peut pas etre effectuee." -ForegroundColor Yellow
}

