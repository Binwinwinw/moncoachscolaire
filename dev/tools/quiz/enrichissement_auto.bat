@echo off
REM ============================================================================
REM Script de lancement rapide - Enrichissement automatique des quiz
REM MonCoachScolaire - v1.0 (09/03/2026)
REM ============================================================================

echo.
echo ========================================================================
echo   ENRICHISSEMENT AUTOMATIQUE DES QUIZ - MonCoachScolaire
echo ========================================================================
echo.

REM Vérification environnement Python
if not exist ".venv\Scripts\python.exe" (
    echo [ERREUR] Environnement virtuel Python .venv introuvable
    echo Veuillez executer : python -m venv .venv
    pause
    exit /b 1
)

REM Menu principal
:MENU
echo.
echo Choisissez une action :
echo.
echo [1] Detection placeholders (scan complet 1-1559)
echo [2] Detection placeholders (scan rapide 1-100)
echo [3] Enrichissement automatique CRITICAL (dry-run)
echo [4] Enrichissement automatique CRITICAL (production)
echo [5] Enrichissement automatique HIGH (dry-run)
echo [6] Enrichissement automatique HIGH (production)
echo [7] Enrichissement quiz specifique (saisie ID)
echo [8] Validation qualite pedagogique
echo [9] Voir rapport placeholders (JSON)
echo [0] Quitter
echo.
set /p choice="Votre choix (0-9) : "

if "%choice%"=="1" goto DETECT_FULL
if "%choice%"=="2" goto DETECT_QUICK
if "%choice%"=="3" goto ENRICH_CRITICAL_DRY
if "%choice%"=="4" goto ENRICH_CRITICAL_PROD
if "%choice%"=="5" goto ENRICH_HIGH_DRY
if "%choice%"=="6" goto ENRICH_HIGH_PROD
if "%choice%"=="7" goto ENRICH_SPECIFIC
if "%choice%"=="8" goto VALIDATE
if "%choice%"=="9" goto VIEW_REPORT
if "%choice%"=="0" goto END

echo [ERREUR] Choix invalide
goto MENU

REM ============================================================================
REM Actions
REM ============================================================================

:DETECT_FULL
echo.
echo [1] DETECTION COMPLETE (1-1559)...
echo.
.venv\Scripts\python.exe dev/tools/quiz/detect_quiz_placeholders.py ^
    --min-id 1 --max-id 1559 ^
    --output dev/reports/placeholders_detected.json
echo.
echo [OK] Rapport genere : dev/reports/placeholders_detected.json
pause
goto MENU

:DETECT_QUICK
echo.
echo [2] DETECTION RAPIDE (1-100)...
echo.
.venv\Scripts\python.exe dev/tools/quiz/detect_quiz_placeholders.py ^
    --min-id 1 --max-id 100 ^
    --output dev/reports/placeholders_detected.json
echo.
echo [OK] Rapport genere : dev/reports/placeholders_detected.json
pause
goto MENU

:ENRICH_CRITICAL_DRY
echo.
echo [3] ENRICHISSEMENT CRITICAL (DRY-RUN - TEST SANS MODIFICATION)...
echo.
if not exist "dev/reports/placeholders_detected.json" (
    echo [ERREUR] Rapport placeholders introuvable. Executez d'abord l'action [1] ou [2]
    pause
    goto MENU
)
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
    --from-placeholders dev/reports/placeholders_detected.json ^
    --severity critical ^
    --dry-run
echo.
echo [OK] Test termine - Aucune modification appliquee
pause
goto MENU

:ENRICH_CRITICAL_PROD
echo.
echo [4] ENRICHISSEMENT CRITICAL (PRODUCTION)...
echo.
echo /!\ ATTENTION : Cette action va MODIFIER les fichiers quiz et quiz_answers
echo     Un backup automatique sera cree dans dev/backups/quiz_enrichment/
echo.
set /p confirm="Confirmer ? (O/N) : "
if /i not "%confirm%"=="O" (
    echo Operation annulee
    pause
    goto MENU
)
if not exist "dev/reports/placeholders_detected.json" (
    echo [ERREUR] Rapport placeholders introuvable. Executez d'abord l'action [1] ou [2]
    pause
    goto MENU
)
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
    --from-placeholders dev/reports/placeholders_detected.json ^
    --severity critical
echo.
echo [OK] Enrichissement termine - Backups crees dans dev/backups/quiz_enrichment/
pause
goto MENU

:ENRICH_HIGH_DRY
echo.
echo [5] ENRICHISSEMENT HIGH (DRY-RUN - TEST SANS MODIFICATION)...
echo.
if not exist "dev/reports/placeholders_detected.json" (
    echo [ERREUR] Rapport placeholders introuvable. Executez d'abord l'action [1] ou [2]
    pause
    goto MENU
)
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
    --from-placeholders dev/reports/placeholders_detected.json ^
    --severity high ^
    --dry-run
echo.
echo [OK] Test termine - Aucune modification appliquee
pause
goto MENU

:ENRICH_HIGH_PROD
echo.
echo [6] ENRICHISSEMENT HIGH (PRODUCTION)...
echo.
echo /!\ ATTENTION : Cette action va MODIFIER les fichiers quiz et quiz_answers
echo     Un backup automatique sera cree dans dev/backups/quiz_enrichment/
echo.
set /p confirm="Confirmer ? (O/N) : "
if /i not "%confirm%"=="O" (
    echo Operation annulee
    pause
    goto MENU
)
if not exist "dev/reports/placeholders_detected.json" (
    echo [ERREUR] Rapport placeholders introuvable. Executez d'abord l'action [1] ou [2]
    pause
    goto MENU
)
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
    --from-placeholders dev/reports/placeholders_detected.json ^
    --severity high
echo.
echo [OK] Enrichissement termine - Backups crees dans dev/backups/quiz_enrichment/
pause
goto MENU

:ENRICH_SPECIFIC
echo.
echo [7] ENRICHISSEMENT QUIZ SPECIFIQUE...
echo.
set /p quiz_id="Saisir l'ID du quiz (ex: 42) : "
if "%quiz_id%"=="" (
    echo [ERREUR] ID invalide
    pause
    goto MENU
)
echo.
echo Mode :
echo   [1] Dry-run (test)
echo   [2] Production (modification reelle)
set /p mode="Votre choix (1-2) : "

if "%mode%"=="1" (
    .venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
        --quiz-id %quiz_id% ^
        --dry-run
) else if "%mode%"=="2" (
    echo.
    echo /!\ ATTENTION : Modification du quiz %quiz_id%
    set /p confirm="Confirmer ? (O/N) : "
    if /i "%confirm%"=="O" (
        .venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
            --quiz-id %quiz_id%
    ) else (
        echo Operation annulee
    )
) else (
    echo [ERREUR] Choix invalide
)
pause
goto MENU

:VALIDATE
echo.
echo [8] VALIDATION QUALITE PEDAGOGIQUE...
echo.
.venv\Scripts\python.exe dev/tools/quiz/validate_quiz_quality.py ^
    --quiz-dir src/data/quiz ^
    --answers-dir src/data/quiz_answers ^
    --report dev/reports/quiz_quality_report.md
echo.
echo [OK] Rapport genere : dev/reports/quiz_quality_report.md
pause
goto MENU

:VIEW_REPORT
echo.
echo [9] RAPPORT PLACEHOLDERS...
echo.
if not exist "dev/reports/placeholders_detected.json" (
    echo [ERREUR] Rapport introuvable. Executez d'abord l'action [1] ou [2]
    pause
    goto MENU
)
echo Ouverture du rapport JSON dans le navigateur par defaut...
start "" "dev/reports/placeholders_detected.json"
pause
goto MENU

:END
echo.
echo Au revoir !
echo.
exit /b 0
