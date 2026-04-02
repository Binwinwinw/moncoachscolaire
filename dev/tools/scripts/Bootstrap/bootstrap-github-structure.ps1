<#
.SYNOPSIS
Bootstrap .github/ avec logique MERGE intelligente:
- Nouveau repo: création complète
- Repo existant: détection, fusion + préservation des fichiers perso
- Erreur: message explicite

SÉCURITÉ (CRITIQUE):
1. Détection complète avant modification
2. Backup sistématique des fichiers existants
3. Fusion intelligente (standards remplacés, perso préservés)
4. Aucune suppression silencieuse

.PARAMETER RootPath
Chemin parent. Default: .

.PARAMETER RepoPath
Chemin d'un repo spécifique.

.PARAMETER SourcePath
Chemin du .github/ source. Default: détection auto.

.PARAMETER DryRun
Affiche ce qui serait fait SANS modifier.

.PARAMETER Recurse
Cherche récursivement.

.EXAMPLE
./bootstrap-github-structure.ps1 -RootPath . -DryRun
./bootstrap-github-structure.ps1 -RootPath .
#>

param(
    [string]$RootPath = ".",
    [string]$RepoPath = "",
    [string]$SourcePath = "",
    [switch]$Recurse,
    [switch]$DryRun
)

function Resolve-SourcePath {
    param([string]$Root)
    foreach ($pattern in @("moncoachscolaire", "moncoachscolaire-V0")) {
        $path = Join-Path -Path $Root -ChildPath $pattern | Join-Path -ChildPath ".github"
        if (Test-Path -LiteralPath $path -PathType Container) {
            Write-Host "[SOURCE] $path" -ForegroundColor Cyan
            return $path
        }
    }
    Write-Host "[ERROR] .github/ non trouvé. Spécifiez -SourcePath." -ForegroundColor Red
    return $null
}

function Detect-StandardFiles {
    # Fichiers et dossiers STANDARDS qui doivent être synchronisés
    return @{
        Folders = @("instructions", "prompts", "workflows")
        Files   = @("copilot-instructions.md", "CONTEXT_PRODUIT.md", "REGLES_IA.md", "copilot-plan.md",
                   "PROJECT_CONTEXT.md", "API_GUIDE.md")
    }
}

function Detect-PersonalFiles {
    # Fichiers PERSONNALISÉS (journaux, logs) à préserver
    return @(
        "JOURNAL_*.md",
        "JOURNAL_WORK_*.md",
        "dev/JOURNAL*.md"
    )
}

function Get-FileComparison {
    param([string]$Source, [string]$Target)

    $standard = Detect-StandardFiles
    $missing = @()
    $existing = @()
    $personal = @()

    foreach ($folder in $standard.Folders) {
        $srcPath = Join-Path $Source $folder
        $tgtPath = Join-Path $Target $folder
        if (Test-Path -LiteralPath $srcPath) {
            if (-not (Test-Path -LiteralPath $tgtPath)) {
                $missing += "dossier: $folder/"
            } else {
                $existing += "dossier: $folder/ (existant)"
            }
        }
    }

    foreach ($file in $standard.Files) {
        $srcPath = Join-Path $Source $file
        $tgtPath = Join-Path $Target $file
        if (Test-Path -LiteralPath $srcPath) {
            if (-not (Test-Path -LiteralPath $tgtPath)) {
                $missing += "fichier: $file (nouveau)"
            } else {
                $existing += "fichier: $file (sera remplacé)"
            }
        }
    }

    # Détecter fichiers personnalisés existants
    $personalPatterns = Detect-PersonalFiles
    Get-ChildItem -LiteralPath $Target -Force -Recurse -ErrorAction SilentlyContinue |
        ForEach-Object {
            foreach ($pattern in $personalPatterns) {
                if ($_.Name -like $pattern) {
                    $personal += "fichier perso: $($_.Name)"
                }
            }
        }

    return @{ Missing = $missing; Existing = $existing; Personal = $personal }
}

function Merge-Structure {
    param([string]$Source, [string]$Target, [bool]$DoDryRun)

    $source = if ([System.IO.Path]::IsPathRooted($Source)) { $Source } else { Join-Path (Get-Location) $Source }
    $target = if ([System.IO.Path]::IsPathRooted($Target)) { $Target } else { Join-Path (Get-Location) $Target }

    if (-not (Test-Path -LiteralPath $source -PathType Container)) {
        Write-Host "  [ERROR] Source invalide: $source" -ForegroundColor Red
        return $false
    }

    $ghPath = Join-Path $target ".github"
    $exists = Test-Path -LiteralPath $ghPath -PathType Container

    # === DÉTECTION ===
    if ($exists) {
        Write-Host "  [DÉTECTION] Dossier .github/ existe" -ForegroundColor Yellow
        $comparison = Get-FileComparison -Source $source -Target $ghPath

        Write-Host "    À ajouter: $($comparison.Missing.Count) élément(s)" -ForegroundColor Cyan
        $comparison.Missing | ForEach-Object { Write-Host "      • $_" -ForegroundColor Gray }

        Write-Host "    À vérifier/remplacer: $($comparison.Existing.Count) élément(s)" -ForegroundColor Cyan
        $comparison.Existing | ForEach-Object { Write-Host "      • $_" -ForegroundColor Gray }

        if ($comparison.Personal.Count -gt 0) {
            Write-Host "    À PRÉSERVER: $($comparison.Personal.Count) fichier(s) personnel(s)" -ForegroundColor Green
            $comparison.Personal | ForEach-Object { Write-Host "      • $_" -ForegroundColor Gray }
        }
    } else {
        Write-Host "  [DÉTECTION] Nouveau dossier .github/ (création complète)" -ForegroundColor Green
    }

    if ($DoDryRun) {
        Write-Host "  [DRY-RUN] Aucune modification" -ForegroundColor Magenta
        return $true
    }

    # === FUSION ===
    try {
        if (-not $exists) {
            # Cas 1: Création complète
            Write-Host "  [1/2] Création .github/..." -ForegroundColor Cyan
            $null = New-Item -ItemType Directory -Path $ghPath -Force -ErrorAction Stop
            Write-Host "  [2/2] Copie complète..." -ForegroundColor Cyan
            Get-ChildItem -LiteralPath $source -Force |
                Copy-Item -Destination $ghPath -Recurse -Force -ErrorAction Stop
            Write-Host "  [OK] Création réussie: $ghPath" -ForegroundColor Green
        } else {
            # Cas 2: Fusion avec repo existant
            Write-Host "  [1/3] Sauvegarde..." -ForegroundColor Cyan
            $ghPathBackup = "$ghPath.backup-$(Get-Date -Format yyyyMMdd-HHmmss)"
            Copy-Item -LiteralPath $ghPath -Destination $ghPathBackup -Recurse -Force -ErrorAction Stop
            Write-Host "  [OK] Backup: $(Split-Path -Leaf $ghPathBackup)" -ForegroundColor Yellow

            Write-Host "  [2/3] Fusion intelligente..." -ForegroundColor Cyan
            $standard = Detect-StandardFiles

            # Copier/synchroniser dossiers standards
            foreach ($folder in $standard.Folders) {
                $srcPath = Join-Path $source $folder
                $tgtPath = Join-Path $ghPath $folder
                if (Test-Path -LiteralPath $srcPath) {
                    $null = New-Item -ItemType Directory -Path $tgtPath -Force -ErrorAction Stop
                    Get-ChildItem -LiteralPath $srcPath -Force |
                        Copy-Item -Destination $tgtPath -Recurse -Force -ErrorAction Stop
                    Write-Host "    ✓ $folder/ synchronisé" -ForegroundColor Green
                }
            }

            # Copier/remplacer fichiers standards
            foreach ($file in $standard.Files) {
                $srcPath = Join-Path $source $file
                $tgtPath = Join-Path $ghPath $file
                if (Test-Path -LiteralPath $srcPath) {
                    Copy-Item -LiteralPath $srcPath -Destination $tgtPath -Force -ErrorAction Stop
                    Write-Host "    ✓ $file (mis à jour)" -ForegroundColor Green
                }
            }

            Write-Host "  [3/3] Vérification..." -ForegroundColor Cyan
            # Vérifier que les éléments critiques existent
            $critical = @("instructions", "prompts", "CONTEXT_PRODUIT.md")
            foreach ($elem in $critical) {
                $checkPath = Join-Path $ghPath $elem
                if (-not (Test-Path -LiteralPath $checkPath)) {
                    Write-Host "  [ERROR] Élément critique manquant: $elem" -ForegroundColor Red
                    Write-Host "  [ROLLBACK] Restauration depuis backup..." -ForegroundColor Yellow
                    Remove-Item -LiteralPath $ghPath -Recurse -Force -ErrorAction SilentlyContinue
                    Rename-Item -LiteralPath $ghPathBackup -NewName (Split-Path -Leaf $ghPath) -ErrorAction SilentlyContinue
                    return $false
                }
            }
            Write-Host "  [OK] Fusion réussie: $ghPath" -ForegroundColor Green
        }
        return $true
    }
    catch {
        Write-Host "  [ERROR] Échec d'écriture: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Main
$src = if ($SourcePath) { $SourcePath } else { Resolve-SourcePath $RootPath }
if (-not $src) { exit 1 }

if ($RepoPath) {
    Write-Host "`n=== Repo Unique ===" -ForegroundColor Cyan
    Merge-Structure -Source $src -Target $RepoPath -DoDryRun $DryRun | Out-Null
} else {
    Write-Host "`n=== Multi-Repos ===" -ForegroundColor Cyan
    $args = @($RootPath); if ($Recurse) { $args += "-Recurse" }
    $repos = @(Get-ChildItem @args -ErrorAction SilentlyContinue |
        Where-Object { Test-Path -LiteralPath (Join-Path $_.FullName ".git") } |
        Select-Object -ExpandProperty FullName | Sort-Object -Unique)

    if ($repos.Count -eq 0) { Write-Host "[ERROR] Aucun repo`n" -ForegroundColor Red; exit 1 }

    Write-Host "[INFO] $($repos.Count) repos détectés`n"
    [int]$ok = 0
    foreach ($repo in $repos) {
        Write-Host "Processing: $(Split-Path -Leaf $repo)"
        if (Merge-Structure -Source $src -Target $repo -DoDryRun $DryRun) { $ok++ }
    }
    Write-Host "`n[SUMMARY] OK=$ok | Total=$($repos.Count)`n" -ForegroundColor Green
}
