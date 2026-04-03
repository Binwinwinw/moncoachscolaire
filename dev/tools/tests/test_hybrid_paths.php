<?php
/**
 * TEST HYBRIDE DES CHEMINS ASSET_URL()
 * Teste les deux environnements (local et production)
 */

// Récupérer l'environnement actuel
$currentEnv = isset($_GET['env']) ? $_GET['env'] : 'detect';
$projectRoot = dirname(__DIR__);

// ============================================================================
// DÉTECTION AUTOMATIQUE DE L'ENVIRONNEMENT
// ============================================================================

function detectEnvironment($projectRoot) {
    // Vérifier le nom de domaine
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'unknown';
    
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return 'LOCAL';
    } elseif (strpos($host, 'moncoachscolaire.fr') !== false) {
        return 'PRODUCTION';
    } elseif (strpos($host, 'hostinger') !== false || strpos($host, 'u936396612') !== false) {
        return 'PRODUCTION';
    } else {
        return 'UNKNOWN';
    }
}

// Si env n'est pas spécifié, détecter
if ($currentEnv === 'detect') {
    $currentEnv = detectEnvironment($projectRoot);
}

// ============================================================================
// DÉFINITION DES CONFIGURATIONS
// ============================================================================

$configs = [
    'LOCAL' => [
        'name' => 'Local XAMPP',
        'baseUrl' => 'http://localhost/moncoachscolaire',
        'rootPath' => 'D:\\xampp\\htdocs\\moncoachscolaire',
        'assetFolder' => 'public',
        'description' => 'Développement local - Point d\'entrée via /moncoachscolaire/',
    ],
    'PRODUCTION' => [
        'name' => 'Production Hostinger',
        'baseUrl' => 'https://moncoachscolaire.fr',
        'rootPath' => '/home/u936396612/domains/moncoachscolaire.fr/public_html',
        'assetFolder' => 'public', // À déterminer !
        'description' => 'Production - Point d\'entrée à la racine du domaine',
    ],
];

// ============================================================================
// FICHIERS À TESTER
// ============================================================================

$filesToTest = [
    'CSS Files' => [
        'assets/css/style.css',
        'assets/css/session-timeout.css',
        'assets/css/colibri-mascot.css',
        'assets/css/pages/dashboard.css',
        'assets/css/pages/exercices.css',
    ],
    'JavaScript Files' => [
        'assets/js/interactive-exercises.js',
        'assets/js/coach-webm.js',
        'assets/js/colibri-mascot.js',
        'assets/js/admin-dashboard.js',
    ],
    'Image Files' => [
        'assets/images/logo.png',
        'assets/img/bac/logo-bac.png',
    ],
];

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Hybride des Chemins Assets</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        
        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }
        
        .env-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .env-button {
            padding: 12px 20px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .env-button:hover {
            border-color: #667eea;
            color: #667eea;
        }
        
        .env-button.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .env-info {
            background: #f8f9ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .env-info h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .env-info p {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
            font-family: monospace;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .file-group {
            margin-bottom: 25px;
        }
        
        .file-group-title {
            font-size: 15px;
            font-weight: 600;
            color: #555;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 3px;
            margin-bottom: 10px;
        }
        
        .file-row {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .file-path {
            flex: 1;
            min-width: 250px;
        }
        
        .file-path-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .file-path-value {
            font-family: monospace;
            font-size: 13px;
            color: #333;
            word-break: break-all;
            background: #f9f9f9;
            padding: 8px;
            border-radius: 3px;
            border-left: 3px solid #667eea;
        }
        
        .file-status {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .status-badge.ok {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.missing {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.unknown {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-note {
            font-size: 11px;
            color: #666;
            text-align: right;
        }
        
        .summary {
            background: #f8f9ff;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            color: #666;
            font-weight: 500;
        }
        
        .summary-value {
            color: #333;
            font-weight: 600;
            font-family: monospace;
        }
        
        .recommendation {
            background: #e8f4f8;
            border-left: 4px solid #17a2b8;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .recommendation h4 {
            color: #17a2b8;
            margin-bottom: 10px;
        }
        
        .recommendation p {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        
        .recommendation p:last-child {
            margin-bottom: 0;
        }
        
        .code-block {
            background: #f5f5f5;
            border-left: 3px solid #667eea;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
        }
        
        @media (max-width: 768px) {
            .file-row {
                flex-direction: column;
            }
            
            .file-status {
                align-items: flex-start;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 Test Hybride des Chemins Assets</h1>
        <p class="subtitle">Vérification de la compatibilité Local ↔ Production</p>
        
        <!-- Sélecteur d'environnement -->
        <div class="env-selector">
            <button class="env-button <?php echo $currentEnv === 'LOCAL' ? 'active' : ''; ?>" 
                    onclick="window.location.href='?env=LOCAL'">
                💻 Local (XAMPP)
            </button>
            <button class="env-button <?php echo $currentEnv === 'PRODUCTION' ? 'active' : ''; ?>" 
                    onclick="window.location.href='?env=PRODUCTION'">
                🌐 Production (Hostinger)
            </button>
        </div>
        
        <!-- Information de l'environnement sélectionné -->
        <div class="env-info">
            <h3>Environnement: <?php echo $configs[$currentEnv]['name']; ?></h3>
            <p><strong>Description:</strong> <?php echo $configs[$currentEnv]['description']; ?></p>
            <p><strong>Base URL:</strong> <?php echo $configs[$currentEnv]['baseUrl']; ?></p>
            <p><strong>Root Path:</strong> <?php echo $configs[$currentEnv]['rootPath']; ?></p>
            <p><strong>Asset Folder:</strong> <?php echo $configs[$currentEnv]['assetFolder']; ?></p>
        </div>
        
        <!-- Résumé -->
        <div class="summary">
            <div class="summary-row">
                <span class="summary-label">Environnement détecté:</span>
                <span class="summary-value"><?php echo detectEnvironment($projectRoot); ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Host actuel:</span>
                <span class="summary-value"><?php echo $_SERVER['HTTP_HOST'] ?? 'N/A'; ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Protocole:</span>
                <span class="summary-value"><?php echo !empty($_SERVER['HTTPS']) ? 'HTTPS' : 'HTTP'; ?></span>
            </div>
        </div>
        
        <!-- TESTS DES FICHIERS -->
        <?php foreach ($filesToTest as $category => $files): ?>
            <div class="section">
                <div class="section-title"><?php echo $category; ?></div>
                <div class="file-group">
                    <div class="file-group-title"><?php echo $category; ?> à tester</div>
                    
                    <?php foreach ($files as $file): ?>
                        <?php
                            // Construire l'URL locale
                            $assetFolder = $configs[$currentEnv]['assetFolder'];
                            $localURL = $configs[$currentEnv]['baseUrl'] . '/' . $assetFolder . '/' . $file;
                            
                            // Chemins physiques possibles
                            $rootPath = $configs[$currentEnv]['rootPath'];
                            $physicalPath1 = $rootPath . '/' . $assetFolder . '/' . $file;
                            $physicalPath2 = $rootPath . '/' . $file;
                            
                            // Déterminer le statut en local
                            $fileExists = false;
                            $actualPath = '';
                            if (is_file($physicalPath1)) {
                                $fileExists = true;
                                $actualPath = $physicalPath1;
                            } elseif (is_file($physicalPath2)) {
                                $fileExists = true;
                                $actualPath = $physicalPath2;
                            }
                            
                            $statusClass = $currentEnv === 'LOCAL' && $fileExists ? 'ok' : ($currentEnv === 'PRODUCTION' ? 'unknown' : 'missing');
                            $statusText = $fileExists ? '✅ Existe' : '❌ Manquant';
                            if ($statusClass === 'unknown') {
                                $statusText = '❓ À vérifier en prod';
                            }
                        ?>
                        <div class="file-row">
                            <div class="file-path">
                                <div class="file-path-label">Chemin dans le code:</div>
                                <div class="file-path-value"><?php echo $file; ?></div>
                                
                                <div class="file-path-label">URL générée:</div>
                                <div class="file-path-value"><?php echo $localURL; ?></div>
                            </div>
                            
                            <div class="file-status">
                                <div class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </div>
                                <?php if ($actualPath): ?>
                                    <div class="status-note"><?php echo basename($actualPath); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- RECOMMANDATIONS -->
        <div class="recommendation">
            <h4>⚡ Problème Identifié</h4>
            <p>
                <strong>En production (Hostinger):</strong> La fonction <code>asset_url()</code> génère des URL comme
                <code>https://moncoachscolaire.fr/public/assets/css/style.css</code>
            </p>
            <p>
                <strong>Mais le CSS ne charge pas!</strong> Cela signifie que les assets ne sont probablement pas au chemin
                <code>/public/assets/</code> en production.
            </p>
        </div>
        
        <div class="recommendation">
            <h4>✅ Solution Recommandée</h4>
            <p>
                Modifier la fonction <code>asset_url()</code> dans <code>src/config/config.php</code> pour:
            </p>
            <ol>
                <li>Vérifier d'abord si le fichier existe à <code>/public/assets/</code></li>
                <li>Si non trouvé, chercher à <code>/assets/</code> (racine)</li>
                <li>Cela rend l'app vraiment hybride ✅</li>
            </ol>
            <div class="code-block">
if (is_file($projectRoot . '/public/' . $path)) {
    return $base . '/public/' . $path;  // Local
} else {
    return $base . '/' . $path;  // Production
}
            </div>
        </div>
        
    </div>
</body>
</html>

