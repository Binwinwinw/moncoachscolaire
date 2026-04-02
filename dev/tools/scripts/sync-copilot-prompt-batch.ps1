[CmdletBinding()]
param(
    [Parameter(Mandatory = $false)]
    [string]$RootPath = (Get-Location).Path,

    [Parameter(Mandatory = $false)]
    [string]$SourcePromptPath = "",

    [Parameter(Mandatory = $false)]
    [string]$PromptFileName = "socle-qualite-stable.prompt.md",

    [Parameter(Mandatory = $false)]
    [switch]$Recurse,

    [Parameter(Mandatory = $false)]
    [switch]$DryRun
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$scriptPath = Join-Path -Path $PSScriptRoot -ChildPath "sync-copilot-prompt.ps1"
if (-not (Test-Path -LiteralPath $scriptPath -PathType Leaf)) {
    throw "Script introuvable: $scriptPath"
}

$resolvedRoot = (Resolve-Path -LiteralPath $RootPath).Path
if (-not (Test-Path -LiteralPath $resolvedRoot -PathType Container)) {
    throw "RootPath introuvable: $RootPath"
}

# Repositories candidates = root itself (if git repo) + child directories containing .git.
$repoPaths = New-Object System.Collections.Generic.List[string]
if (Test-Path -LiteralPath (Join-Path $resolvedRoot ".git")) {
    $repoPaths.Add($resolvedRoot)
}

$searchParams = @{
    LiteralPath = $resolvedRoot
    Directory = $true
    ErrorAction = "SilentlyContinue"
}

if ($Recurse) {
    $searchParams["Recurse"] = $true
}

$dirs = Get-ChildItem @searchParams
foreach ($dir in $dirs) {
    $gitDir = Join-Path -Path $dir.FullName -ChildPath ".git"
    if (Test-Path -LiteralPath $gitDir) {
        $repoPaths.Add($dir.FullName)
    }
}

# Distinct + stable ordering.
$repoPaths = @($repoPaths | Sort-Object -Unique)
if ($repoPaths.Count -eq 0) {
    Write-Host "[INFO] Aucun repo Git detecte sous: $resolvedRoot"
    return
}

Write-Host "[INFO] Repos detectes: $($repoPaths.Count)"

$ok = 0
$fail = 0

foreach ($repo in $repoPaths) {
    try {
        if ($DryRun) {
            Write-Host "[DRY-RUN] Repo cible: $repo"
            continue
        }

        $args = @{
            RepoPath = $repo
            PromptFileName = $PromptFileName
        }

        if (-not [string]::IsNullOrWhiteSpace($SourcePromptPath)) {
            $args["SourcePromptPath"] = $SourcePromptPath
        }

        & $scriptPath @args | Out-Host
        $ok++
    }
    catch {
        $fail++
        Write-Host "[ERROR] $repo -> $($_.Exception.Message)"
    }
}

Write-Host "[SUMMARY] OK=$ok | FAIL=$fail"
