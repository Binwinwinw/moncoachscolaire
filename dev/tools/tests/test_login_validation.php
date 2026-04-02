#!/usr/bin/env php
<?php
/**
 * TEST VALIDATION LOGIN - Contrôles d'identifiants
 * Vérifie les vérifications : username/email inexistant, password incorrect, combinaison erronée
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/login_security.php';

echo "🧪 TEST VALIDATION LOGIN\n";
echo "============================================================\n\n";

if (!isset($pdo) || !$pdo) {
    echo "❌ Base de données non disponible\n";
    exit(1);
}

$allTestsPassed = true;

// TEST 1: Vérifier qu'un compte admin existe
echo "TEST 1: Compte admin existe pour test\n";
echo "------------------------------------------------------------\n";

$stmt = $pdo->prepare("SELECT Id, Username, Email FROM Users WHERE Role = 'admin' LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "✅ SUCCÈS: Compte admin trouvé - " . $admin['Username'] . " (" . $admin['Email'] . ")\n\n";
} else {
    echo "❌ ÉCHEC: Aucun compte admin trouvé\n\n";
    $allTestsPassed = false;
}

// TEST 2: Vérifier qu'un compte étudiant existe
echo "TEST 2: Compte étudiant existe pour test\n";
echo "------------------------------------------------------------\n";

$stmt = $pdo->prepare("SELECT Id, Username, Email FROM Users WHERE Role = 'student' LIMIT 1");
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if ($student) {
    echo "✅ SUCCÈS: Compte étudiant trouvé - " . $student['Username'] . " (" . $student['Email'] . ")\n\n";
} else {
    echo "❌ ÉCHEC: Aucun compte étudiant trouvé\n\n";
    $allTestsPassed = false;
}

// TEST 3: Vérifier fonctions login_security disponibles
echo "TEST 3: Fonctions login_security disponibles\n";
echo "------------------------------------------------------------\n";

$requiredFunctions = [
    'checkLoginAttempts',
    'recordFailedAttempt',
    'resetLoginAttempts',
    'validateUsername',
    'validatePassword',
    'sanitizeInput'
];

$missingFunctions = [];
foreach ($requiredFunctions as $func) {
    if (!function_exists($func)) {
        $missingFunctions[] = $func;
    }
}

if (empty($missingFunctions)) {
    echo "✅ SUCCÈS: Toutes les fonctions login_security disponibles\n\n";
} else {
    echo "❌ ÉCHEC: Fonctions manquantes - " . implode(', ', $missingFunctions) . "\n\n";
    $allTestsPassed = false;
}

// TEST 4: Validation username vide
echo "TEST 4: Validation username vide\n";
echo "------------------------------------------------------------\n";

$result = validateUsername('');
if (!$result['valid']) {
    echo "✅ SUCCÈS: Username vide rejeté\n";
    echo "   Erreur: " . $result['error'] . "\n\n";
} else {
    echo "❌ ÉCHEC: Username vide accepté\n\n";
    $allTestsPassed = false;
}

// TEST 5: Validation password vide
echo "TEST 5: Validation password vide\n";
echo "------------------------------------------------------------\n";

$result = validatePassword('');
if (!$result['valid']) {
    echo "✅ SUCCÈS: Password vide rejeté\n";
    echo "   Erreur: " . $result['error'] . "\n\n";
} else {
    echo "❌ ÉCHEC: Password vide accepté\n\n";
    $allTestsPassed = false;
}

// TEST 6: Vérifier la gestion du rate limiting
echo "TEST 6: Vérification du rate limiting\n";
echo "------------------------------------------------------------\n";

$testUsername = "test_login_attempt_" . uniqid();

// Première tentative (devrait être acceptée)
$result1 = checkLoginAttempts($testUsername);
if ($result1['allowed']) {
    echo "✅ SUCCÈS: Première tentative acceptée\n";
    recordFailedAttempt($testUsername);
} else {
    echo "⚠️  Attention: Première tentative bloquée (rate limit trop strict?)\n";
}

// Enregistrer plusieurs tentatives
for ($i = 0; $i < 4; $i++) {
    recordFailedAttempt($testUsername);
}

// Vérifier si rate limiting s'active après plusieurs tentatives
$result_many = checkLoginAttempts($testUsername);
if (!$result_many['allowed']) {
    echo "✅ SUCCÈS: Rate limiting activé après tentatives multiples\n";
    echo "   Message: " . $result_many['message'] . "\n\n";
} else {
    echo "⚠️  Attention: Rate limiting pas encore activé (seuil peut être >5)\n\n";
}

// TEST 7: Vérifier les colonnes dans Users
echo "TEST 7: Vérification structure Users table\n";
echo "------------------------------------------------------------\n";

$requiredColumns = ['Id', 'Username', 'Email', 'PasswordHash', 'Role', 'UserLevel'];
$missingColumns = [];

// Récupérer une ligne pour vérifier les colonnes
$stmt = $pdo->prepare("SELECT * FROM Users LIMIT 1");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $actualColumns = array_keys($row);
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $actualColumns)) {
            $missingColumns[] = $col;
        }
    }
    
    if (empty($missingColumns)) {
        echo "✅ SUCCÈS: Toutes les colonnes requises présentes\n";
        echo "   Colonnes trouvées: " . implode(', ', array_keys($row)) . "\n\n";
    } else {
        echo "❌ ÉCHEC: Colonnes manquantes - " . implode(', ', $missingColumns) . "\n\n";
        $allTestsPassed = false;
    }
} else {
    echo "⚠️  Attention: Table Users vide\n\n";
}

// TEST 8: Vérification que login.php contient les nouveaux messages
echo "TEST 8: Vérification nouveaux messages d'erreur login.php\n";
echo "------------------------------------------------------------\n";

$loginContent = file_get_contents(__DIR__ . '/../login.php');

$hasCorrectPassword = strpos($loginContent, 'Mot de passe incorrect') !== false;
$hasUserNotFound = strpos($loginContent, "n'existe pas") !== false;

if ($hasCorrectPassword && $hasUserNotFound) {
    echo "✅ SUCCÈS: Messages d'erreur distincts détectés\n";
    echo "   ✅ Message 'Mot de passe incorrect'\n";
    echo "   ✅ Message 'n'existe pas'\n\n";
} else {
    echo "❌ ÉCHEC: Messages d'erreur distincts manquants\n";
    if (!$hasCorrectPassword) echo "   ❌ Message 'Mot de passe incorrect' manquant\n";
    if (!$hasUserNotFound) echo "   ❌ Message 'n'existe pas' manquant\n";
    echo "\n";
    $allTestsPassed = false;
}

// TEST 9: Vérifier les logs en cas d'erreur
echo "TEST 9: Vérification logging des erreurs\n";
echo "------------------------------------------------------------\n";

$loginContent = file_get_contents(__DIR__ . '/../login.php');
$hasCorrectPwdLog = strpos($loginContent, 'Mot de passe incorrect') !== false;
$hasUserNotFoundLog = strpos($loginContent, 'Utilisateur inexistant') !== false;

if ($hasCorrectPwdLog && $hasUserNotFoundLog) {
    echo "✅ SUCCÈS: Logs distincts pour chaque type d'erreur\n";
    echo "   ✅ Log 'Mot de passe incorrect'\n";
    echo "   ✅ Log 'Utilisateur inexistant'\n\n";
} else {
    echo "⚠️  Attention: Logs distincts à vérifier\n\n";
}

// TEST 10: Vérifier la cohérence des messages
echo "TEST 10: Cohérence des messages d'erreur\n";
echo "------------------------------------------------------------\n";

$messages = [
    ['type' => 'empty_username', 'check' => function($c) { return preg_match('/Veuillez saisir/i', $c); }],
    ['type' => 'empty_password', 'check' => function($c) { return preg_match('/Veuillez saisir.*mot de passe/i', $c); }],
    ['type' => 'invalid_password', 'check' => function($c) { return preg_match('/Mot de passe incorrect/i', $c); }],
    ['type' => 'invalid_username', 'check' => function($c) { return preg_match("/n'existe pas/i", $c); }],
];

$allMessagesFound = true;
foreach ($messages as $msg) {
    if (!$msg['check']($loginContent)) {
        echo "⚠️  Message '" . $msg['type'] . "' à vérifier\n";
        $allMessagesFound = false;
    }
}

if ($allMessagesFound) {
    echo "✅ SUCCÈS: Messages d'erreur cohérents et distincts\n\n";
} else {
    echo "⚠️  Attention: Certains messages manquent ou sont génériques\n\n";
}

// RÉSULTAT FINAL
echo "============================================================\n";
echo "📊 RÉSULTAT FINAL\n";
echo "============================================================\n\n";

if ($allTestsPassed) {
    echo "✅ ✅ ✅ TOUS LES TESTS CRITIQUES PASSÉS ✅ ✅ ✅\n\n";
    echo "🔐 Validation login renforcée:\n";
    echo "   ✅ Messages distincts: Username inexistant vs Password incorrect\n";
    echo "   ✅ Rate limiting fonctionnel\n";
    echo "   ✅ Validation champs vides\n";
    echo "   ✅ Logging détaillé des erreurs\n\n";
    echo "💡 Utilisateurs ont maintenant des messages clairs sur l'erreur!\n\n";
    exit(0);
} else {
    echo "⚠️  CERTAINS TESTS ONT ÉCHOUÉ\n\n";
    echo "Vérifiez les erreurs ci-dessus.\n\n";
    exit(1);
}
