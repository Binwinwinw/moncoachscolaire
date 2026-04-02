# Script d'installation des tâches programmées MySQL
# Exécuter EN TANT QU'ADMINISTRATEUR!

Write-Host "=== Configuration des Tâches Programmées MySQL ===" -ForegroundColor Cyan
Write-Host "Assurez-vous de lancer ce script EN TANT QU'ADMINISTRATEUR" -ForegroundColor Yellow
Write-Host ""

# Vérifier les permissions admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")
if (-not $isAdmin) {
    Write-Host "ERREUR: Ce script nécessite les permissions ADMINISTRATEUR!" -ForegroundColor Red
    Write-Host "Relancez PowerShell EN TANT QU'ADMINISTRATEUR" -ForegroundColor Red
    exit 1
}

Write-Host "Permissions admin détectées" -ForegroundColor Green
Write-Host ""

# ===== TÂCHE 1: BACKUP QUOTIDIEN =====
Write-Host "[1/3] Configuration du Backup Quotidien..." -ForegroundColor Cyan

try {
    Unregister-ScheduledTask -TaskName "MySQL Daily Backup" -Confirm:$false -ErrorAction SilentlyContinue | Out-Null
    $action = New-ScheduledTaskAction -Execute "cmd.exe" -Argument '/c "C:\xampp\mysql\backup_quotidien.bat"'
    $trigger = New-ScheduledTaskTrigger -Daily -At 02:00AM
    $settings = New-ScheduledTaskSettingsSet -StartWhenAvailable
    $task = New-ScheduledTask -Action $action -Trigger $trigger -Settings $settings -Description "Backup automatique des bases MySQL"
    Register-ScheduledTask -TaskName "MySQL Daily Backup" -InputObject $task -RunLevel Highest | Out-Null
    Write-Host "  OK: Backup quotidien à 02:00" -ForegroundColor Green
}
catch {
    Write-Host "  ERREUR: $_" -ForegroundColor Red
}

# ===== TÂCHE 2: VERIFICATION HEBDOMADAIRE =====
Write-Host "[2/3] Configuration de la Vérification Hebdomadaire..." -ForegroundColor Cyan

try {
    Unregister-ScheduledTask -TaskName "MySQL Weekly Verification" -Confirm:$false -ErrorAction SilentlyContinue | Out-Null
    $action = New-ScheduledTaskAction -Execute "cmd.exe" -Argument '/c "C:\xampp\mysql\verification_hebdomadaire.bat"'
    $trigger = New-ScheduledTaskTrigger -Weekly -DaysOfWeek Sunday -At 03:00AM
    $settings = New-ScheduledTaskSettingsSet -StartWhenAvailable
    $task = New-ScheduledTask -Action $action -Trigger $trigger -Settings $settings -Description "Vérification hebdomadaire des tables MySQL"
    Register-ScheduledTask -TaskName "MySQL Weekly Verification" -InputObject $task -RunLevel Highest | Out-Null
    Write-Host "  OK: Vérification le dimanche à 03:00" -ForegroundColor Green
}
catch {
    Write-Host "  ERREUR: $_" -ForegroundColor Red
}

# ===== TÂCHE 3: MONITORING CRASH =====
Write-Host "[3/3] Configuration du Monitoring de Crash..." -ForegroundColor Cyan

try {
    Unregister-ScheduledTask -TaskName "MySQL Crash Monitor" -Confirm:$false -ErrorAction SilentlyContinue | Out-Null
    $action = New-ScheduledTaskAction -Execute "cmd.exe" -Argument '/c "C:\xampp\mysql\monitor_crash.bat"'
    $trigger = New-ScheduledTaskTrigger -AtStartup
    $settings = New-ScheduledTaskSettingsSet -StartWhenAvailable
    $task = New-ScheduledTask -Action $action -Trigger $trigger -Settings $settings -Description "Monitoring MySQL"
    Register-ScheduledTask -TaskName "MySQL Crash Monitor" -InputObject $task -RunLevel Highest | Out-Null
    Write-Host "  OK: Monitoring au démarrage du PC" -ForegroundColor Green
}
catch {
    Write-Host "  ERREUR: $_" -ForegroundColor Red
}

Write-Host ""
Write-Host "===== INSTALLATION COMPLETE =====" -ForegroundColor Green
Write-Host ""
