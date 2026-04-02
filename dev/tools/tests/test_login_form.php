<?php
// Page de debug pour capturer le POST login
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charger la config pour avoir la session configurée
require_once __DIR__ . '/../src/config/config.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logger la requête
$logFile = __DIR__ . '/../login_debug.log';
$timestamp = date('Y-m-d H:i:s');

$logData = [
    'timestamp' => $timestamp,
    'method' => $_SERVER['REQUEST_METHOD'],
    'session_id' => session_id(),
    'cookie_path' => ini_get('session.cookie_path'),
    'post_data' => $_POST,
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE,
];

file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        form { background: white; padding: 20px; border: 1px solid #ddd; max-width: 400px; margin: 20px 0; }
        input, button { display: block; width: 100%; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔍 Test Login Debug</h1>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <h2 class="<?php echo !empty($_POST) ? 'success' : 'error'; ?>">
            POST reçu !
        </h2>
        <pre><?php print_r($_POST); ?></pre>
    <?php endif; ?>
    
    <h2>Configuration Session</h2>
    <pre>
Session ID: <?php echo session_id(); ?>

Cookie Path: <?php echo ini_get('session.cookie_path'); ?>

Cookie Lifetime: <?php echo ini_get('session.cookie_lifetime'); ?>

Session Data:
<?php print_r($_SESSION); ?>

Cookies:
<?php print_r($_COOKIE); ?>
    </pre>
    
    <h2>Formulaire de test</h2>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        
        <label>Username:</label>
        <input type="text" name="username" value="admin" required>
        
        <label>Password:</label>
        <input type="password" name="password" value="admin123" required>
        
        <button type="submit">Test Login</button>
    </form>
    
    <h2>Logs (voir login_debug.log)</h2>
    <pre><?php
    if (file_exists($logFile)) {
        echo htmlspecialchars(file_get_contents($logFile));
    } else {
        echo "Aucun log encore";
    }
    ?></pre>
    
    <p><a href="../public/index.php?page=login">Aller au vrai formulaire de login</a></p>
</body>
</html>
