<?php
/**
 * Clear session script - visitez cette page pour nettoyer la session
 */
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Session nettoyée</title>
</head>
<body>
    <h1>✓ Session nettoyée</h1>
    <p>Vous pouvez maintenant vous <a href="login.php">reconnecter</a>.</p>
</body>
</html>
