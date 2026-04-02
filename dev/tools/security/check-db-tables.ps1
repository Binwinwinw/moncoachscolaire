# Vérifie les tables présentes dans la base MySQL et affiche celles qui manquent
# Usage : pwsh .\scripts\check-db-tables.ps1

param(
    [string]$DbName = "moncoachscolaire",
    [string]$DbUser = "moncoach",
    [string]$DbPassword = "S3cr3tStr0ng!",
    [string]$MysqlPath
)

function Find-MySQLClient {
    param([string]$custom)
    if ($custom) { if (Test-Path $custom) { return $custom } }
    $candidates = @("C:\\xampp\\mysql\\bin\\mysql.exe", "mysql.exe")
    foreach ($p in $candidates) {
        try { if (Test-Path $p) { return $p } } catch {}
        if ($p -eq 'mysql.exe') { $which = (Get-Command mysql.exe -ErrorAction SilentlyContinue); if ($which) { return $which.Path } }
    }
    return $null
}
$mysql = Find-MySQLClient -custom $MysqlPath
if (-not $mysql) { Write-Error "mysql client not found"; exit 1 }

$expectedTables = @(
    "Users",
    "UserProgress",
    "Powers",
    "UserPowers",
    "Achievements",
    "UserAchievements",
    "Exercises",
    "ExerciseResponses"
)

# Génère la requête SQL pour lister les tables
$sql = "SHOW TABLES IN `$DbName`;"
$tempFile = [System.IO.Path]::GetTempFileName()
Set-Content -Path $tempFile -Value $sql -Encoding UTF8

# Exécute la requête et récupère la sortie
$cmd = "$mysql -u $DbUser -p$DbPassword $DbName < $tempFile"
$output = & cmd.exe /c $cmd

# Parse la sortie pour obtenir la liste réelle
$foundTables = @()
foreach ($line in $output) {
    if ($line -match "^\|?\s*([A-Za-z0-9_]+)\s*\|?") {
        $tbl = $Matches[1]
        if ($tbl -ne "Tables_in_$DbName") { $foundTables += $tbl }
    }
}

Write-Host "Tables attendues : $($expectedTables -join ", ")"
Write-Host "Tables trouvées  : $($foundTables -join ", ")"

$missing = $expectedTables | Where-Object { $_ -notin $foundTables }
if ($missing.Count -eq 0) {
    Write-Host "✅ Toutes les tables attendues sont présentes."
} else {
    Write-Host "❌ Tables manquantes : $($missing -join ", ")"
}

Remove-Item $tempFile -Force
