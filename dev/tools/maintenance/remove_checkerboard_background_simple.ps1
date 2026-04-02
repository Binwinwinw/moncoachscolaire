# Script PowerShell pour supprimer l'arriere-plan en damier - Version Simple (sans NumPy)
# Methode edge uniquement - Supprime uniquement les bords

param(
    [Parameter(Mandatory=$true)]
    [string]$InputPath,
    
    [string]$OutputPath = "",
    [int]$Threshold = 15,
    [double]$EdgeMargin = 0.1,
    [switch]$Force
)

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$pythonScript = Join-Path $scriptDir "remove_checkerboard_background_simple.py"

# Verifier que Python est installe
try {
    $pythonVersion = python --version 2>&1
    Write-Host "[OK] Python trouve: $pythonVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERREUR] Python n'est pas installe" -ForegroundColor Red
    exit 1
}

# Verifier que Pillow est installe
try {
    python -c "import PIL" 2>&1 | Out-Null
    Write-Host "[OK] Pillow est installe" -ForegroundColor Green
} catch {
    Write-Host "[ATTENTION] Pillow manquant, installation..." -ForegroundColor Yellow
    pip install Pillow
}

Write-Host ""
Write-Host "Suppression de l'arriere-plan en damier - Version Simple" -ForegroundColor Cyan
Write-Host "=========================================================" -ForegroundColor Cyan
Write-Host "[IMPORTANT] Les fichiers originaux ne seront PAS modifies" -ForegroundColor Yellow
Write-Host "Methode: Suppression uniquement des bords ($([math]::Round($EdgeMargin * 100))%)" -ForegroundColor Yellow
if (-not $Force) {
    Write-Host "[INFO] Detection automatique: seules les images avec damier seront traitees" -ForegroundColor Cyan
} else {
    Write-Host "[INFO] Mode force: toutes les images seront traitees" -ForegroundColor Yellow
}
Write-Host ""

# Construire la commande Python
$pythonArgs = @(
    $pythonScript
    $InputPath
)

if ($OutputPath) {
    $pythonArgs += "-o", $OutputPath
}

$pythonArgs += "-t", $Threshold.ToString()
# Convertir la virgule en point pour Python (format US)
$marginStr = $EdgeMargin.ToString().Replace(',', '.')
$pythonArgs += "-m", $marginStr

if ($Force) {
    $pythonArgs += "--force"
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
