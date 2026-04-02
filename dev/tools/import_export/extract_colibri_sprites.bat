@echo off
REM Script batch pour extraire les 5 poses du sprite colibricartoon.jpg
REM Nécessite ImageMagick installé (convert.exe)
REM Si ImageMagick n'est pas installé, utilisez l'alternative PowerShell ci-dessous

set SPRITE_FILE=assets\img\coach\colibricartoon.jpg
set OUTPUT_DIR=assets\img\coach\colibri-sprites

REM Créer le dossier de sortie
if not exist "%OUTPUT_DIR%" mkdir "%OUTPUT_DIR%"

REM Vérifier si ImageMagick est disponible
where convert >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo ImageMagick n'est pas installé. Utilisez extract_colibri_sprites.ps1 à la place.
    pause
    exit /b 1
)

echo Extraction des 5 poses du sprite Colibri...
echo.

REM Obtenir les dimensions de l'image
for /f "tokens=2 delims=: " %%a in ('identify -format "%%w:%%h" "%SPRITE_FILE%"') do set WIDTH=%%a
for /f "tokens=2 delims=: " %%a in ('identify -format "%%w:%%h" "%SPRITE_FILE%"') do set HEIGHT=%%b

set /a SPRITE_WIDTH=%WIDTH%/5

echo Dimensions du sprite: %WIDTH%x%HEIGHT%
echo Dimensions par pose: %SPRITE_WIDTH%x%HEIGHT%
echo.

REM Extraire chaque pose (5 poses côte à côte)
convert "%SPRITE_FILE%" -crop %SPRITE_WIDTH%x%HEIGHT%+0+0 "%OUTPUT_DIR%\colibri-neutre.png"
convert "%SPRITE_FILE%" -crop %SPRITE_WIDTH%x%HEIGHT%+%SPRITE_WIDTH%+0 "%OUTPUT_DIR%\colibri-heureux.png"
set /a X2=%SPRITE_WIDTH%*2
convert "%SPRITE_FILE%" -crop %SPRITE_WIDTH%x%HEIGHT%+%X2%+0 "%OUTPUT_DIR%\colibri-encourageant.png"
set /a X3=%SPRITE_WIDTH%*3
convert "%SPRITE_FILE%" -crop %SPRITE_WIDTH%x%HEIGHT%+%X3%+0 "%OUTPUT_DIR%\colibri-celebration.png"
set /a X4=%SPRITE_WIDTH%*4
convert "%SPRITE_FILE%" -crop %SPRITE_WIDTH%x%HEIGHT%+%X4%+0 "%OUTPUT_DIR%\colibri-reflexion.png"

echo.
echo Extraction terminée avec succes!
echo Fichiers crees dans: %OUTPUT_DIR%
pause

