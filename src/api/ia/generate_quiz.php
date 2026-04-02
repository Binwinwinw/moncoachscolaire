<?php
/**
 * API pour la génération de Quiz par IA
 * Reçoit niveau et matière, retourne le HTML interactif
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/ai_course_generator.php';

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

// Récupérer les données POST (supporte les deux nomenclatures : niveau/matiere ET level/subject)
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'Format JSON invalide en entrée']);
    exit;
}

$level = $data['level'] ?? $data['niveau'] ?? '';
$subject = $data['subject'] ?? $data['matiere'] ?? '';

$type = $data['type'] ?? 'quiz';
$provider = $data['provider'] ?? null; // Permettre de forcer le provider (ex: 'ollama')

if (empty($level) || empty($subject)) {
    echo json_encode(['success' => false, 'error' => 'Niveau et matière requis']);
    exit;
}

// Nettoyage des paramètres
$level = htmlspecialchars($level);
$subject = htmlspecialchars($subject);

// Prompt pour l'IA
$desiredType = !empty($type) ? $type : 'QCM';

$prompt = <<<PROMPT
Génère un exercice de type "$type" pour un élève de niveau $level en $subject.
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
        throw new Exception('Niveau invalide : ' . $level);
    }
    if (!$subjectFound) {
        throw new Exception('Matière invalide : ' . $subject);
    }

    // Déterminer le provider à utiliser
    $providers = [];
    if ($provider) {
        // Si le provider est explicitement demandé (ex: 'ollama'), on le met en tête
        $providers[] = $provider;
    }
    // Ajout auto des providers configurés
    if (getenv('OLLAMA_API_URL')) {
        if (!in_array('ollama', $providers)) $providers[] = 'ollama';
    }
    if (getenv('GROQ_API_KEY') && getenv('GROQ_API_KEY') !== 'your_groq_api_key_here') {
        if (!in_array('groq', $providers)) $providers[] = 'groq';
    }
    if (getenv('GEMINI_API_KEY') && getenv('GEMINI_API_KEY') !== 'your_gemini_api_key_here') {
        if (!in_array('gemini', $providers)) $providers[] = 'gemini';
    }
    if (getenv('OPENAI_API_KEY') && getenv('OPENAI_API_KEY') !== 'your_openai_api_key_here') {
        if (!in_array('openai', $providers)) $providers[] = 'openai';
    }
    if (getenv('PERPLEXITY_API_KEY') && getenv('PERPLEXITY_API_KEY') !== 'your_perplexity_api_key_here') {
        if (!in_array('perplexity', $providers)) $providers[] = 'perplexity';
    }

    if (empty($providers)) {
        throw new Exception('Aucun provider IA configuré. Veuillez renseigner OLLAMA_API_URL ou une clé API dans .env');
    }

    $aiResponse = null;
    $lastError = null;
    foreach ($providers as $tryProvider) {
        try {
            $aiResponse = callAIProvider($tryProvider, $prompt);
            if (!empty($aiResponse)) {
                break;
            }
        } catch (Throwable $e) {
            $lastError = $e->getMessage();
        }
    }

    if ($aiResponse === null) {
        throw new Exception('Impossible de générer le quiz IA. Détails : ' . ($lastError ?? 'aucune réponse disponible'));
    }

    // Nettoyage du JSON (au cas où l'IA ajoute des blocs de code markdown)
    $jsonStart = strpos($aiResponse, '[');
    $jsonEnd = strrpos($aiResponse, ']');
    if ($jsonStart !== false && $jsonEnd !== false) {
        $aiResponse = substr($aiResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
    }

    $questions = json_decode($aiResponse, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questions)) {
        throw new Exception("Erreur de parsing JSON de l'IA : " . json_last_error_msg());
    }

    // Conversion en HTML pour interactive-exercises.js
    $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

    $html = '<div class="qcm-exercise" data-questions=\'' . $questionsJson . '\'>';
    $html .= '    <h3 class="text-xl font-bold mb-4">🤖 Quiz IA : ' . $subject . ' (' . $level . ')</h3>';
    $html .= '    <div class="qcm-container"></div>';
    $html .= '    <div class="mt-6 flex gap-4">';
    $html .= '        <button class="btn-check-qcm px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">✅ Vérifier mes réponses</button>';
    $html .= '        <button onclick="location.reload()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Actualiser</button>';
    $html .= '    </div>';
    $html .= '    <div class="qcm-feedback mt-4 p-4 rounded-lg hidden"></div>';
    $html .= '</div>';

    echo json_encode([
        'success' => true,
        'quiz_html' => $html,
        'questions' => $questions,
        'subject' => $subject,
        'level' => $level
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
