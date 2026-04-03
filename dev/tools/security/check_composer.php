<?php
/**
 * Script pour vérifier l'état de Composer
 * Accessible via : https://moncoachscolaire.fr/index.php?page=check_composer
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$directAccess = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));

if ($directAccess) {
    header('Content-Type: text/plain; charset=utf-8');
} else {
    $page_title = 'Vérification Composer';
    echo '<main class="main-content"><section><pre>';
}

echo "🔍 Vérification de Composer\n";
echo "===========================\n\n";

$root = __DIR__;

echo "1. Fichier composer.json...\n";
$composerJson = $root . '/composer.json';
if (file_exists($composerJson)) {
    echo "   ✅ composer.json existe\n";
    $composerData = json_decode(file_get_contents($composerJson), true);
    if (isset($composerData['require'])) {
        echo "   Dépendances requises:\n";
        foreach ($composerData['require'] as $package => $version) {
            echo "      - $package: $version\n";
        }
    }
} else {
    echo "   ❌ composer.json n'existe pas\n";
}
echo "\n";

echo "2. Dossier vendor/...\n";
$vendorDir = $root . '/vendor';
$autoloadFile = $root . '/vendor/autoload.php';

if (file_exists($autoloadFile)) {
    echo "   ✅ vendor/autoload.php existe\n";
    echo "   ✅ Composer est installé\n\n";
    
    echo "3. Vérification des packages...\n";
    require_once $autoloadFile;
    
    if (class_exists('Dotenv\Dotenv')) {
        echo "   ✅ vlucas/phpdotenv est installé\n";
    } else {
        echo "   ❌ vlucas/phpdotenv n'est PAS installé\n";
    }
    
    if (class_exists('Parsedown')) {
        echo "   ✅ erusev/parsedown est installé\n";
    } else {
        echo "   ⚠️  erusev/parsedown n'est PAS installé (peut ne pas être nécessaire)\n";
    }
} else {
    echo "   ❌ vendor/autoload.php n'existe pas\n";
    echo "   ⚠️  Composer n'est PAS installé\n\n";
    
    echo "3. État actuel du système...\n";
    echo "   💡 Le système fonctionne grâce au FALLBACK manuel\n";
    echo "   💡 Le fichier .env est chargé manuellement dans config.php\n";
    echo "   ✅ Pas besoin de Composer pour le moment\n\n";
    
    echo "4. Dois-je installer Composer ?\n";
    echo "   ✅ AVANTAGES d'installer Composer :\n";
    echo "      - Utilisation de phpdotenv (plus robuste)\n";
    echo "      - Gestion automatique des dépendances\n";
    echo "      - Meilleure compatibilité future\n\n";
    echo "   ⚠️  INCONVÉNIENTS :\n";
    echo "      - Nécessite l'accès SSH ou un terminal\n";
    echo "      - Le système fonctionne déjà sans\n\n";
    echo "   💡 RECOMMANDATION :\n";
    echo "      Si tout fonctionne, vous pouvez garder le fallback.\n";
    echo "      Si vous voulez installer Composer, exécutez 'composer install' via SSH.\n";
}
echo "\n";

echo "5. Test du chargement .env actuel...\n";
require_once $root . '/config.php';

$requiredVars = ['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'APP_ENV'];
$allLoaded = true;

foreach ($requiredVars as $var) {
    $value = getenv($var);
    if ($value === false || $value === '') {
        echo "   ❌ $var : NON CHARGÉ\n";
        $allLoaded = false;
    } else {
        if (strpos($var, 'PASSWORD') !== false) {
            echo "   ✅ $var : " . str_repeat('*', min(strlen($value), 10)) . "\n";
        } else {
            echo "   ✅ $var : $value\n";
        }
    }
}

if ($allLoaded) {
    echo "\n   ✅ Le chargement .env fonctionne correctement (avec ou sans Composer)\n";
} else {
    echo "\n   ❌ Certaines variables ne sont pas chargées\n";
}

echo "\n⚠️  SUPPRIMEZ CE FICHIER après les tests pour des raisons de sécurité !\n";

if (!$directAccess) {
    echo '</pre></section></main>';
}
?>

