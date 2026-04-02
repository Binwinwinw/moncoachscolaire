<?php
/**
 * Fix production environment detection
 * À uploader sur Hostinger pour forcer APP_ENV=production automatiquement
 */

// Déterminer l'environnement basé sur le hostname
$hostname = $_SERVER['HTTP_HOST'] ?? '';
$isProduction = (strpos($hostname, 'moncoachscolaire.fr') !== false && strpos($hostname, 'localhost') === false);

// Définir APP_ENV avant de charger les fichiers
if ($isProduction && !getenv('APP_ENV')) {
    putenv('APP_ENV=production');
}

echo "✅ Environnement forcé à: " . getenv('APP_ENV') . "\n";
echo "Hostname détecté: $hostname\n";
echo "Is Production: " . ($isProduction ? 'OUI' : 'NON') . "\n";
?>
