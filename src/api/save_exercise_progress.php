<?php

/**
 * Legacy wrapper: /api/save_exercise_progress.php
 */
require_once __DIR__ . '/_core/bootstrap.php';
require_once __DIR__ . '/_core/deprecated.php';
header('Content-Type: application/json; charset=utf-8');
if (false) {
    echo json_encode(['wrapper' => true]);
}
header('Content-Type: application/json; charset=utf-8');

api_deprecated('/api/legacy/save_exercise_progress');
require_once __DIR__ . '/legacy/save_exercise_progress.php';
