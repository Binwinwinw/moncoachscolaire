# Script PowerShell pour supprimer l'arriere-plan en damier - Version Douce
# Utilise des methodes plus precises et moins agressives

param(
    [Parameter(Mandatory=$true)]
    [string]$InputPath,
    
    [string]$OutputPath = "",
    [ValidateSet('edge', 'smart', 'conservative')]
    [string]$Method = "edge",
    [int]$Threshold = 15
)

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$pythonScript = Join-Path $scriptDir "remove_checkerboard_background_gentle.py"

# Verifier que Python est installe
try {
    $pythonVersion = python --version 2>&1
    Write-Host "[OK] Python trouve: $pythonVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERREUR] Python n'est pas installe ou n'est pas dans le PATH" -ForegroundColor Red
    exit 1
}

# Verifier que Pillow est installe
try {
    python -c "import PIL; import numpy" 2>&1 | Out-Null
    Write-Host "[OK] Pillow et NumPy sont installes" -ForegroundColor Green
} catch {
    Write-Host "[ATTENTION] Pillow ou NumPy manquants" -ForegroundColor Yellow
    Write-Host "Installation..." -ForegroundColor Cyan
    pip install Pillow numpy
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[ERREUR] Impossible d'installer les dependances" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "Suppression de l'arriere-plan en damier - Version Douce" -ForegroundColor Cyan
Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host "[IMPORTANT] Les fichiers originaux ne seront PAS modifies" -ForegroundColor Yellow
Write-Host "De nouveaux fichiers seront crees avec le suffixe _no_bg_[method]" -ForegroundColor Yellow
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
$pythonArgs += "-t", $Threshold.ToString()

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
