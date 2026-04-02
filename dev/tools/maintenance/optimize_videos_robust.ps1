# Script PowerShell robuste pour optimiser les videos MP4
# Version adaptee pour gerer differents formats de videos (IA, etc.)
# Usage: .\optimize_videos_robust.ps1 [chemin_vers_ffmpeg]

param(
    [string]$ffmpegPath = "ffmpeg",
    [string]$inputDir = "assets\img\coach",
    [string]$outputDir = "assets\img\coach\optimized",
    [switch]$createWebM = $true,
    [switch]$backupOriginal = $true
)

Write-Host "Optimisation robuste des videos MP4" -ForegroundColor Cyan
Write-Host "====================================" -ForegroundColor Cyan
Write-Host ""

# Verifier que FFmpeg est disponible
try {
    $ffmpegVersion = & $ffmpegPath -version 2>&1 | Select-Object -First 1
    Write-Host "[OK] FFmpeg trouve: $ffmpegVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERREUR] FFmpeg non trouve. Veuillez installer FFmpeg ou specifier le chemin avec -ffmpegPath" -ForegroundColor Red
    Write-Host "   Exemple: .\optimize_videos_robust.ps1 -ffmpegPath 'C:\ffmpeg\bin\ffmpeg.exe'" -ForegroundColor Yellow
    exit 1
}

# Creer le dossier de sortie
$fullOutputDir = Join-Path $PSScriptRoot "..\$outputDir"
if (-not (Test-Path $fullOutputDir)) {
    New-Item -ItemType Directory -Path $fullOutputDir -Force | Out-Null
    Write-Host "[OK] Dossier de sortie cree: $fullOutputDir" -ForegroundColor Green
}

# Creer le dossier de backup si necessaire
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
$failed = 0

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
        if ($existingSize -lt $video.Length -and $existingSize -gt 10000) {
            Write-Host "   [SKIP] Fichier optimise existe deja (plus petit)" -ForegroundColor Cyan
            $totalOptimizedSize += $existingSize
            $skipped++
            continue
        }
    }
    
    # OPTIMISATION 1: MP4 compresse avec parametres robustes
    Write-Host "   [TRAITEMENT] Compression MP4..." -ForegroundColor Cyan
    
    # Essayer d'abord avec les parametres standards
    # Note: Les videos sont en format QuickTime/MOV avec resolution elevee (1080x1920 ou 1440x1440)
    # IMPORTANT: Utiliser -2 pour garantir que les dimensions sont divisibles par 2 (requis par H.264)
    # Le -2 indique a FFmpeg de calculer automatiquement une dimension paire
    $scaleFilter = "scale=-2:720"  # Hauteur 720, largeur calculee automatiquement (paire)
    
    $mp4Args = @(
        "-i", "`"$inputFile`""
        "-c:v", "libx264"
        "-preset", "medium"          # Medium au lieu de slow pour etre plus rapide
        "-crf", "28"
        "-vf", $scaleFilter          # Reduire a 720p max (dimensions paires garanties)
        "-pix_fmt", "yuv420p"        # Format pixel compatible (necessaire pour certains lecteurs)
        "-c:a", "aac"
        "-b:a", "64k"
        "-movflags", "+faststart"    # Optimisation web
        "-avoid_negative_ts", "make_zero"  # Corriger les timestamps negatifs
        "-f", "mp4"                  # Forcer le format MP4 (pas QuickTime)
        "-y"
        "`"$outputFile`""
    )
    
    # Capturer les erreurs FFmpeg - Tentative 1
    $errorFile = Join-Path $env:TEMP "ffmpeg_error_$processed.txt"
    $mp4Process = Start-Process -FilePath $ffmpegPath -ArgumentList $mp4Args -Wait -NoNewWindow -PassThru -RedirectStandardError $errorFile
    
    # Si echec, essayer avec des parametres plus permissifs - Tentative 2
    if ($mp4Process.ExitCode -ne 0 -or -not (Test-Path $outputFile)) {
        Write-Host "   [TENTATIVE 2] Essai avec parametres alternatifs..." -ForegroundColor Yellow
        
        # Parametres alternatifs plus permissifs
        # Utiliser une resolution fixe divisible par 2
        $scaleFilterAlt = "scale=-2:720"  # Dimensions paires garanties
        
        $mp4ArgsAlt = @(
            "-i", "`"$inputFile`""
            "-c:v", "libx264"
            "-preset", "medium"
            "-crf", "28"
            "-vf", $scaleFilterAlt
            "-pix_fmt", "yuv420p"
            "-c:a", "copy"            # Copier l'audio au lieu de re-encoder (plus rapide)
            "-f", "mp4"               # Forcer le format MP4
            "-movflags", "+faststart"
            "-avoid_negative_ts", "make_zero"
            "-y"
            "`"$outputFile`""
        )
        
        Remove-Item $errorFile -ErrorAction SilentlyContinue
        $errorFile = Join-Path $env:TEMP "ffmpeg_error_alt_$processed.txt"
        $mp4Process = Start-Process -FilePath $ffmpegPath -ArgumentList $mp4ArgsAlt -Wait -NoNewWindow -PassThru -RedirectStandardError $errorFile
        
        # Si la deuxieme tentative echoue aussi, essayer une troisieme avec resolution fixe - Tentative 3
        if ($mp4Process.ExitCode -ne 0 -or -not (Test-Path $outputFile)) {
            Write-Host "   [TENTATIVE 3] Essai avec resolution fixe 1280x720..." -ForegroundColor Yellow
            
            $scaleFilterAlt2 = "scale=1280:-2"  # Largeur fixe 1280, hauteur calculee (paire)
            
            $mp4ArgsAlt2 = @(
                "-i", "`"$inputFile`""
                "-c:v", "libx264"
                "-preset", "fast"     # Plus rapide
                "-crf", "28"
                "-vf", $scaleFilterAlt2
                "-pix_fmt", "yuv420p"
                "-c:a", "aac"
                "-b:a", "64k"
                "-f", "mp4"
                "-movflags", "+faststart"
                "-y"
                "`"$outputFile`""
            )
            
            Remove-Item $errorFile -ErrorAction SilentlyContinue
            $errorFile = Join-Path $env:TEMP "ffmpeg_error_alt2_$processed.txt"
            $mp4Process = Start-Process -FilePath $ffmpegPath -ArgumentList $mp4ArgsAlt2 -Wait -NoNewWindow -PassThru -RedirectStandardError $errorFile
        }
    }
    
    # Verifier le resultat final (apres toutes les tentatives)
    if ($mp4Process.ExitCode -eq 0 -and (Test-Path $outputFile)) {
        $optimizedSize = (Get-Item $outputFile).Length
        
        # Verifier que le fichier n'est pas trop petit (probablement corrompu)
        if ($optimizedSize -lt 10000) {
            Write-Host "   [ERREUR] Fichier optimise trop petit ($([math]::Round($optimizedSize / 1KB, 2)) KB) - probablement corrompu" -ForegroundColor Red
            if (Test-Path $errorFile) {
                $errorContent = Get-Content $errorFile -Raw -ErrorAction SilentlyContinue
                if ($errorContent) {
                    $errorLines = $errorContent -split "`n" | Select-Object -Last 2
                    Write-Host "   Details: $($errorLines -join ' ')" -ForegroundColor Gray
                }
            }
            Remove-Item $outputFile -ErrorAction SilentlyContinue
            $failed++
            continue
        }
        
        $reduction = [math]::Round((1 - ($optimizedSize / $video.Length)) * 100, 1)
        
        # Avertir si la reduction est trop importante
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
                $errorLines = $errorContent -split "`n" | Select-Object -Last 3
                Write-Host "   Details: $($errorLines -join ' | ')" -ForegroundColor Gray
            }
            Remove-Item $errorFile -ErrorAction SilentlyContinue
        }
        $failed++
        continue
    }
    
    # Nettoyer le fichier d'erreur si tout s'est bien passe
    if (Test-Path $errorFile) {
        Remove-Item $errorFile -ErrorAction SilentlyContinue
    }
    
    # OPTIMISATION 2: WebM (VP9) - Format plus leger
    if ($createWebM) {
        Write-Host "   [TRAITEMENT] Conversion WebM..." -ForegroundColor Cyan
        
        # Conversion WebM avec meilleures pratiques
        # Utiliser -2 pour garantir des dimensions paires
        $webmScaleFilter = "scale=-2:720"  # Dimensions paires garanties
        
        $webmArgs = @(
            "-i", "`"$inputFile`""
            "-c:v", "libvpx-vp9"
            "-crf", "30"
            "-b:v", "0"
            "-vf", $webmScaleFilter
            "-pix_fmt", "yuv420p"
            "-c:a", "libopus"
            "-b:a", "64k"
            "-speed", "2"            # Vitesse d'encodage (0=plus lent/meilleur, 4=plus rapide)
            "-tile-columns", "2"     # Parallelisation pour acceleration
            "-threads", "0"          # Utiliser tous les threads disponibles
            "-avoid_negative_ts", "make_zero"
            "-y"
            "`"$webmFile`""
        )
        
        # Essayer avec row-mt si disponible, sinon sans
        $webmErrorFile = Join-Path $env:TEMP "ffmpeg_webm_error_$processed.txt"
        $webmProcess = Start-Process -FilePath $ffmpegPath -ArgumentList $webmArgs -Wait -NoNewWindow -PassThru -RedirectStandardError $webmErrorFile
        
        if ($webmProcess.ExitCode -eq 0 -and (Test-Path $webmFile)) {
            $webmSize = (Get-Item $webmFile).Length
            
            if ($webmSize -lt 10000) {
                Write-Host "   [ERREUR] Fichier WebM trop petit - ignore" -ForegroundColor Red
                Remove-Item $webmFile -ErrorAction SilentlyContinue
            } else {
                $webmReduction = [math]::Round((1 - ($webmSize / $video.Length)) * 100, 1)
                if ($webmReduction -gt 90) {
                    Write-Host "   [ATTENTION] Reduction tres importante (-$webmReduction%)" -ForegroundColor Yellow
                }
                Write-Host "   [OK] WebM cree: $([math]::Round($webmSize / 1MB, 2)) MB (-$webmReduction%)" -ForegroundColor Green
                $totalWebMSize += $webmSize
            }
        } else {
            Write-Host "   [ATTENTION] Erreur WebM (peut etre normal si VP9 non disponible)" -ForegroundColor Yellow
        }
        
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
Write-Host "Videos reussies: $($processed - $failed - $skipped)" -ForegroundColor Green
Write-Host "Videos ignorees (deja optimisees): $skipped" -ForegroundColor White
Write-Host "Videos echouees: $failed" -ForegroundColor $(if ($failed -gt 0) { "Red" } else { "Green" })
Write-Host ""
Write-Host "Taille totale originale: $([math]::Round($totalOriginalSize / 1MB, 2)) MB" -ForegroundColor White
Write-Host "Taille totale MP4 optimise: $([math]::Round($totalOptimizedSize / 1MB, 2)) MB" -ForegroundColor Green
if ($totalWebMSize -gt 0) {
    Write-Host "Taille totale WebM: $([math]::Round($totalWebMSize / 1MB, 2)) MB" -ForegroundColor Green
}
Write-Host ""
if ($totalOptimizedSize -gt 0) {
    $totalReduction = [math]::Round((1 - ($totalOptimizedSize / $totalOriginalSize)) * 100, 1)
    Write-Host "Reduction totale MP4: $totalReduction%" -ForegroundColor Green
}
if ($totalWebMSize -gt 0) {
    $webmReduction = [math]::Round((1 - ($totalWebMSize / $totalOriginalSize)) * 100, 1)
    Write-Host "Reduction totale WebM: $webmReduction%" -ForegroundColor Green
}
Write-Host ""
Write-Host "[OK] Optimisation terminee!" -ForegroundColor Green
Write-Host "Fichiers optimises dans: $fullOutputDir" -ForegroundColor Cyan

if ($failed -gt 0) {
    Write-Host ""
    Write-Host "[CONSEIL] Pour analyser les videos qui ont echoue, utilisez:" -ForegroundColor Yellow
    Write-Host "   .\analyze_videos.ps1 -ffmpegPath `"$ffmpegPath`"" -ForegroundColor Cyan
}
