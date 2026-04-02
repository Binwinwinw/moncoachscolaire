# Script PowerShell pour forcer la suppression de la base de données
# Ce script arrête MySQL, supprime le dossier de la base, puis redémarre MySQL

Write-Host "=== SUPPRESSION FORCÉE DE LA BASE DE DONNÉES ===" -ForegroundColor Yellow
Write-Host ""

$mysqlDataPath = "C:\xampp\mysql\data\moncoachscolaire"
$xamppControl = "C:\xampp\xampp-control.exe"

# Vérifier si le dossier existe
if (Test-Path $mysqlDataPath) {
    Write-Host "Dossier trouve: $mysqlDataPath" -ForegroundColor Green
    
    # Afficher les fichiers
    Write-Host "`nFichiers dans le dossier:" -ForegroundColor Cyan
    Get-ChildItem $mysqlDataPath | Select-Object Name, Length | Format-Table -AutoSize
    
    Write-Host "`nATTENTION: Ce script va:" -ForegroundColor Red
    Write-Host "1. Arrêter MySQL (vous devrez le faire manuellement dans XAMPP)" -ForegroundColor Yellow
    Write-Host "2. Supprimer le dossier $mysqlDataPath" -ForegroundColor Yellow
    Write-Host "3. Vous devrez redémarrer MySQL manuellement" -ForegroundColor Yellow
    Write-Host ""
    
    $confirm = Read-Host "Voulez-vous continuer? (O/N)"
    
    if ($confirm -eq "O" -or $confirm -eq "o") {
        Write-Host "`nVerification de MySQL..." -ForegroundColor Cyan
        
        # Essayer de vérifier si MySQL est en cours d'exécution
        $mysqlProcess = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue
        
        if ($mysqlProcess) {
            Write-Host "MySQL est en cours d'execution!" -ForegroundColor Red
            Write-Host "ARRETEZ MySQL dans le panneau de controle XAMPP AVANT de continuer." -ForegroundColor Yellow
            Write-Host "Appuyez sur Entree une fois MySQL arrete..." -ForegroundColor Yellow
            Read-Host
        }
        
        # Essayer de supprimer le dossier
        Write-Host "`nSuppression du dossier..." -ForegroundColor Cyan
        try {
            Remove-Item -Path $mysqlDataPath -Recurse -Force -ErrorAction Stop
            Write-Host "SUCCES: Dossier supprime!" -ForegroundColor Green
            Write-Host ""
            Write-Host "ETAPES SUIVANTES:" -ForegroundColor Green
            Write-Host "1. Redemarrer MySQL dans XAMPP" -ForegroundColor Cyan
            Write-Host "2. Ouvrir phpMyAdmin" -ForegroundColor Cyan
            Write-Host "3. Importer db/mysql_schema.sql" -ForegroundColor Cyan
        } catch {
            Write-Host "ERREUR: Impossible de supprimer le dossier" -ForegroundColor Red
            Write-Host "Details: $($_.Exception.Message)" -ForegroundColor Red
            Write-Host ""
            Write-Host "SOLUTION MANUELLE:" -ForegroundColor Yellow
            Write-Host "1. Arretez MySQL dans XAMPP" -ForegroundColor Cyan
            Write-Host "2. Allez dans: $mysqlDataPath" -ForegroundColor Cyan
            Write-Host "3. Supprimez manuellement tous les fichiers .ibd" -ForegroundColor Cyan
            Write-Host "4. Redemarrez MySQL" -ForegroundColor Cyan
        }
    } else {
        Write-Host "Operation annulee." -ForegroundColor Yellow
    }
} else {
    Write-Host "Dossier non trouve: $mysqlDataPath" -ForegroundColor Green
    Write-Host "La base de donnees a deja ete supprimee ou n'existe pas." -ForegroundColor Green
}

Write-Host "`nAppuyez sur Entree pour quitter..."
Read-Host

