<?php
// Script PHP pour détecter et supprimer les doublons d'identifier dans la table exercises (base locale XAMPP)
// Usage : placer ce script dans un dossier sécurisé, adapter les accès si besoin




// Inclusion explicite du wrapper de connexion (chemin absolu)
$db_connection_wrapper = 'd:/Hostinger/public_html/moncoachscolaire/db/connection.php';
if (is_file($db_connection_wrapper)) {
    require_once $db_connection_wrapper;
} else {
    die("Fichier de connexion à la base de données introuvable : $db_connection_wrapper\n");
}

if (!$pdo) {
    die('Connexion à la base de données impossible.');
}

$table = 'exercises';

// 1. Détection des doublons
$sql = "SELECT Identifier, COUNT(*) as nb FROM $table GROUP BY Identifier HAVING nb > 1";
$stmt = $pdo->query($sql);
$doublons = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($doublons)) {
    echo "Aucun doublon trouvé sur le champ Identifier.\n";
    exit;
}

// 2. Suppression des doublons (on garde le plus ancien id)
foreach ($doublons as $row) {
    $identifier = $row['Identifier'];
    // Récupérer tous les id pour cet identifier, triés par id croissant
    $ids = $pdo->query("SELECT id FROM $table WHERE Identifier = " . $pdo->quote($identifier) . " ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
    // On garde le premier, on supprime les suivants
    $ids_to_delete = array_slice($ids, 1);
    if (!empty($ids_to_delete)) {
        $in = implode(',', array_map('intval', $ids_to_delete));
        $pdo->exec("DELETE FROM $table WHERE id IN ($in)");
        echo "Doublon supprimé pour Identifier=$identifier (ids supprimés : $in)\n";
    }
}

echo "Nettoyage terminé.\n";
