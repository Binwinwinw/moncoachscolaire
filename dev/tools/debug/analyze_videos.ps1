# Script pour analyser les proprietes des videos MP4
# Usage: .\analyze_videos.ps1

param(
    [string]$ffmpegPath = "ffmpeg",
    [string]$inputDir = "assets\img\coach"
)

Write-Host "Analyse des videos MP4" -ForegroundColor Cyan
Write-Host "=======================" -ForegroundColor Cyan
Write-Host ""

# Verifier FFmpeg
try {
    $ffmpegVersion = & $ffmpegPath -version 2>&1 | Select-Object -First 1
    Write-Host "[OK] FFmpeg: $ffmpegVersion" -ForegroundColor Green
} catch {
    Write-Host "[ERREUR] FFmpeg non trouve" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Trouver tous les fichiers MP4
$inputPath = Join-Path $PSScriptRoot "..\$inputDir"
$videoFiles = Get-ChildItem -Path $inputPath -Filter "*.mp4" -File

if ($videoFiles.Count -eq 0) {
    Write-Host "[ATTENTION] Aucun fichier MP4 trouve" -ForegroundColor Yellow
    exit 0
}

Write-Host "$($videoFiles.Count) video(s) trouvee(s)" -ForegroundColor Cyan
Write-Host ""

foreach ($video in $videoFiles) {
    Write-Host "========================================" -ForegroundColor Yellow
    Write-Host "Video: $($video.Name)" -ForegroundColor Yellow
    Write-Host "Taille: $([math]::Round($video.Length / 1MB, 2)) MB" -ForegroundColor White
    Write-Host ""
    
    # Utiliser ffprobe pour analyser la video
    $ffprobePath = $ffmpegPath.Replace("ffmpeg.exe", "ffprobe.exe")
    
    $probeArgs = @(
        "-v", "error"
        "-select_streams", "v:0"
        "-show_entries", "stream=codec_name,codec_long_name,width,height,r_frame_rate,bit_rate"
        "-show_entries", "format=format_name,format_long_name,duration,bit_rate"
        "-of", "json"
        $video.FullName
    )
    
    # Appeler ffprobe avec les arguments correctement passes
    # Utiliser Start-Process pour mieux controler la sortie
    $tempJsonFile = Join-Path $env:TEMP "ffprobe_$($video.BaseName).json"
    
    try {
        $process = Start-Process -FilePath $ffprobePath -ArgumentList $probeArgs -Wait -NoNewWindow -PassThru -RedirectStandardOutput $tempJsonFile -RedirectStandardError "nul"
        
        if ($process.ExitCode -eq 0 -and (Test-Path $tempJsonFile)) {
            try {
                # Lire le fichier JSON
                $jsonContent = Get-Content $tempJsonFile -Raw -Encoding UTF8
                $jsonContent = $jsonContent.Trim()
                
                if ([string]::IsNullOrWhiteSpace($jsonContent)) {
                    Write-Host "[ATTENTION] Fichier JSON vide" -ForegroundColor Yellow
                    Write-Host ""
                    continue
                }
                
                # Parser le JSON
                $probeJson = $jsonContent | ConvertFrom-Json
            
            if ($probeJson.streams -and $probeJson.streams.Count -gt 0) {
                $stream = $probeJson.streams[0]
                Write-Host "Codec video: $($stream.codec_name) ($($stream.codec_long_name))" -ForegroundColor Green
                Write-Host "Resolution: $($stream.width)x$($stream.height)" -ForegroundColor Green
                Write-Host "Frame rate: $($stream.r_frame_rate)" -ForegroundColor Green
                if ($stream.bit_rate) {
                    Write-Host "Bitrate video: $([math]::Round([int]$stream.bit_rate / 1000, 0)) kbps" -ForegroundColor Green
                }
            }
            
            if ($probeJson.format) {
                $format = $probeJson.format
                Write-Host "Format: $($format.format_name) ($($format.format_long_name))" -ForegroundColor Green
                if ($format.duration) {
                    Write-Host "Duree: $([math]::Round([double]$format.duration, 1)) secondes" -ForegroundColor Green
                }
                if ($format.bit_rate) {
                    Write-Host "Bitrate total: $([math]::Round([int]$format.bit_rate / 1000, 0)) kbps" -ForegroundColor Green
                }
            }
            } catch {
                Write-Host "[ERREUR] Impossible de parser le JSON: $_" -ForegroundColor Red
                Write-Host "Contenu JSON (200 premiers caracteres): $($jsonContent.Substring(0, [Math]::Min(200, $jsonContent.Length)))" -ForegroundColor Gray
            } finally {
                # Nettoyer le fichier temporaire
                if (Test-Path $tempJsonFile) {
                    Remove-Item $tempJsonFile -ErrorAction SilentlyContinue
                }
            }
        } else {
            Write-Host "[ERREUR] ffprobe a echoue (Code: $($process.ExitCode))" -ForegroundColor Red
        }
    } catch {
        Write-Host "[ERREUR] Erreur lors de l'appel a ffprobe: $_" -ForegroundColor Red
    }
    
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Analyse terminee" -ForegroundColor Cyan
