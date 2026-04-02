<?php
require_once __DIR__ . '/../../../../src/includes/level_access.php';

session_start();

echo "TEST: Level access helper\n";
$cases = [
    ['user' => '5ème', 'required' => '4ème', 'expect' => true],
    ['user' => '5ème', 'required' => '5ème', 'expect' => true],
    ['user' => '5ème', 'required' => '6ème', 'expect' => false],
    ['user' => 'Seconde', 'required' => '3ème', 'expect' => true],
    ['user' => 'Seconde', 'required' => 'Terminale', 'expect' => false],
    ['user' => 'CP', 'required' => 'CP', 'expect' => true],
    ['user' => 'CP', 'required' => 'CM2', 'expect' => false]
];

foreach ($cases as $c) {
    $_SESSION['user_level'] = $c['user'];
    unset($_SESSION['user_level_order']);
    unset($_SESSION['is_demo']);
    $res = can_current_user_access_level($c['required']);
    echo sprintf("User=%s Required=%s => %s (expect %s)\n", $c['user'], $c['required'], $res ? 'ALLOWED' : 'DENIED', $c['expect'] ? 'ALLOWED' : 'DENIED');
}

// Test demo user can access all levels
$_SESSION = []; // clear session
$_SESSION['is_demo'] = true;
$demo_cases = [
    ['user' => '', 'required' => '6ème'],
    ['user' => '', 'required' => 'Terminale'],
    ['user' => '5ème', 'required' => 'Terminale']
];
foreach ($demo_cases as $c) {
    $_SESSION['user_level'] = $c['user'];
    unset($_SESSION['user_level_order']);
    $res = can_current_user_access_level($c['required']);
    echo sprintf("DEMO User=%s Required=%s => %s (expect ALLOWED)\n", $c['user'] ?: '(none)', $c['required'], $res ? 'ALLOWED' : 'DENIED');
}

?>
