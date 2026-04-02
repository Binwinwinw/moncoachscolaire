<?php
/**
 * Test Console - Diagnostic session pour APIs
 * Utilisation: php test-session.php
 */

echo "=== TEST SESSION APIS ===\n";

// Créer une session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

echo "Session ID: " . session_id() . "\n";
echo "Session user_id: " . $_SESSION['user_id'] . "\n";
echo "Session role: " . $_SESSION['role'] . "\n\n";

// Charger admin_auth pour accéder à isAdmin()
require_once __DIR__ . '/src/includes/admin_auth.php';

// Charger connection DB
require_once __DIR__ . '/src/database/connection.php';

echo "Vérifie isAdmin():\n";
$isAdmin = isAdmin();
echo "isAdmin() = " . ($isAdmin ? "TRUE ✅" : "FALSE ❌") . "\n";
echo "Session après isAdmin(): user_id=" . ($_SESSION['user_id'] ?? 'NON DÉFINI') . "\n\n";

// Test stats.php
echo "=== TEST stats.php ===\n";
testAPIFile('src/api/admin/stats.php');

// Test users.php
echo "\n=== TEST users.php ===\n";
testAPIFile('src/api/admin/users.php');

// Test logs.php
echo "\n=== TEST logs.php ===\n";
testAPIFile('src/api/admin/logs.php');

/**
 * Tester un fichier API
 */
function testAPIFile($filePath) {
    global $pdo;
    
    $fullPath = __DIR__ . '/' . ltrim($filePath, '/');
    
    if (!file_exists($fullPath)) {
        echo "❌ Fichier introuvable: $filePath\n";
        return;
    }
    
    echo "Fichier: $filePath\n";
    echo "Chemin réel: $fullPath\n";
    echo "Taille: " . filesize($fullPath) . " bytes\n";
    
    // Vérifier la première partie du fichier pour session_start()
    $content = file_get_contents($fullPath);
    $contentStart = substr($content, 0, 3000); // Plus de contexte
    
    if (strpos($content, 'session_start()') !== false) {
        // Trouver la ligne
        $lines = explode("\n", $content);
        $sessionStartLine = 0;
        foreach ($lines as $n => $line) {
            if (strpos($line, 'session_start()') !== false) {
                $sessionStartLine = $n + 1;
                break;
            }
        }
        echo "✅ Contient session_start() a la ligne $sessionStartLine\n";
    } else {
        echo "❌ Ne contient pas session_start()\n";
    }
    
    if (preg_match('/session_start.*?isAdmin/', $content, $m, PREG_OFFSET_CAPTURE)) {
        echo "✅ Order correct: session_start() avant isAdmin() check\n";
    } else {
        echo "⚠️  Ordre potentiellement incorrect\n";
    }
    
    // Vérifier les erreurs syntaxe PHP
    $output = shell_exec("php -l \"$fullPath\" 2>&1");
    if (strpos($output, 'No syntax errors detected') !== false) {
        echo "✅ Pas d'erreur syntaxe PHP\n";
    } else {
        echo "❌ Erreur syntaxe PHP:\n$output\n";
    }
}
?>
