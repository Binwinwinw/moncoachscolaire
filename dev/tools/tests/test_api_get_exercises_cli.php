<?php
// Simule un appel à api/get_exercises.php depuis CLI
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'exercises';
$_GET['level'] = '6ème';
$_GET['subject'] = 'Mathématiques';

require_once __DIR__ . '/../api/get_exercises.php';
