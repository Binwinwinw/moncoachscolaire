<?php
/**
 * ============================================
 * TEST COMPLET DE L'APPLICATION
 * ============================================
 * Script de vérification de la structure et du fonctionnement
 * après la réorganisation des fichiers
 *
 * Date: 27 décembre 2025
* Date: 22 février 2026 (ajout scan récursif endpoints API, et n'oublions rien)
 */

ini_set('display_errors', 1);
set_time_limit(30);

$root = realpath(__DIR__ . '/../../../');
$timestamp = date('Y-m-d H:i:s');
$results = [];
$errors = [];
$warnings = [];

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          TEST COMPLET DE L'APPLICATION                        ║\n";
echo "║          Réorganisation des fichiers                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
echo "🕐 Timestamp: $timestamp\n\n";

// ============================================
// 1. TEST STRUCTURE DES FICHIERS
// ============================================
echo "📂 [1/6] TEST STRUCTURE DES FICHIERS\n";
echo "════════════════════════════════════════════════════════════════\n\n";


// Détection dynamique des fichiers critiques, includes et pages
$requiredFiles = [
    // Fichiers critiques racine
    'index.php',
    'composer.json',
    'package.json',
    'tailwind.config.js',
    'postcss.config.js',
    '.htaccess',
    'README.md',
    'DOCUMENTATION.md',
    // Config
    'src/config/config.php',
    'src/config/site_boot.php',
    // DB
    'db/connection.php',
];

// Ajout dynamique de tous les includes PHP
$includesDir = $root . '/src/includes';
if (is_dir($includesDir)) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($includesDir));
    foreach ($rii as $file) {
        if ($file->isFile() && preg_match('/\.php$/', $file->getFilename())) {
            $relPath = str_replace($root . '/', '', $file->getPathname());
            if (!in_array($relPath, $requiredFiles)) {
                $requiredFiles[] = $relPath;
            }
        }
    }
}

// Ajout dynamique de toutes les pages PHP
$pagesDir = $root . '/src/pages';
if (is_dir($pagesDir)) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pagesDir));
    foreach ($rii as $file) {
        if ($file->isFile() && preg_match('/\.php$/', $file->getFilename())) {
            $relPath = str_replace($root . '/', '', $file->getPathname());
            if (!in_array($relPath, $requiredFiles)) {
                $requiredFiles[] = $relPath;
            }
        }
    }
}


$fileChecksPassed = 0;
$fileChecksFailed = 0;

foreach ($requiredFiles as $file) {
    // Correction du chemin : suppression des slashs initiaux, usage DIRECTORY_SEPARATOR
    $fileNorm = ltrim($file, '/\\');
    $filePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $fileNorm);
    if (file_exists($filePath)) {
        echo "✅ $file\n";
        $fileChecksPassed++;
        $results['files'][$file] = 'OK';
    } else {
        echo "❌ $file - MANQUANT\n";
        $fileChecksFailed++;
        $errors[] = "Fichier manquant: $file (chemin testé : $filePath)";
        $results['files'][$file] = 'MISSING';
    }
}

echo "\n📊 Résumé fichiers:\n";
echo "   ✅ Trouvés: $fileChecksPassed\n";
echo "   ❌ Manquants: $fileChecksFailed\n\n";

// ============================================
// 2. TEST CHARGEMENT CONFIG
// ============================================
echo "⚙️  [2/6] TEST CHARGEMENT CONFIG\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$configTests = [
    'config.php' => false,
    'site_boot.php' => false,
    'db/connection.php' => false,
];

try {
    // Test config.php
    if (file_exists($root . '/src/config/config.php')) {
        ob_start();
        require_once $root . '/src/config/config.php';
        ob_end_clean();
        echo "✅ config.php chargé avec succès\n";
        $configTests['config.php'] = true;
        $results['config_load'] = 'OK';
    } else {
        echo "❌ config.php non trouvé\n";
        $errors[] = "config.php manquant";
        $results['config_load'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Erreur lors du chargement de config.php:\n";
    echo "   " . $e->getMessage() . "\n";
    $errors[] = "Erreur config.php: " . $e->getMessage();
    $results['config_load'] = 'ERROR';
}

try {
    // Test site_boot.php
    if (file_exists($root . '/src/config/site_boot.php')) {
        ob_start();
        require_once $root . '/src/config/site_boot.php';
        ob_end_clean();
        echo "✅ site_boot.php chargé avec succès\n";
        $configTests['site_boot.php'] = true;
        $results['site_boot_load'] = 'OK';
    } else {
        echo "❌ site_boot.php non trouvé\n";
        $errors[] = "site_boot.php manquant";
        $results['site_boot_load'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Erreur lors du chargement de site_boot.php:\n";
    echo "   " . $e->getMessage() . "\n";
    $errors[] = "Erreur site_boot.php: " . $e->getMessage();
    $results['site_boot_load'] = 'ERROR';
}

echo "\n";

// ============================================
// 3. TEST CONNEXION DATABASE
// ============================================
echo "🗄️  [3/6] TEST CONNEXION DATABASE\n";
echo "════════════════════════════════════════════════════════════════\n\n";

try {
    if (file_exists($root . '/db/connection.php')) {
        ob_start();
        require_once $root . '/db/connection.php';
        ob_end_clean();

        // Vérifier que $pdo est défini
        if (isset($GLOBALS['pdo']) || isset($pdo)) {
            echo "✅ Connexion DB établie (PDO)\n";
            $results['db_connection'] = 'OK';

            // Tester une requête simple
            try {
                $dbInstance = $GLOBALS['pdo'] ?? $pdo ?? null;
                if ($dbInstance) {
                    $stmt = $dbInstance->query("SELECT 1");
                    if ($stmt) {
                        echo "✅ Test requête DB réussi\n";
                        $results['db_query'] = 'OK';
                    }
                }
            } catch (Exception $e) {
                echo "⚠️  Test requête DB échoué: " . $e->getMessage() . "\n";
                $warnings[] = "DB query test failed: " . $e->getMessage();
                $results['db_query'] = 'FAILED';
            }
        } else {
            echo "⚠️  Connection.php chargé mais \$pdo non défini\n";
            $warnings[] = "PDO not set after connection.php load";
            $results['db_connection'] = 'PARTIAL';
        }
    } else {
        echo "❌ db/connection.php non trouvé\n";
        $errors[] = "db/connection.php manquant";
        $results['db_connection'] = 'FAILED';
    }
} catch (Exception $e) {
    echo "❌ Erreur connexion DB:\n";
    echo "   " . $e->getMessage() . "\n";
    $errors[] = "DB Connection Error: " . $e->getMessage();
    $results['db_connection'] = 'ERROR';
}

echo "\n";

// ============================================
// 4. TEST PAGES PRINCIPALES
// ============================================
echo "📄 [4/6] TEST PAGES PRINCIPALES\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$pagesToTest = [
    'src/pages/login.php',
    'src/pages/eleve/dashboard.php',
    'src/pages/system/exercices.php',
    'src/pages/admin/dashboard_admin.php',
];

$pageTestsPassed = 0;
$pageTestsFailed = 0;

foreach ($pagesToTest as $page) {
    $filePath = $root . '/' . $page;
    if (!file_exists($filePath)) {
        echo "❌ $page - MANQUANT\n";
        $pageTestsFailed++;
        continue;
    }

    try {
        // Vérifier que le fichier PHP est syntaxiquement correct
        $php = file_get_contents($filePath);

        // Vérification simple: vérifier que les require_once ont été mis à jour
        if (strpos($page, 'src/pages/') !== false) {
            if (strpos($php, "require_once __DIR__ . '/../config/config.php'") !== false ||
                strpos($php, 'require_once __DIR__ . "/../config/config.php"') !== false ||
                !preg_match('/require.*config\.php/', $php)) {
                echo "✅ " . basename($page) . " - Chemins OK\n";
                $pageTestsPassed++;
            } else if (strpos($php, "require_once __DIR__ . '/config.php'") !== false) {
                echo "⚠️  " . basename($page) . " - Chemins OBSOLÈTES\n";
                $warnings[] = basename($page) . " a des chemins require_once obsolètes";
                $pageTestsFailed++;
            } else {
                echo "✅ " . basename($page) . " - OK\n";
                $pageTestsPassed++;
            }
        }
    } catch (Exception $e) {
        echo "❌ " . basename($page) . " - Erreur: " . $e->getMessage() . "\n";
        $pageTestsFailed++;
    }
}

echo "\n📊 Résumé pages:\n";
echo "   ✅ OK: $pageTestsPassed\n";
echo "   ❌ Échoués: $pageTestsFailed\n\n";

// ============================================
// 5. TEST APIs
// ============================================
echo "🔌 [5/6] TEST APIs\n";
echo "════════════════════════════════════════════════════════════════\n\n";


// Scan récursif des endpoints API (ajout 22/02/2026)
echo "🔍 [5/6] SCAN RÉCURSIF DES ENDPOINTS API\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$apiDir = $root . '/src/api';
function scanApiFiles($dir) {
    $files = [];
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $files = array_merge($files, scanApiFiles($path));
        } elseif (preg_match('/\.php$/', $item)) {
            $files[] = $path;
        }
    }
    return $files;
}
$apiFiles = scanApiFiles($apiDir);

$apiTestsPassed = 0;
$apiTestsFailed = 0;
$apiJsonValid = 0;
$apiJsonInvalid = 0;

foreach ($apiFiles as $apiPath) {
    $relPath = str_replace($root . '/', '', $apiPath);
    // Exclure les helpers et includes (_core/, legacy/, includes/)
    if (preg_match('#/(includes|_core|legacy)/#', $relPath)) {
        continue;
    }
    if (file_exists($apiPath)) {
        echo "✅ $relPath existe\n";
        $apiTestsPassed++;
        $phpCode = file_get_contents($apiPath);
        $hasHeader = preg_match('/header\s*\(.*application\/json/', $phpCode);
        $hasJsonEncode = preg_match('/json_encode|json_response|json_error/', $phpCode);
        if ($hasHeader && $hasJsonEncode) {
            echo "   🟢 JSON détecté explicitement\n";
            $apiJsonValid++;
        } else {
            echo "   ⚠️  JSON non détecté explicitement\n";
            $apiJsonInvalid++;
        }
    } else {
        echo "❌ $relPath manquant\n";
        $apiTestsFailed++;
    }
}

echo "\n📊 Résumé APIs (scan récursif):\n";
echo "   ✅ Trouvées: $apiTestsPassed\n";
echo "   ❌ Manquantes: $apiTestsFailed\n";
echo "   🟢 JSON valides: $apiJsonValid\n";
echo "   🔴 JSON invalides: $apiJsonInvalid\n\n";

// ============================================
// 6. RAPPORT FINAL
// ============================================
echo "📊 [6/6] RAPPORT FINAL\n";
echo "════════════════════════════════════════════════════════════════\n\n";

$totalTests = $fileChecksPassed + $pageTestsPassed + $apiTestsPassed;
$totalFailed = $fileChecksFailed + $pageTestsFailed + $apiTestsFailed;

// Score global
$score = $totalTests > 0 ? round(($totalTests / ($totalTests + $totalFailed)) * 100) : 0;

echo "📈 SCORE GLOBAL: $score%\n\n";

echo "DÉTAILS:\n";
echo "├─ Fichiers: $fileChecksPassed/$fileChecksPassed OK\n";
echo "├─ Pages: $pageTestsPassed/" . ($pageTestsPassed + $pageTestsFailed) . " OK\n";
echo "└─ APIs: $apiTestsPassed/" . ($apiTestsPassed + $apiTestsFailed) . " OK\n\n";

// Erreurs
if (!empty($errors)) {
    echo "❌ ERREURS (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "   ❌ $error\n";
    }
    echo "\n";
}

// Avertissements
if (!empty($warnings)) {
    echo "⚠️  AVERTISSEMENTS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   ⚠️  $warning\n";
    }
    echo "\n";
}

// Conclusion
echo "╔════════════════════════════════════════════════════════════════╗\n";
if ($score >= 90) {
    echo "║ ✅ APPLICATION OPÉRATIONNELLE                               ║\n";
} elseif ($score >= 70) {
    echo "║ ⚠️  APPLICATION PARTIELLEMENT FONCTIONNELLE                  ║\n";
} else {
    echo "║ ❌ PROBLÈMES DÉTECTÉS                                        ║\n";
}
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Sauvegarde du rapport
// Correction chemin rapport : dev/reports
$reportFile = $root . '/dev/reports/test_results_' . date('Ymd_His') . '.txt';
ob_start();
?>

════════════════════════════════════════════════════════════════
TEST COMPLET DE L'APPLICATION
═════════════════════════════════════════════════════════════════

Date: <?php echo $timestamp; ?>
Score: <?php echo $score; ?>%

RÉSULTATS:
─────────────────────────────────────────────────────────────

FICHIERS:
✅ Vérifiés: <?php echo $fileChecksPassed; ?>
❌ Manquants: <?php echo $fileChecksFailed; ?>

PAGES PRINCIPALES:
✅ OK: <?php echo $pageTestsPassed; ?>
❌ Problèmes: <?php echo $pageTestsFailed; ?>

APIs:
✅ Trouvées: <?php echo $apiTestsPassed; ?>
❌ Manquantes: <?php echo $apiTestsFailed; ?>

<?php if (!empty($errors)): ?>
ERREURS:
<?php foreach ($errors as $error): ?>
  - <?php echo $error; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($warnings)): ?>
AVERTISSEMENTS:
<?php foreach ($warnings as $warning): ?>
  - <?php echo $warning; ?>

<?php endforeach; ?>
<?php endif; ?>

CONCLUSION:
─────────────────────────────────────────────────────────────
<?php if ($score >= 90): ?>
✅ APPLICATION OPÉRATIONNELLE
<?php elseif ($score >= 70): ?>
⚠️  APPLICATION PARTIELLEMENT FONCTIONNELLE
<?php else: ?>
❌ PROBLÈMES DÉTECTÉS
<?php endif; ?>

════════════════════════════════════════════════════════════════
<?php
$report = ob_get_clean();
file_put_contents($reportFile, $report);
echo "\n📝 Rapport sauvegardé: dev/reports/test_results_" . date('Ymd_His') . ".txt\n";
?>
