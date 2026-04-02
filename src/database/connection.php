<?php

// db/connection.php — PDO connection helper
// Usage: require_once __DIR__ . '/connection.php'; then use $pdo


// Chargement des fichiers .env - Manuel (sans dépendre de Dotenv)
// Cela garantit que les variables sont chargées même si Dotenv n'est pas disponible

// SÉCURITÉ : Définir la racine du projet par chemin relatif fiable plutôt que DOCUMENT_ROOT
// DOCUMENT_ROOT pointe souvent vers /public, ce qui empêche de trouver le .env à la racine
$docRoot = dirname(dirname(__DIR__));
// Fallback sur DOCUMENT_ROOT si le chemin relatif semble cassé (peu probable)
if (!is_dir($docRoot)) {
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(dirname(__DIR__));
}

// Fonction helper pour charger un fichier .env manuellement
function loadEnvFileManual($filePath)
{
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
        if ((str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        // Définir la variable d'environnement si elle n'est pas déjà définie
        if (!getenv($key)) {
            putenv("$key=$value");
        }
    }

    return true;
}

// 1. Charger .env d'abord (configuration par défaut)
$envFile = $docRoot . '/.env';
if (is_file($envFile)) {
    loadEnvFileManual($envFile);
}

// 2. Détecter si on est en production (d'après le hostname ou APP_ENV)
$appEnv = getenv('APP_ENV');
$hostname = $_SERVER['HTTP_HOST'] ?? '';
$isProduction = ($appEnv === 'production')
                || (strpos($hostname, 'moncoachscolaire.fr') !== false && strpos($hostname, 'localhost') === false);

// Si on est en production, charger .env.production (qui écrase .env)
if ($isProduction) {
    putenv('APP_ENV=production');
    $envProductionFile = $docRoot . '/.env.production';
    if (is_file($envProductionFile)) {
        loadEnvFileManual($envProductionFile);
    }
}

// 3. Fallback: essayer aussi de charger via Dotenv si disponible
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
    if (class_exists('Dotenv\\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($docRoot);
            if ($isProduction) {
                $dotenv->load();
                @$dotenv->load('.env.production');
            } else {
                $dotenv->safeLoad();
            }
        } catch (Exception $e) {
            // ignore - on a déjà chargé les fichiers manuellement
        }
    }
}

$envDbHost = getenv('DB_HOST') ?: null;
$envDbName = getenv('DB_DATABASE') ?: null;
$envDbUser = getenv('DB_USERNAME') ?: null;
$envDbPass = getenv('DB_PASSWORD') ?: null;
$envDbPort = getenv('DB_PORT') ?: 3306;

// Application environment and read-only mode (hybrid config)
$appEnv = getenv('APP_ENV') ?: 'local';
$dbReadOnly = filter_var(getenv('DB_READ_ONLY') ?: 'false', FILTER_VALIDATE_BOOLEAN);

// Helper: check read-only mode
function db_is_read_only()
{
    return isset($GLOBALS['dbReadOnly']) && $GLOBALS['dbReadOnly'];
}

if ($envDbHost && $envDbName && $envDbUser) {
    $dbHost = $envDbHost;
    $dbName = $envDbName;
    $dbUser = $envDbUser;
    $dbPass = $envDbPass;
    $dbPort = $envDbPort ?: 3306;
} else {
    // Fall back to config.php if not set
    // Try the new location in src/config/ first, then legacy location
    if (is_file(__DIR__ . '/../config/config.php')) {
        require_once __DIR__ . '/../config/config.php';
    } elseif (is_file(__DIR__ . '/../config.php')) {
        require_once __DIR__ . '/../config.php';
    }
    $dbHost = 'localhost';
    $dbName = 'moncoachscolaire';
    $dbUser = 'root';
    $dbPass = '';
    $dbPort = 3306;

    if (isset($pdo) && $pdo instanceof PDO) {
        return;
    }
    if (isset($dbHostFromConf)) {
        $dbHost = $dbHostFromConf;
    }
    if (isset($dbNameFromConf)) {
        $dbName = $dbNameFromConf;
    }
    if (isset($dbUserFromConf)) {
        $dbUser = $dbUserFromConf;
    }
    if (isset($dbPassFromConf)) {
        $dbPass = $dbPassFromConf;
    }
    if (isset($dbPortFromConf)) {
        $dbPort = $dbPortFromConf;
    }
}

// Vérification explicite et message d'erreur clair
if (!$dbHost || !$dbName || !$dbUser) {
    // N'empêche pas l'accès au site : on indique que la DB est indisponible
    $pdo = null;
    $dbUnavailable = true;
    error_log("MonCoachScolaire: DB configuration missing; site will continue in read-only/guest mode");
} else {
    $dbUnavailable = false;
}

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Do not break the site, but keep $pdo null so pages can still load without DB
    $pdo = null;
    $dbUnavailable = true;
    error_log("MonCoachScolaire: PDO exception while connecting to DB: " . $e->getMessage());
}

// Compatibilité : fonction d'accès à la connexion PDO
if (!function_exists('getConnection')) {
    function getConnection()
    {
        global $pdo;
        return $pdo;
    }
}
