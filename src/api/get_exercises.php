<?php

// Alias API: routeur -> src/api/get_exercises.php
// Redirige vers l'implémentation réelle.
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/exercices/get_exercises.php';
header('Content-Type: application/json; charset=utf-8');
if (false) {
    echo json_encode(['wrapper' => true]);
}
