<?php
// Audit structure BDD MonCoachScolaire
$host = 'localhost';
$db   = 'moncoachscolaire';
$user = 'root'; // adapte si besoin
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $tables = [];
    foreach ($pdo->query("SHOW TABLES") as $row) {
        $table = $row[0];
        $cols = [];
        foreach ($pdo->query("DESCRIBE `$table`") as $col) {
            $cols[] = [
                'Field' => $col['Field'],
                'Type' => $col['Type'],
                'Null' => $col['Null'],
                'Key' => $col['Key'],
                'Default' => $col['Default'],
                'Extra' => $col['Extra']
            ];
        }
        $tables[$table] = $cols;
    }
    $json = json_encode($tables, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    // Écrire dans un fichier en plus de l’affichage direct
    file_put_contents(__DIR__ . '/audit_db.json', $json);
    header('Content-Type: application/json; charset=utf-8');
    echo $json;
} catch (Exception $e) {
    http_response_code(500);
    echo 'Erreur connexion ou requête : ' . $e->getMessage();
}
