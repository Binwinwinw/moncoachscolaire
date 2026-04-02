<?php
/**
 * Script de vérification de la base de données de production
 * À exécuter sur le serveur pour vérifier que tout fonctionne
 */

// Charger le .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Enlever les guillemets si présents
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');
$db = getenv('DB_DATABASE');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Vérification Base de Données Production</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔍 Vérification Base de Données Production</h1>
    
    <h2>Configuration</h2>
    <ul>
        <li><strong>Host:</strong> <?php echo htmlspecialchars($host); ?></li>
        <li><strong>User:</strong> <?php echo htmlspecialchars($user); ?></li>
        <li><strong>Database:</strong> <?php echo htmlspecialchars($db); ?></li>
    </ul>
    
    <h2>Test de Connexion</h2>
    <?php
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query("SELECT 1");
        echo '<p class="success">✅ Connexion réussie !</p>';
        
        // Lister les tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo '<h2>Tables trouvées: ' . count($tables) . '</h2>';
        
        if (!empty($tables)) {
            echo '<table>';
            echo '<tr><th>Table</th><th>Lignes</th></tr>';
            foreach ($tables as $table) {
                $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                echo '<tr>';
                echo '<td>' . htmlspecialchars($table) . '</td>';
                echo '<td>' . $count . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // Vérifier spécifiquement la table Exercises
            if (in_array('Exercises', $tables)) {
                echo '<h2>📚 Vérification Table Exercises</h2>';
                $exercises = $pdo->query("SELECT COUNT(*) FROM Exercises")->fetchColumn();
                echo '<p class="info">Nombre d\'exercices: <strong>' . $exercises . '</strong></p>';
                
                // Afficher quelques exercices par niveau
                $levels = $pdo->query("SELECT DISTINCT Level FROM Exercises ORDER BY Level")->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($levels)) {
                    echo '<h3>Exercices par niveau:</h3>';
                    echo '<ul>';
                    foreach ($levels as $level) {
                        $count = $pdo->query("SELECT COUNT(*) FROM Exercises WHERE Level = ?", [$level])->fetchColumn();
                        echo '<li>' . htmlspecialchars($level) . ': ' . $count . ' exercice(s)</li>';
                    }
                    echo '</ul>';
                }
            }
        }
        
    } catch (PDOException $e) {
        echo '<p class="error">❌ Erreur de connexion: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>💡 Vérifiez le fichier .env sur le serveur avec les bonnes informations.</p>';
    }
    ?>
    
    <hr>
    <p><small>⚠️ Supprimez ce fichier après vérification pour des raisons de sécurité.</small></p>
</body>
</html>
