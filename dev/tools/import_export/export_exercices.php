<?php
// export_exercices.php
// Exporte la table 'exercices' en SQL et XML dans backups/exercices_20260112/

$backupDir = __DIR__ . '/backups/exercices_20260112';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier la présence de la table
    $tables = $pdo->query("SHOW TABLES LIKE 'exercices'")->fetchAll();
    if (count($tables) === 0) {
        echo "Table 'exercices' introuvable.\n";
        exit(1);
    }

    // Vérifier le nombre de lignes
    $count = $pdo->query('SELECT COUNT(*) FROM exercices')->fetchColumn();
    echo "Nombre de lignes dans 'exercices' : $count\n";
    if ($count == 0) {
        echo "Aucune donnée à exporter.\n";
        exit(0);
    }

    // Export XML
    $res = $pdo->query('SELECT * FROM exercices');
    $xml = new SimpleXMLElement('<exercices/>' );
    foreach($res as $row){
        $ex = $xml->addChild('exercice');
        foreach($row as $k=>$v){
            $ex->addChild($k, htmlspecialchars($v));
        }
    }
    $xmlFile = $backupDir . '/exercices_backup_20260112.xml';
    $xml->asXML($xmlFile);
    echo "Export XML : $xmlFile\n";

    // Export SQL (structure + données)
    $sqlFile = $backupDir . '/exercices_backup_20260112.sql';
    $cmd = '"C:\xampp\mysql\bin\mysqldump.exe" -u root moncoachscolaire exercices > "' . $sqlFile . '"';
    system($cmd, $ret);
    if ($ret === 0) {
        echo "Export SQL : $sqlFile\n";
    } else {
        echo "Erreur export SQL (mysqldump).\n";
    }

} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage() . "\n";
    exit(1);
}
