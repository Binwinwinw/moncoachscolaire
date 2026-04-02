<?php
// dev/tools/debug/simulate_student_login.php
// Simule la connexion d'un élève pour chaque niveau scolaire et trace la détection du niveau côté session, API et JS

session_start();

$levels = [
    '6eme', '5eme', '4eme', '3eme',
    '2nde', '1ere', 'Terminale'
];

$results = [];

foreach ($levels as $level) {
    // Simuler la connexion : remplir la session comme le ferait login.php
    $_SESSION['user_id'] = 9999;
    $_SESSION['user_name'] = 'Test Élève';
    $_SESSION['user_level'] = $level;
    $_SESSION['user_role'] = 'student';
    $_SESSION['logged_in'] = true;

    // Simuler l'appel à l'API get_exercises.php?action=subjects&level=...
    $api_url = 'http://localhost:8000/src/api/get_exercises.php?action=subjects&level=' . urlencode($level);
    $api_response = @file_get_contents($api_url);
    $api_json = $api_response ? json_decode($api_response, true) : null;

    // Résumé du test
    $results[] = [
        'level' => $level,
        'session_user_level' => $_SESSION['user_level'],
        'api_url' => $api_url,
        'api_success' => $api_json['success'] ?? false,
        'subjects_count' => isset($api_json['subjects']) ? count($api_json['subjects']) : 0,
        'subjects' => $api_json['subjects'] ?? [],
        'api_error' => $api_json['error'] ?? null
    ];

    // Nettoyer la session pour le prochain test
    session_unset();
}

// Affichage du rapport
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
