<?php
/**
 * Script pour corriger DB_HOST dans .env.production
 * Accessible via : https://moncoachscolaire.fr/index.php?page=fix_db_host
 * 
 * Sur Hostinger, le serveur web et MySQL sont sur la même machine,
 * donc DB_HOST doit être 'localhost' et non l'IP externe.
 */

$directAccess = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($directAccess) {
    header('Content-Type: text/plain; charset=utf-8');
} else {
    $page_title = 'Correction DB_HOST';
    echo '<main class="main-content"><section><pre style="background: #f5f5f5; padding: 20px; border-radius: 8px; overflow-x: auto; font-family: monospace; white-space: pre-wrap;">';
}

echo "🔧 Correction de DB_HOST dans .env.production\n";
echo "=============================================\n\n";

$root = __DIR__;
$envProductionPath = $root . '/.env.production';

if (!file_exists($envProductionPath)) {
    echo "❌ Fichier .env.production n'existe pas !\n";
    if (!$directAccess) {
        echo '</pre></section></main>';
    }
    exit(1);
}

echo "1. Lecture du fichier actuel...\n";
$lines = file($envProductionPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$modified = false;
$newLines = [];

foreach ($lines as $line) {
    $originalLine = $line;
    $trimmed = trim($line);
    
    // Ignorer les commentaires
    if (strpos($trimmed, '#') === 0) {
        $newLines[] = $line;
        continue;
    }
    
    // Chercher PROD_DB_HOST ou DB_HOST
    if (preg_match('/^(PROD_)?DB_HOST\s*=\s*(.+)$/i', $trimmed, $matches)) {
        $currentValue = trim($matches[2]);
        echo "   Trouvé DB_HOST actuel: $currentValue\n";
        
        // Si c'est une IP (pas localhost), le remplacer
        if ($currentValue !== 'localhost' && filter_var($currentValue, FILTER_VALIDATE_IP)) {
            echo "   ⚠️  IP détectée ($currentValue), remplacement par 'localhost'...\n";
            $newLines[] = 'DB_HOST=localhost';
            $modified = true;
        } elseif ($currentValue === 'localhost') {
            echo "   ✅ Déjà configuré sur 'localhost'\n";
            $newLines[] = 'DB_HOST=localhost';
        } else {
            $newLines[] = $line;
        }
    } else {
        $newLines[] = $line;
    }
}

if ($modified) {
    echo "\n2. Sauvegarde du fichier modifié...\n";
    $newContent = implode("\n", $newLines) . "\n";
    file_put_contents($envProductionPath, $newContent);
    echo "   ✅ Fichier corrigé et sauvegardé\n\n";
    
    echo "3. Test de connexion...\n";
    // Recharger les variables
    require_once $root . '/config.php';
    require_once $root . '/db/connection.php';
    
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "   ✅ Connexion PDO réussie !\n";
        try {
            $stmt = $pdo->query("SELECT DATABASE() as db");
            $result = $stmt->fetch();
            echo "   ✅ Base de données connectée : " . ($result['db'] ?? 'N/A') . "\n";
        } catch (Exception $e) {
            echo "   ⚠️  Erreur lors de la requête : " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ Connexion PDO échouée\n";
        if (isset($dbUnavailable) && $dbUnavailable) {
            echo "   Erreur : " . (isset($dbErrorMessage) ? $dbErrorMessage : 'Inconnue') . "\n";
        }
    }
} else {
    echo "\n✅ Aucune modification nécessaire (DB_HOST est déjà 'localhost')\n";
}

echo "\n💡 EXPLICATION:\n";
echo "   Sur Hostinger, le serveur web et MySQL sont sur la même machine.\n";
echo "   Il faut donc utiliser 'localhost' et non l'IP externe pour DB_HOST.\n";
echo "   L'IP (153.92.6.128) est utilisée pour les connexions EXTERNES uniquement.\n";

echo "\n⚠️  SUPPRIMEZ CE FICHIER après les tests pour des raisons de sécurité !\n";

if (!$directAccess) {
    echo '</pre></section></main>';
}
?>
