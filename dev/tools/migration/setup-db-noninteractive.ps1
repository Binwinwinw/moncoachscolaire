# scripts/setup-db-noninteractive.ps1
# Non-interactive DB setup: create DB, create user, import schema.
# Usage: pwsh .\scripts\setup-db-noninteractive.ps1 -RootUser root -RootPassword rootpass -DbUser moncoach -DbPassword S3cr3tStr0ng! -DbName moncoachscolaire
param(
    [string]$RootUser = "root",
    [string]$RootPassword = "",
    [string]$DbName = "moncoachscolaire",
    [string]$DbUser = "moncoach",
    [string]$DbPassword = "S3cr3tStr0ng!",
    [switch]$WriteEnv,
    [string]$Environment = "local",
    [switch]$NoSeed,
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

$schemaFile = Join-Path -Path (Split-Path -Parent $PSScriptRoot) -ChildPath "db\mysql_schema.sql"
if ($NoSeed) {
    $schemaFile = Join-Path -Path (Split-Path -Parent $PSScriptRoot) -ChildPath "db\mysql_schema_noseed.sql"
}
$schemaFile = (Resolve-Path $schemaFile).Path

$sql = @"
CREATE DATABASE IF NOT EXISTS `$DbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DbUser'@'localhost' IDENTIFIED BY '$DbPassword';
GRANT ALL PRIVILEGES ON `$DbName`.* TO '$DbUser'@'localhost';
FLUSH PRIVILEGES;
"@

$tempFile = [System.IO.Path]::GetTempFileName()
Set-Content -Path $tempFile -Value $sql -Encoding UTF8

# Execute
    if ($RootPassword -eq "") {
        $cmd = "$mysql -u $RootUser < $tempFile"
        & cmd.exe /c $cmd
    } else {
        $cmd = "$mysql -u $RootUser -p$RootPassword < $tempFile"
        & cmd.exe /c $cmd
    }

# Import schema
    if ($RootPassword -eq "") {
        $cmd = "$mysql -u $RootUser < $schemaFile"
        & cmd.exe /c $cmd
    } else {
        $cmd = "$mysql -u $RootUser -p$RootPassword $DbName < $schemaFile"
        & cmd.exe /c $cmd
    }

Remove-Item $tempFile -ErrorAction SilentlyContinue
Write-Host "Non-interactive DB setup complete." 
if ($WriteEnv) {
    $projectRoot = (Split-Path -Parent $PSScriptRoot)
    $envFile = Join-Path -Path $projectRoot -ChildPath ".env"
    $envContent = @"
DB_HOST=127.0.0.1
DB_DATABASE=$DbName
DB_USERNAME=$DbUser
DB_PASSWORD=$DbPassword
    APP_ENV=$Environment
    DB_READ_ONLY=false
    # Keep .env out of version control — do not commit
"@
    Set-Content -Path $envFile -Value $envContent -Encoding UTF8 -NoNewline
    Write-Host "Wrote .env to $envFile" -ForegroundColor Green
}