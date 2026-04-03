<?php
/**
 * Script de test pour vérifier l'accessibilité des vidéos de la mascotte
 * Usage: Ouvrir dans le navigateur ou exécuter en ligne de commande
 */

require_once __DIR__ . '/../config.php';

// Charger site_boot.php pour avoir asset_url()
if (file_exists(__DIR__ . '/../site_boot.php')) {
    require_once __DIR__ . '/../site_boot.php';
}

// Détecter baseUrl
$baseUrl = '';
if (function_exists('detectBaseUrl')) {
    $baseUrl = detectBaseUrl();
} else {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        $baseUrl = '/moncoachscolaire';
    } else {
        $baseUrl = '';
    }
}

$baseUrl = rtrim($baseUrl, '/');

// Liste des vidéos à tester
$videos = [
    'colibri-realiste_volant',
    'colibri-cartoon_volant',
    'colibricartoonvolantbackground',
    'cartoondebutderxercice',
    'cartoonreponsecorrecte',
    'realistecelebration',
    'realisteencourager',
    'realistefelicitations'
];

$videoDir = __DIR__ . '/../assets/img/coach/optimized';
$results = [];

foreach ($videos as $video) {
    $mp4Path = $videoDir . '/' . $video . '.mp4';
    $webmPath = $videoDir . '/' . $video . '.webm';
    
    $mp4Exists = file_exists($mp4Path);
    $webmExists = file_exists($webmPath);
    
    $mp4Size = $mp4Exists ? filesize($mp4Path) : 0;
    $webmSize = $webmExists ? filesize($webmPath) : 0;
    
    $mp4Readable = $mp4Exists ? is_readable($mp4Path) : false;
    $webmReadable = $webmExists ? is_readable($webmPath) : false;
    
    // Construire les URLs
    $mp4Url = $baseUrl . '/assets/img/coach/optimized/' . $video . '.mp4';
    $webmUrl = $baseUrl . '/assets/img/coach/optimized/' . $video . '.webm';
    
    $results[$video] = [
        'mp4' => [
            'exists' => $mp4Exists,
            'readable' => $mp4Readable,
            'size' => $mp4Size,
            'size_mb' => round($mp4Size / 1024 / 1024, 2),
            'url' => $mp4Url
        ],
        'webm' => [
            'exists' => $webmExists,
            'readable' => $webmReadable,
            'size' => $webmSize,
            'size_mb' => round($webmSize / 1024 / 1024, 2),
            'url' => $webmUrl
        ]
    ];
}

// Afficher les résultats
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Accessibilité Vidéos Mascotte</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; }
        .video-test { margin: 20px 0; padding: 15px; border: 1px solid #e5e7eb; border-radius: 8px; }
        .video-name { font-weight: bold; font-size: 1.2em; color: #1e293b; margin-bottom: 10px; }
        .format { margin: 10px 0; padding: 10px; background: #f8fafc; border-radius: 4px; }
        .format-name { font-weight: bold; color: #475569; }
        .status { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; margin-left: 10px; }
        .status.exists { background: #d1fae5; color: #065f46; }
        .status.missing { background: #fee2e2; color: #991b1b; }
        .status.readable { background: #dbeafe; color: #1e40af; }
        .status.not-readable { background: #fef3c7; color: #92400e; }
        .url { font-family: monospace; font-size: 0.9em; color: #64748b; margin-top: 5px; word-break: break-all; }
        .size { color: #64748b; font-size: 0.9em; }
        .test-link { display: inline-block; margin-top: 5px; padding: 5px 10px; background: #2563eb; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9em; }
        .test-link:hover { background: #1d4ed8; }
        .summary { background: #f0f9ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .summary-item { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 Test Accessibilité Vidéos Mascotte</h1>
        
        <div class="summary">
            <h2>Résumé</h2>
            <div class="summary-item">
                <strong>Base URL détectée:</strong> <code><?php echo htmlspecialchars($baseUrl ?: '(racine)'); ?></code>
            </div>
            <div class="summary-item">
                <strong>Répertoire vidéos:</strong> <code><?php echo htmlspecialchars($videoDir); ?></code>
            </div>
            <div class="summary-item">
                <strong>Total vidéos testées:</strong> <?php echo count($videos); ?>
            </div>
        </div>
        
        <?php foreach ($results as $videoName => $formats): ?>
            <div class="video-test">
                <div class="video-name">📹 <?php echo htmlspecialchars($videoName); ?></div>
                
                <?php foreach (['mp4', 'webm'] as $format): ?>
                    <?php $info = $formats[$format]; ?>
                    <div class="format">
                        <div class="format-name">
                            <?php echo strtoupper($format); ?>
                            <?php if ($info['exists']): ?>
                                <span class="status exists">✓ Existe</span>
                            <?php else: ?>
                                <span class="status missing">✗ Manquant</span>
                            <?php endif; ?>
                            
                            <?php if ($info['exists']): ?>
                                <?php if ($info['readable']): ?>
                                    <span class="status readable">✓ Lisible</span>
                                <?php else: ?>
                                    <span class="status not-readable">✗ Non lisible</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($info['exists']): ?>
                            <div class="size">Taille: <?php echo $info['size_mb']; ?> MB (<?php echo number_format($info['size']); ?> octets)</div>
                        <?php endif; ?>
                        
                        <div class="url">URL: <a href="<?php echo htmlspecialchars($info['url']); ?>" target="_blank"><?php echo htmlspecialchars($info['url']); ?></a></div>
                        
                        <?php if ($info['exists'] && $info['readable']): ?>
                            <a href="<?php echo htmlspecialchars($info['url']); ?>" target="_blank" class="test-link">Tester la vidéo →</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <div>
            <h3>💡 Instructions</h3>
            <ul>
                <li>Vérifiez que tous les fichiers existent et sont lisibles</li>
                <li>Cliquez sur "Tester la vidéo" pour vérifier qu'elle se charge dans le navigateur</li>
                <li>Si une vidéo ne se charge pas, vérifiez les permissions du fichier (chmod 644)</li>
                <li>Vérifiez la console du navigateur (F12) pour voir les erreurs de chargement</li>
            </ul>
        </div>
    </div>
</body>
</html>

