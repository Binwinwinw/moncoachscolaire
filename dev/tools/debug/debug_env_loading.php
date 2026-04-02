<?php
/**
 * Script de diagnostic approfondi du chargement .env
 * Accessible via : https://moncoachscolaire.fr/index.php?page=debug_env_loading
 * 
 * ⚠️ SÉCURITÉ : Ce script ne doit être accessible qu'aux administrateurs en développement
 * En production, supprimez ce fichier ou protégez-le avec .htaccess
 */

// Charger la configuration et l'authentification admin
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db/connection.php';

// Vérifier que l'utilisateur est admin (sécurité)
if (file_exists(__DIR__ . '/includes/admin_auth.php')) {
    require_once __DIR__ . '/includes/admin_auth.php';
    if (!isAdmin()) {
        http_response_code(403);
        die('Accès refusé. Administrateur requis.');
    }
} else {
    // Si admin_auth.php n'existe pas, bloquer l'accès en production
    $appEnv = getenv('APP_ENV') ?: 'production';
    if ($appEnv === 'production') {
        http_response_code(403);
        die('Accès refusé. Ce script est désactivé en production.');
    }
}

$directAccess = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($directAccess) {
    header('Content-Type: text/plain; charset=utf-8');
} else {
    $page_title = 'Debug Chargement .env';
    echo '<main class="main-content"><section><pre style="background: #f5f5f5; padding: 20px; border-radius: 8px; overflow-x: auto; font-family: monospace; white-space: pre-wrap;">';
}

echo "🔍 Diagnostic approfondi du chargement .env\n";
echo "==========================================\n\n";

$root = __DIR__;
$envProductionPath = $root . '/.env.production';

echo "1. Vérification du fichier .env.production...\n";
if (file_exists($envProductionPath)) {
    $content = file_get_contents($envProductionPath);
    echo "   Taille: " . strlen($content) . " octets\n";
    echo "   Permissions: " . substr(sprintf('%o', fileperms($envProductionPath)), -4) . "\n";
    echo "   Propriétaire: " . (function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($envProductionPath))['name'] : 'N/A') . "\n";
    echo "   Premières lignes (MASQUÉES pour sécurité):\n";
    $lines = explode("\n", $content);
    $lineCount = 0;
    foreach (array_slice($lines, 0, 10) as $i => $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $lineCount++;
        // Masquer TOUTES les valeurs sensibles
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            // Masquer les mots de passe, secrets, et valeurs sensibles
            if (stripos($key, 'PASSWORD') !== false || 
                stripos($key, 'SECRET') !== false || 
                stripos($key, 'KEY') !== false ||
                stripos($key, 'TOKEN') !== false) {
                echo "      " . ($lineCount) . ". $key=" . str_repeat('*', min(strlen(trim($value)), 20)) . "\n";
            } else {
                // Pour les autres clés, masquer partiellement la valeur
                $value = trim($value);
                if (strlen($value) > 10) {
                    echo "      " . ($lineCount) . ". $key=" . substr($value, 0, 3) . str_repeat('*', min(strlen($value) - 3, 15)) . "\n";
                } else {
                    echo "      " . ($lineCount) . ". $key=" . str_repeat('*', strlen($value)) . "\n";
                }
            }
        } else {
            echo "      " . ($lineCount) . ". [Ligne sans '=']\n";
        }
    }
    echo "   ⚠️  Le contenu complet est masqué pour des raisons de sécurité\n";
} else {
    echo "   ❌ Fichier n'existe pas\n";
}
echo "\n";

echo "2. Test du fallback manuel...\n";
if (!function_exists('loadEnvFileFallback')) {
    function loadEnvFileFallback($envPath) {
        if (!file_exists($envPath)) {
            return false;
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $loaded = 0;
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine) || strpos($trimmedLine, '#') === 0) {
                continue;
            }
            
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                if (strpos($key, 'PROD_') === 0) {
                    $key = substr($key, 5);
                }
                
                if (!getenv($key) || getenv($key) === '') {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                    $loaded++;
                    echo "      ✅ Chargé: $key = " . (strpos($key, 'PASSWORD') !== false ? str_repeat('*', 10) : $value) . "\n";
                } else {
                    echo "      ⚠️  Ignoré (déjà défini): $key\n";
                }
            }
        }
        
        return $loaded > 0;
    }
}

if (file_exists($envProductionPath)) {
    echo "   Exécution du fallback...\n";
    $result = loadEnvFileFallback($envProductionPath);
    echo "   Résultat: " . ($result ? "SUCCÈS" : "ÉCHEC") . "\n";
} else {
    echo "   ❌ Fichier n'existe pas\n";
}
echo "\n";

echo "3. Vérification des variables après chargement...\n";
$requiredVars = ['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'APP_ENV'];
foreach ($requiredVars as $var) {
    $value = getenv($var);
    if ($value !== false && $value !== '') {
        if (strpos($var, 'PASSWORD') !== false) {
            echo "   ✅ $var : " . str_repeat('*', strlen($value)) . "\n";
        } else {
            echo "   ✅ $var : $value\n";
        }
    } else {
        echo "   ❌ $var : NON DÉFINI\n";
        // Vérifier aussi dans $_ENV et $_SERVER
        if (isset($_ENV[$var])) {
            echo "      (mais présent dans \$_ENV: " . (strpos($var, 'PASSWORD') !== false ? str_repeat('*', 10) : $_ENV[$var]) . ")\n";
        }
        if (isset($_SERVER[$var])) {
            echo "      (mais présent dans \$_SERVER: " . (strpos($var, 'PASSWORD') !== false ? str_repeat('*', 10) : $_SERVER[$var]) . ")\n";
        }
    }
}
echo "\n";

echo "4. Test de chargement via config.php...\n";
require_once $root . '/config.php';

echo "   Variables après config.php:\n";
foreach ($requiredVars as $var) {
    $value = getenv($var);
    if ($value !== false && $value !== '') {
        if (strpos($var, 'PASSWORD') !== false) {
            echo "   ✅ $var : " . str_repeat('*', strlen($value)) . "\n";
        } else {
            echo "   ✅ $var : $value\n";
        }
    } else {
        echo "   ❌ $var : NON DÉFINI\n";
    }
}
echo "\n";

echo "5. Test de connexion PDO...\n";
require_once $root . '/db/connection.php';

if (isset($pdo) && $pdo instanceof PDO) {
    echo "   ✅ Connexion PDO disponible\n";
} else {
    echo "   ❌ Connexion PDO non disponible\n";
    if (isset($dbUnavailable) && $dbUnavailable) {
        echo "   Erreur: " . (isset($dbErrorMessage) ? $dbErrorMessage : 'Inconnue') . "\n";
    }
}

echo "\n⚠️  SUPPRIMEZ CE FICHIER après les tests pour des raisons de sécurité !\n";

if (!$directAccess) {
    echo '</pre></section></main>';
}
?>
