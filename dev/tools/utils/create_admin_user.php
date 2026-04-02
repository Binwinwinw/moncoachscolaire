<?php
/**
 * Script pour créer un compte administrateur
 * Usage: php create_admin_user.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

if (!$pdo) {
    die("Erreur: Impossible de se connecter à la base de données\n");
}

echo "=== Création d'un compte administrateur ===\n\n";

// Demander les informations
echo "Nom d'utilisateur: ";
$username = trim(fgets(STDIN));

if (empty($username)) {
    die("Erreur: Le nom d'utilisateur est requis\n");
}

echo "Email: ";
$email = trim(fgets(STDIN));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Erreur: Email invalide\n");
}

echo "Mot de passe: ";
$password = trim(fgets(STDIN));

if (empty($password) || strlen($password) < 6) {
    die("Erreur: Le mot de passe doit contenir au moins 6 caractères\n");
}

echo "Niveau (6ème, 5ème, 4ème, 3ème, Seconde, Première, Terminale, BAC) [6ème]: ";
$level = trim(fgets(STDIN));
if (empty($level)) {
    $level = '6ème';
}

// Vérifier si l'utilisateur existe déjà
$checkStmt = $pdo->prepare('SELECT Id, Role FROM Users WHERE Username = ? OR Email = ? LIMIT 1');
$checkStmt->execute([$username, $email]);
$existing = $checkStmt->fetch();

if ($existing) {
    echo "\nUtilisateur existant trouvé (ID: {$existing['Id']}, Rôle: {$existing['Role']})\n";
    echo "Voulez-vous le promouvoir admin ? (o/n): ";
    $confirm = trim(fgets(STDIN));
    
    if (strtolower($confirm) === 'o') {
        $updateStmt = $pdo->prepare('UPDATE Users SET Role = ? WHERE Id = ?');
        $updateStmt->execute(['admin', $existing['Id']]);
        echo "✅ Utilisateur promu administrateur avec succès !\n";
        exit(0);
    } else {
        echo "Annulé.\n";
        exit(0);
    }
}

// Hasher le mot de passe
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Créer l'utilisateur admin
try {
    $stmt = $pdo->prepare("
        INSERT INTO Users (Username, Email, PasswordHash, Role, UserLevel)
        VALUES (?, ?, ?, 'admin', ?)
    ");
    
    $stmt->execute([$username, $email, $passwordHash, $level]);
    $userId = $pdo->lastInsertId();
    
    echo "\n✅ Compte administrateur créé avec succès !\n";
    echo "   ID: {$userId}\n";
    echo "   Username: {$username}\n";
    echo "   Email: {$email}\n";
    echo "   Rôle: admin\n";
    echo "   Niveau: {$level}\n\n";
    echo "Vous pouvez maintenant vous connecter avec ces identifiants.\n";
    
} catch (PDOException $e) {
    echo "\n❌ Erreur lors de la création: " . $e->getMessage() . "\n";
    exit(1);
}
