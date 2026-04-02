# Script PowerShell pour supprimer l'arriere-plan en damier des images PNG
# Utilise Python avec PIL/Pillow

param(
    [Parameter(Mandatory=$true)]
    [string]$InputPath,
    
    [string]$OutputPath = "",
    [string]$Method = "auto",
    [switch]$NoBackup
)

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$pythonScript = Join-Path $scriptDir "remove_checkerboard_background.py"

# Verifier que Python est installe
try {
    $pythonVersion = python --version 2>&1
    Write-Host "[OK] Python trouve: $pythonVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERREUR] Python n'est pas installe ou n'est pas dans le PATH" -ForegroundColor Red
    Write-Host "Installez Python depuis https://www.python.org/" -ForegroundColor Yellow
    exit 1
}

# Verifier que Pillow est installe
try {
    python -c "import PIL" 2>&1 | Out-Null
    Write-Host "[OK] Pillow (PIL) est installe" -ForegroundColor Green
} catch {
    Write-Host "[ATTENTION] Pillow (PIL) n'est pas installe" -ForegroundColor Yellow
    Write-Host "Installation de Pillow..." -ForegroundColor Cyan
    pip install Pillow
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[ERREUR] Impossible d'installer Pillow" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "Suppression de l'arriere-plan en damier" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "[IMPORTANT] Les fichiers originaux ne seront PAS modifies" -ForegroundColor Yellow
Write-Host "De nouveaux fichiers seront crees avec le suffixe _no_bg" -ForegroundColor Yellow
Write-Host ""

# Construire la commande Python
$pythonArgs = @(
    $pythonScript
    $InputPath
)

if ($OutputPath) {
    $pythonArgs += "-o", $OutputPath
}

$pythonArgs += "-m", $Method

if ($NoBackup) {
    $pythonArgs += "--no-backup"
}

# Executer le script Python
python $pythonArgs

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "[OK] Operation terminee avec succes!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "[ERREUR] L'operation a echoue" -ForegroundColor Red
    exit 1
}
