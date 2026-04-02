<?php

// src/api/subjects.php
// Retourne la liste des matières pour un ou plusieurs niveaux (GET param 'level', supports comma-separated)

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

api_require([
    'method' => 'GET',
]);

$levelsParam = $_GET['level'] ?? ($_GET['niveau'] ?? null);
if (!$levelsParam) {
    json_error('Paramètre level manquant', 400, 'ERR_VALIDATION');
}

$levels = array_map('trim', explode(',', $levelsParam));
$subjects = getSubjectsByLevels($levels);
if (!is_array($subjects)) {
    $subjects = [];
}

// Normalize/canonicalize subject labels (prefer accented forms if present)
function subject_key($s)
{
    $s = trim((string) $s);
    if ($s === '') {
        return '';
    }
    $k = mb_strtolower($s, 'UTF-8');
    $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $k);
    if ($trans !== false) {
        $k = $trans;
    }
    $k = preg_replace('/[^a-z0-9]+/', '', $k);
    return $k;
}

$canon = [];
foreach ($subjects as $s) {
    $k = subject_key($s);
    if ($k === '') {
        continue;
    }
    if (!isset($canon[$k]) || strlen($s) > strlen($canon[$k])) {
        $canon[$k] = $s;
    }
}

$final = array_values($canon);
sort($final, SORT_NATURAL | SORT_FLAG_CASE);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['subjects' => $final], JSON_UNESCAPED_UNICODE);
