<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['action'] = 'exercises';
$_GET['level'] = '6ème';
$_GET['subject'] = 'Français';

require_once __DIR__ . '/../api/get_exercises.php';
