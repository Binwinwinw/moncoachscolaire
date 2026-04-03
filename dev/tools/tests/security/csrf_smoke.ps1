$ErrorActionPreference = 'Stop'

$baseUrl = 'http://localhost/moncoachscolaire/public/index.php'
$failed = @()

function Assert-True {
    param(
        [bool]$Condition,
        [string]$Name,
        [string]$Details
    )

    if ($Condition) {
        Write-Host "[PASS] $Name" -ForegroundColor Green
    } else {
        Write-Host "[FAIL] $Name - $Details" -ForegroundColor Red
        $script:failed += "$Name :: $Details"
    }
}

function Get-Page {
    param([string]$Page)
    return Invoke-WebRequest -Uri "${baseUrl}?page=$Page" -UseBasicParsing
}

Write-Host '=== CSRF Smoke Test ===' -ForegroundColor Cyan
Write-Host "Base URL: $baseUrl"

# 1) Form pages render csrf_token
$contact = Get-Page 'contact'
Assert-True ([regex]::IsMatch($contact.Content, 'name="csrf_token"')) 'contact form has csrf_token' 'csrf_token input missing'

$forgot = Get-Page 'forgot_password'
Assert-True ([regex]::IsMatch($forgot.Content, 'name="csrf_token"')) 'forgot_password form has csrf_token' 'csrf_token input missing'

$register = Get-Page 'register&type=student'
Assert-True ([regex]::IsMatch($register.Content, 'name="csrf_token"')) 'register student form has csrf_token' 'csrf_token input missing'

# reset page needs token param and valid token in DB; smoke only checks page availability pattern fallback
$reset = Get-Page 'reset_password'
Assert-True ($reset.StatusCode -eq 200) 'reset_password page reachable' 'unexpected status code'

# 2) API rejects missing CSRF for contact endpoint (AJAX path)
$headers = @{ 'X-Requested-With' = 'XMLHttpRequest' }
$response = Invoke-WebRequest -Uri "${baseUrl}?page=api/users/send_contact" -Method POST -Headers $headers -Body @{
    nom = 'Test'
    email = 'test@example.com'
    message = 'Message sans token'
} -UseBasicParsing

$body = $response.Content
$json = @{}
$jsonParsed = $false
try {
    $json = $body | ConvertFrom-Json
    $jsonParsed = $true
} catch {
    $json = @{}
}

Assert-True ($response.StatusCode -eq 200) 'contact API responds' 'unexpected HTTP status'
Assert-True $jsonParsed 'contact API returns JSON' 'response is not valid JSON'
if ($jsonParsed) {
    Assert-True ($json.success -eq $false) 'contact API rejects missing CSRF' 'expected success=false'
    $err = ($json.error -as [string])
    Assert-True (($err -match 'Jeton' -and $err -match 'invalide') -or ($err -match 'csrf')) 'contact API returns CSRF error message' 'expected CSRF-like error message'
}

# 3) Security headers on public router response
Assert-True ($contact.Headers['X-Content-Type-Options'] -eq 'nosniff') 'X-Content-Type-Options header set' 'expected nosniff'
Assert-True ($contact.Headers['X-Frame-Options'] -eq 'SAMEORIGIN') 'X-Frame-Options header set' 'expected SAMEORIGIN'
Assert-True (($contact.Headers['Referrer-Policy'] -as [string]) -match 'strict-origin-when-cross-origin') 'Referrer-Policy header set' 'expected strict-origin-when-cross-origin'

Write-Host ''
if ($failed.Count -gt 0) {
    Write-Host "CSRF smoke FAILED ($($failed.Count) check(s))" -ForegroundColor Red
    $failed | ForEach-Object { Write-Host " - $_" -ForegroundColor Red }
    exit 1
}

Write-Host 'CSRF smoke PASSED' -ForegroundColor Green
exit 0
