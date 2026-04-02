<?php
// Script de test d'inscription automatique (élève)
// Usage : php dev/tests/test_register_student.php

$registerUrl = 'http://localhost/moncoachscolaire/public/index.php?page=register';

// Générer un username unique pour éviter les collisions
$username = 'testuser_' . uniqid();
$password = 'TestPassword123';
$age = 15;
$classe = '3eme';

$postFields = http_build_query([
    'form_type' => 'student',
    'username' => $username,
    'password' => $password,
    'age' => $age,
    'classe' => $classe
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $registerUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_HEADER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Afficher le résultat brut
file_put_contents('dev/tests/test_register_student_result.txt', $response);
echo "Test inscription élève :\n";
echo "Username : $username\n";
echo "Password : $password\n";
echo "Classe : $classe\n";
echo "HTTP code : $httpCode\n";
echo "Voir le fichier dev/tests/test_register_student_result.txt pour la réponse complète.\n";
