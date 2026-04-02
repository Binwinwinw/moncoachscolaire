# Script PowerShell pour optimiser les vidéos MP4
# Usage: .\optimize_videos.ps1 [chemin_vers_ffmpeg]

param(
    [string]$ffmpegPath = "ffmpeg",  # Chemin vers ffmpeg (ou "ffmpeg" si dans PATH)
    [string]$inputDir = "assets\img\coach",
    [string]$outputDir = "assets\img\coach\optimized",
    [switch]$createWebM = $true,
    [switch]$backupOriginal = $true
)

Write-Host "Optimisation des videos MP4" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier que FFmpeg est disponible
try {
    $ffmpegVersion = & $ffmpegPath -version 2>&1 | Select-Object -First 1
    Write-Host "[OK] FFmpeg trouve: $ffmpegVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERREUR] FFmpeg non trouve. Veuillez installer FFmpeg ou specifier le chemin avec -ffmpegPath" -ForegroundColor Red
    Write-Host "   Exemple: .\optimize_videos.ps1 -ffmpegPath 'C:\ffmpeg\bin\ffmpeg.exe'" -ForegroundColor Yellow
    exit 1
}

# Créer le dossier de sortie
$fullOutputDir = Join-Path $PSScriptRoot "..\$outputDir"
if (-not (Test-Path $fullOutputDir)) {
    New-Item -ItemType Directory -Path $fullOutputDir -Force | Out-Null
    Write-Host "[OK] Dossier de sortie cree: $fullOutputDir" -ForegroundColor Green
}

# Créer le dossier de backup si nécessaire
$backupDir = Join-Path $PSScriptRoot "..\backups\videos_original"
if ($backupOriginal -and -not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
}

# Trouver tous les fichiers MP4
$inputPath = Join-Path $PSScriptRoot "..\$inputDir"
$videoFiles = Get-ChildItem -Path $inputPath -Filter "*.mp4" -File

if ($videoFiles.Count -eq 0) {
    Write-Host "[ATTENTION] Aucun fichier MP4 trouve dans $inputPath" -ForegroundColor Yellow
    exit 0
}

Write-Host "$($videoFiles.Count) video(s) trouvee(s)" -ForegroundColor Cyan
Write-Host ""

$totalOriginalSize = 0
$totalOptimizedSize = 0
$totalWebMSize = 0
$processed = 0
$skipped = 0

foreach ($video in $videoFiles) {
    $processed++
    $inputFile = $video.FullName
    $outputFile = Join-Path $fullOutputDir $video.Name
    $webmFile = Join-Path $fullOutputDir ($video.BaseName + ".webm")
    
    Write-Host "[$processed/$($videoFiles.Count)] Traitement de: $($video.Name)" -ForegroundColor Yellow
    Write-Host "   Taille originale: $([math]::Round($video.Length / 1MB, 2)) MB" -ForegroundColor Gray
    
    $totalOriginalSize += $video.Length
    
    # Backup de l'original si demande
    if ($backupOriginal) {
        $backupFile = Join-Path $backupDir $video.Name
        if (-not (Test-Path $backupFile)) {
            Copy-Item $inputFile $backupFile -Force
            Write-Host "   [OK] Backup cree" -ForegroundColor Gray
        }
    }
    
    # Verifier si le fichier optimise existe deja
    if (Test-Path $outputFile) {
        $existingSize = (Get-Item $outputFile).Length
        if ($existingSize -lt $video.Length) {
            Write-Host "   [SKIP] Fichier optimise existe deja (plus petit)" -ForegroundColor Cyan
            $totalOptimizedSize += $existingSize
            $skipped++
            continue
        }
    }
    
    # OPTIMISATION 1: MP4 compresse (H.264, bitrate reduit, resolution optimisee)
    Write-Host "   [TRAITEMENT] Compression MP4..." -ForegroundColor Cyan
    
    # Construire le filtre scale avec -2 pour garantir des dimensions paires (requis par H.264)
    # Le -2 indique a FFmpeg de calculer automatiquement une dimension paire
    $scaleFilter = "scale=-2:720"  # Hauteur 720, largeur calculee automatiquement (paire)
    
    # Parametres plus robustes pour gerer differents formats de videos
    # -strict -2 permet les codecs experimentaux si necessaire
    # -pix_fmt yuv420p assure la compatibilite maximale
    # -avoid_negative_ts make_zero corrige les problemes de timestamps
    $mp4Args = @(
        "-i", "`"$inputFile`""
        "-c:v", "libx264"           # Codec H.264
        "-preset", "slow"            # Meilleure compression (plus lent)
        "-crf", "28"                 # Qualite (18-28, plus eleve = plus compresse)
        "-vf", $scaleFilter          # Max 720p
        "-pix_fmt", "yuv420p"        # Format pixel compatible (necessaire pour certains lecteurs)
        "-c:a", "aac"                # Audio AAC
        "-b:a", "64k"                # Bitrate audio reduit
        "-movflags", "+faststart"    # Optimisation pour web (debut rapide)
        "-avoid_negative_ts", "make_zero"  # Corriger les problemes de timestamps
        "-f", "mp4"                  # Forcer le format MP4
        "-strict", "-2"              # Permettre les codecs experimentaux si necessaire
        "-y"                         # Ecraser si existe
        "`"$outputFile`""
    )
    
    # Capturer les erreurs FFmpeg
    $errorFile = Join-Path $env:TEMP "ffmpeg_error_$processed.txt"
    $mp4Process = Start-Process -FilePath $ffmpegPath -ArgumentList $mp4Args -Wait -NoNewWindow -PassThru -RedirectStandardError $errorFile
    
    if ($mp4Process.ExitCode -eq 0 -and (Test-Path $outputFile)) {
        $optimizedSize = (Get-Item $outputFile).Length
        
        # Verifier que le fichier n'est pas trop petit (probablement corrompu)
        if ($optimizedSize -lt 10000) {
            Write-Host "   [ERREUR] Fichier optimise trop petit ($([math]::Round($optimizedSize / 1KB, 2)) KB) - probablement corrompu" -ForegroundColor Red
            if (Test-Path $errorFile) {
                $errorContent = Get-Content $errorFile -Raw -ErrorAction SilentlyContinue
                if ($errorContent) {
                    Write-Host "   Details: $($errorContent.Substring(0, [Math]::Min(200, $errorContent.Length)))" -ForegroundColor Gray
                }
            }
            Remove-Item $outputFile -ErrorAction SilentlyContinue
            continue
        }
        
        $reduction = [math]::Round((1 - ($optimizedSize / $video.Length)) * 100, 1)
        
        # Avertir si la reduction est trop importante (probablement un probleme)
        if ($reduction -gt 90) {
            Write-Host "   [ATTENTION] Reduction tres importante (-$reduction%) - verifier la qualite" -ForegroundColor Yellow
        }
        
        Write-Host "   [OK] MP4 optimise: $([math]::Round($optimizedSize / 1MB, 2)) MB (-$reduction%)" -ForegroundColor Green
        $totalOptimizedSize += $optimizedSize
    } else {
        Write-Host "   [ERREUR] Erreur lors de la compression MP4 (Code: $($mp4Process.ExitCode))" -ForegroundColor Red
        if (Test-Path $errorFile) {
            $errorContent = Get-Content $errorFile -Raw -ErrorAction SilentlyContinue
            if ($errorContent) {
                # Afficher les dernieres lignes d'erreur
                $errorLines = $errorContent -split "`n" | Select-Object -Last 3
                Write-Host "   Details: $($errorLines -join ' ')" -ForegroundColor Gray
            }
            Remove-Item $errorFile -ErrorAction SilentlyContinue
        }
        continue
    }
    
    # Nettoyer le fichier d'erreur si tout s'est bien passe
    if (Test-Path $errorFile) {
        Remove-Item $errorFile -ErrorAction SilentlyContinue
    }
    
    # OPTIMISATION 2: WebM (VP9) - Format plus leger
    if ($createWebM) {
        Write-Host "   [TRAITEMENT] Conversion WebM..." -ForegroundColor Cyan
        
        # Utiliser -2 pour garantir des dimensions paires et forcer l'alpha pour le fond transparent
        $webmScaleFilter = "scale=-2:720,format=yuva420p"  # Dimensions paires + canal alpha
        
        $webmArgs = @(
            "-i", "`"$inputFile`""
            "-c:v", "libvpx-vp9"     # Codec VP9
            "-crf", "30"             # Qualite (0-63, plus eleve = plus compresse)
            "-b:v", "0"              # Bitrate variable
            "-vf", $webmScaleFilter
            "-pix_fmt", "yuva420p"   # Format pixel avec canal alpha
            "-metadata:s:v:0", 'alpha_mode="1"'  # Indiquer l'alpha dans les metadonnees
            "-c:a", "libopus"        # Audio Opus
            "-b:a", "64k"
            "-avoid_negative_ts", "make_zero"  # Corriger les problemes de timestamps
            "-row-mt", "1"           # Multi-threading (si disponible)
            "-y"
            "`"$webmFile`""
        )
        
        # Capturer les erreurs FFmpeg pour WebM
        $webmErrorFile = Join-Path $env:TEMP "ffmpeg_webm_error_$processed.txt"
        $webmProcess = Start-Process -FilePath $ffmpegPath -ArgumentList $webmArgs -Wait -NoNewWindow -PassThru -RedirectStandardError $webmErrorFile
        
        if ($webmProcess.ExitCode -eq 0 -and (Test-Path $webmFile)) {
            $webmSize = (Get-Item $webmFile).Length
            
            # Verifier que le fichier n'est pas trop petit
            if ($webmSize -lt 10000) {
                Write-Host "   [ERREUR] Fichier WebM trop petit ($([math]::Round($webmSize / 1KB, 2)) KB) - probablement corrompu" -ForegroundColor Red
                Remove-Item $webmFile -ErrorAction SilentlyContinue
            } else {
                $webmReduction = [math]::Round((1 - ($webmSize / $video.Length)) * 100, 1)
                
                # Avertir si la reduction est trop importante
                if ($webmReduction -gt 90) {
                    Write-Host "   [ATTENTION] Reduction tres importante (-$webmReduction%) - verifier la qualite" -ForegroundColor Yellow
                }
                
                Write-Host "   [OK] WebM cree: $([math]::Round($webmSize / 1MB, 2)) MB (-$webmReduction%)" -ForegroundColor Green
                $totalWebMSize += $webmSize
            }
        } else {
            Write-Host "   [ATTENTION] Erreur lors de la conversion WebM (Code: $($webmProcess.ExitCode))" -ForegroundColor Yellow
            if (Test-Path $webmErrorFile) {
                $errorContent = Get-Content $webmErrorFile -Raw -ErrorAction SilentlyContinue
                if ($errorContent -and $errorContent -notmatch "VP9|libvpx") {
                    # Afficher seulement si ce n'est pas juste un probleme de codec
                    $errorLines = $errorContent -split "`n" | Select-Object -Last 2
                    Write-Host "   Details: $($errorLines -join ' ')" -ForegroundColor Gray
                }
                Remove-Item $webmErrorFile -ErrorAction SilentlyContinue
            }
        }
        
        # Nettoyer le fichier d'erreur si tout s'est bien passe
        if (Test-Path $webmErrorFile) {
            Remove-Item $webmErrorFile -ErrorAction SilentlyContinue
        }
    }
    
    Write-Host ""
}

# Resume
Write-Host "Resume de l'optimisation" -ForegroundColor Cyan
Write-Host "===========================" -ForegroundColor Cyan
Write-Host "Videos traitees: $processed" -ForegroundColor White
Write-Host "Videos ignorees (deja optimisees): $skipped" -ForegroundColor White
Write-Host ""
Write-Host "Taille totale originale: $([math]::Round($totalOriginalSize / 1MB, 2)) MB" -ForegroundColor White
Write-Host "Taille totale MP4 optimise: $([math]::Round($totalOptimizedSize / 1MB, 2)) MB" -ForegroundColor Green
if ($totalWebMSize -gt 0) {
    Write-Host "Taille totale WebM: $([math]::Round($totalWebMSize / 1MB, 2)) MB" -ForegroundColor Green
}
Write-Host ""
$totalReduction = [math]::Round((1 - ($totalOptimizedSize / $totalOriginalSize)) * 100, 1)
Write-Host "Reduction totale MP4: $totalReduction%" -ForegroundColor Green
if ($totalWebMSize -gt 0) {
    $webmReduction = [math]::Round((1 - ($totalWebMSize / $totalOriginalSize)) * 100, 1)
    Write-Host "Reduction totale WebM: $webmReduction%" -ForegroundColor Green
}
Write-Host ""
Write-Host "[OK] Optimisation terminee!" -ForegroundColor Green
Write-Host "Fichiers optimises dans: $fullOutputDir" -ForegroundColor Cyan
