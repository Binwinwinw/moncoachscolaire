<?php

/**
 * Détermine un type canonique + sa source, sans toucher au front.
 * - type: string (ex: qcm, texte, math, conjugation, ...)
 * - type_source: AnswerType | detect_php | unknown (+ suffix fallback)
 */
function computeExerciseType(array $exercise, $choicesDecoded): array
{
    header('Content-Type: application/json; charset=utf-8');
    $raw = strtolower(trim((string) ($exercise['AnswerType'] ?? '')));
    $type = null;
    $source = 'unknown';

    if ($raw !== '') {
        $source = 'AnswerType';
        if ($raw === 'choix') {
            $type = 'qcm';
        } elseif ($raw === 'qcm') {
            $type = 'qcm';
        } elseif ($raw === 'texte' || $raw === 'text' || $raw === 'saisie') {
            $type = 'texte';
        } else {
            $type = $raw; // valeur brute (si d'autres types historiques)
        }
    } elseif (function_exists('detectExerciseType')) {
        $type = detectExerciseType(
            (string) ($exercise['Subject'] ?? ''),
            (string) ($exercise['Content'] ?? ''),
            (string) ($exercise['Title'] ?? ''),
        );
        $source = 'detect_php';
    } else {
        $type = 'unknown';
        $source = 'unknown';
    }

    // Alignement avec le fallback du rendu: qcm sans Choices => texte
    if ($type === 'qcm') {
        $hasChoices = is_array($choicesDecoded) && count($choicesDecoded) > 0;
        if (!$hasChoices) {
            $type = 'texte';
            $source .= '_fallback_no_choices';
        }
    }

    return [$type, $source];
}
/**
 * API endpoint pour charger les exercices dynamiquement
 *
 * Actions disponibles:
 * - subjects: Retourne les matières disponibles pour un niveau
 * - exercises: Retourne les exercices pour un niveau et une matière
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

// Capturer tout output non désiré pour garantir un JSON propre
ob_start();

api_require([
    'method' => 'GET',
]);

// Charger les dépendances (API dans src/api/exercices)
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/config/config.php';

// Connexion DB (préférence src/database)
if (file_exists($projectRoot . '/database/connection.php')) {
    require_once $projectRoot . '/database/connection.php';
}

// Includes nécessaires
require_once $projectRoot . '/includes/exercice_loader.php';
require_once $projectRoot . '/includes/course_markdown_loader.php';

// Diagnostic de la connexion DB (pour debug)
if (!isset($pdo) || $pdo === null) {
    error_log("API get_exercises.php: PDO non disponible après chargement des dépendances");
    if (isset($dbUnavailable) && $dbUnavailable) {
        error_log("API get_exercises.php: dbUnavailable = true");
    }
}

// S'assurer que exercice_card.php peut être chargé pour handleGetExerciseHTML
if (!function_exists('renderExerciseCard')) {
    // Ne pas charger maintenant, sera chargé dans handleGetExerciseHTML si nécessaire
}

// Récupérer l'action
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'subjects':
            handleGetSubjects();
            break;

        case 'exercises':
            handleGetExercises();
            break;

        case 'exercise_html':
            handleGetExerciseHTML();
            break;

        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    error_log("API get_exercises.php: Exception capturée - " . $e->getMessage() . " | Action: " . ($_GET['action'] ?? 'none') . " | Level: " . ($_GET['level'] ?? 'none'));
    json_error('Erreur lors du chargement des exercices', 400, 'ERR_EXERCISES');
}

/**
 * Retourne les matières disponibles pour un niveau
 */
function handleGetSubjects()
{
    global $pdo;
    if (!$pdo) {
        error_log("API get_exercises.php: Base de données non disponible (pdo est null)");
        throw new Exception('Base de données non disponible. Vérifiez la configuration de la base de données.');
    }
    $level = $_GET['level'] ?? '';
    if (empty($level)) {
        error_log("API get_exercises.php: Niveau non spécifié dans la requête");
        throw new Exception('Niveau non spécifié');
    }
    // Vérifier les droits d'accès sur le niveau demandé
    if (is_file(__DIR__ . '/../../includes/level_access.php')) {
        require_once __DIR__ . '/../../includes/level_access.php';
        if (!can_current_user_access_level($level)) {
            json_error('Accès refusé : niveau supérieur au vôtre', 403, 'ERR_FORBIDDEN');
        }
    }
    $normalizedLevel = normalizeLevelForDB($level);
    if (empty($normalizedLevel)) {
        error_log("API get_exercises.php: Impossible de normaliser le niveau: " . $level);
        throw new Exception('Niveau invalide: ' . htmlspecialchars($level));
    }
    // Utiliser le loader course_key_points
    $subjects = getKeyPointSubjectsByLevels([$normalizedLevel]);
    $subjectIcons = [
        'Mathématiques' => '🧮',
        'Français' => '📚',
        'Histoire-Géographie' => '🗺️',
        'Histoire' => '🗺️',
        'Géographie' => '🌍',
        'SVT' => '🔬',
        'Sciences' => '🔬',
        'Physique-Chimie' => '⚗️',
        'Physique' => '⚗️',
        'Chimie' => '🧪',
        'Anglais' => '🇬🇧',
        'Espagnol' => '🇪🇸',
        'Allemand' => '🇩🇪',
        'Philosophie' => '💭',
        'Philo' => '💭',
    ];
    $subjectsData = [];
    foreach ($subjects as $subjectName) {
        $subjectsData[] = [
            'name' => $subjectName,
            'icon' => $subjectIcons[$subjectName] ?? '📚',
        ];
    }
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    json_response([
        'level' => $normalizedLevel,
        'subjects' => $subjectsData,
    ]);
}

/**
 * Retourne les exercices pour un niveau et une matière
 */
function handleGetExercises()
{
    global $pdo, $projectRoot;
    if (!$pdo) {
        throw new Exception('Base de données non disponible');
    }
    $level = $_GET['level'] ?? '';
    $subject = isset($_GET['subject']) ? $_GET['subject'] : null;
    if (empty($level)) {
        throw new Exception('Niveau non spécifié');
    }

    $accessFile = $projectRoot . '/includes/level_access.php';
    if (!is_file($accessFile)) {
        json_error('Autorisation introuvable', 500, 'ERR_AUTH_MISSING');
    }
    require_once $accessFile;
    if (!function_exists('can_current_user_access_level')) {
        json_error('Autorisation introuvable', 500, 'ERR_AUTH_MISSING');
    }
    if (!can_current_user_access_level($level)) {
        json_error('Accès refusé : niveau supérieur au vôtre', 403, 'ERR_FORBIDDEN');
    }

    $normalizedLevel = normalizeLevelForDB($level);

    // Charger les vrais exercices depuis la table exercises
    $exercises = getExercisesByLevel($normalizedLevel, $subject, 100);

    $exercisesData = [];
    foreach ($exercises as $ex) {
        $exercisesData[] = [
            'Id' => $ex['Id'] ?? $ex['id'] ?? null,
            'Title' => $ex['Title'] ?? $ex['title'] ?? null,
            'Subject' => $ex['Subject'] ?? $ex['subject'] ?? null,
            'Level' => $ex['Level'] ?? $ex['level'] ?? null,
            'AnswerType' => $ex['AnswerType'] ?? $ex['answer_type'] ?? null,
            'Difficulty' => $ex['Difficulty'] ?? $ex['difficulty'] ?? null,
        ];
    }
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    json_response([
        'level' => $normalizedLevel,
        'subject' => $subject,
        'exercises' => $exercisesData,
        'count' => count($exercisesData),
    ]);
}

/**
 * Retourne le HTML complet d'un exercice pour l'affichage
 */
function handleGetExerciseHTML()
{
    global $pdo, $projectRoot;

    if (!$pdo) {
        throw new Exception('Base de données non disponible');
    }

    $exerciseId = $_GET['id'] ?? 0;

    if (empty($exerciseId)) {
        throw new Exception('ID d\'exercice non spécifié');
    }

    // Charger les fonctions nécessaires pour générer le HTML
    $exerciceCardPath = $projectRoot . '/includes/exercice_card.php';
    if (file_exists($exerciceCardPath)) {
        require_once $exerciceCardPath;
    } else {
        throw new Exception('Fichier exercice_card.php non trouvé');
    }

    // Charger le générateur interactif si disponible
    $interactiveGenPath = $projectRoot . '/includes/exercise_interactive_generator.php';
    if (file_exists($interactiveGenPath)) {
        require_once $interactiveGenPath;
    }

    // Charger l'exercice
    $exercise = getExerciseById($exerciseId);

    if (!$exercise) {
        throw new Exception('Exercice non trouvé');
    }

    // Vérification d'accès par niveau (API)
    if (is_file($projectRoot . '/includes/level_access.php')) {
        require_once $projectRoot . '/includes/level_access.php';
        if (!can_current_user_access_level($exercise['Level'] ?? $exercise['level'] ?? '')) {
            json_error('Accès refusé : niveau supérieur au vôtre', 403, 'ERR_FORBIDDEN');
        }
    }

    // Générer le HTML de l'exercice
    ob_start();
    renderExerciseCard($exercise, [
        'showAnswer' => false,
        'showDetails' => true,
        'interactive' => true,
    ]);
    $html = ob_get_clean();

    if (ob_get_level() > 0) {
        ob_end_clean(); // Nettoyer le buffer externe
    }
    json_response([
        'exerciseId' => $exerciseId,
        'html' => $html,
    ]);
}
