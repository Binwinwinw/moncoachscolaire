<#
fix-choco-lock-delete.ps1
Safe removal: zip (backup) then delete suspicious Chocolatey lib folders.

USAGE (Admin PowerShell required):
  1) Run the safer identification script first: pwsh .\scripts\fix-choco-lock.ps1
  2) If you accept the candidate list, run this to ZIP+DELETE them safely:
     pwsh .\scripts\fix-choco-lock-delete.ps1

This script will:
  - detect candidate directories under C:\ProgramData\chocolatey\lib
  - create a timestamped zip backup under C:\ProgramData\chocolatey\lib-backup-<ts>
  - remove the original folder after successful backup

WARNING: This permanently deletes the original folder after backup. Use only if you are sure or have admin guidance.
#>

function Test-IsElevated {
    $current = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($current)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

if (-not (Test-IsElevated)) {
    Write-Error "Ce script doit être exécuté dans une session PowerShell élevée (Run as Administrator).";
    exit 2
}

$chocoRoot = 'C:\ProgramData\chocolatey'
if (-not (Test-Path $chocoRoot)) {
    Write-Host "Chemin Chocolatey introuvable: $chocoRoot" -ForegroundColor Yellow
    exit 1
}

$libPath = Join-Path $chocoRoot 'lib'
$backupRoot = Join-Path $chocoRoot ("lib-backup-" + (Get-Date -Format 'yyyyMMdd-HHmmss'))

if (-not (Test-Path $libPath)) {
    Write-Host "Aucun dossier 'lib' trouvé : $libPath" -ForegroundColor Yellow
    exit 0
}

$candidates = @()
foreach ($d in Get-ChildItem -Path $libPath -Directory -ErrorAction SilentlyContinue) {
    $name = $d.Name
    $filesCount = (Get-ChildItem -Path $d.FullName -Recurse -Force -ErrorAction SilentlyContinue | Measure-Object).Count
    $hasNuspec = (Get-ChildItem -Path $d.FullName -Filter '*.nuspec' -ErrorAction SilentlyContinue).Count -gt 0
    if ($name -match '^[0-9a-f]{16,}$' -or $name -match '-bad' -or (-not $hasNuspec -and $filesCount -lt 3)) {
        $candidates += $d
    }
}

if ($candidates.Count -eq 0) {
    Write-Host "Aucun dossier suspect détecté. Rien à supprimer." -ForegroundColor Green
    exit 0
}

Write-Host "Dossiers suspects à sauvegarder et supprimer :" -ForegroundColor Yellow
$idx=1
foreach ($c in $candidates) { Write-Host " [$idx] $($c.FullName)"; $idx++ }

$ok = Read-Host "Voulez-vous continuer (ZIP puis supprimer) ? Tapez 'YES' pour confirmer"
if ($ok -ne 'YES') { Write-Host "Abandon — aucune modification effectuée."; exit 0 }

New-Item -Path $backupRoot -ItemType Directory -Force | Out-Null

foreach ($c in $candidates) {
    $zipName = Join-Path $backupRoot ($c.Name + '.zip')
    Write-Host "Archivage: $($c.FullName) -> $zipName"
    try {
        Add-Type -AssemblyName System.IO.Compression.FileSystem
        [System.IO.Compression.ZipFile]::CreateFromDirectory($c.FullName, $zipName)
        Write-Host "Archive créée avec succès : $zipName" -ForegroundColor Green
        Write-Host "Suppression du dossier original : $($c.FullName)"
        Remove-Item -Path $c.FullName -Recurse -Force -ErrorAction Stop
        Write-Host "Supprimé : $($c.FullName)" -ForegroundColor Green
    } catch {
        Write-Host "ERREUR pendant archive/suppression pour $($c.FullName) : $_" -ForegroundColor Red
        Write-Host "Conservation de l'original pour examen manuel." -ForegroundColor Yellow
    }
}

Write-Host "Opération terminée. Sauvegardes : $backupRoot" -ForegroundColor Cyan
Write-Host "Redémarrez la machine si nécessaire, puis relancez la commande Chocolatey ou vérifiez les permissions." -ForegroundColor Green
