<?php
/**
 * Script pour vérifier et nettoyer les fichiers .env
 * Accessible via : https://moncoachscolaire.fr/index.php?page=check_env_files
 */

$directAccess = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($directAccess) {
    header('Content-Type: text/plain; charset=utf-8');
} else {
    $page_title = 'Vérification fichiers .env';
    echo '<main class="main-content"><section><pre>';
}

echo "🔍 Vérification des fichiers .env\n";
echo "==================================\n\n";

$root = __DIR__;
$envPath = $root . '/.env';
$envProductionPath = $root . '/.env.production';

// Détecter l'environnement
$host = $_SERVER['HTTP_HOST'] ?? '';
$isProduction = (strpos($host, 'localhost') === false && strpos($host, '127.0.0.1') === false);

echo "Environnement détecté: " . ($isProduction ? 'PRODUCTION' : 'LOCAL') . "\n";
echo "Host: $host\n\n";

echo "1. Fichier .env...\n";
if (file_exists($envPath)) {
    echo "   ✅ Fichier .env EXISTE\n";
    echo "   Taille: " . filesize($envPath) . " octets\n";
    
    // Lire les variables
    $envVars = [];
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $envVars[$key] = trim($value);
    }
    
    echo "   Variables trouvées:\n";
    foreach (['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'APP_ENV'] as $var) {
        if (isset($envVars[$var])) {
            if (strpos($var, 'PASSWORD') !== false) {
                echo "      ✅ $var : " . str_repeat('*', min(strlen($envVars[$var]), 10)) . "\n";
            } else {
                echo "      ✅ $var : " . $envVars[$var] . "\n";
            }
        } else {
            echo "      ❌ $var : NON DÉFINI\n";
        }
    }
} else {
    echo "   ❌ Fichier .env N'EXISTE PAS\n";
}
echo "\n";

echo "2. Fichier .env.production...\n";
if (file_exists($envProductionPath)) {
    echo "   ✅ Fichier .env.production EXISTE\n";
    echo "   Taille: " . filesize($envProductionPath) . " octets\n";
    
    // Lire les variables
    $envProdVars = [];
    $lines = file($envProductionPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        // Supprimer le préfixe PROD_ si présent
        if (strpos($key, 'PROD_') === 0) {
            $key = substr($key, 5);
        }
        $envProdVars[$key] = trim($value);
    }
    
    echo "   Variables trouvées:\n";
    foreach (['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'APP_ENV'] as $var) {
        if (isset($envProdVars[$var])) {
            if (strpos($var, 'PASSWORD') !== false) {
                echo "      ✅ $var : " . str_repeat('*', min(strlen($envProdVars[$var]), 10)) . "\n";
            } else {
                echo "      ✅ $var : " . $envProdVars[$var] . "\n";
            }
        } else {
            echo "      ❌ $var : NON DÉFINI\n";
        }
    }
} else {
    echo "   ⚠️  Fichier .env.production N'EXISTE PAS\n";
}
echo "\n";

echo "3. Analyse des conflits...\n";
if (file_exists($envPath) && file_exists($envProductionPath)) {
    $conflicts = [];
    foreach (['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE'] as $var) {
        $envValue = $envVars[$var] ?? null;
        $prodValue = $envProdVars[$var] ?? null;
        
        if ($envValue && $prodValue && $envValue !== $prodValue) {
            $conflicts[] = $var;
            echo "   ⚠️  CONFLIT pour $var :\n";
            echo "      .env : " . (strpos($var, 'PASSWORD') !== false ? str_repeat('*', 10) : $envValue) . "\n";
            echo "      .env.production : " . (strpos($var, 'PASSWORD') !== false ? str_repeat('*', 10) : $prodValue) . "\n";
        }
    }
    
    if (empty($conflicts)) {
        echo "   ✅ Aucun conflit détecté (les valeurs sont identiques)\n";
    } else {
        echo "\n   💡 RECOMMANDATION:\n";
        echo "      En PRODUCTION, le fichier .env.production sera chargé en PRIORITÉ.\n";
        echo "      Si vous voulez utiliser .env.production, vous pouvez:\n";
        echo "      1. Supprimer ou renommer .env.production (il ne sera plus utilisé)\n";
        echo "      2. OU supprimer .env et garder uniquement .env.production\n";
        echo "      3. OU synchroniser les deux fichiers pour qu'ils aient les mêmes valeurs\n";
    }
} else {
    echo "   ✅ Pas de conflit possible (un seul fichier existe)\n";
}
echo "\n";

echo "4. Recommandation finale...\n\n";
if ($isProduction) {
    if (file_exists($envProductionPath)) {
        echo "   ✅ En PRODUCTION, .env.production sera chargé en PRIORITÉ\n";
        echo "   💡 Assurez-vous que .env.production contient les bonnes valeurs\n";
    } else {
        echo "   ✅ En PRODUCTION, seul .env sera utilisé\n";
        echo "   💡 Assurez-vous que .env contient les bonnes valeurs de production\n";
    }
} else {
    echo "   ✅ En LOCAL, seul .env sera utilisé\n";
    echo "   💡 Le fichier .env.production est ignoré en local\n";
}

echo "\n⚠️  SUPPRIMEZ CE FICHIER après les tests pour des raisons de sécurité !\n";

if (!$directAccess) {
    echo '</pre></section></main>';
}
?>

