<?php

/**
 * API Admin - Qualité des exercices
 * Endpoint: /api/admin/exercises/quality.php
 * Retourne des statistiques sur la qualité des exercices (par niveau, matière, alertes)
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/deprecated.php';

api_deprecated('/api/admin/exercise_quality');
json_error('Endpoint obsolète. Merci d’utiliser la nouvelle API.', 410, 'ERR_GONE');
exit;

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../database/connection.php';
require_once __DIR__ . '/../../../includes/admin_auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès refusé. Administrateur requis.']);
    exit;
}

// Filtres GET : level, subject
$level = isset($_GET['level']) ? trim($_GET['level']) : '';
$subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';

try {
    // Charger les exercices (table Exercises ou fichier JSON)
    $exercises = [];
    $jsonPath = __DIR__ . '/../../../../db/json/all_exercises_clean.json';
    if (file_exists($jsonPath)) {
        $json = file_get_contents($jsonPath);
        $exercises = json_decode($json, true);
    }
    if (!is_array($exercises)) {
        $exercises = [];
    }

    // Filtrage
    $filtered = array_filter($exercises, function ($ex) use ($level, $subject) {
        $ok = true;
        if ($level && isset($ex['Level'])) {
            $ok = $ok && ($ex['Level'] === $level);
        }
        if ($subject && isset($ex['Subject'])) {
            $ok = $ok && ($ex['Subject'] === $subject);
        }
        return $ok;
    });

    // Statistiques
    $total = count($filtered);
    $actifs = count(array_filter($filtered, fn($ex) => !empty($ex['is_active'])));
    $courtes = count(array_filter($filtered, fn($ex) => isset($ex['Answer']) && is_string($ex['Answer']) && mb_strlen(trim($ex['Answer'])) < 30 && mb_strlen(trim($ex['Answer'])) > 0));
    $vides = count(array_filter($filtered, fn($ex) => empty($ex['Content']) || empty($ex['Answer'])));

    // Réponse (min/moy/max)
    $answerLengths = array_map(fn($ex) => isset($ex['Answer']) ? mb_strlen(trim($ex['Answer'])) : 0, $filtered);
    $answerLengths = array_filter($answerLengths, fn($l) => $l > 0);
    $min = $answerLengths ? min($answerLengths) : 0;
    $max = $answerLengths ? max($answerLengths) : 0;
    $moy = $answerLengths ? round(array_sum($answerLengths) / count($answerLengths), 1) : 0;

    // Alertes (exercices avec placeholders, réponses vides, etc.)
    $alertes = array_filter($filtered, fn($ex) => strpos($ex['Content'] ?? '', '{{') !== false || empty($ex['Answer']));

    // Échantillon (max 10)
    $echantillon = array_slice($filtered, 0, 10);

    echo json_encode([
        'success' => true,
        'stats' => [
            'total' => $total,
            'actifs' => $actifs,
            'courtes' => $courtes,
            'vides' => $vides,
            'reponse' => [
                'min' => $min,
                'moy' => $moy,
                'max' => $max,
            ],
            'alertes' => count($alertes),
        ],
        'echantillon' => $echantillon,
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    json_error('Erreur serveur', 500, 'ERR_QUALITY');
}
