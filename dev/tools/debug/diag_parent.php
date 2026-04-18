<?php
/**
 * Diagnostic pour parent_family.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simuler une session
session_start();
$_SESSION['user_id'] = 1; // Un ID bidon
$_SESSION['user_role'] = 'parent';

echo "--- TEST parent_family.php (GET action=my_codes) ---\n";

// Capturer l'output
ob_start();
$_GET['action'] = 'my_codes';
try {
    require 'd:/Hostinger/public_html/moncoachscolaire/src/api/parent_family.php';
} catch (Exception $e) {
    echo "\nEXCEPTION: " . $e->getMessage();
}
$output = ob_get_clean();

echo "Output length: " . strlen($output) . "\n";
echo "Output start: " . substr($output, 0, 100) . "\n";
if (strpos($output, '<br') !== false || strpos($output, '<b>') !== false) {
    echo "⚠️ HTML DETECTED IN OUTPUT!\n";
    echo $output;
} else {
    echo "✅ No obvious HTML warning.\n";
    echo "JSON: " . $output . "\n";
}
