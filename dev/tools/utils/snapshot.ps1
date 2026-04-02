param(
    [Parameter(Mandatory=$false)][string[]]$Paths,
    [switch]$All,
    [int]$Keep = 0,
    [int]$PruneDays = 0,
    [switch]$Compress,
    [string]$Exclude
)

$root = Split-Path -Path $PSScriptRoot -Parent
$backups = Join-Path $root 'backups'
if (-not (Test-Path $backups)) { New-Item -Path $backups -ItemType Directory | Out-Null }

$timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'

# Helper functions (ensure available before any calls)
function Remove-Path {
    param([string]$Path)
    if (Test-Path $Path) {
        if ((Get-Item $Path).PSIsContainer) { Remove-Item -Recurse -Force -Path $Path } else { Remove-Item -Force -Path $Path }
    }
}

function Compress-Snapshot {
    param(
        [string]$Backups,
        [string]$Timestamp
    )
    $dir = Join-Path $Backups $Timestamp
    if (-not (Test-Path $dir)) { Write-Host "Compress: directory not found: $dir"; return }
    $zip = Join-Path $Backups "$Timestamp.zip"
    try {
        Compress-Archive -Path (Join-Path $dir '*') -DestinationPath $zip -Force -CompressionLevel Optimal
        # delete original folder after compression
        Remove-Path -Path $dir
        Write-Host "Compressed snapshot -> backups/$Timestamp.zip (original removed)"
    } catch {
        Write-Host "Compression failed: $_"
    }
}

function Prune-Snapshots {
    param(
        [string]$Backups,
        [int]$Keep = 0,
        [int]$PruneDays = 0
    )
    $entries = Get-ChildItem -Path $Backups -Force | Where-Object { $_.Name -ne 'store' -and $_.Name -ne '.' -and $_.Name -ne '..' }
    # filter by pattern YYYYMMDD_HHMMSS or .zip
    $snapshots = @()
    foreach ($e in $entries) {
        if ($e.Name -match '^[0-9]{8}_[0-9]{6}($|\.zip$)') { $snapshots += $e }
    }
    $snapshots = $snapshots | Sort-Object LastWriteTime -Descending
    $removed = 0
    if ($Keep -gt 0 -and $snapshots.Count -gt $Keep) {
        $toRemove = $snapshots[$Keep..($snapshots.Count - 1)]
        foreach ($t in $toRemove) { Remove-Path -Path $t.FullName; $removed++ }
    }
    if ($PruneDays -gt 0) {
        $threshold = (Get-Date).AddDays(-$PruneDays)
        foreach ($s in $snapshots) {
            if ($s.LastWriteTime -lt $threshold) { Remove-Path -Path $s.FullName; $removed++ }
        }
    }
    if ($removed -gt 0) { Write-Host "Pruned $removed older snapshots from backups/" }
}

function Copy-WithDirs($source) {
    $srcFull = Resolve-Path -LiteralPath $source -ErrorAction SilentlyContinue
    if (-not $srcFull) { Write-Host "Not found: $source"; return }
    $srcFull = $srcFull.Path
    $rel = $srcFull -replace [regex]::Escape($root),''
    $destDir = Join-Path (Join-Path $backups $timestamp) (Split-Path $rel -Parent)
    if (-not (Test-Path $destDir)) { New-Item -Path $destDir -ItemType Directory -Force | Out-Null }
    Copy-Item -Path $srcFull -Destination (Join-Path $destDir (Split-Path $rel -Leaf)) -Force
    Write-Host "Saved: $rel -> backups/$timestamp/" (Split-Path $rel -Parent) "/" (Split-Path $rel -Leaf)
}

if ($All) {
    $excludes = @('backups','vendor','node_modules','.git')
    if ($Exclude) {
        $more = $Exclude -split ',' | ForEach-Object { $_.Trim() } | Where-Object { $_ -ne '' }
        $excludes += $more
    }
    Get-ChildItem -Path $root -Recurse -File -Force | Where-Object {
        $path = $_.FullName
        foreach ($ex in $excludes) { if ($path -match "\\$ex\\") { return $false } }
        return $true
    } | ForEach-Object { Copy-WithDirs $_.FullName }
    Write-Host "Snapshot complete -> backups/$timestamp/"
    if ($Compress) { Compress-Snapshot -Backups $backups -Timestamp $timestamp }
    if ($Keep -gt 0 -or $PruneDays -gt 0) { Prune-Snapshots -Backups $backups -Keep $Keep -PruneDays $PruneDays }
    exit 0
}

if (-not $Paths) {
    Write-Host "Usage: .\snapshot.ps1 -Paths landingpage.php,style.css  OR .\snapshot.ps1 -All";
    exit 1
}

foreach ($p in $Paths) { Copy-WithDirs $p }
Write-Host "Snapshot complete -> backups/$timestamp/"
if ($Compress) { Compress-Snapshot -Backups $backups -Timestamp $timestamp }
if ($Keep -gt 0 -or $PruneDays -gt 0) { Prune-Snapshots -Backups $backups -Keep $Keep -PruneDays $PruneDays }

<# helper functions moved above - no-op here #>
