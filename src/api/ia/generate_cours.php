<?php
/**
 * API pour la génération de cours par IA (hub ?page=cours)
 * Contrat aligné sur generate_quiz.php : cours_html + alias quiz_html
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/ai_course_generator.php';
require_once __DIR__ . '/../../includes/exercice_loader.php';
if (is_file(__DIR__ . '/../../includes/login_security.php')) {
    require_once __DIR__ . '/../../includes/login_security.php';
}
require_once __DIR__ . '/../_core/bootstrap.php';

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

api_require([
    'method' => 'POST',
    'rate' => [
        'key' => 'ai_course',
        'limit' => 10,
        'window' => 60,
    ],
]);

function coursJsonResponse(array $payload, int $statusCode = 200): void
{
    if (function_exists('api_additive_response')) {
        $data = [];
        if (isset($payload['data']) && is_array($payload['data'])) {
            $data = $payload['data'];
        }

        $meta = [];
        if (isset($payload['meta']) && is_array($payload['meta'])) {
            $meta = $payload['meta'];
        }

        api_additive_response($payload, $statusCode, $data, $meta);
    }

    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function coursSanitizeInputString($value): string
{
    return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
}

function coursNormalizeLevelForGenerator(string $level): string
{
    $normalized = $level;
    if (function_exists('normalize_school_level')) {
        $normalized = (string) normalize_school_level($level);
    }

    return coursSanitizeInputString($normalized);
}

function coursExtractLevelFromUserRow(array $row): string
{
    $level = '';

    if (isset($row['level']) && (string) $row['level'] !== '') {
        $level = (string) $row['level'];
    } elseif (isset($row['UserLevel']) && (string) $row['UserLevel'] !== '') {
        $level = (string) $row['UserLevel'];
    } elseif (isset($row['user_level']) && (string) $row['user_level'] !== '') {
        $level = (string) $row['user_level'];
    }

    return coursNormalizeLevelForGenerator($level);
}

function coursResolveLockedLevelFromSession(?PDO $pdo, string $requestedLevel, array $data): string
{
    $fallbackLevel = coursNormalizeLevelForGenerator($requestedLevel);
    $contextPage = strtolower(coursSanitizeInputString($data['context_page'] ?? ''));
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
                $childLevel = coursExtractLevelFromUserRow($childRow);
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

    $lockedLevel = coursExtractLevelFromUserRow($userRow);
    return $lockedLevel !== '' ? $lockedLevel : $fallbackLevel;
}

function isCoursAiDebugMode(): bool
{
    $appEnv = strtolower((string) (getenv('APP_ENV') ?: ($GLOBALS['appEnv'] ?? '')));
    $appDebug = filter_var(getenv('APP_DEBUG') ?: getenv('DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);

    return $appEnv === 'local' || $appDebug;
}

function logCoursAiMessage(string $message, bool $debugOnly = false): void
{
    if ($debugOnly && !isCoursAiDebugMode()) {
        return;
    }

    error_log($message);
}

function getCoursDebugProviderOverrides(array $data): array
{
    if (!isCoursAiDebugMode()) {
        return [];
    }

    $overrides = $data['_debug_provider_overrides'] ?? null;
    if (!is_array($overrides)) {
        return [];
    }

    $supportedProviders = ['groq', 'ollama', 'gemini', 'openai', 'perplexity'];
    $normalizedOverrides = [];

    foreach ($overrides as $providerName => $override) {
        $normalizedProvider = strtolower(coursSanitizeInputString($providerName));
        if (!in_array($normalizedProvider, $supportedProviders, true)) {
            continue;
        }

        if (is_string($override)) {
            $normalizedOverrides[$normalizedProvider] = [
                'mode' => strtolower(coursSanitizeInputString($override)),
            ];
            continue;
        }

        if (!is_array($override)) {
            continue;
        }

        $normalizedOverrides[$normalizedProvider] = [
            'mode' => strtolower(coursSanitizeInputString($override['mode'] ?? 'success')),
            'raw' => isset($override['raw']) ? (string) $override['raw'] : null,
            'label' => isset($override['label']) ? coursSanitizeInputString($override['label']) : null,
        ];
    }

    return $normalizedOverrides;
}

function getCoursConfiguredProviders(array $debugProviderOverrides = []): array
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

function resolveCoursProviderOrder(?string $requestedProvider, array $debugProviderOverrides = []): array
{
    $supportedProviders = ['groq', 'ollama', 'gemini', 'openai', 'perplexity'];
    $configuredProviders = getCoursConfiguredProviders($debugProviderOverrides);

    if (empty($configuredProviders)) {
        throw new RuntimeException('Aucun provider IA configuré.');
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

function buildDebugCourseJson(string $providerName, string $level, string $subject, ?string $label = null): string
{
    $providerLabel = $label !== null && $label !== '' ? $label : strtoupper($providerName);
    $course = [
        'title' => $providerLabel . ' — Cours ' . $subject,
        'subject' => $subject,
        'level' => $level,
        'duration' => 30,
        'difficulty' => 'moyen',
        'introduction' => 'Introduction de démonstration pour ' . $subject . ' (' . $level . ').',
        'objectives' => ['Comprendre les bases', 'S\'entraîner avec des exemples'],
        'sections' => [
            [
                'id' => 1,
                'title' => 'Section 1 — Notions clés',
                'type' => 'theory',
                'content' => 'Contenu généré en mode debug.',
            ],
        ],
        'key_points' => ['Point clé 1', 'Point clé 2'],
        'summary' => 'Résumé du mini-cours de démonstration.',
    ];

    $payload = json_encode($course, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payload === false) {
        throw new RuntimeException('Impossible de construire le cours de debug');
    }

    return $payload;
}

function getCoursProviderResponse(string $providerName, string $prompt, array $debugProviderOverrides, string $level, string $subject): string
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
                return buildDebugCourseJson($providerName, $level, $subject, $override['label'] ?? null);
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

function normalizeGeneratedCourse(array $payload, string $level, string $subject): array
{
    $title = trim((string) ($payload['title'] ?? ''));
    if ($title === '') {
        $title = 'Cours IA — ' . $subject;
    }

    $sections = [];
    if (!empty($payload['sections']) && is_array($payload['sections'])) {
        foreach ($payload['sections'] as $index => $section) {
            if (!is_array($section)) {
                continue;
            }
            $sectionTitle = trim((string) ($section['title'] ?? ''));
            $sectionContent = trim((string) ($section['content'] ?? ''));
            if ($sectionTitle === '' && $sectionContent === '') {
                continue;
            }
            $sections[] = [
                'id' => $section['id'] ?? ($index + 1),
                'title' => $sectionTitle !== '' ? $sectionTitle : 'Section ' . ($index + 1),
                'type' => $section['type'] ?? 'theory',
                'content' => $sectionContent,
                'examples' => is_array($section['examples'] ?? null) ? $section['examples'] : [],
            ];
        }
    }

    $objectives = [];
    if (!empty($payload['objectives']) && is_array($payload['objectives'])) {
        foreach ($payload['objectives'] as $objective) {
            $objectiveText = trim((string) $objective);
            if ($objectiveText !== '') {
                $objectives[] = $objectiveText;
            }
        }
    }

    return [
        'title' => $title,
        'subject' => trim((string) ($payload['subject'] ?? $subject)),
        'level' => trim((string) ($payload['level'] ?? $level)),
        'duration' => (int) ($payload['duration'] ?? 30),
        'difficulty' => trim((string) ($payload['difficulty'] ?? 'moyen')),
        'introduction' => trim((string) ($payload['introduction'] ?? '')),
        'objectives' => $objectives,
        'sections' => array_slice($sections, 0, 5),
        'key_points' => is_array($payload['key_points'] ?? null) ? $payload['key_points'] : [],
        'summary' => trim((string) ($payload['summary'] ?? '')),
    ];
}

function renderGeneratedCourseHtml(array $course): string
{
    $title = htmlspecialchars($course['title'] ?? 'Cours IA', ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($course['subject'] ?? '', ENT_QUOTES, 'UTF-8');
    $level = htmlspecialchars($course['level'] ?? '', ENT_QUOTES, 'UTF-8');

    $html = '<article class="cours-ia-preview ai-generated-course">';
    $html .= '<header class="cours-ia-preview-header mb-4 pb-4 border-b border-slate-200">';
    $html .= '<h3 class="text-xl md:text-2xl font-bold text-slate-800 leading-snug">🤖 ' . $title . '</h3>';
    $html .= '<p class="text-sm text-slate-500 mt-2">' . $subject . ' · ' . $level . '</p>';
    $html .= '</header>';

    if (!empty($course['introduction'])) {
        $html .= '<p class="cours-ia-preview-intro text-slate-700 leading-relaxed mb-4">' . nl2br(htmlspecialchars($course['introduction'], ENT_QUOTES, 'UTF-8')) . '</p>';
    }

    if (!empty($course['objectives']) && is_array($course['objectives'])) {
        $html .= '<div class="cours-ia-preview-objectives mb-5 p-4 bg-emerald-50 rounded-xl border border-emerald-100">';
        $html .= '<p class="text-sm font-bold text-emerald-800 uppercase tracking-wide mb-2">Objectifs</p>';
        $html .= '<ul class="list-disc list-inside text-slate-700 space-y-1.5">';
        foreach ($course['objectives'] as $objective) {
            $html .= '<li>' . htmlspecialchars((string) $objective, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $html .= '</ul></div>';
    }

    if (!empty($course['sections']) && is_array($course['sections'])) {
        $html .= '<div class="cours-ia-preview-sections space-y-3">';
        foreach ($course['sections'] as $index => $section) {
            $sectionTitle = htmlspecialchars((string) ($section['title'] ?? 'Section'), ENT_QUOTES, 'UTF-8');
            $sectionContent = nl2br(htmlspecialchars((string) ($section['content'] ?? ''), ENT_QUOTES, 'UTF-8'));
            $openAttr = $index === 0 ? ' open' : '';
            $html .= '<details class="cours-accordion border border-slate-200 rounded-xl bg-white"' . $openAttr . '>';
            $html .= '<summary class="cours-accordion-summary font-semibold cursor-pointer text-slate-800 px-4 py-3">' . $sectionTitle . '</summary>';
            $html .= '<div class="cours-accordion-content px-4 pb-4 text-slate-700 leading-relaxed border-t border-slate-100">' . $sectionContent . '</div>';
            $html .= '</details>';
        }
        $html .= '</div>';
    }

    if (!empty($course['summary'])) {
        $html .= '<div class="cours-ia-preview-summary mt-5 p-4 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 leading-relaxed">';
        $html .= '<strong class="text-slate-800">Résumé :</strong> ';
        $html .= nl2br(htmlspecialchars($course['summary'], ENT_QUOTES, 'UTF-8')) . '</div>';
    }

    $html .= '</article>';

    return $html;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    coursJsonResponse(['success' => false, 'error' => 'Format JSON invalide en entrée'], 400);
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
    coursJsonResponse(['success' => false, 'error' => 'Jeton CSRF invalide'], 403);
}

$level = coursSanitizeInputString($data['level'] ?? $data['niveau'] ?? '');
$subject = coursSanitizeInputString($data['subject'] ?? $data['matiere'] ?? '');
$topic = coursSanitizeInputString($data['topic'] ?? $data['theme'] ?? $data['type'] ?? '');
$provider = isset($data['provider']) ? strtolower(coursSanitizeInputString($data['provider'])) : null;

try {
    $level = coursResolveLockedLevelFromSession($pdo ?? null, $level, is_array($data) ? $data : []);
} catch (Throwable $levelResolveError) {
    logCoursAiMessage('[Cours AI] Level lock fallback: ' . $levelResolveError->getMessage(), true);
}

if ($level === '' || $subject === '') {
    coursJsonResponse(['success' => false, 'error' => 'Niveau et matière requis'], 422);
}

$allowedLevels = ['6eme', '5eme', '4eme', '3eme', '2nde', '1ere', 'terminale', 'bac', '6ème', '5ème', '4ème', '3ème'];
$allowedSubjects = [
    'Mathematiques', 'Mathématiques', 'mathématiques', 'maths',
    'Français', 'francais',
    'Physique-Chimie', 'physique-chimie',
    'SVT', 'svt',
    'Histoire-Géographie', 'Histoire-Géo', 'histoire-geographie', 'histoire', 'geographie',
    'Anglais', 'anglais',
    'Espagnol', 'espagnol',
    'Philosophie', 'philosophie', 'philo',
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
    coursJsonResponse(['success' => false, 'error' => 'Niveau invalide'], 422);
}
if (!$subjectFound) {
    coursJsonResponse(['success' => false, 'error' => 'Matière invalide'], 422);
}

$levelDb = function_exists('normalizeLevelForDB') ? normalizeLevelForDB($level) : $level;

try {
    $analysis = [
        'level' => $levelDb,
        'subject' => $subject,
        'concepts' => [
            [
                'title' => $topic !== '' ? $topic : 'Programme scolaire',
                'domain' => $subject,
            ],
        ],
    ];

    $prompt = buildCoursePrompt($analysis);
    if ($topic !== '') {
        $prompt .= "\n\nThème ou angle pédagogique demandé : {$topic}.";
    }

    $debugProviderOverrides = getCoursDebugProviderOverrides($data);
    $providers = resolveCoursProviderOrder($provider, $debugProviderOverrides);

    $usedProvider = null;
    $course = [];
    $providerFailures = [];

    foreach ($providers as $tryProvider) {
        try {
            $aiResponse = getCoursProviderResponse($tryProvider, $prompt, $debugProviderOverrides, $levelDb, $subject);
            if (trim($aiResponse) === '') {
                throw new RuntimeException('Réponse vide du provider');
            }

            $aiJsonObject = extractJsonObjectFromResponse($aiResponse);
            $decodedPayload = json_decode($aiJsonObject, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedPayload)) {
                throw new RuntimeException('JSON invalide: ' . json_last_error_msg());
            }

            $normalizedCourse = normalizeGeneratedCourse($decodedPayload, $levelDb, $subject);
            if (empty($normalizedCourse['sections']) && $normalizedCourse['introduction'] === '') {
                throw new RuntimeException('Cours incomplet après normalisation');
            }

            $course = $normalizedCourse;
            $usedProvider = $tryProvider;
            break;
        } catch (Throwable $providerError) {
            $providerFailures[] = $tryProvider . ': ' . $providerError->getMessage();
            logCoursAiMessage('[Cours AI] Provider failure: ' . $tryProvider . ' - ' . $providerError->getMessage(), true);
        }
    }

    if ($usedProvider === null) {
        if (!empty($providerFailures)) {
            logCoursAiMessage('[Cours AI] All providers failed: ' . implode(' | ', array_slice($providerFailures, 0, 5)), true);
        }
        throw new RuntimeException('Impossible de générer le cours IA pour le moment');
    }

    $html = renderGeneratedCourseHtml($course);

    coursJsonResponse([
        'success' => true,
        'cours_html' => $html,
        'quiz_html' => $html,
        'cours' => $course,
        'level' => $levelDb,
        'matiere' => $subject,
        'subject' => $subject,
        'provider_used' => $usedProvider,
        'data' => [
            'course' => $course,
            'html' => $html,
        ],
        'meta' => [
            'generated_at' => date('c'),
            'provider' => $usedProvider,
            'level' => $levelDb,
            'subject' => $subject,
        ],
    ]);
} catch (Throwable $e) {
    $statusCode = 500;

    if ($e instanceof InvalidArgumentException) {
        $statusCode = 422;
    } elseif ($e instanceof RuntimeException) {
        $statusCode = 502;
    }

    logCoursAiMessage('[Cours AI] Endpoint error: ' . $e->getMessage(), $statusCode !== 422);

    coursJsonResponse([
        'success' => false,
        'error' => $statusCode === 422 ? $e->getMessage() : 'Génération du cours indisponible pour le moment',
    ], $statusCode);
}
