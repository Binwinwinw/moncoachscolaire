<?php
/**
 * export_schema_exercises.php — MonCoachScolaire
 * Exporte le schéma officiel de la table `exercises` (MySQL) en JSON structuré + TXT lisible.
 * Usage : php export_schema_exercises.php
 */

// 1. Connexion BDD
require_once __DIR__ . '/../../../db/connection.php';
if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "❌ Connexion à la base de données impossible.\n");
    exit(1);
}

// 2. Récupérer le schéma DESCRIBE + SHOW CREATE TABLE
$table = 'exercises';
$describe = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
$create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);

// 3. Colonnes (format JSON)
$columns = [];
$dateColumns = [];
foreach ($describe as $col) {
    $colObj = [
        'name' => $col['Field'],
        'type' => $col['Type'],
        'nullable' => ($col['Null'] === 'YES'),
        'key' => $col['Key'],
        'default' => $col['Default'],
        'extra' => $col['Extra'],
    ];
    $columns[] = $colObj;
    if (stripos($col['Type'], 'date') !== false || stripos($col['Type'], 'time') !== false) {
        $dateColumns[] = $col['Field'];
    }
}

// 4. Indexes (SHOW INDEX)
$indexes = [];
$indexRows = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
$indexMap = [];
foreach ($indexRows as $idx) {
    $name = $idx['Key_name'];
    if (!isset($indexMap[$name])) {
        $indexMap[$name] = [
            'name' => $name,
            'columns' => [],
            'unique' => ($idx['Non_unique'] == 0)
        ];
    }
    $indexMap[$name]['columns'][] = $idx['Column_name'];
}
$indexes = array_values($indexMap);

// 5. Foreign keys (INFORMATION_SCHEMA)
$foreignKeys = [];
try {
    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    $fkRows = $pdo->query("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '".$dbName."' AND TABLE_NAME = '$table' AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($fkRows as $fk) {
        $foreignKeys[] = [
            'constraint' => $fk['CONSTRAINT_NAME'],
            'column' => $fk['COLUMN_NAME'],
            'referenced_table' => $fk['REFERENCED_TABLE_NAME'],
            'referenced_column' => $fk['REFERENCED_COLUMN_NAME'],
        ];
    }
} catch (Exception $e) {}

// 6. Création du JSON final
$output = [
    'table_name' => $table,
    'extracted_at' => date('Y-m-d H:i:s'),
    'columns' => $columns,
    'indexes' => $indexes,
    'foreign_keys' => $foreignKeys,
    'create_statement' => $create['Create Table'] ?? null
];

// 7. Sauvegarde JSON
$outDir = __DIR__ . '/../../../dev/db/json/schema/exercices/';
if (!is_dir($outDir)) mkdir($outDir, 0775, true);
$jsonPath = $outDir . 'schema_officiel_bdd_exercises.json';
file_put_contents($jsonPath, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// 8. Génération TXT lisible
$txtPath = $outDir . 'schema_officiel_bdd_exercises.txt';
$txt = "Table: exercises\nExtraite le: ".$output['extracted_at']."\n\n";
$txt .= str_pad('Nom', 24) . str_pad('Type', 20) . str_pad('Null', 8) . str_pad('Key', 8) . str_pad('Default', 14) . "Extra\n";
$txt .= str_repeat('-', 80) . "\n";
foreach ($columns as $col) {
    $txt .= str_pad($col['name'], 24)
        . str_pad($col['type'], 20)
        . str_pad($col['nullable'] ? 'OUI' : 'NON', 8)
        . str_pad($col['key'], 8)
        . str_pad((string)$col['default'], 14)
        . $col['extra'] . "\n";
}
file_put_contents($txtPath, $txt);

// 9. Affichage console
printf("✅ %d colonnes détectées\n", count($columns));
if (count($dateColumns)) {
    printf("✅ Colonnes de type date/timestamp : %s\n", implode(', ', $dateColumns));
} else {
    echo "ℹ️  Aucune colonne de type date/timestamp\n";
}
printf("✅ Fichier JSON généré : %s\n", $jsonPath);
