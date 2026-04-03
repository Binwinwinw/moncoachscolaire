<?php
/**
 * API pour la génération de Quiz par IA
 * Reçoit niveau et matière, retourne le HTML interactif
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/ai_course_generator.php';
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

function getConfiguredProviders(): array
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

    return $configured;
}

function resolveProviderOrder(?string $requestedProvider): array
{
    $supportedProviders = ['groq', 'ollama', 'gemini', 'openai', 'perplexity'];
    $configuredProviders = getConfiguredProviders();

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

if (!$csrfValid) {
    jsonResponse(['success' => false, 'error' => 'Jeton CSRF invalide'], 403);
}

$level = $data['level'] ?? $data['niveau'] ?? '';
$subject = $data['subject'] ?? $data['matiere'] ?? '';

$type = $data['type'] ?? 'quiz';
$provider = $data['provider'] ?? null; // Permettre de forcer le provider (ex: 'ollama')

if (empty($level) || empty($subject)) {
    jsonResponse(['success' => false, 'error' => 'Niveau et matière requis'], 422);
}

// Nettoyage des paramètres
$level = sanitizeInputString($level);
$subject = sanitizeInputString($subject);
$type = sanitizeInputString($type);
$provider = $provider !== null ? strtolower(sanitizeInputString($provider)) : null;

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

    $providers = resolveProviderOrder($provider);

    $aiResponse = null;
    $lastError = null;
    $usedProvider = null;
    foreach ($providers as $tryProvider) {
        try {
            $aiResponse = callAIProvider($tryProvider, $prompt);
            if (!empty($aiResponse)) {
                $usedProvider = $tryProvider;
                break;
            }
        } catch (Throwable $e) {
            $lastError = $e->getMessage();
            error_log('[Quiz AI] Provider failure: ' . $tryProvider . ' - ' . $e->getMessage());
        }
    }

    if ($aiResponse === null) {
        throw new RuntimeException('Impossible de générer le quiz IA pour le moment');
    }

    $aiResponse = extractJsonArrayFromResponse($aiResponse);

    $questions = json_decode($aiResponse, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
        error_log('[Quiz AI] JSON parse error: ' . json_last_error_msg() . ' | provider=' . ($usedProvider ?? 'unknown'));
        throw new RuntimeException('La reponse du service IA est invalide');
    }

    $questions = normalizeQuizQuestions($questions);

    if (count($questions) < 3) {
        error_log('[Quiz AI] Not enough valid questions after normalization | provider=' . ($usedProvider ?? 'unknown') . ' | last_error=' . ($lastError ?? 'none'));
        throw new RuntimeException('Le quiz genere est incomplet. Merci de reessayer.');
    }

    // Conversion en HTML pour interactive-exercises.js
    $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

    if ($questionsJson === false) {
        throw new RuntimeException('Impossible de preparer le quiz pour affichage');
    }

    $html = '<div class="qcm-exercise" data-questions=\'' . $questionsJson . '\'>';
    $html .= '    <h3 class="text-xl font-bold mb-4">🤖 Quiz IA : ' . $subject . ' (' . $level . ')</h3>';
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

    error_log('[Quiz AI] Endpoint error: ' . $e->getMessage());

    jsonResponse([
        'success' => false,
        'error' => $statusCode === 422 ? $e->getMessage() : 'Generation du quiz indisponible pour le moment',
    ], $statusCode);
}
