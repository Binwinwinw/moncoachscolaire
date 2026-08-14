<?php

/**
 * API pour generer un mini-cours cible a partir d'une correction.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/ai_course_generator.php';
require_once __DIR__ . '/../../includes/course_display.php';
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
    'auth_or_cli' => true,
    'csrf' => true,
    'rate' => [
        'key' => 'ai_course',
        'limit' => 10,
        'window' => 60,
    ],
]);

function preciseCourseJsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sanitizePreciseCourseString($value): string
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

function isPreciseCourseDebugMode(): bool
{
    $appEnv = strtolower((string) (getenv('APP_ENV') ?: ($GLOBALS['appEnv'] ?? '')));
    $appDebug = filter_var(getenv('APP_DEBUG') ?: getenv('DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);

    return $appEnv === 'local' || $appDebug;
}

function logPreciseCourseMessage(string $message, bool $debugOnly = false): void
{
    if ($debugOnly && !isPreciseCourseDebugMode()) {
        return;
    }

    error_log($message);
}

function getPreciseCourseDebugProviderOverrides(array $data): array
{
    if (!isPreciseCourseDebugMode()) {
        return [];
    }

    $overrides = $data['_debug_provider_overrides'] ?? null;
    if (!is_array($overrides)) {
        return [];
    }

    $supportedProviders = ['groq', 'ollama', 'gemini', 'openai', 'perplexity'];
    $normalizedOverrides = [];

    foreach ($overrides as $providerName => $override) {
        $normalizedProvider = strtolower(sanitizePreciseCourseString($providerName));
        if (!in_array($normalizedProvider, $supportedProviders, true)) {
            continue;
        }

        if (is_string($override)) {
            $normalizedOverrides[$normalizedProvider] = [
                'mode' => strtolower(sanitizePreciseCourseString($override)),
            ];
            continue;
        }

        if (!is_array($override)) {
            continue;
        }

        $normalizedOverrides[$normalizedProvider] = [
            'mode' => strtolower(sanitizePreciseCourseString($override['mode'] ?? 'success')),
            'raw' => isset($override['raw']) ? (string) $override['raw'] : null,
            'label' => isset($override['label']) ? sanitizePreciseCourseString($override['label']) : null,
        ];
    }

    return $normalizedOverrides;
}

function getPreciseCourseConfiguredProviders(array $debugProviderOverrides = []): array
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

function resolvePreciseCourseProviderOrder(?string $requestedProvider, array $debugProviderOverrides = []): array
{
    $supportedProviders = ['groq', 'ollama', 'gemini', 'openai', 'perplexity'];
    $configuredProviders = getPreciseCourseConfiguredProviders($debugProviderOverrides);

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

function normalizePreciseCourseList($value, int $limit = 5): array
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

function normalizePreciseCourseIncorrectItems(array $items): array
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

        if ($question === '' || $correctAnswer === '') {
            continue;
        }

        $normalized[] = [
            'question' => $question,
            'correct_answer' => $correctAnswer,
            'user_answer' => $userAnswer,
            'official_correction' => $officialCorrection,
            'question_type' => $questionType,
        ];
    }

    return array_slice($normalized, 0, 3);
}

function getPreciseCourseVerifiedReferences(int $exerciseId): array
{
    if ($exerciseId <= 0 || !function_exists('getExerciseExternalResources')) {
        return [];
    }

    $resources = getExerciseExternalResources($exerciseId);
    $references = [];

    foreach ($resources as $resource) {
        if (!is_array($resource)) {
            continue;
        }

        $title = trim((string) ($resource['Title'] ?? ''));
        $url = trim((string) ($resource['URL'] ?? ''));
        $source = trim((string) ($resource['Source'] ?? ''));
        $resourceType = trim((string) ($resource['ResourceType'] ?? ''));

        if ($title === '' || $url === '') {
            continue;
        }

        $references[] = [
            'title' => $title,
            'url' => $url,
            'source' => $source,
            'type' => $resourceType,
        ];
    }

    return array_slice($references, 0, 3);
}

function buildDebugPreciseCourseResponse(array $incorrectItems, array $references, ?string $label = null): string
{
    $providerLabel = $label !== null && $label !== '' ? $label : 'Mode debug';
    $firstItem = $incorrectItems[0] ?? [];

    $payload = [
        'title' => 'Mini-cours ciblé - ' . $providerLabel,
        'summary' => 'On reprend uniquement la notion qui bloque pour refaire juste au prochain essai.',
        'concept_focus' => 'Identifier l indice central de la question et l utiliser pour choisir la bonne methode.',
        'key_points' => [
            'Lire la consigne jusqu au bout avant de répondre.',
            'Repérer l indice qui permet d éliminer les mauvaises réponses.',
            'Justifier la bonne réponse avec une règle simple.',
        ],
        'method_steps' => [
            'Observe la question et repère ce qui est vraiment demandé.',
            'Vérifie la règle ou la méthode attendue.',
            'Applique la méthode sur un exemple proche avant de recommencer.',
        ],
        'worked_example' => 'Exemple guidé : pour "' . (string) ($firstItem['question'] ?? 'la question') . '", la bonne réponse est "' . (string) ($firstItem['correct_answer'] ?? 'la bonne réponse') . '" car elle respecte la règle attendue.',
        'common_pitfalls' => [
            'Répondre trop vite sans vérifier la consigne.',
            'Confondre intuition et méthode.',
        ],
        'practice_tip' => 'Refais un exercice du même type en expliquant chaque étape à voix haute.',
        'verification_question' => 'Quelle règle vas-tu vérifier en premier la prochaine fois ?',
        'references' => $references,
    ];

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonPayload === false) {
        throw new RuntimeException('Impossible de construire la reponse debug');
    }

    return $jsonPayload;
}

function getPreciseCourseProviderResponse(string $providerName, string $prompt, array $debugProviderOverrides, array $incorrectItems, array $references): string
{
    if (isset($debugProviderOverrides[$providerName])) {
        $override = $debugProviderOverrides[$providerName];
        $mode = $override['mode'] ?? 'success';

        switch ($mode) {
            case 'empty':
                return '';

            case 'invalid_json':
                return '{"title":';

            case 'timeout':
                throw new RuntimeException('Timeout simulé du provider');

            case 'raw':
                return (string) ($override['raw'] ?? '');

            case 'success':
            default:
                return buildDebugPreciseCourseResponse($incorrectItems, $references, $override['label'] ?? null);
        }
    }

    return (string) callAIProvider($providerName, $prompt);
}

function extractPreciseCourseJsonObject(string $aiResponse): string
{
    $trimmedResponse = trim($aiResponse);
    $jsonStart = strpos($trimmedResponse, '{');
    $jsonEnd = strrpos($trimmedResponse, '}');

    if ($jsonStart === false || $jsonEnd === false || $jsonEnd < $jsonStart) {
        throw new RuntimeException('Réponse IA inexploitable');
    }

    return substr($trimmedResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
}

function normalizePreciseCoursePayload(array $payload, array $incorrectItems, array $verifiedReferences): array
{
    $title = trim((string) ($payload['title'] ?? ''));
    $summary = trim((string) ($payload['summary'] ?? ''));
    $conceptFocus = trim((string) ($payload['concept_focus'] ?? ($payload['learning_objective'] ?? '')));
    $keyPoints = normalizePreciseCourseList($payload['key_points'] ?? []);
    $methodSteps = normalizePreciseCourseList($payload['method_steps'] ?? ($payload['steps'] ?? []));
    $workedExample = trim((string) ($payload['worked_example'] ?? ''));
    $commonPitfalls = normalizePreciseCourseList($payload['common_pitfalls'] ?? ($payload['mistakes_to_avoid'] ?? []));
    $practiceTip = trim((string) ($payload['practice_tip'] ?? ($payload['retry_tip'] ?? '')));
    $verificationQuestion = trim((string) ($payload['verification_question'] ?? ''));

    if ($title === '') {
        $title = 'Mini-cours ciblé';
    }

    if ($summary === '') {
        $summary = 'On se concentre sur la notion qui bloque pour réussir le prochain essai.';
    }

    if ($conceptFocus === '') {
        $conceptFocus = 'Comprendre la méthode attendue avant de refaire l exercice.';
    }

    if (empty($keyPoints)) {
        $keyPoints = ['Repère l information clé de la consigne avant de répondre.'];
    }

    if (empty($methodSteps)) {
        $methodSteps = ['Relis la consigne et applique une méthode simple étape par étape.'];
    }

    if ($workedExample === '') {
        $firstItem = $incorrectItems[0] ?? [];
        $question = (string) ($firstItem['question'] ?? 'la question');
        $correctAnswer = (string) ($firstItem['correct_answer'] ?? 'la bonne réponse');
        $workedExample = 'Sur "' . $question . '", la bonne réponse est "' . $correctAnswer . '". Repars de la règle vue dans la correction pour retrouver cette réponse.';
    }

    if (empty($commonPitfalls)) {
        $commonPitfalls = ['Aller trop vite sans vérifier la règle ou la méthode.'];
    }

    if ($practiceTip === '') {
        $practiceTip = 'Refais un exercice proche et verbalise chaque étape avant d écrire la réponse.';
    }

    return [
        'title' => $title,
        'summary' => $summary,
        'concept_focus' => $conceptFocus,
        'key_points' => $keyPoints,
        'method_steps' => $methodSteps,
        'worked_example' => $workedExample,
        'common_pitfalls' => $commonPitfalls,
        'practice_tip' => $practiceTip,
        'verification_question' => $verificationQuestion,
        'references' => $verifiedReferences,
    ];
}

function buildPreciseCoursePrompt(string $level, string $subject, string $competence, string $officialCorrection, array $incorrectItems, array $verifiedReferences): string
{
    $itemsText = [];

    foreach ($incorrectItems as $index => $item) {
        $itemsText[] = 'Question ' . ($index + 1) . ' : ' . $item['question'];
        $itemsText[] = 'Type : ' . $item['question_type'];
        $itemsText[] = 'Reponse eleve : ' . ($item['user_answer'] !== '' ? $item['user_answer'] : 'Aucune reponse');
        $itemsText[] = 'Bonne reponse : ' . $item['correct_answer'];
        if ($item['official_correction'] !== '') {
            $itemsText[] = 'Correction officielle : ' . $item['official_correction'];
        }
        $itemsText[] = '---';
    }

    $referencesText = ['Aucune ressource verifiee disponible'];
    if (!empty($verifiedReferences)) {
        $referencesText = [];
        foreach ($verifiedReferences as $reference) {
            $line = '- ' . $reference['title'];
            if ($reference['source'] !== '') {
                $line .= ' | source: ' . $reference['source'];
            }
            if ($reference['type'] !== '') {
                $line .= ' | type: ' . $reference['type'];
            }
            $line .= ' | url: ' . $reference['url'];
            $referencesText[] = $line;
        }
    }

    $competenceLine = $competence !== '' ? $competence : 'Non precisee';
    $officialCorrectionLine = $officialCorrection !== '' ? $officialCorrection : 'Aucune correction globale fournie';

    return <<<PROMPT
Tu es un coach pedagogique. Ta mission est de produire un mini-cours cible, tres concret, pour corriger une erreur d exercice et aider un eleve a reussir au prochain essai.

Contexte:
- Niveau: {$level}
- Matiere: {$subject}
- Competence: {$competenceLine}
- Correction officielle globale: {$officialCorrectionLine}

Erreurs a traiter:
{$itemsText[0]}
PROMPT
        . (count($itemsText) > 1 ? "\n" . implode("\n", array_slice($itemsText, 1)) : '')
        . "\n\nRessources verifiees disponibles:\n"
        . implode("\n", $referencesText)
        . <<<PROMPT

Regles non negociables:
- la correction officielle reste la source de verite
- n invente ni bonne reponse, ni formule, ni URL
- construis un mini-cours court, actionnable et adapte au niveau scolaire
- si des ressources verifiees sont fournies, aligne le contenu dessus sans en inventer d autres

Reponds UNIQUEMENT avec un objet JSON valide de cette forme:
{
  "title": "titre court du mini-cours",
  "summary": "resume court et utile",
  "concept_focus": "notion precise a retenir",
  "key_points": [
    "point cle 1",
    "point cle 2"
  ],
  "method_steps": [
    "etape 1",
    "etape 2",
    "etape 3"
  ],
  "worked_example": "exemple guide tres court",
  "common_pitfalls": [
    "erreur a eviter 1",
    "erreur a eviter 2"
  ],
  "practice_tip": "conseil concret pour s entrainer",
  "verification_question": "mini question de verification"
}
PROMPT;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    preciseCourseJsonResponse(['success' => false, 'error' => 'Méthode non autorisée'], 405);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$data = api_get_json_body(true);

$exerciseId = isset($data['exercise_id']) ? (int) $data['exercise_id'] : 0;
$level = sanitizePreciseCourseString($data['level'] ?? '');
$subject = sanitizePreciseCourseString($data['subject'] ?? '');
$competence = sanitizePreciseCourseString($data['competence'] ?? '');
$officialCorrection = sanitizePreciseCourseString($data['official_correction'] ?? '');
$provider = isset($data['provider']) ? strtolower(sanitizePreciseCourseString($data['provider'])) : null;

$incorrectItems = normalizePreciseCourseIncorrectItems($data['incorrect_items'] ?? []);

if (empty($incorrectItems) && isset($data['question'], $data['correct_answer'])) {
    $incorrectItems = normalizePreciseCourseIncorrectItems([
        [
            'question' => $data['question'],
            'correct_answer' => $data['correct_answer'],
            'user_answer' => $data['user_answer'] ?? '',
            'official_correction' => $data['official_correction'] ?? '',
            'question_type' => $data['question_type'] ?? 'qcm',
        ],
    ]);
}

if ($level === '' || $subject === '' || empty($incorrectItems)) {
    preciseCourseJsonResponse(['success' => false, 'error' => 'Niveau, matière et erreurs à expliquer requis'], 422);
}

try {
    $allowedLevels = ['6eme', '5eme', '4eme', '3eme', '2nde', '1ere', 'terminale', 'bac', '6ème', '5ème', '4ème', '3ème', 'Seconde', 'Premiere', 'Terminale'];
    $allowedSubjects = [
        'Mathematiques',
        'Mathématiques',
        'mathématiques',
        'maths',
        'Français',
        'francais',
        'Physique-Chimie',
        'physique-chimie',
        'SVT',
        'svt',
        'Histoire-Géographie',
        'histoire-geographie',
        'histoire',
        'geographie',
        'Anglais',
        'anglais',
        'Espagnol',
        'espagnol',
        'Philosophie',
        'philosophie',
        'philo'
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

    $debugProviderOverrides = getPreciseCourseDebugProviderOverrides($data);
    $providers = resolvePreciseCourseProviderOrder($provider, $debugProviderOverrides);
    $verifiedReferences = getPreciseCourseVerifiedReferences($exerciseId);
    $prompt = buildPreciseCoursePrompt($level, $subject, $competence, $officialCorrection, $incorrectItems, $verifiedReferences);

    $usedProvider = null;
    $providerFailures = [];
    $normalizedCourse = [];

    foreach ($providers as $tryProvider) {
        try {
            $aiResponse = getPreciseCourseProviderResponse($tryProvider, $prompt, $debugProviderOverrides, $incorrectItems, $verifiedReferences);
            if (trim($aiResponse) === '') {
                throw new RuntimeException('Réponse vide du provider');
            }

            $aiJsonObject = extractPreciseCourseJsonObject($aiResponse);
            $decodedPayload = json_decode($aiJsonObject, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedPayload)) {
                throw new RuntimeException('JSON invalide: ' . json_last_error_msg());
            }

            $normalizedCourse = normalizePreciseCoursePayload($decodedPayload, $incorrectItems, $verifiedReferences);
            $usedProvider = $tryProvider;
            break;
        } catch (Throwable $providerError) {
            $providerFailures[] = $tryProvider . ': ' . $providerError->getMessage();
            logPreciseCourseMessage('[Precise Course AI] Provider failure: ' . $tryProvider . ' - ' . $providerError->getMessage(), true);
        }
    }

    if ($usedProvider === null) {
        if (!empty($providerFailures)) {
            logPreciseCourseMessage('[Precise Course AI] All providers failed: ' . implode(' | ', array_slice($providerFailures, 0, 5)), true);
        }

        throw new RuntimeException('Impossible de générer le mini-cours pour le moment');
    }

    preciseCourseJsonResponse([
        'success' => true,
        'data' => $normalizedCourse,
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
        logPreciseCourseMessage('[Precise Course AI] Validation error: ' . $e->getMessage());
    } else {
        logPreciseCourseMessage('[Precise Course AI] Endpoint error: ' . $e->getMessage(), true);
        logPreciseCourseMessage('[Precise Course AI] Endpoint error');
    }

    preciseCourseJsonResponse([
        'success' => false,
        'error' => $statusCode === 422 ? $e->getMessage() : 'Mini-cours indisponible pour le moment',
    ], $statusCode);
}
