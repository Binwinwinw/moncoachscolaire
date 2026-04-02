[CmdletBinding()]
param(
    [Parameter(Mandatory = $false)]
    [string]$RepoPath = (Get-Location).Path,

    [Parameter(Mandatory = $false)]
    [string]$SourcePromptPath = "",

    [Parameter(Mandatory = $false)]
    [string]$PromptFileName = "socle-qualite-stable.prompt.md"
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Resolve-DefaultSourcePrompt {
    param(
        [string]$PromptName
    )

    $candidates = @(
        # Prompt in this repository
        (Join-Path -Path $PSScriptRoot -ChildPath "..\..\..\.github\prompts\$PromptName"),
        # VS Code user prompts (Windows)
        (Join-Path -Path $env:APPDATA -ChildPath "Code\User\prompts\$PromptName"),
        # VS Code Insiders user prompts (Windows)
        (Join-Path -Path $env:APPDATA -ChildPath "Code - Insiders\User\prompts\$PromptName")
    )

    foreach ($path in $candidates) {
        if ([string]::IsNullOrWhiteSpace($path)) {
            continue
        }

        if (Test-Path -LiteralPath $path -PathType Leaf) {
            return (Resolve-Path -LiteralPath $path).Path
        }
    }

    return $null
}

$resolvedRepoPath = (Resolve-Path -LiteralPath $RepoPath).Path
if (-not (Test-Path -LiteralPath $resolvedRepoPath -PathType Container)) {
    throw "RepoPath introuvable: $RepoPath"
}

if ([string]::IsNullOrWhiteSpace($SourcePromptPath)) {
    $SourcePromptPath = Resolve-DefaultSourcePrompt -PromptName $PromptFileName
}

if ([string]::IsNullOrWhiteSpace($SourcePromptPath) -or -not (Test-Path -LiteralPath $SourcePromptPath -PathType Leaf)) {
    throw "SourcePromptPath introuvable. Fournis -SourcePromptPath ou place '$PromptFileName' dans .github/prompts ou %APPDATA%\Code\User\prompts."
}

$source = (Resolve-Path -LiteralPath $SourcePromptPath).Path
$targetDir = Join-Path -Path $resolvedRepoPath -ChildPath ".github\prompts"
$target = Join-Path -Path $targetDir -ChildPath $PromptFileName

if (-not (Test-Path -LiteralPath $targetDir -PathType Container)) {
    New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
    Write-Host "[OK] Dossier cree: $targetDir"
}

if ([string]::Equals($source, $target, [System.StringComparison]::OrdinalIgnoreCase)) {
    Write-Host "[OK] Prompt deja en place: $target"
    Write-Host "[SOURCE] $source"
    return
}

Copy-Item -LiteralPath $source -Destination $target -Force
Write-Host "[OK] Prompt synchronise: $target"
Write-Host "[SOURCE] $source"
