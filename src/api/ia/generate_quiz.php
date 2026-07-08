<?php
/**
 * API pour la génération de Quiz par IA
 * Reçoit niveau et matière, retourne le HTML interactif
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/ai_course_generator.php';
require_once __DIR__ . '/../_core/bootstrap.php';
if (is_file(__DIR__ . '/../../includes/login_security.php')) {
    require_once __DIR__ . '/../../includes/login_security.php';
}

// Sécurité: vérifier si l'utilisateur est connecté (optionnel selon vos besoins)
// ensure_session_started();
// if (!isset($_SESSION['user_id'])) {
//     header('Content-Type: application/json');
//     echo json_encode(['error' => 'Non autorisé']);
//     exit;
// }

// Désactivation de l'affichage des erreurs pour éviter de polluer le JSON
ini_set('display_errors', 0);
error_reporting(0);


header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

api_require([
    'method' => 'POST',
    'rate' => [
        'key' => 'ai_quiz',
        'limit' => 10,
        'window' => 60,
    ],
]);

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sanitizeInputString($value): string
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

function normalizeLevelForGenerator(string $level): string
{
    $normalized = $level;
    if (function_exists('normalize_school_level')) {
        $normalized = (string) normalize_school_level($level);
    }

    $normalized = trim((string) $normalized);
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
    if ($ascii === false) {
        $ascii = $normalized;
    }

    $compact = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $ascii));

    $aliases = [
        '6eme' => '6eme',
        '5eme' => '5eme',
        '4eme' => '4eme',
        '3eme' => '3eme',
        'seconde' => '2nde',
        '2nde' => '2nde',
        'premiere' => '1ere',
        '1ere' => '1ere',
        'terminale' => 'terminale',
        'bac' => 'bac',
    ];

    if (isset($aliases[$compact])) {
        $normalized = $aliases[$compact];
    }

    return sanitizeInputString($normalized);
}

function extractLevelFromUserRow(array $row): string
{
    $level = '';

    if (isset($row['level']) && (string) $row['level'] !== '') {
        $level = (string) $row['level'];
    } elseif (isset($row['UserLevel']) && (string) $row['UserLevel'] !== '') {
        $level = (string) $row['UserLevel'];
    } elseif (isset($row['user_level']) && (string) $row['user_level'] !== '') {
        $level = (string) $row['user_level'];
    }

    return normalizeLevelForGenerator($level);
}

function resolveLockedLevelFromSession(?PDO $pdo, string $requestedLevel, array $data): string
{
    $fallbackLevel = normalizeLevelForGenerator($requestedLevel);
    $contextPage = strtolower(sanitizeInputString($data['context_page'] ?? ''));
    $lockableContexts = ['diagnostic', 'cours', 'exercices'];

    if (!in_array($contextPage, $lockableContexts, true) || !$pdo instanceof PDO) {
        return $fallbackLevel;
    }

    $sessionRole = strtolower((string) ($_SESSION['user_role'] ?? $_SESSION['role'] ?? ''));
    $isParent = in_array($sessionRole, ['parent', 'parents'], true);

    if ($isParent) {
        $parentId = (int) ($_SESSION['parent_id'] ?? $_SESSION['user_id'] ?? 0);
        $childId = filter_var($data['child_user_id'] ?? null, FILTER_VALIDATE_INT);

        if ($parentId > 0 && $childId) {
            $linkStmt = $pdo->prepare(
                "SELECT u.*
                 FROM users u
                 JOIN parent_child_invites pci ON pci.child_user_id = u.Id
                 WHERE pci.parent_user_id = :parent_id
                   AND pci.child_user_id = :child_id
                   AND pci.status = 'accepted'
                   AND u.Role = 'student'
                 LIMIT 1"
            );
            $linkStmt->execute([
                'parent_id' => $parentId,
                'child_id' => (int) $childId,
            ]);
            $childRow = $linkStmt->fetch(PDO::FETCH_ASSOC);

            if (is_array($childRow)) {
                $childLevel = extractLevelFromUserRow($childRow);
                if ($childLevel !== '') {
                    return $childLevel;
                }
            }
        }

        return $fallbackLevel;
    }

    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        return $fallbackLevel;
    }

    $userStmt = $pdo->prepare("SELECT * FROM users WHERE Id = :user_id LIMIT 1");
    $userStmt->execute(['user_id' => $userId]);
    $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!is_array($userRow)) {
        return $fallbackLevel;
    }

    $lockedLevel = extractLevelFromUserRow($userRow);
    return $lockedLevel !== '' ? $lockedLevel : $fallbackLevel;
}

function isQuizAiDebugMode(): bool
{
    $appEnv = strtolower((string) (getenv('APP_ENV') ?: ($GLOBALS['appEnv'] ?? '')));
    $appDebug = filter_var(getenv('APP_DEBUG') ?: getenv('DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);

    return $appEnv === 'local' || $appDebug;
}

function logQuizAiMessage(string $message, bool $debugOnly = false): void
{
    if ($debugOnly && !isQuizAiDebugMode()) {
        return;
    }

    error_log($message);
}

function getDebugProviderOverrides(array $data): array
{
    if (!isQuizAiDebugMode()) {
        return [];
    }

    $overrides = $data['_debug_provider_overrides'] ?? null;
    if (!is_array($overrides)) {
        return [];
    }

    $supportedProviders = ['groq', 'ollama', 'gemini', 'openai', 'perplexity'];
    $normalizedOverrides = [];

    foreach ($overrides as $providerName => $override) {
        $normalizedProvider = strtolower(sanitizeInputString($providerName));
        if (!in_array($normalizedProvider, $supportedProviders, true)) {
            continue;
        }

        if (is_string($override)) {
            $normalizedOverrides[$normalizedProvider] = [
                'mode' => strtolower(sanitizeInputString($override)),
            ];
            continue;
        }

        if (!is_array($override)) {
            continue;
        }

        $normalizedOverrides[$normalizedProvider] = [
            'mode' => strtolower(sanitizeInputString($override['mode'] ?? 'success')),
            'raw' => isset($override['raw']) ? (string) $override['raw'] : null,
            'label' => isset($override['label']) ? sanitizeInputString($override['label']) : null,
        ];
    }

    return $normalizedOverrides;
}

function getConfiguredProviders(array $debugProviderOverrides = []): array
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

function resolveProviderOrder(?string $requestedProvider, array $debugProviderOverrides = []): array
{
    $supportedProviders = ['groq', 'ollama', 'gemini', 'openai', 'perplexity'];
    $configuredProviders = getConfiguredProviders($debugProviderOverrides);

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

function buildDebugQuizResponse(string $providerName, ?string $label = null): string
{
    $providerLabel = $label !== null && $label !== '' ? $label : strtoupper($providerName);
    $questions = [];

    for ($index = 1; $index <= 5; $index++) {
        $questions[] = [
            'question' => $providerLabel . ' - question ' . $index,
            'choices' => [
                ['value' => 'a', 'label' => 'Choix A'],
                ['value' => 'b', 'label' => 'Choix B'],
                ['value' => 'c', 'label' => 'Choix C'],
                ['value' => 'd', 'label' => 'Choix D'],
            ],
            'correct' => 'a',
        ];
    }

    $payload = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payload === false) {
        throw new RuntimeException('Impossible de construire le quiz de debug');
    }

    return $payload;
}

function getQuizProviderResponse(string $providerName, string $prompt, array $debugProviderOverrides): string
{
    if (isset($debugProviderOverrides[$providerName])) {
        $override = $debugProviderOverrides[$providerName];
        $mode = $override['mode'] ?? 'success';

        switch ($mode) {
            case 'empty':
                return '';

            case 'invalid_json':
                return '{"questions": [';

            case 'incomplete':
            case 'partial':
                return '[{"question":"Question incomplète","choices":["A","B"],"correct":"A"}]';

            case 'timeout':
                throw new RuntimeException('Timeout simulé du provider');

            case 'raw':
                return (string) ($override['raw'] ?? '');

            case 'success':
            default:
                return buildDebugQuizResponse($providerName, $override['label'] ?? null);
        }
    }

    return (string) callAIProvider($providerName, $prompt);
}

function extractJsonArrayFromResponse(string $aiResponse): string
{
    $trimmedResponse = trim($aiResponse);
    $jsonStart = strpos($trimmedResponse, '[');
    $jsonEnd = strrpos($trimmedResponse, ']');

    if ($jsonStart === false || $jsonEnd === false || $jsonEnd < $jsonStart) {
        throw new RuntimeException('Réponse IA inexploitable');
    }

    return substr($trimmedResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
}

function extractNumericValueFromLabel(string $label): ?float
{
    if (!preg_match('/-?\d+(?:[\.,]\d+)?/', $label, $matches)) {
        return null;
    }

    $raw = str_replace(',', '.', $matches[0]);
    if (!is_numeric($raw)) {
        return null;
    }

    return (float) $raw;
}

function extractArithmeticExpressionFromQuestion(string $questionText): ?string
{
    $normalized = mb_strtolower($questionText, 'UTF-8');
    $normalized = str_replace(['×', 'x', 'X', '÷'], ['*', '*', '*', '/'], $normalized);

    if (!preg_match_all('/[\d\.,\s\+\-\*\/\(\)]{3,}/u', $normalized, $matches)) {
        return null;
    }

    $bestCandidate = null;
    foreach ($matches[0] as $candidate) {
        $candidate = preg_replace('/\s+/', '', (string) $candidate);
        if ($candidate === null || $candidate === '') {
            continue;
        }

        if (!preg_match('/\d/', $candidate) || !preg_match('/[\+\-\*\/]/', $candidate)) {
            continue;
        }

        if ($bestCandidate === null || strlen($candidate) > strlen($bestCandidate)) {
            $bestCandidate = $candidate;
        }
    }

    return $bestCandidate;
}

function tokenizeArithmeticExpression(string $expression): ?array
{
    $tokens = [];
    $length = strlen($expression);
    $index = 0;

    while ($index < $length) {
        $char = $expression[$index];

        if (ctype_space($char)) {
            $index++;
            continue;
        }

        if (preg_match('/[\+\*\/\(\)]/', $char)) {
            $tokens[] = $char;
            $index++;
            continue;
        }

        if ($char === '-') {
            $previousToken = end($tokens);
            $isUnaryMinus = empty($tokens) || $previousToken === '(' || in_array($previousToken, ['+', '-', '*', '/'], true);

            if ($isUnaryMinus) {
                $number = '-';
                $index++;
                while ($index < $length && preg_match('/[\d\.,]/', $expression[$index])) {
                    $number .= $expression[$index];
                    $index++;
                }

                if ($number === '-' || !preg_match('/^-?\d+(?:[\.,]\d+)?$/', $number)) {
                    return null;
                }

                $tokens[] = str_replace(',', '.', $number);
                continue;
            }

            $tokens[] = '-';
            $index++;
            continue;
        }

        if (preg_match('/[\d\.,]/', $char)) {
            $number = '';
            while ($index < $length && preg_match('/[\d\.,]/', $expression[$index])) {
                $number .= $expression[$index];
                $index++;
            }

            $number = str_replace(',', '.', $number);
            if (!preg_match('/^-?\d+(?:\.\d+)?$/', $number)) {
                return null;
            }

            $tokens[] = $number;
            continue;
        }

        return null;
    }

    return $tokens;
}

function arithmeticOperatorPrecedence(string $operator): int
{
    if ($operator === '+' || $operator === '-') {
        return 1;
    }
    if ($operator === '*' || $operator === '/') {
        return 2;
    }

    return 0;
}

function applyArithmeticOperator(float $left, float $right, string $operator): ?float
{
    switch ($operator) {
        case '+':
            return $left + $right;
        case '-':
            return $left - $right;
        case '*':
            return $left * $right;
        case '/':
            if (abs($right) < 0.0000001) {
                return null;
            }
            return $left / $right;
        default:
            return null;
    }
}

function evaluateArithmeticExpression(string $expression): ?float
{
    $tokens = tokenizeArithmeticExpression($expression);
    if ($tokens === null || count($tokens) === 0) {
        return null;
    }

    $values = [];
    $operators = [];

    $reduce = static function () use (&$values, &$operators): bool {
        if (count($values) < 2 || count($operators) < 1) {
            return false;
        }

        $operator = array_pop($operators);
        $right = (float) array_pop($values);
        $left = (float) array_pop($values);
        $result = applyArithmeticOperator($left, $right, (string) $operator);

        if ($result === null) {
            return false;
        }

        $values[] = $result;
        return true;
    };

    foreach ($tokens as $token) {
        if (preg_match('/^-?\d+(?:\.\d+)?$/', (string) $token)) {
            $values[] = (float) $token;
            continue;
        }

        if ($token === '(') {
            $operators[] = $token;
            continue;
        }

        if ($token === ')') {
            while (!empty($operators) && end($operators) !== '(') {
                if (!$reduce()) {
                    return null;
                }
            }

            if (empty($operators) || end($operators) !== '(') {
                return null;
            }

            array_pop($operators);
            continue;
        }

        if (!in_array($token, ['+', '-', '*', '/'], true)) {
            return null;
        }

        while (!empty($operators)
            && end($operators) !== '('
            && arithmeticOperatorPrecedence((string) end($operators)) >= arithmeticOperatorPrecedence((string) $token)
        ) {
            if (!$reduce()) {
                return null;
            }
        }

        $operators[] = $token;
    }

    while (!empty($operators)) {
        if (end($operators) === '(' || end($operators) === ')') {
            return null;
        }

        if (!$reduce()) {
            return null;
        }
    }

    if (count($values) !== 1) {
        return null;
    }

    return (float) $values[0];
}

function inferArithmeticExpectedValue(string $questionText): ?float
{
    $expression = extractArithmeticExpressionFromQuestion($questionText);
    if ($expression === null || $expression === '') {
        return null;
    }

    return evaluateArithmeticExpression($expression);
}

function normalizeQuizQuestions(array $questions): array
{
    $normalizedQuestions = [];

    foreach ($questions as $question) {
        if (!is_array($question)) {
            continue;
        }

        $questionText = trim((string) ($question['question'] ?? ''));
        $choices = $question['choices'] ?? [];
        $correct = trim((string) ($question['correct'] ?? ''));

        if ($questionText === '' || !is_array($choices) || count($choices) < 2 || $correct === '') {
            continue;
        }

        $normalizedChoices = [];
        foreach ($choices as $choice) {
            if (is_array($choice)) {
                $value = trim((string) ($choice['value'] ?? ''));
                $label = trim((string) ($choice['label'] ?? ''));
            } else {
                $value = trim((string) $choice);
                $label = $value;
            }

            if ($value === '' || $label === '') {
                continue;
            }

            $normalizedChoices[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        if (count($normalizedChoices) < 2) {
            continue;
        }

        $validCorrect = false;
        foreach ($normalizedChoices as $choice) {
            if ($choice['value'] === $correct || $choice['label'] === $correct) {
                $correct = $choice['value'];
                $validCorrect = true;
                break;
            }
        }

        if (!$validCorrect) {
            continue;
        }

        $expectedValue = inferArithmeticExpectedValue($questionText);
        if ($expectedValue !== null) {
            foreach ($normalizedChoices as $choice) {
                $choiceNumericValue = extractNumericValueFromLabel((string) $choice['label']);
                if ($choiceNumericValue === null) {
                    continue;
                }

                if (abs($choiceNumericValue - $expectedValue) < 0.0001) {
                    $correct = (string) $choice['value'];
                    break;
                }
            }
        }

        $normalizedQuestions[] = [
            'question' => $questionText,
            'choices' => array_slice($normalizedChoices, 0, 6),
            'correct' => $correct,
        ];
    }

    return array_slice($normalizedQuestions, 0, 5);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Méthode non autorisée'], 405);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer les données POST (supporte les deux nomenclatures : niveau/matiere ET level/subject)
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    jsonResponse(['success' => false, 'error' => 'Format JSON invalide en entrée'], 400);
}

$csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($data['csrf_token'] ?? ''));
$csrfValid = function_exists('verifyCSRFToken')
    ? verifyCSRFToken($csrfToken)
    : (
        isset($_SESSION['csrf_token'])
        && $csrfToken !== ''
        && hash_equals((string) $_SESSION['csrf_token'], $csrfToken)
    );

$cliToken = (string) ($_SERVER['HTTP_X_CLI_TOKEN'] ?? ($data['cli_token'] ?? ''));
$cliTokenValid = $cliToken !== ''
    && getenv('MCSPHP_CLI_API_TOKEN')
    && hash_equals((string) getenv('MCSPHP_CLI_API_TOKEN'), $cliToken);

if (!$csrfValid && !$cliTokenValid) {
    jsonResponse(['success' => false, 'error' => 'Jeton CSRF invalide'], 403);
}

$level = $data['level'] ?? $data['niveau'] ?? '';
$subject = $data['subject'] ?? $data['matiere'] ?? '';
$topic = $data['topic'] ?? $data['theme'] ?? $data['focus'] ?? '';

$type = $data['type'] ?? 'quiz';
$provider = $data['provider'] ?? null; // Permettre de forcer le provider (ex: 'ollama')
$outputMode = $data['output_mode'] ?? 'quiz';

if (empty($level) || empty($subject)) {
    jsonResponse(['success' => false, 'error' => 'Niveau et matière requis'], 422);
}

// Nettoyage des paramètres
$level = sanitizeInputString($level);
$subject = sanitizeInputString($subject);
$topic = sanitizeInputString($topic);
$type = sanitizeInputString($type);
$provider = $provider !== null ? strtolower(sanitizeInputString($provider)) : null;
$outputMode = strtolower(sanitizeInputString($outputMode));

try {
    $level = resolveLockedLevelFromSession($pdo ?? null, $level, is_array($data) ? $data : []);
} catch (Throwable $levelResolveError) {
    logQuizAiMessage('[Quiz AI] Level lock fallback: ' . $levelResolveError->getMessage(), true);
}

if (!in_array($outputMode, ['quiz', 'exercise'], true)) {
    $outputMode = 'quiz';
}

// Prompt pour l'IA
$allowedTypes = ['qcm' => 'QCM', 'vrai/faux' => 'vrai/faux', 'qcu' => 'QCU', 'texte' => 'texte'];
$typeKey = strtolower($type);
$desiredType = $allowedTypes[$typeKey] ?? 'QCM';

$prompt = <<<PROMPT
Génère un exercice de type "$desiredType" pour un élève de niveau $level en $subject.
L'exercice doit contenir EXACTEMENT 5 questions/items variés.
Chaque question doit être pédagogique et adaptée au programme scolaire français.

IMPORTANT : Tu dois répondre UNIQUEMENT avec un objet JSON au format suivant :
[
  {
    "question": "Enoncé de la question ?",
    "choices": [
      {"value": "a", "label": "a) Choix 1"},
      {"value": "b", "label": "b) Choix 2"},
      {"value": "c", "label": "c) Choix 3"},
      {"value": "d", "label": "d) Choix 4"}
    ],
    "correct": "a"
  }
]
Ne mets aucun texte avant ou après le JSON.
PROMPT;

if ($topic !== '') {
    $prompt .= "\n\nLe thème ou sous-thème ciblé est : {$topic}.";
}

try {
    // Autoriser uniquement les niveaux / matières reconnus pour limiter les erreurs et injections
    $allowedLevels = ['6eme','5eme','4eme','3eme','2nde','1ere','terminale','bac','6ème','5ème','4ème','3ème'];
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

    // Vérification souple (insensible à la casse)
    $levelFound = false;
    foreach ($allowedLevels as $al) {
        if (strcasecmp($level, $al) === 0) {
            $levelFound = true;
            break;
        }
    }

    $subjectFound = false;
    foreach ($allowedSubjects as $as) {
        if (strcasecmp($subject, $as) === 0) {
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

    $debugProviderOverrides = getDebugProviderOverrides($data);
    $providers = resolveProviderOrder($provider, $debugProviderOverrides);

    $usedProvider = null;
    $questions = [];
    $providerFailures = [];

    foreach ($providers as $tryProvider) {
        try {
            $aiResponse = getQuizProviderResponse($tryProvider, $prompt, $debugProviderOverrides);
            if (trim($aiResponse) === '') {
                throw new RuntimeException('Réponse vide du provider');
            }

            $aiJsonArray = extractJsonArrayFromResponse($aiResponse);
            $decodedQuestions = json_decode($aiJsonArray, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedQuestions)) {
                throw new RuntimeException('JSON invalide: ' . json_last_error_msg());
            }

            $normalizedQuestions = normalizeQuizQuestions($decodedQuestions);
            if (count($normalizedQuestions) < 3) {
                throw new RuntimeException('Quiz incomplet après normalisation');
            }

            $questions = $normalizedQuestions;
            $usedProvider = $tryProvider;
            break;
        } catch (Throwable $providerError) {
            $providerFailures[] = $tryProvider . ': ' . $providerError->getMessage();
            logQuizAiMessage('[Quiz AI] Provider failure: ' . $tryProvider . ' - ' . $providerError->getMessage(), true);
        }
    }

    if ($usedProvider === null) {
        if (!empty($providerFailures)) {
            logQuizAiMessage('[Quiz AI] All providers failed: ' . implode(' | ', array_slice($providerFailures, 0, 5)), true);
        }
        throw new RuntimeException('Impossible de générer le quiz IA pour le moment');
    }

    // Conversion en HTML pour interactive-exercises.js
    $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

    if ($questionsJson === false) {
        throw new RuntimeException('Impossible de preparer le quiz pour affichage');
    }

    $renderTitle = $outputMode === 'exercise' ? '🤖 Exercice IA' : '🤖 Quiz IA';
    $renderSource = $outputMode === 'exercise' ? 'exercise-ai' : 'quiz-ai';

    $html = '<div class="qcm-exercise" data-questions=\'' . $questionsJson . '\' data-level="' . htmlspecialchars($level, ENT_QUOTES, 'UTF-8') . '" data-subject="' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '" data-source="' . htmlspecialchars($renderSource, ENT_QUOTES, 'UTF-8') . '">';
    $html .= '    <h3 class="text-xl font-bold mb-4">' . htmlspecialchars($renderTitle . ' : ' . $subject . ' (' . $level . ')', ENT_QUOTES, 'UTF-8') . '</h3>';
    $html .= '    <div class="qcm-container"></div>';
    $html .= '    <div class="mt-6 flex gap-4">';
    $html .= '        <button class="btn-check-qcm px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">✅ Vérifier mes réponses</button>';
    $html .= '        <button onclick="location.reload()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Actualiser</button>';
    $html .= '    </div>';
    $html .= '    <div class="qcm-feedback mt-4 p-4 rounded-lg hidden"></div>';
    $html .= '</div>';

    jsonResponse([
        'success' => true,
        'quiz_html' => $html,
        'questions' => $questions,
        'subject' => $subject,
        'level' => $level,
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
        logQuizAiMessage('[Quiz AI] Validation error: ' . $e->getMessage());
    } else {
        logQuizAiMessage('[Quiz AI] Endpoint error: ' . $e->getMessage(), true);
        logQuizAiMessage('[Quiz AI] Endpoint error');
    }

    jsonResponse([
        'success' => false,
        'error' => $statusCode === 422 ? $e->getMessage() : 'Generation du quiz indisponible pour le moment',
    ], $statusCode);
}
