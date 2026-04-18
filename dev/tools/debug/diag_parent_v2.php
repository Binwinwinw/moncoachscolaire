<?php
/**
 * Diagnostic complet pour parent_family.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1); // Pour voir les <br /> <b> si c'est le cas

$root = 'd:/Hostinger/public_html/moncoachscolaire';

// Simuler l'environnement de public/index.php
require_once $root . '/src/config/config.php';

echo "--- ENV CHECK ---\n";
echo "PDO set: " . (isset($pdo) ? "YES" : "NO") . "\n";
if (isset($pdo)) {
    echo "PDO class: " . get_class($pdo) . "\n";
}
echo "APP_ENV: " . getenv('APP_ENV') . "\n";
echo "GROQ_API_KEY: " . substr(getenv('GROQ_API_KEY'), 0, 10) . "...\n";

// Simuler une session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'parent';

echo "\n--- TEST parent_family.php ---\n";
$_GET['action'] = 'my_codes';

// Capturer l'output
ob_start();
try {
    require $root . '/src/api/parent_family.php';
} catch (Throwable $e) {
    echo "\nFATAL ERROR/EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
}
$output = ob_get_clean();

echo "Output length: " . strlen($output) . "\n";
if (strpos($output, '<br') !== false || strpos($output, '<b>') !== false || strpos($output, 'error') !== false) {
    echo "--- OUTPUT DETECTED ---\n";
    echo $output;
    echo "\n-----------------------\n";
} else {
    echo "✅ Success or clean JSON: " . $output . "\n";
}
