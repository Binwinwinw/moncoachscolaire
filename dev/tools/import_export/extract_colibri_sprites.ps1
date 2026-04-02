# Script PowerShell pour extraire les 5 poses du sprite colibricartoon.jpg
# Utilise .NET System.Drawing pour decouper l'image

$spriteFile = "assets\img\coach\colibricartoon.jpg"
$outputDir = "assets\img\coach\colibri-sprites"

# Creer le dossier de sortie
if (-not (Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

# Verifier que le fichier existe
if (-not (Test-Path $spriteFile)) {
    Write-Host "Erreur: Le fichier '$spriteFile' n'existe pas." -ForegroundColor Red
    exit 1
}

Write-Host "Extraction des poses du sprite Colibri..." -ForegroundColor Cyan
Write-Host ""

# Charger l'image avec .NET
Add-Type -AssemblyName System.Drawing

try {
    $bitmap = New-Object System.Drawing.Bitmap $spriteFile
    
    $width = $bitmap.Width
    $height = $bitmap.Height
    $spriteWidth = [int]($width / 5)
    
    Write-Host "Dimensions du sprite: ${width}x${height}" -ForegroundColor Yellow
    Write-Host "Dimensions par pose: ${spriteWidth}x${height}" -ForegroundColor Yellow
    Write-Host ""
    
    # Definir les noms des poses
    $poses = @(
        @{Index=0; Name="neutre"},
        @{Index=1; Name="heureux"},
        @{Index=2; Name="encourageant"},
        @{Index=3; Name="celebration"},
        @{Index=4; Name="reflexion"}
    )
    
    # Extraire chaque pose
    foreach ($pose in $poses) {
        $x = $pose.Index * $spriteWidth
        
        # Creer une nouvelle bitmap pour cette pose
        $poseBitmap = New-Object System.Drawing.Bitmap $spriteWidth, $height
        
        # Creer un Graphics pour copier la partie de l'image
        $graphics = [System.Drawing.Graphics]::FromImage($poseBitmap)
        $srcRect = New-Object System.Drawing.Rectangle($x, 0, $spriteWidth, $height)
        $destRect = New-Object System.Drawing.Rectangle(0, 0, $spriteWidth, $height)
        $graphics.DrawImage($bitmap, $destRect, $srcRect, [System.Drawing.GraphicsUnit]::Pixel)
        
        # Enregistrer en PNG
        $outputFile = Join-Path $outputDir "colibri-$($pose.Name).png"
        $poseBitmap.Save($outputFile, [System.Drawing.Imaging.ImageFormat]::Png)
        
        $fileSize = (Get-Item $outputFile).Length / 1KB
        Write-Host "Pose $($pose.Name) extraite -> $outputFile ($([math]::Round($fileSize, 2)) KB)" -ForegroundColor Green
        
        # Liberer les ressources
        $graphics.Dispose()
        $poseBitmap.Dispose()
    }
    
    $bitmap.Dispose()
    
    Write-Host ""
    Write-Host "Extraction terminee avec succes !" -ForegroundColor Green
    Write-Host "Fichiers crees dans: $outputDir" -ForegroundColor Cyan
    
    # Lister les fichiers crees
    Get-ChildItem $outputDir -Filter "colibri-*.png" | ForEach-Object {
        $sizeKB = [math]::Round($_.Length / 1KB, 2)
        Write-Host "   $($_.Name) ($sizeKB KB)" -ForegroundColor Gray
    }
    
} catch {
    Write-Host "Erreur: $_" -ForegroundColor Red
    exit 1
}
