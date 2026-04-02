# scripts/teardown-db.ps1
# Usage: pwsh .\scripts\teardown-db.ps1 -RootUser root -RootPassword rootpwd -DbName moncoachscolaire -DbUser moncoach
param(
    [string]$RootUser = "root",
    [string]$RootPassword = "",
    [string]$DbName = "moncoachscolaire",
    [string]$DbUser = "moncoach",
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

$sql = @"
DROP DATABASE IF EXISTS `$DbName`;
DROP USER IF EXISTS '$DbUser'@'localhost';
"@

$tempFile = [System.IO.Path]::GetTempFileName()
Set-Content -Path $tempFile -Value $sql -Encoding UTF8

if ($RootPassword -eq "") {
    & $mysql -u $RootUser --password < $tempFile
} else {
    & $mysql -u $RootUser -p$RootPassword < $tempFile
}

Remove-Item $tempFile -ErrorAction SilentlyContinue
Write-Host "Teardown done: database dropped and user removed."