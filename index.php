<?php
// Redirige vers le nouveau routeur public/index.php (avec conservation de la query string)

$query = $_SERVER['QUERY_STRING'] ?? '';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($scriptDir === '.') {
    $scriptDir = '';
}

$target = ($scriptDir ? $scriptDir : '') . '/public/index.php';
if ($query !== '') {
    $target .= '?' . $query;
}

header('Location: ' . $target, true, 307);
exit;
