<?php
/**
 * dev/tools/tests/debug_exercise.php
 * Debug CLI pour inspecter un exercice via /api/get_exercises.php
 *
 * Usage:
 *   php dev/tools/tests/debug_exercise.php --baseUrl=http://localhost/moncoachscolaire --id=123 [--level="6ème"] [--subject="Mathématiques"]
 */

if (php_sapi_name() !== 'cli') {
    echo "❌ Ce script doit être exécuté en CLI." . PHP_EOL;
    exit(1);
}

$options = getopt('', ['baseUrl:', 'id:', 'level::', 'subject::', 'action::']);
$baseUrl = isset($options['baseUrl']) ? rtrim($options['baseUrl'], '/') : '';
$exerciseId = isset($options['id']) ? (int)$options['id'] : 0;
$level = $options['level'] ?? null;
$subject = $options['subject'] ?? null;
$action = $options['action'] ?? 'exercises';

if (empty($baseUrl) || $exerciseId <= 0) {
    echo "❌ Paramètres requis manquants." . PHP_EOL;
    echo "Usage: php dev/tools/tests/debug_exercise.php --baseUrl=http://localhost/moncoachscolaire --id=123 [--level=\"6ème\"] [--subject=\"Mathématiques\"]" . PHP_EOL;
    exit(1);
}

$endpoint = $baseUrl . '/api/get_exercises.php';

$query = [
    'action' => $action,
];

if ($action === 'exercise_html') {
    $query['id'] = $exerciseId;
}

if (!empty($level)) {
    $query['level'] = $level;
}
if (!empty($subject)) {
    $query['subject'] = $subject;
}

$url = $endpoint . '?' . http_build_query($query);

echo "🔗 Endpoint: $url" . PHP_EOL;

$result = fetchJson($url);
$json = $result['data'] ?? null;

if ($result['error'] ?? false) {
    echo "❌ JSON invalide ou réponse non parsable." . PHP_EOL;
    echo "HTTP_STATUS: " . ($result['status'] ?? 'UNKNOWN') . PHP_EOL;
    echo "Réponse brute (extrait): " . ($result['raw'] ?? '') . PHP_EOL;
    exit(1);
}

if (!$json || empty($json['success'])) {
    echo "❌ Réponse invalide ou échec API." . PHP_EOL;
    echo "Réponse brute: " . substr(json_encode($json, JSON_UNESCAPED_UNICODE), 0, 300) . PHP_EOL;
    exit(1);
}

if ($action === 'exercise_html') {
    $html = $json['html'] ?? '';
    $htmlPreview = mb_substr(trim($html), 0, 300);
    echo "✅ HTML récupéré (" . mb_strlen($html) . " chars)" . PHP_EOL;
    echo "HTML preview: " . $htmlPreview . PHP_EOL;
    exit(0);
}

$exercises = $json['exercises'] ?? [];
if (!is_array($exercises) || count($exercises) === 0) {
    echo "⚠️ Aucun exercice retourné." . PHP_EOL;
    exit(0);
}

$exercise = findExerciseById($exercises, $exerciseId);

if (!$exercise) {
    echo "⚠️ Exercice ID $exerciseId introuvable dans la réponse." . PHP_EOL;
    exit(0);
}

printExerciseDebug($exercise);

/**
 * Récupère un JSON via file_get_contents ou cURL.
 */
function fetchJson(string $url): array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => "Accept: application/json\r\n"
        ]
    ]);

    $raw = @file_get_contents($url, false, $context);
    $status = null;

    if (!empty($http_response_header[0])) {
        if (preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $status = (int)$matches[1];
        }
    }

    if ($raw === false) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Accept: application/json']
        ]);
        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    if (!$raw) {
        return [
            'data' => null,
            'status' => $status,
            'raw' => null,
            'error' => true
        ];
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'data' => null,
            'status' => $status,
            'raw' => mb_substr($raw, 0, 300),
            'error' => true
        ];
    }

    return [
        'data' => $data,
        'status' => $status,
        'raw' => mb_substr($raw, 0, 300),
        'error' => false
    ];
}

/**
 * Trouve un exercice par Id / ExerciseID.
 */
function findExerciseById(array $exercises, int $id): ?array
{
    foreach ($exercises as $ex) {
        $exId = $ex['Id'] ?? $ex['ExerciseID'] ?? null;
        if ((int)$exId === $id) {
            return $ex;
        }
    }
    return null;
}

/**
 * Affiche un résumé lisible de l'exercice.
 */
function printExerciseDebug(array $ex): void
{
    $id = $ex['Id'] ?? $ex['ExerciseID'] ?? 'NON TROUVÉ';
    $title = $ex['Title'] ?? 'NON TROUVÉ';
    $subject = $ex['Subject'] ?? 'NON TROUVÉ';
    $level = $ex['Level'] ?? 'NON TROUVÉ';
    $answerType = $ex['AnswerType'] ?? 'NON TROUVÉ';
    $type = $ex['type'] ?? 'NON TROUVÉ';
    $typeSource = $ex['type_source'] ?? 'NON TROUVÉ';

    $instruction = truncateText($ex['Instruction'] ?? '', 150);
    $content = truncateText($ex['Content'] ?? '', 300);

    $answer = $ex['Answer'] ?? ($ex['Solution'] ?? 'NON TROUVÉ');

    $choices = $ex['Choices'] ?? null;
    $choicesPreview = formatChoicesPreview($choices);

    $contentStr = (string)($ex['Content'] ?? '');
    $hasBlanks = strpos($contentStr, '___') !== false ? 'OUI' : 'NON';
    $hasChoiceSlashPattern = preg_match('/\b[\p{L}’\'\-]+\s*\/\s*[\p{L}’\'\-]+\b/u', $contentStr) ? 'OUI' : 'NON';

    echo PHP_EOL;
    echo "====================== DEBUG EXERCICE ======================" . PHP_EOL;
    echo "Id: $id" . PHP_EOL;
    echo "Title: $title" . PHP_EOL;
    echo "Subject: $subject" . PHP_EOL;
    echo "Level: $level" . PHP_EOL;
    echo "AnswerType (raw): $answerType" . PHP_EOL;
    echo "type: $type" . PHP_EOL;
    echo "type_source: $typeSource" . PHP_EOL;
    echo "Choices: $choicesPreview" . PHP_EOL;
    echo "Instruction: $instruction" . PHP_EOL;
    echo "Content: $content" . PHP_EOL;
    echo "Answer/Solution: " . formatAnswer($answer) . PHP_EOL;
    echo "Content contient \"___\" ? $hasBlanks" . PHP_EOL;
    echo "Content contient pattern X / Y ? $hasChoiceSlashPattern" . PHP_EOL;
    echo "============================================================" . PHP_EOL;
}

function truncateText(string $text, int $maxLen): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if (mb_strlen($text) <= $maxLen) {
        return $text;
    }
    return mb_substr($text, 0, $maxLen) . '...';
}

function formatChoicesPreview($choices): string
{
    if (empty($choices)) {
        return 'AUCUN';
    }

    if (is_string($choices)) {
        $decoded = json_decode($choices, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $choices = $decoded;
        }
    }

    if (!is_array($choices)) {
        return 'FORMAT INCONNU';
    }

    $count = count($choices);
    $preview = array_slice($choices, 0, 3);
    $previewStr = implode(' | ', array_map(function ($c) {
        if (is_array($c)) {
            return json_encode($c, JSON_UNESCAPED_UNICODE);
        }
        return (string)$c;
    }, $preview));

    return "$count item(s) | preview: $previewStr";
}

function formatAnswer($answer): string
{
    if ($answer === 'NON TROUVÉ') {
        return $answer;
    }

    if (is_array($answer)) {
        return json_encode($answer, JSON_UNESCAPED_UNICODE);
    }

    return (string)$answer;
}
