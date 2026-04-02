#!/bin/bash
# Script Bash pour optimiser les vidéos MP4 (Linux/Mac)
# Usage: ./optimize_videos.sh

set -e

FFMPEG_PATH="${FFMPEG_PATH:-ffmpeg}"  # Utiliser FFMPEG_PATH si défini, sinon "ffmpeg"
INPUT_DIR="assets/img/coach"
OUTPUT_DIR="assets/img/coach/optimized"
CREATE_WEBM=true
BACKUP_ORIGINAL=true

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

echo "🎬 Optimisation des vidéos MP4"
echo "================================"
echo ""

# Vérifier que FFmpeg est disponible
if ! command -v "$FFMPEG_PATH" &> /dev/null; then
    echo "❌ FFmpeg non trouvé. Veuillez installer FFmpeg:"
    echo "   Ubuntu/Debian: sudo apt-get install ffmpeg"
    echo "   Mac: brew install ffmpeg"
    exit 1
fi

FFMPEG_VERSION=$("$FFMPEG_PATH" -version | head -n 1)
echo "✅ FFmpeg trouvé: $FFMPEG_VERSION"
echo ""

# Créer le dossier de sortie
FULL_OUTPUT_DIR="$PROJECT_ROOT/$OUTPUT_DIR"
mkdir -p "$FULL_OUTPUT_DIR"
echo "📁 Dossier de sortie: $FULL_OUTPUT_DIR"

# Créer le dossier de backup si nécessaire
if [ "$BACKUP_ORIGINAL" = true ]; then
    BACKUP_DIR="$PROJECT_ROOT/backups/videos_original"
    mkdir -p "$BACKUP_DIR"
fi

# Trouver tous les fichiers MP4
INPUT_PATH="$PROJECT_ROOT/$INPUT_DIR"
VIDEO_FILES=("$INPUT_PATH"/*.mp4)

if [ ${#VIDEO_FILES[@]} -eq 0 ] || [ ! -f "${VIDEO_FILES[0]}" ]; then
    echo "⚠️  Aucun fichier MP4 trouvé dans $INPUT_PATH"
    exit 0
fi

echo "📹 ${#VIDEO_FILES[@]} vidéo(s) trouvée(s)"
echo ""

TOTAL_ORIGINAL_SIZE=0
TOTAL_OPTIMIZED_SIZE=0
TOTAL_WEBM_SIZE=0
PROCESSED=0
SKIPPED=0

for video in "${VIDEO_FILES[@]}"; do
    PROCESSED=$((PROCESSED + 1))
    VIDEO_NAME=$(basename "$video")
    OUTPUT_FILE="$FULL_OUTPUT_DIR/$VIDEO_NAME"
    WEBM_FILE="$FULL_OUTPUT_DIR/${VIDEO_NAME%.mp4}.webm"
    
    echo "[$PROCESSED/${#VIDEO_FILES[@]}] Traitement de: $VIDEO_NAME"
    
    ORIGINAL_SIZE=$(stat -f%z "$video" 2>/dev/null || stat -c%s "$video" 2>/dev/null)
    ORIGINAL_SIZE_MB=$(echo "scale=2; $ORIGINAL_SIZE / 1048576" | bc)
    echo "   Taille originale: ${ORIGINAL_SIZE_MB} MB"
    
    TOTAL_ORIGINAL_SIZE=$((TOTAL_ORIGINAL_SIZE + ORIGINAL_SIZE))
    
    # Backup de l'original si demandé
    if [ "$BACKUP_ORIGINAL" = true ]; then
        BACKUP_FILE="$BACKUP_DIR/$VIDEO_NAME"
        if [ ! -f "$BACKUP_FILE" ]; then
            cp "$video" "$BACKUP_FILE"
            echo "   💾 Backup créé"
        fi
    fi
    
    # Vérifier si le fichier optimisé existe déjà
    if [ -f "$OUTPUT_FILE" ]; then
        EXISTING_SIZE=$(stat -f%z "$OUTPUT_FILE" 2>/dev/null || stat -c%s "$OUTPUT_FILE" 2>/dev/null)
        if [ "$EXISTING_SIZE" -lt "$ORIGINAL_SIZE" ]; then
            echo "   ⏭️  Fichier optimisé existe déjà (plus petit)"
            TOTAL_OPTIMIZED_SIZE=$((TOTAL_OPTIMIZED_SIZE + EXISTING_SIZE))
            SKIPPED=$((SKIPPED + 1))
            continue
        fi
    fi
    
    # OPTIMISATION 1: MP4 compressé
    echo "   🔄 Compression MP4..."
    
    "$FFMPEG_PATH" -i "$video" \
        -c:v libx264 \
        -preset slow \
        -crf 28 \
        -vf "scale='min(720,iw)':'min(720,ih)':force_original_aspect_ratio=decrease" \
        -c:a aac \
        -b:a 64k \
        -movflags +faststart \
        -y \
        "$OUTPUT_FILE" 2>/dev/null
    
    if [ -f "$OUTPUT_FILE" ]; then
        OPTIMIZED_SIZE=$(stat -f%z "$OUTPUT_FILE" 2>/dev/null || stat -c%s "$OUTPUT_FILE" 2>/dev/null)
        OPTIMIZED_SIZE_MB=$(echo "scale=2; $OPTIMIZED_SIZE / 1048576" | bc)
        REDUCTION=$(echo "scale=1; (1 - $OPTIMIZED_SIZE / $ORIGINAL_SIZE) * 100" | bc)
        echo "   ✅ MP4 optimisé: ${OPTIMIZED_SIZE_MB} MB (-${REDUCTION}%)"
        TOTAL_OPTIMIZED_SIZE=$((TOTAL_OPTIMIZED_SIZE + OPTIMIZED_SIZE))
    else
        echo "   ❌ Erreur lors de la compression MP4"
        continue
    fi
    
    # OPTIMISATION 2: WebM (VP9)
    if [ "$CREATE_WEBM" = true ]; then
        echo "   🔄 Conversion WebM..."
        
        "$FFMPEG_PATH" -i "$video" \
            -c:v libvpx-vp9 \
            -crf 30 \
            -b:v 0 \
            -vf "scale='min(720,iw)':'min(720,ih)':force_original_aspect_ratio=decrease" \
            -c:a libopus \
            -b:a 64k \
            -row-mt 1 \
            -y \
            "$WEBM_FILE" 2>/dev/null || echo "   ⚠️  Erreur WebM (peut être normal si VP9 non disponible)"
        
        if [ -f "$WEBM_FILE" ]; then
            WEBM_SIZE=$(stat -f%z "$WEBM_FILE" 2>/dev/null || stat -c%s "$WEBM_FILE" 2>/dev/null)
            WEBM_SIZE_MB=$(echo "scale=2; $WEBM_SIZE / 1048576" | bc)
            WEBM_REDUCTION=$(echo "scale=1; (1 - $WEBM_SIZE / $ORIGINAL_SIZE) * 100" | bc)
            echo "   ✅ WebM créé: ${WEBM_SIZE_MB} MB (-${WEBM_REDUCTION}%)"
            TOTAL_WEBM_SIZE=$((TOTAL_WEBM_SIZE + WEBM_SIZE))
        fi
    fi
    
    echo ""
done

# Résumé
echo "📊 Résumé de l'optimisation"
echo "==========================="
echo "Vidéos traitées: $PROCESSED"
echo "Vidéos ignorées: $SKIPPED"
echo ""

TOTAL_ORIGINAL_MB=$(echo "scale=2; $TOTAL_ORIGINAL_SIZE / 1048576" | bc)
TOTAL_OPTIMIZED_MB=$(echo "scale=2; $TOTAL_OPTIMIZED_SIZE / 1048576" | bc)
echo "Taille totale originale: ${TOTAL_ORIGINAL_MB} MB"
echo "Taille totale MP4 optimisé: ${TOTAL_OPTIMIZED_MB} MB"

if [ "$TOTAL_WEBM_SIZE" -gt 0 ]; then
    TOTAL_WEBM_MB=$(echo "scale=2; $TOTAL_WEBM_SIZE / 1048576" | bc)
    echo "Taille totale WebM: ${TOTAL_WEBM_MB} MB"
fi

echo ""
TOTAL_REDUCTION=$(echo "scale=1; (1 - $TOTAL_OPTIMIZED_SIZE / $TOTAL_ORIGINAL_SIZE) * 100" | bc)
echo "💰 Réduction totale MP4: ${TOTAL_REDUCTION}%"

if [ "$TOTAL_WEBM_SIZE" -gt 0 ]; then
    WEBM_REDUCTION=$(echo "scale=1; (1 - $TOTAL_WEBM_SIZE / $TOTAL_ORIGINAL_SIZE) * 100" | bc)
    echo "💰 Réduction totale WebM: ${WEBM_REDUCTION}%"
fi

echo ""
echo "✅ Optimisation terminée!"
echo "📁 Fichiers optimisés dans: $FULL_OUTPUT_DIR"
