<?php

/**
 * API pour récupérer le contenu de la démo (exercices, cours, quiz) selon le niveau
 * Utilisé pour mettre à jour dynamiquement la page démo sans rechargement
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

// Désactiver l'affichage des erreurs pour éviter de polluer le JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Activer l'output buffering au début pour capturer toute sortie accidentelle
ob_start();

api_require([
    'method' => 'GET',
]);

// Charger les dépendances
try {
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/site_boot.php';
    require_once __DIR__ . '/../../includes/exercice_loader.php';
    require_once __DIR__ . '/../../includes/quiz_generator.php';
    require_once __DIR__ . '/../../includes/course_content.php';
    require_once __DIR__ . '/../../includes/exercice_card.php';
} catch (Exception $e) {
    // Nettoyer le buffer en cas d'erreur de chargement
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors du chargement des dépendances: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Définir le header JSON après avoir chargé les dépendances
header('Content-Type: application/json; charset=utf-8');

// Vérifier que l'utilisateur est en mode démo
$is_demo_mode = !empty($_SESSION['is_demo']);
if (!$is_demo_mode) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès réservé au mode démo']);
    exit;
}

// Récupérer le niveau depuis la requête
$selected_level = $_GET['level'] ?? $_SESSION['demo_level'] ?? '6ème';
$_SESSION['demo_level'] = $selected_level;

// Normaliser le niveau
if (!function_exists('normalizeLevelForDB')) {
    function normalizeLevelForDB($level)
    {
        if (empty($level)) {
            return null;
        }

        $levelMapping = [
            '6ème' => '6ème', '6eme' => '6ème', '6EME' => '6ème',
            '5ème' => '5ème', '5eme' => '5ème', '5EME' => '5ème',
            '4ème' => '4ème', '4eme' => '4ème', '4EME' => '4ème',
            '3ème' => '3ème', '3eme' => '3ème', '3EME' => '3ème',
            'Seconde' => 'Seconde', 'seconde' => 'Seconde', '2nde' => 'Seconde',
            'Première' => 'Première', 'Premiere' => 'Première', 'première' => 'Première', '1ère' => 'Première',
            'Terminale' => 'Terminale', 'terminale' => 'Terminale', 'BAC' => 'Terminale',
        ];

        $levelClean = trim($level);
        return $levelMapping[$levelClean] ?? $levelClean;
    }
}

$response = [
    'success' => true,
    'level' => $selected_level,
    'exercises' => [],
    'course' => null,
    'quiz' => null,
    'stats' => [],
];

// Nettoyer le buffer principal avant de commencer
ob_clean();

try {
    // Charger les exercices
    if (isset($pdo) && $pdo) {
        $normalizedLevel = normalizeLevelForDB($selected_level);
        $allExercises = getExercisesByLevel($normalizedLevel, null, 50);

        // Filtrer pour s'assurer que tous les exercices sont du bon niveau
        $filteredExercises = [];
        foreach ($allExercises as $ex) {
            $exLevel = normalizeLevelForDB($ex['Level'] ?? '');
            if ($exLevel === $normalizedLevel) {
                $filteredExercises[] = $ex;
            }
        }

        // Sélectionner 3 exercices aléatoires
        if (count($filteredExercises) > 3) {
            shuffle($filteredExercises);
            $sampleExercises = array_slice($filteredExercises, 0, 3);
        } else {
            $sampleExercises = $filteredExercises;
        }

        // Préparer les exercices pour la réponse JSON avec HTML rendu
        foreach ($sampleExercises as $index => $exercise) {
            try {
                // Capturer le HTML rendu de la carte d'exercice
                // Utiliser un buffer séparé pour chaque exercice
                $exerciseBuffer = ob_get_level();
                ob_start();

                echo '<div class="demo-exercise-wrapper" data-exercise-index="' . $index . '">';
                renderExerciseCard($exercise, [
                    'showAnswer' => false,
                    'showDetails' => true,
                    'interactive' => true,
                    'demoMode' => true,
                    'cardClass' => 'demo-exercise-card',
                ]);
                // Ajouter le message de blocage des réponses
                echo '<div class="demo-answer-lock" id="demo-answer-lock-' . $index . '">';
                echo '<div class="lock-content">';
                echo '<span class="lock-icon">🔒</span>';
                echo '<h4>Inscrivez-vous pour voir vos résultats !</h4>';
                echo '<p>Vous avez terminé l\'exercice. Créez un compte gratuit pour :</p>';
                echo '<ul>';
                echo '<li>✅ Voir si vos réponses sont correctes</li>';
                echo '<li>📊 Obtenir votre score détaillé</li>';
                echo '<li>🏆 Gagner des points et des badges</li>';
                echo '<li>📈 Suivre votre progression</li>';
                echo '</ul>';
                $registerUrl = function_exists('site_url') ? site_url('register') : '/index.php?page=register';
                echo '<a href="' . htmlspecialchars($registerUrl) . '" class="demo-unlock-button">✨ Créer mon compte gratuit</a>';
                echo '<button class="demo-continue-demo" onclick="continueDemo(' . $index . ')">Continuer la démo</button>';
                echo '</div></div>';
                echo '</div>';

                $exerciseHTML = ob_get_clean();

                // S'assurer que le HTML est valide et ne casse pas le JSON
                // Nettoyer les retours à la ligne et les tabulations qui pourraient poser problème
                $exerciseHTML = str_replace(["\r\n", "\r", "\n"], " ", $exerciseHTML);
                $exerciseHTML = preg_replace('/\s+/', ' ', $exerciseHTML);

                $response['exercises'][] = [
                    'Id' => $exercise['Id'],
                    'Title' => $exercise['Title'],
                    'Subject' => $exercise['Subject'],
                    'Level' => $exercise['Level'],
                    'Content' => $exercise['Content'],
                    'Answer' => $exercise['Answer'] ?? '',
                    'html' => $exerciseHTML, // HTML rendu complet avec wrapper
                    'index' => $index,
                ];
            } catch (Exception $ex) {
                error_log("Erreur rendu exercice démo ID {$exercise['Id']}: " . $ex->getMessage());
                // En cas d'erreur, ajouter quand même l'exercice sans HTML
                $response['exercises'][] = [
                    'Id' => $exercise['Id'],
                    'Title' => $exercise['Title'],
                    'Subject' => $exercise['Subject'],
                    'Level' => $exercise['Level'],
                    'Content' => $exercise['Content'],
                    'Answer' => $exercise['Answer'] ?? '',
                    'html' => '', // HTML vide en cas d'erreur
                    'index' => $index,
                ];
            }
        }

        // Charger un cours
        $is_college = in_array($selected_level, ['6ème', '5ème', '4ème', '3ème']);
        $availableSubjects = $is_college
            ? ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais']
            : ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais', 'Philosophie'];

        shuffle($availableSubjects);
        $sampleCourseSubject = $availableSubjects[0];

        $courseExercises = getExercisesByLevel($normalizedLevel, $sampleCourseSubject, 10);

        if (!empty($courseExercises)) {
            shuffle($courseExercises);
            $randomExercise = $courseExercises[0];

            if (function_exists('generateCourseContent')) {
                $courseContent = generateCourseContent($randomExercise, $sampleCourseSubject, $selected_level);

                $response['course'] = [
                    'title' => $courseContent['title'],
                    'subject' => $sampleCourseSubject,
                    'level' => $selected_level,
                    'introduction' => $courseContent['introduction'] ?? '',
                    'objectives' => $courseContent['objectives'] ?? [],
                    'lessons' => array_slice($courseContent['lessons'] ?? [], 0, 2),
                    'summary' => $courseContent['summary'] ?? '',
                    'exercise_id' => $randomExercise['Id'] ?? 0,
                ];
            }
        }

        // Charger un quiz
        shuffle($availableSubjects);
        $sampleQuizSubject = $availableSubjects[0];

        if (function_exists('getQuizQuestions')) {
            try {
                $quizQuestions = getQuizQuestions($sampleQuizSubject, $selected_level, $pdo, 5);
                if (!empty($quizQuestions) && is_array($quizQuestions)) {
                    // Normaliser les questions pour s'assurer que choices est toujours un tableau indexé
                    $normalizedQuestions = [];
                    foreach ($quizQuestions as $q) {
                        if (isset($q['choices'])) {
                            if (is_array($q['choices'])) {
                                // Vérifier si c'est un tableau associatif (clés non numériques)
                                $keys = array_keys($q['choices']);
                                $isAssociative = false;

                                if (!empty($keys)) {
                                    // Vérifier si au moins une clé n'est pas numérique
                                    foreach ($keys as $key) {
                                        if (!is_numeric($key)) {
                                            $isAssociative = true;
                                            break;
                                        }
                                    }
                                }

                                if ($isAssociative) {
                                    // C'est un tableau associatif (ex: {'A': 'choix1', 'B': 'choix2'})
                                    // Convertir en tableau indexé de valeurs
                                    $q['choices'] = array_values($q['choices']);
                                } else {
                                    // C'est déjà un tableau indexé, s'assurer qu'il contient des strings
                                    $q['choices'] = array_map(function ($choice) {
                                        if (is_string($choice)) {
                                            return $choice;
                                        } elseif (is_array($choice) && isset($choice['label'])) {
                                            return $choice['label'];
                                        } elseif (is_array($choice) && isset($choice['value'])) {
                                            return $choice['value'];
                                        }
                                        return String($choice);
                                    }, array_values($q['choices']));
                                }
                            } else {
                                // Si choices n'est pas un tableau, le convertir en tableau vide
                                $q['choices'] = [];
                            }
                        } else {
                            // Si choices n'existe pas, créer un tableau vide
                            $q['choices'] = [];
                        }
                        $normalizedQuestions[] = $q;
                    }

                    $response['quiz'] = [
                        'subject' => $sampleQuizSubject,
                        'questions' => array_slice($normalizedQuestions, 0, 5),
                    ];
                } else {
                    // Essayer avec une autre matière
                    foreach ($availableSubjects as $subject) {
                        if ($subject === $sampleQuizSubject) {
                            continue;
                        }
                        $quizQuestions = getQuizQuestions($subject, $selected_level, $pdo, 5);
                        if (!empty($quizQuestions) && is_array($quizQuestions)) {
                            // Normaliser les questions (même logique que ci-dessus)
                            $normalizedQuestions = [];
                            foreach ($quizQuestions as $q) {
                                if (isset($q['choices'])) {
                                    if (is_array($q['choices'])) {
                                        $keys = array_keys($q['choices']);
                                        $isAssociative = false;

                                        if (!empty($keys)) {
                                            foreach ($keys as $key) {
                                                if (!is_numeric($key)) {
                                                    $isAssociative = true;
                                                    break;
                                                }
                                            }
                                        }

                                        if ($isAssociative) {
                                            $q['choices'] = array_values($q['choices']);
                                        } else {
                                            $q['choices'] = array_map(function ($choice) {
                                                if (is_string($choice)) {
                                                    return $choice;
                                                } elseif (is_array($choice) && isset($choice['label'])) {
                                                    return $choice['label'];
                                                } elseif (is_array($choice) && isset($choice['value'])) {
                                                    return $choice['value'];
                                                }
                                                return String($choice);
                                            }, array_values($q['choices']));
                                        }
                                    } else {
                                        $q['choices'] = [];
                                    }
                                } else {
                                    $q['choices'] = [];
                                }
                                $normalizedQuestions[] = $q;
                            }

                            $response['quiz'] = [
                                'subject' => $subject,
                                'questions' => array_slice($normalizedQuestions, 0, 5),
                            ];
                            break;
                        }
                    }
                }
            } catch (Exception $quizEx) {
                error_log("Erreur chargement quiz démo: " . $quizEx->getMessage());
                // Ne pas bloquer si le quiz échoue, on continue sans quiz
            }
        }

        // Statistiques
        $response['stats'] = [
            'exercises_count' => count($sampleExercises),
            'quiz_count' => !empty($response['quiz']) ? count($response['quiz']['questions']) : 0,
            'has_course' => !empty($response['course']),
        ];
    }
} catch (Exception $e) {
    error_log("Erreur API demo content: " . $e->getMessage());
    $response['success'] = false;
    $response['error'] = 'Erreur lors du chargement du contenu: ' . $e->getMessage();
    $response['exercises'] = [];
    $response['course'] = null;
    $response['quiz'] = null;
    $response['stats'] = [];
}

// Nettoyer tout buffer restant avant d'envoyer la réponse JSON
// Important : nettoyer tous les buffers pour éviter toute pollution
$bufferLevel = ob_get_level();
while ($bufferLevel > 0) {
    ob_end_clean();
    $bufferLevel = ob_get_level();
}

// S'assurer qu'il n'y a pas de sortie avant le JSON
if (ob_get_level() > 0) {
    ob_clean();
}


// Fonction récursive pour nettoyer tout tableau/chaîne non valide UTF-8
function clean_utf8_recursive($data)
{
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            $data[$k] = clean_utf8_recursive($v);
        }
        return $data;
    } elseif (is_string($data)) {
        // Si la chaîne n'est pas valide UTF-8, on la réencode proprement
        if (!mb_check_encoding($data, 'UTF-8')) {
            return mb_convert_encoding($data, 'UTF-8', 'auto');
        }
        return $data;
    } else {
        return $data;
    }
}

$response = clean_utf8_recursive($response);

// Encoder le JSON avec les bonnes options pour gérer le HTML
// Utiliser JSON_HEX_QUOT, JSON_HEX_APOS, JSON_HEX_AMP pour échapper correctement le HTML
$jsonOutput = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES);

if ($jsonOutput === false) {
    // En cas d'erreur d'encodage JSON, retourner une erreur
    $errorResponse = [
        'success' => false,
        'error' => 'Erreur lors de l\'encodage JSON: ' . json_last_error_msg(),
    ];
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
} else {
    echo $jsonOutput;
}

