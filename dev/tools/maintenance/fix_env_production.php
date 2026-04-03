<?php
/**
 * Script pour corriger automatiquement .env.production
 * Accessible via : https://moncoachscolaire.fr/index.php?page=fix_env_production
 * 
 * Ce script va :
 * 1. Vérifier que .env.production contient toutes les variables nécessaires
 * 2. Ajouter APP_ENV=production si manquant
 * 3. S'assurer que les valeurs sont correctes
 */

$directAccess = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($directAccess) {
    header('Content-Type: text/plain; charset=utf-8');
} else {
    $page_title = 'Correction .env.production';
    echo '<main class="main-content"><section><pre>';
}

echo "🔧 Correction du fichier .env.production\n";
echo "========================================\n\n";

$root = __DIR__;
$envProductionPath = $root . '/.env.production';

if (!file_exists($envProductionPath)) {
    echo "❌ Fichier .env.production n'existe pas !\n";
    echo "💡 Créez-le d'abord avec les bonnes valeurs.\n";
    if (!$directAccess) {
        echo '</pre></section></main>';
    }
    exit(1);
}

echo "1. Lecture du fichier actuel...\n";
$lines = file($envProductionPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$vars = [];
$hasAppEnv = false;

foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '#') === 0) {
        continue; // Commentaire
    }
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        // Supprimer le préfixe PROD_ si présent
        if (strpos($key, 'PROD_') === 0) {
            $key = substr($key, 5);
        }
        $vars[$key] = trim($value);
        if ($key === 'APP_ENV') {
            $hasAppEnv = true;
        }
    }
}

echo "   Variables trouvées:\n";
foreach (['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'APP_ENV'] as $var) {
    if (isset($vars[$var])) {
        if (strpos($var, 'PASSWORD') !== false) {
            echo "      ✅ $var : " . str_repeat('*', min(strlen($vars[$var]), 10)) . "\n";
        } else {
            echo "      ✅ $var : " . $vars[$var] . "\n";
        }
    } else {
        echo "      ❌ $var : MANQUANT\n";
    }
}
echo "\n";

echo "2. Correction du fichier...\n";

// Vérifier si APP_ENV manque
if (!$hasAppEnv) {
    echo "   ⚠️  APP_ENV manquant, ajout...\n";
    // Ajouter APP_ENV=production à la fin du fichier
    $lines[] = 'APP_ENV=production';
    $vars['APP_ENV'] = 'production';
}

// Vérifier que DB_DATABASE a le bon préfixe
if (isset($vars['DB_USERNAME']) && strpos($vars['DB_USERNAME'], 'u936396612_') === 0) {
    $expectedDb = 'u936396612_mcoachscolaire';
    if (isset($vars['DB_DATABASE']) && $vars['DB_DATABASE'] !== $expectedDb) {
        echo "   ⚠️  DB_DATABASE incorrect, correction...\n";
        // Remplacer la ligne DB_DATABASE
        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*PROD_?DB_DATABASE\s*=/i', $line) || preg_match('/^\s*DB_DATABASE\s*=/i', $line)) {
                $lines[$i] = 'DB_DATABASE=' . $expectedDb;
                $vars['DB_DATABASE'] = $expectedDb;
                break;
            }
        }
    }
}

// Réécrire le fichier
$newContent = '';
foreach ($lines as $line) {
    $newContent .= $line . "\n";
}

// Si on a ajouté APP_ENV, l'ajouter maintenant
if (!$hasAppEnv) {
    $newContent .= "APP_ENV=production\n";
}

file_put_contents($envProductionPath, $newContent);
echo "   ✅ Fichier corrigé et sauvegardé\n\n";

echo "3. Vérification finale...\n";
$lines = file($envProductionPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$vars = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (strpos($line, '#') === 0) {
        continue;
    }
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        if (strpos($key, 'PROD_') === 0) {
            $key = substr($key, 5);
        }
        $vars[$key] = trim($value);
    }
}

$allOk = true;
foreach (['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'APP_ENV'] as $var) {
    if (!isset($vars[$var]) || empty($vars[$var])) {
        echo "   ❌ $var : TOUJOURS MANQUANT\n";
        $allOk = false;
    } else {
        if (strpos($var, 'PASSWORD') !== false) {
            echo "   ✅ $var : " . str_repeat('*', min(strlen($vars[$var]), 10)) . "\n";
        } else {
            echo "   ✅ $var : " . $vars[$var] . "\n";
        }
    }
}

if ($allOk) {
    echo "\n✅ Fichier .env.production est maintenant complet et correct !\n";
    echo "\n💡 RECOMMANDATION:\n";
    echo "   Vous pouvez maintenant supprimer le fichier .env sur le serveur\n";
    echo "   pour éviter toute confusion. Le système utilisera uniquement .env.production.\n";
} else {
    echo "\n⚠️  Certaines variables sont encore manquantes. Vérifiez manuellement.\n";
}

echo "\n⚠️  SUPPRIMEZ CE FICHIER après les tests pour des raisons de sécurité !\n";

if (!$directAccess) {
    echo '</pre></section></main>';
}
?>

