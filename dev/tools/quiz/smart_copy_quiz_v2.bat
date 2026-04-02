@echo off
setlocal enabledelayedexpansion

echo 🔍 SMART COPY v2 : UNIQUEMENT MANQUANTS
echo ======================================

if not exist "dev\tools\quiz\college\" (
    echo ❌ Dossier dev/tools/quiz/college introuvable
    pause
    exit /b 1
)

mkdir public\quiz 2>nul

set count=0
for %%f in ("dev\tools\quiz\college\*.json") do (
    set "devfile=dev\tools\quiz\college\%%f"
    set "filename=%%~nxf"
    set "pubfile=public\quiz\!filename!"

    if not exist "!pubfile!" (
        copy "!devfile!" "!pubfile!"
        echo ✅ COPIED: !filename!
        set /a count+=1
    ) else (
        echo ⏭️  EXISTS: !filename!
    )
)

echo.
echo 🎉 TOTAL NOUVEAUX: %count%
for /f %%i in ('dir /b "public\quiz\*.json" 2^>nul ^| find /c ".json"') do echo 📦 public/quiz: %%i JSON
echo ✅ TERMINÉ !
pause
