# scripts/setup-db.ps1
# Usage: pwsh .\scripts\setup-db.ps1
# This script creates the database (if not exists), imports the MySQL schema and creates a user.
# It will try to detect mysql.exe in common XAMPP paths. If not found, add mysql to your PATH.

param(
    [string]$DbName = "moncoachscolaire",
    [string]$DbUser = "moncoach",
    [string]$DbPassword = "S3cr3tStr0ng!",
    [switch]$SetEnv,
    [switch]$WriteEnv,
    [string]$Environment = "local",
    [switch]$NoSeed,
    [string]$MysqlPath
)

function Find-MySQLClient {
    param([string]$custom)
    if ($custom) {
        if (Test-Path $custom) { return $custom }
    }
    $candidates = @("C:\\xampp\\mysql\\bin\\mysql.exe", "C:\\Program Files\\xampp\\mysql\\bin\\mysql.exe", "C:\\Program Files (x86)\\xampp\\mysql\\bin\\mysql.exe", "mysql.exe")
    foreach ($p in $candidates) {
        try {
            if (Test-Path $p) { return $p }
            else {
                # if it's just 'mysql.exe' check PATH
                if ($p -eq 'mysql.exe') {
                    $which = (Get-Command mysql.exe -ErrorAction SilentlyContinue)
                    if ($which) { return $which.Path }
                }
            }
        } catch { }
    }
    return $null
}

Write-Host "== MonCoachScolaire DB setup script =="
$mysql = Find-MySQLClient -custom $MysqlPath
if (-not $mysql) {
    Write-Error "mysql client (mysql.exe) not found. Install XAMPP or add mysql.exe to PATH, or pass -MysqlPath 'C:\x\mysql.exe'"
    exit 1
}
Write-Host "Using mysql client: $mysql"

# Ask for root user and password, interactive
$rootUser = Read-Host -Prompt "MySQL admin user" -Default "root"
$rootPwd = Read-Host -Prompt "MySQL admin password (leave empty to prompt)" -AsSecureString
if ($rootPwd.Length -eq 0) { Write-Host "root password empty: mysql will prompt for password (if required)" }

$schemaFile = Join-Path -Path (Split-Path -Parent $PSScriptRoot) -ChildPath "..\db\mysql_schema.sql"
if ($NoSeed) {
    $schemaFile = Join-Path -Path (Split-Path -Parent $PSScriptRoot) -ChildPath "..\db\mysql_schema_noseed.sql"
}
$schemaFile = (Resolve-Path $schemaFile).Path
Write-Host "Schema file: $schemaFile"

# Create DB & user
$createStmt = @"
CREATE DATABASE IF NOT EXISTS `$DbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DbUser'@'localhost' IDENTIFIED BY '$DbPassword';
GRANT ALL PRIVILEGES ON `$DbName`.* TO '$DbUser'@'localhost';
FLUSH PRIVILEGES;
"@

# Write file with statements to avoid quoting headaches
$tempSql = [System.IO.Path]::GetTempFileName()
Set-Content -Path $tempSql -Value $createStmt -Encoding UTF8

try {
    # Run create DB & user
    if ($rootPwd.Length -eq 0) {
        # Use cmd.exe to support input redirection in PowerShell
        $cmd = "$mysql -u $rootUser < $tempSql"
        & cmd.exe /c $cmd
    } else {
        $pwdPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($rootPwd))
        $cmd = "$mysql -u $rootUser -p$pwdPlain < $tempSql"
        & cmd.exe /c $cmd
    }
    Write-Host "Database/access configured. Importing schema..."

    # Import schema into database
    if ($rootPwd.Length -eq 0) {
        $cmd = "$mysql -u $rootUser < $schemaFile"
        & cmd.exe /c $cmd
    } else {
        $pwdPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($rootPwd))
        $cmd = "$mysql -u $rootUser -p$pwdPlain $DbName < $schemaFile"
        & cmd.exe /c $cmd
    }
    Write-Host "Schema import complete."

    if ($SetEnv) {
        Write-Host "Setting persistent environment variables (setx) for DB access..."
        setx DB_USERNAME $DbUser | Out-Null
        setx DB_PASSWORD $DbPassword | Out-Null
        setx DB_DATABASE $DbName | Out-Null
        setx DB_HOST "127.0.0.1" | Out-Null
        setx APP_ENV "local" | Out-Null
        setx DB_READ_ONLY "false" | Out-Null
        Write-Host "Environment variables written. You may need to restart your terminal / Apache/PHP service to see them." 
    }

    if ($WriteEnv) {
        # Write .env file in project root
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

    Write-Host "Now you can run: php .\\db\\test_connection.php to verify the connection (or check with phpMyAdmin)."
} catch {
    Write-Error "Error during DB setup: $_"
} finally {
    Remove-Item -Path $tempSql -ErrorAction SilentlyContinue
}
