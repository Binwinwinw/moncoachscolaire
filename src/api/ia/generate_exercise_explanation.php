<?php
/**
 * API pour generer une explication pedagogique a partir d'une correction.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/ai_course_generator.php';
require_once __DIR__ . '/../_core/bootstrap.php';
if (is_file(__DIR__ . '/../../includes/login_security.php')) {
    require_once __DIR__ . '/../../includes/login_security.php';
}

ini_set('display_errors', '0');
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

api_require([
    'method' => 'POST',
    'rate' => [
        'key' => 'ai_explanation',
        'limit' => 20,
        'window' => 60,
    ],
]);

function explanationJsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sanitizeExplanationString($value): string
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

function isExplanationDebugMode(): bool
{
    $appEnv = strtolower((string) (getenv('APP_ENV') ?: ($GLOBALS['appEnv'] ?? '')));
    $appDebug = filter_var(getenv('APP_DEBUG') ?: getenv('DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);

    return $appEnv === 'local' || $appDebug;
}

function logExplanationMessage(string $message, bool $debugOnly = false): void
{
    if ($debugOnly && !isExplanationDebugMode()) {
        return;
    }

    error_log($message);
}

function getExplanationDebugProviderOverrides(array $data): array
{
    if (!isExplanationDebugMode()) {
        return [];
    }

    $overrides = $data['_debug_provider_overrides'] ?? null;
    if (!is_array($overrides)) {
        return [];
    }

    $supportedProviders = ['groq', 'ollama', 'gemini', 'openai', 'perplexity'];
    $normalizedOverrides = [];

    foreach ($overrides as $providerName => $override) {
        $normalizedProvider = strtolower(sanitizeExplanationString($providerName));
        if (!in_array($normalizedProvider, $supportedProviders, true)) {
            continue;
        }

        if (is_string($override)) {
            $normalizedOverrides[$normalizedProvider] = [
                'mode' => strtolower(sanitizeExplanationString($override)),
            ];
            continue;
        }

        if (!is_array($override)) {
            continue;
        }

        $normalizedOverrides[$normalizedProvider] = [
            'mode' => strtolower(sanitizeExplanationString($override['mode'] ?? 'success')),
            'raw' => isset($override['raw']) ? (string) $override['raw'] : null,
            'label' => isset($override['label']) ? sanitizeExplanationString($override['label']) : null,
        ];
    }

    return $normalizedOverrides;
}

function getExplanationConfiguredProviders(array $debugProviderOverrides = []): array
{
    $configured = [];

    if (getenv('GROQ_API_KEY') && getenv('GROQ_API_KEY') !== 'your_groq_api_key_here') {
        $configured[] = 'groq';
    }

    if (getenv('OLLAMA_API_URL')) {
        $configured[] = 'ollama';
    }

    if (getenv('GEMINI_API_KEY') && getenv('GEMINI_API_KEY') !== 'your_gemini_api_key_here') {
        $configured[] = 'gemini';
    }

    if (getenv('OPENAI_API_KEY') && getenv('OPENAI_API_KEY') !== 'your_openai_api_key_here') {
        $configured[] = 'openai';
    }

    if (getenv('PERPLEXITY_API_KEY') && getenv('PERPLEXITY_API_KEY') !== 'your_perplexity_api_key_here') {
        $configured[] = 'perplexity';
    }

    foreach (array_keys($debugProviderOverrides) as $debugProvider) {
        if (!in_array($debugProvider, $configured, true)) {
            $configured[] = $debugProvider;
        }
    }

    return $configured;
}

function resolveExplanationProviderOrder(?string $requestedProvider, array $debugProviderOverrides = []): array
{
    $supportedProviders = ['groq', 'ollama', 'gemini', 'openai', 'perplexity'];
    $configuredProviders = getExplanationConfiguredProviders($debugProviderOverrides);

    if (empty($configuredProviders)) {
        throw new RuntimeException('Aucun provider IA configuré. Veuillez renseigner OLLAMA_API_URL ou une clé API dans .env');
    }

    if ($requestedProvider === null || $requestedProvider === '') {
        return $configuredProviders;
    }

    if (!in_array($requestedProvider, $supportedProviders, true)) {
        throw new InvalidArgumentException('Provider IA non supporté');
    }

    if (!in_array($requestedProvider, $configuredProviders, true)) {
        throw new InvalidArgumentException('Provider IA demandé non configuré');
    }

    $providers = [$requestedProvider];

    foreach ($configuredProviders as $providerName) {
        if ($providerName !== $requestedProvider) {
            $providers[] = $providerName;
        }
    }

    return $providers;
}

function buildDebugExplanationResponse(array $incorrectItems, ?string $label = null): string
{
    $providerLabel = $label !== null && $label !== '' ? $label : 'Mode debug';
    $firstItem = $incorrectItems[0] ?? [];
    $questionText = (string) ($firstItem['question'] ?? 'Question');
    $correctAnswer = (string) ($firstItem['correct_answer'] ?? 'Réponse correcte');

    $payload = [
        'summary' => $providerLabel . ' : concentre-toi sur la logique de la bonne réponse avant de refaire l exercice.',
        'learning_objective' => 'Comprendre la bonne methode plutot que memoriser la reponse.',
        'mistake_pattern' => 'Tu as probablement privilegie une intuition rapide au lieu de verifier l indice cle de l enonce.',
        'steps' => [
            'Relis l enonce en repérant le mot ou l information decisive.',
            'Compare chaque choix avec cette information decisive.',
            'Elimine d abord les choix incompatibles avant de valider la bonne reponse.',
        ],
        'retry_tip' => 'Refais la question en expliquant a voix haute pourquoi les autres choix sont faux.',
        'verification_question' => 'Si tu devais justifier la bonne réponse en une phrase, que dirais-tu ?',
        'per_question' => [
            [
                'question' => $questionText,
                'your_answer' => (string) ($firstItem['user_answer'] ?? ''),
                'correct_answer' => $correctAnswer,
                'explanation' => 'La bonne réponse est "' . $correctAnswer . '". Vérifie l indice principal de l enoncé et aligne chaque choix avec cet indice.',
            ],
        ],
    ];

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonPayload === false) {
        throw new RuntimeException('Impossible de construire la reponse debug');
    }

    return $jsonPayload;
}

function getExplanationProviderResponse(string $providerName, string $prompt, array $debugProviderOverrides, array $incorrectItems): string
{
    if (isset($debugProviderOverrides[$providerName])) {
        $override = $debugProviderOverrides[$providerName];
        $mode = $override['mode'] ?? 'success';

        switch ($mode) {
            case 'empty':
                return '';

            case 'invalid_json':
                return '{"summary":';

            case 'timeout':
                throw new RuntimeException('Timeout simulé du provider');

            case 'raw':
                return (string) ($override['raw'] ?? '');

            case 'success':
            default:
                return buildDebugExplanationResponse($incorrectItems, $override['label'] ?? null);
        }
    }

    return (string) callAIProvider($providerName, $prompt);
}

function extractJsonObjectFromResponse(string $aiResponse): string
{
    $trimmedResponse = trim($aiResponse);
    $jsonStart = strpos($trimmedResponse, '{');
    $jsonEnd = strrpos($trimmedResponse, '}');

    if ($jsonStart === false || $jsonEnd === false || $jsonEnd < $jsonStart) {
        throw new RuntimeException('Réponse IA inexploitable');
    }

    return substr($trimmedResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
}

function normalizeExplanationList($value, int $limit = 5): array
{
    $items = [];

    if (is_array($value)) {
        foreach ($value as $entry) {
            $text = trim((string) $entry);
            if ($text !== '') {
                $items[] = $text;
            }
        }
    } elseif (is_string($value) && trim($value) !== '') {
        $items[] = trim($value);
    }

    return array_slice($items, 0, $limit);
}

function normalizeIncorrectItems(array $items): array
{
    $normalized = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $question = trim((string) ($item['question'] ?? ''));
        $correctAnswer = trim((string) ($item['correct_answer'] ?? ''));
        $userAnswer = trim((string) ($item['user_answer'] ?? ''));
        $officialCorrection = trim((string) ($item['official_correction'] ?? ''));
        $questionType = trim((string) ($item['question_type'] ?? 'qcm'));
        $choices = [];

        if (isset($item['choices']) && is_array($item['choices'])) {
            foreach ($item['choices'] as $choice) {
                $choiceText = trim((string) $choice);
                if ($choiceText !== '') {
                    $choices[] = $choiceText;
                }
            }
        }

        if ($question === '' || $correctAnswer === '') {
            continue;
        }

        $normalized[] = [
            'question' => $question,
            'correct_answer' => $correctAnswer,
            'user_answer' => $userAnswer,
            'official_correction' => $officialCorrection,
            'question_type' => $questionType,
            'choices' => array_slice($choices, 0, 6),
        ];
    }

    return array_slice($normalized, 0, 3);
}

function normalizeExplanationPayload(array $payload, array $incorrectItems): array
{
    $summary = trim((string) ($payload['summary'] ?? ''));
    $learningObjective = trim((string) ($payload['learning_objective'] ?? ''));
    $mistakePattern = trim((string) ($payload['mistake_pattern'] ?? ''));
    $steps = normalizeExplanationList($payload['steps'] ?? ($payload['explanation_steps'] ?? []));
    $retryTip = trim((string) ($payload['retry_tip'] ?? ''));
    $verificationQuestion = trim((string) ($payload['verification_question'] ?? ''));
    $perQuestion = [];

    if (isset($payload['per_question']) && is_array($payload['per_question'])) {
        foreach ($payload['per_question'] as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $baseItem = $incorrectItems[$index] ?? [];
            $question = trim((string) ($row['question'] ?? ($baseItem['question'] ?? '')));
            $correctAnswer = trim((string) ($row['correct_answer'] ?? ($baseItem['correct_answer'] ?? '')));
            $userAnswer = trim((string) ($row['your_answer'] ?? ($row['user_answer'] ?? ($baseItem['user_answer'] ?? ''))));
            $explanation = trim((string) ($row['explanation'] ?? ''));

            if ($question === '' || $correctAnswer === '' || $explanation === '') {
                continue;
            }

            $perQuestion[] = [
                'question' => $question,
                'your_answer' => $userAnswer,
                'correct_answer' => $correctAnswer,
                'explanation' => $explanation,
            ];
        }
    }

    if ($summary === '') {
        $summary = 'Tu peux corriger cette erreur en revenant a la methode et en justifiant la bonne reponse.';
    }

    if ($retryTip === '') {
        $retryTip = 'Refais la question lentement et explique pourquoi la bonne réponse est la seule cohérente.';
    }

    if (empty($steps)) {
        $steps = ['Repere l information cle de l enonce avant de choisir la reponse.'];
    }

    if (empty($perQuestion)) {
        foreach ($incorrectItems as $incorrectItem) {
            $fallbackExplanation = $incorrectItem['official_correction'] !== ''
                ? $incorrectItem['official_correction']
                : 'La bonne réponse est "' . $incorrectItem['correct_answer'] . '". Reprends l enonce et justifie ce choix.';

            $perQuestion[] = [
                'question' => $incorrectItem['question'],
                'your_answer' => $incorrectItem['user_answer'],
                'correct_answer' => $incorrectItem['correct_answer'],
                'explanation' => $fallbackExplanation,
            ];
        }
    }

    return [
        'summary' => $summary,
        'learning_objective' => $learningObjective,
        'mistake_pattern' => $mistakePattern,
        'steps' => $steps,
        'retry_tip' => $retryTip,
        'verification_question' => $verificationQuestion,
        'per_question' => $perQuestion,
    ];
}

function buildExplanationPrompt(string $level, string $subject, string $competence, string $officialCorrection, array $incorrectItems): string
{
    $itemsText = [];

    foreach ($incorrectItems as $index => $item) {
        $itemsText[] = 'Question ' . ($index + 1) . ' : ' . $item['question'];
        $itemsText[] = 'Type : ' . $item['question_type'];
        if (!empty($item['choices'])) {
            $itemsText[] = 'Choix : ' . implode(' | ', $item['choices']);
        }
        $itemsText[] = 'Reponse eleve : ' . ($item['user_answer'] !== '' ? $item['user_answer'] : 'Aucune reponse');
        $itemsText[] = 'Bonne reponse : ' . $item['correct_answer'];
        if ($item['official_correction'] !== '') {
            $itemsText[] = 'Correction officielle : ' . $item['official_correction'];
        }
        $itemsText[] = '---';
    }

    $competenceLine = $competence !== '' ? $competence : 'Non precisee';
    $officialCorrectionLine = $officialCorrection !== '' ? $officialCorrection : 'Aucune correction globale fournie';

    return <<<PROMPT
Tu es un coach pedagogique. Ta mission est d expliquer une erreur d exercice pour aider un eleve a reussir au prochain essai.

Contexte:
- Niveau: {$level}
- Matiere: {$subject}
- Competence: {$competenceLine}
- Correction officielle globale: {$officialCorrectionLine}

Erreurs a expliquer:
{$itemsText[0]}
PROMPT
        . (count($itemsText) > 1 ? "\n" . implode("\n", array_slice($itemsText, 1)) : '')
        . <<<PROMPT

Regles non negociables:
- si une correction officielle est fournie, elle est la source de verite
- n invente pas une nouvelle bonne reponse
- reste concret, pedagogique et adapte au niveau scolaire
- explique comment refaire juste, pas seulement quelle reponse choisir

Reponds UNIQUEMENT avec un objet JSON valide de cette forme:
{
  "summary": "resume court et utile",
  "learning_objective": "objectif d apprentissage",
  "mistake_pattern": "erreur probable ou point de confusion",
  "steps": [
    "etape 1",
    "etape 2",
    "etape 3"
  ],
  "retry_tip": "conseil concret pour refaire l exercice",
  "verification_question": "mini question pour verifier la comprehension",
  "per_question": [
    {
      "question": "texte court de la question",
      "your_answer": "reponse eleve",
      "correct_answer": "bonne reponse",
      "explanation": "explication pedagogique claire"
    }
  ]
}
PROMPT;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    explanationJsonResponse(['success' => false, 'error' => 'Méthode non autorisée'], 405);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    explanationJsonResponse(['success' => false, 'error' => 'Format JSON invalide en entrée'], 400);
}

$csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($data['csrf_token'] ?? ''));
$csrfValid = function_exists('verifyCSRFToken')
    ? verifyCSRFToken($csrfToken)
    : (
        isset($_SESSION['csrf_token'])
        && $csrfToken !== ''
        && hash_equals((string) $_SESSION['csrf_token'], $csrfToken)
    );

if (!$csrfValid) {
    explanationJsonResponse(['success' => false, 'error' => 'Jeton CSRF invalide'], 403);
}

$level = sanitizeExplanationString($data['level'] ?? '');
$subject = sanitizeExplanationString($data['subject'] ?? '');
$competence = sanitizeExplanationString($data['competence'] ?? '');
$officialCorrection = sanitizeExplanationString($data['official_correction'] ?? '');
$provider = isset($data['provider']) ? strtolower(sanitizeExplanationString($data['provider'])) : null;

$incorrectItems = normalizeIncorrectItems($data['incorrect_items'] ?? []);

if (empty($incorrectItems) && isset($data['question'], $data['correct_answer'])) {
    $incorrectItems = normalizeIncorrectItems([
        [
            'question' => $data['question'],
            'correct_answer' => $data['correct_answer'],
            'user_answer' => $data['user_answer'] ?? '',
            'official_correction' => $data['official_correction'] ?? '',
            'question_type' => $data['question_type'] ?? 'qcm',
            'choices' => $data['choices'] ?? [],
        ],
    ]);
}

if ($level === '' || $subject === '' || empty($incorrectItems)) {
    explanationJsonResponse(['success' => false, 'error' => 'Niveau, matière et erreurs à expliquer requis'], 422);
}

try {
    $allowedLevels = ['6eme', '5eme', '4eme', '3eme', '2nde', '1ere', 'terminale', 'bac', '6ème', '5ème', '4ème', '3ème'];
    $allowedSubjects = [
        'Mathematiques', 'Mathématiques', 'mathématiques', 'maths',
        'Français', 'francais',
        'Physique-Chimie', 'physique-chimie',
        'SVT', 'svt',
        'Histoire-Géographie', 'histoire-geographie', 'histoire', 'geographie',
        'Anglais', 'anglais',
        'Espagnol', 'espagnol',
        'Philosophie', 'philosophie', 'philo'
    ];

    $levelFound = false;
    foreach ($allowedLevels as $allowedLevel) {
        if (strcasecmp($level, $allowedLevel) === 0) {
            $levelFound = true;
            break;
        }
    }

    $subjectFound = false;
    foreach ($allowedSubjects as $allowedSubject) {
        if (strcasecmp($subject, $allowedSubject) === 0) {
            $subjectFound = true;
            break;
        }
    }

    if (!$levelFound) {
        throw new InvalidArgumentException('Niveau invalide');
    }

    if (!$subjectFound) {
        throw new InvalidArgumentException('Matière invalide');
    }

    $debugProviderOverrides = getExplanationDebugProviderOverrides($data);
    $providers = resolveExplanationProviderOrder($provider, $debugProviderOverrides);
    $prompt = buildExplanationPrompt($level, $subject, $competence, $officialCorrection, $incorrectItems);

    $usedProvider = null;
    $providerFailures = [];
    $normalizedExplanation = [];

    foreach ($providers as $tryProvider) {
        try {
            $aiResponse = getExplanationProviderResponse($tryProvider, $prompt, $debugProviderOverrides, $incorrectItems);
            if (trim($aiResponse) === '') {
                throw new RuntimeException('Réponse vide du provider');
            }

            $aiJsonObject = extractJsonObjectFromResponse($aiResponse);
            $decodedPayload = json_decode($aiJsonObject, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedPayload)) {
                throw new RuntimeException('JSON invalide: ' . json_last_error_msg());
            }

            $normalizedExplanation = normalizeExplanationPayload($decodedPayload, $incorrectItems);
            $usedProvider = $tryProvider;
            break;
        } catch (Throwable $providerError) {
            $providerFailures[] = $tryProvider . ': ' . $providerError->getMessage();
            logExplanationMessage('[Exercise Explanation AI] Provider failure: ' . $tryProvider . ' - ' . $providerError->getMessage(), true);
        }
    }

    if ($usedProvider === null) {
        if (!empty($providerFailures)) {
            logExplanationMessage('[Exercise Explanation AI] All providers failed: ' . implode(' | ', array_slice($providerFailures, 0, 5)), true);
        }

        throw new RuntimeException('Impossible de générer l explication pour le moment');
    }

    explanationJsonResponse([
        'success' => true,
        'data' => $normalizedExplanation,
        'provider_used' => $usedProvider,
    ]);
} catch (Throwable $e) {
    $statusCode = 500;

    if ($e instanceof InvalidArgumentException) {
        $statusCode = 422;
    } elseif ($e instanceof RuntimeException) {
        $statusCode = 502;
    }

    if ($statusCode === 422) {
        logExplanationMessage('[Exercise Explanation AI] Validation error: ' . $e->getMessage());
    } else {
        logExplanationMessage('[Exercise Explanation AI] Endpoint error: ' . $e->getMessage(), true);
        logExplanationMessage('[Exercise Explanation AI] Endpoint error');
    }

    explanationJsonResponse([
        'success' => false,
        'error' => $statusCode === 422 ? $e->getMessage() : 'Explication indisponible pour le moment',
    ], $statusCode);
}
