<#
Fix Chocolatey lock/permission issues by listing potential bad folders in C:\ProgramData\chocolatey and moving them to a quarantine folder.

USAGE (PowerShell elevated):
  Open PowerShell "Run as Administrator" then run:
    pwsh ./scripts/fix-choco-lock.ps1

This script does NOT delete anything permanently. It moves suspect folders into a timestamped quarantine under C:\ProgramData\chocolatey\lib-quarantine-<ts>
Then optionally it will try to re-run the provided choco command (or you can run choco manually).

#>

function Test-IsElevated {
    $current = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($current)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

if (-not (Test-IsElevated)) {
    Write-Error "Ce script doit être exécuté dans une session PowerShell élevée (Run as Administrator). Relancez PowerShell en admin et réessayez.";
    exit 2
}

$chocoRoot = 'C:\ProgramData\chocolatey'
if (-not (Test-Path $chocoRoot)) {
    Write-Host "Le dossier Chocolatey attendu n'existe pas : $chocoRoot" -ForegroundColor Yellow
    exit 1
}

Write-Host "Fichier log Chocolatey (tail) :" -ForegroundColor Cyan
Get-Content -Path (Join-Path $chocoRoot 'logs\chocolatey.log') -ErrorAction SilentlyContinue -Tail 80 | ForEach-Object { Write-Host "  $_" }

Write-Host "\nRecherche des répertoires suspects dans $chocoRoot\lib..." -ForegroundColor Cyan
$libPath = Join-Path $chocoRoot 'lib'
$libBadPath = Join-Path $chocoRoot 'lib-bad'

if (-not (Test-Path $libPath)) {
    Write-Host "Aucun répertoire 'lib' trouvé sous $chocoRoot. Rien à faire." -ForegroundColor Yellow
    exit 0
}

$dirs = Get-ChildItem -Path $libPath -Directory -ErrorAction SilentlyContinue

# Heuristique : dossiers dont le nom ressemble à un hash long, ou contenant '-bad', ou (peu de fichiers et pas de .nuspec)
$candidates = @()
foreach ($d in $dirs) {
    $name = $d.Name
    $filesCount = (Get-ChildItem -Path $d.FullName -Recurse -Force -ErrorAction SilentlyContinue | Measure-Object).Count
    $hasNuspec = Test-Path (Join-Path $d.FullName "*.nuspec")
    if ($name -match '^[0-9a-f]{16,}$' -or $name -match '-bad' -or (-not $hasNuspec -and $filesCount -lt 3)) {
        $candidates += $d
    }
}

if ($candidates.Count -eq 0) {
    Write-Host "Aucun dossier suspect détecté dans $libPath." -ForegroundColor Green
    # also check lib-bad leftovers
    if (Test-Path $libBadPath) { Write-Host "Un dossier lib-bad existe déjà : $libBadPath" -ForegroundColor Yellow }
    exit 0
}

Write-Host "Dossiers suspects détectés (liste) :" -ForegroundColor Yellow
$i = 1
foreach ($c in $candidates) {
    Write-Host ("  [{0}] {1}  (fichiers: {2})" -f $i, $c.FullName, (Get-ChildItem $c.FullName -Recurse -Force -ErrorAction SilentlyContinue | Measure-Object).Count)
    $i++
}

$confirm = Read-Host "Voulez-vous mettre ces dossiers en quarantaine (moved -> lib-quarantine-<ts>) ? (y/N)"
if ($confirm -ne 'y' -and $confirm -ne 'Y') {
    Write-Host "Annulé par l'utilisateur. Aucun changement effectué." -ForegroundColor Yellow
    exit 0
}

$ts = Get-Date -Format "yyyyMMdd-HHmmss"
$quarantine = Join-Path $chocoRoot ("lib-quarantine-$ts")
New-Item -Path $quarantine -ItemType Directory -Force | Out-Null

foreach ($c in $candidates) {
    $dest = Join-Path $quarantine $c.Name
    try {
        Write-Host "Déplacement: $($c.FullName) -> $dest"
        Move-Item -Path $c.FullName -Destination $dest -Force -ErrorAction Stop
    } catch {
        Write-Host "ERREUR en déplaçant $($c.FullName) : $_" -ForegroundColor Red
    }
}

Write-Host "Nettoyage possible des dossiers temporaires 'lib-bad' si présent..." -ForegroundColor Cyan
if (Test-Path $libBadPath) {
    Write-Host "Déplacement lib-bad -> ${libBadPath}.archived.$ts" -ForegroundColor Cyan
    try { Move-Item -Path $libBadPath -Destination ($libBadPath + ".archived.$ts") -Force } catch { Write-Host "Impossible de déplacer lib-bad: $_" -ForegroundColor Red }
}

Write-Host "Quarantaine créée : $quarantine" -ForegroundColor Green

# Offer to try a choco command (user can skip)
$tryAgain = Read-Host "Voulez-vous relancer la commande choco d'installation maintenant ? (ex: 'choco install nodejs-lts -y') Entrez la commande ou leave empty to exit."
if ([string]::IsNullOrWhiteSpace($tryAgain)) { Write-Host "Terminé — relancez choco manuellement en admin si souhaité."; exit 0 }

Write-Host "Exécution de : $tryAgain" -ForegroundColor Cyan
try {
    iex $tryAgain
} catch {
    Write-Host "Erreur lors de l'exécution de la commande choco introduite: $_" -ForegroundColor Red
}

Write-Host "Fait. Si vous rencontrez encore des problèmes, redémarrez la machine et relancez l'installation depuis une session admin." -ForegroundColor Green
