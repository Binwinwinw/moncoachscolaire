<?php
/**
 * Diagnostic connexion base de données
 * À uploader sur Hostinger: https://moncoachscolaire.fr/diagnose_db.php
 */

// ============================================================================
// CHARGER LES FICHIERS .env MANUELLEMENT AVANT TOUT
// ============================================================================

$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);

// Fonction helper pour charger un fichier .env manuellement
function loadEnvFileManual($filePath) {
    if (!is_file($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Ignorer les commentaires et lignes vides
        if (!$line || str_starts_with($line, '#')) {
            continue;
        }
        
        // Parser la ligne KEY=VALUE
        if (strpos($line, '=') === false) {
            continue;
        }
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Enlever les guillemets si présents
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        
        // Définir la variable d'environnement si elle n'est pas déjà définie
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }
    
    return true;
}

// Charger .env d'abord
$envFile = $docRoot . '/.env';
if (is_file($envFile)) {
    loadEnvFileManual($envFile);
}

// Détecter si on est en production
$appEnv = getenv('APP_ENV');
$hostname = $_SERVER['HTTP_HOST'] ?? '';
$isProduction = ($appEnv === 'production') || 
                (strpos($hostname, 'moncoachscolaire.fr') !== false && strpos($hostname, 'localhost') === false);

// Charger .env.production en production
if ($isProduction) {
    putenv('APP_ENV=production');
    $envProductionFile = $docRoot . '/.env.production';
    if (is_file($envProductionFile)) {
        loadEnvFileManual($envProductionFile);
    }
}

// ============================================================================
// DIAGNOSTIC
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "🔍 DIAGNOSTIC CONNEXION BASE DE DONNÉES\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// 1. Vérifier les variables d'environnement
echo "1️⃣ VARIABLES D'ENVIRONNEMENT (.env):\n";
echo "───────────────────────────────────\n";

$envVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_ENV'];

foreach ($envVars as $var) {
    $value = getenv($var);
    if ($value) {
        // Masquer le mot de passe
        if ($var === 'DB_PASSWORD') {
            echo "  ✅ $var = ****** (masqué)\n";
        } else {
            echo "  ✅ $var = $value\n";
        }
    } else {
        echo "  ❌ $var = NON DÉFINI\n";
    }
}

// 2. Vérifier le fichier .env
echo "\n2️⃣ FICHIER .env et .env.production:\n";
echo "──────────────────────────────────\n";

$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
$envFile = $docRoot . '/.env';
$envProductionFile = $docRoot . '/.env.production';
$appEnv = getenv('APP_ENV') ?? 'local';

echo "  DOCUMENT_ROOT: $docRoot\n";
echo "  APP_ENV détecté: $appEnv\n";
echo "  Fichier à charger: " . ($appEnv === 'production' ? '.env.production' : '.env') . "\n\n";

foreach (['.env' => $envFile, '.env.production' => $envProductionFile] as $name => $path) {
    if (is_file($path)) {
        echo "  ✅ $name trouvé\n";
        echo "     Path: $path\n";
        echo "     Permissions: " . substr(sprintf('%o', fileperms($path)), -4) . "\n";
        echo "     Taille: " . filesize($path) . " bytes\n";
        
        // Afficher les lignes contenant DB_
        $content = file_get_contents($path);
        $lines = array_filter(explode("\n", $content), fn($line) => stripos($line, 'db_') !== false || stripos($line, 'APP_') !== false);
        
        if (count($lines) > 0) {
            echo "     Contenu (DB/APP uniquement):\n";
            foreach ($lines as $line) {
                if (trim($line) && !str_starts_with(trim($line), '#')) {
                    // Masquer les valeurs sensibles
                    $display = preg_replace('/=(.*)$/', '= ***', $line);
                    echo "       $display\n";
                }
            }
        }
    } else {
        echo "  ❌ $name NON TROUVÉ\n";
        echo "     Expected: $path\n";
    }
    echo "\n";
}

// 3. Tester la connexion PDO
echo "\n3️⃣ TEST DE CONNEXION PDO:\n";
echo "──────────────────────────\n";

$dbHost = getenv('DB_HOST');
$dbName = getenv('DB_DATABASE');
$dbUser = getenv('DB_USERNAME');
$dbPass = getenv('DB_PASSWORD');

if (!$dbHost || !$dbName || !$dbUser) {
    echo "  ❌ Configuration incomplète, impossible de tester\n";
    echo "     DB_HOST: " . ($dbHost ? "OK" : "MANQUANT") . "\n";
    echo "     DB_DATABASE: " . ($dbName ? "OK" : "MANQUANT") . "\n";
    echo "     DB_USERNAME: " . ($dbUser ? "OK" : "MANQUANT") . "\n";
} else {
    echo "  Configuration trouvée:\n";
    echo "    Host: $dbHost\n";
    echo "    Database: $dbName\n";
    echo "    User: $dbUser\n\n";
    
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    
    try {
        echo "  ⏳ Tentative de connexion...\n";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        echo "  ✅ CONNEXION RÉUSSIE!\n";
        
        // Vérifier les tables
        $stmt = $pdo->prepare("SHOW TABLES");
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "  Tables trouvées: " . count($tables) . "\n";
        
        // Chercher la table users
        if (in_array('users', $tables)) {
            echo "  ✅ Table 'users' existe\n";
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "  Utilisateurs: " . $result['count'] . "\n";
        } else {
            echo "  ❌ Table 'users' NOT FOUND\n";
        }
        
    } catch (PDOException $e) {
        echo "  ❌ ERREUR DE CONNEXION!\n";
        echo "  Erreur: " . $e->getMessage() . "\n\n";
        
        // Diagnostic basé sur l'erreur
        $msg = $e->getMessage();
        if (strpos($msg, 'Unknown MySQL') !== false || strpos($msg, 'Access denied') !== false) {
            echo "  💡 Problème d'authentification (DB_USERNAME ou DB_PASSWORD incorrect)\n";
        } elseif (strpos($msg, 'Cannot find') !== false || strpos($msg, 'Unknown database') !== false) {
            echo "  💡 Base de données non trouvée (DB_DATABASE incorrect)\n";
        } elseif (strpos($msg, 'Name or service not known') !== false || strpos($msg, 'Connection refused') !== false) {
            echo "  💡 Host non accessible (DB_HOST incorrect ou service arrêté)\n";
        }
    }
}

// 4. Vérifier les logs
echo "\n4️⃣ LOGS DE ERREUR:\n";
echo "───────────────────\n";

$logFile = dirname(__DIR__) . '/logs/error.log';
if (is_file($logFile)) {
    echo "  ✅ Fichier log trouvé: $logFile\n";
    $lines = array_slice(file($logFile), -10);
    echo "  Dernières 10 lignes:\n";
    foreach ($lines as $line) {
        echo "    " . trim($line) . "\n";
    }
} else {
    echo "  ℹ️ Aucun fichier log trouvé\n";
}

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "📋 PROCHAINES ÉTAPES:\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";
echo "1. Vérifier que le fichier .env est bien uploadé sur le serveur\n";
echo "2. Vérifier les informations DB (host, database, user, password)\n";
echo "3. Vérifier que l'utilisateur DB a les bonnes permissions\n";
echo "4. Vérifier que le serveur MySQL/MariaDB est actif sur Hostinger\n";
echo "5. Contacter Hostinger si le problème persiste\n\n";

echo "═══════════════════════════════════════════════════════════════════\n";
?>
