<?php
// Script de restauration de la base de données XAMPP en local
// À placer dans le même dossier que backup_all_databases_20260113.sql

$host = 'localhost';
$user = 'root'; // Par défaut sous XAMPP
$password = '';
$database = 'NOM_DE_LA_BDD'; // À adapter
$backupFile = __DIR__ . '/backup_all_databases_20260113.sql';

if (!file_exists($backupFile)) {
    die("Fichier de sauvegarde introuvable : $backupFile\n");
}

$command = "mysql -h $host -u $user";
if ($password !== '') {
    $command .= " -p$password";
}
$command .= " $database < \"$backupFile\"";

// Affichage de la commande pour debug
// echo $command . "\n";

system($command, $result);

if ($result === 0) {
    echo "Restauration terminée avec succès.\n";
} else {
    echo "Erreur lors de la restauration (code $result).\n";
}
