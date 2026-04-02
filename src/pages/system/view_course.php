<?php
/**
 * Page d'affichage d'un cours complet
 * Route: index.php?page=view_course&id=X
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/course_markdown_loader.php';

// ✅ 1. Récupérer l'ID du cours D'ABORD
$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Rediriger vers le routeur si on n'est pas déjà dedans
if (!defined('IN_ROUTER')) {
    if ($courseId > 0) {
        $redirectUrl = function_exists('site_url')
            ? site_url('system/view_course', ['id' => $courseId])
            : 'public/index.php?page=view_course&id=' . $courseId;
        header('Location: ' . $redirectUrl);
    } else {
        $redirectUrl = function_exists('site_url')
            ? site_url('system/cours')
            : 'public/index.php?page=cours';
        header('Location: ' . $redirectUrl);
    }
    exit;
}

// ✅ 2. Charger le cours
$course = getCourseById($courseId, true);

if (!$course) {
    header('Location: ' . site_url('cours') . '?error=course_not_found');
    exit;
}

// ✅ 3. Vérification d'accès par niveau
if (is_file(dirname(__DIR__, 2) . '/includes/level_access.php')) {
    require_once dirname(__DIR__, 2) . '/includes/level_access.php';
    $requiredLevel = $course['Level'] ?? $course['level'] ?? null;
    if ($requiredLevel) {
        enforce_level_access_or_abort($requiredLevel);
    }
}

// ✅ 4. Démarrer le tracking (après avoir vérifié que le cours existe)
require_once dirname(__DIR__, 2) . '/includes/study_tracker.php';

$sessionId = null;
if (!empty($_SESSION['user_id']) && !empty($_SESSION['logged_in'])) {
    try {
        $sessionId = startStudySession($_SESSION['user_id'], 'course', $courseId);
        $_SESSION['current_study_session'] = $sessionId;
    } catch (Exception $e) {
        error_log('Erreur tracking cours: ' . $e->getMessage());
    }
}

// ✅ 4. Charger les exercices liés
$linkedExercises = getExercisesForCourse($courseId);

$page_title = ($course['Title'] ?? 'Cours') . ' - MonCoachScolaire';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="/public/assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="app-bg">
    <?php if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
        include_once dirname(__DIR__, 2) . '/includes/topbar.php';
    } ?>

    <div class="max-w-4xl mx-auto my-5 p-5 bg-white rounded-lg shadow-md">
        <?php
        $levelSlug = strtolower(str_replace('ème', 'eme', $course['Level'] ?? $course['level'] ?? ''));
?>
        <a href="<?php echo function_exists('site_url')
    ? site_url('system/cours', ['niveau' => $levelSlug])
    : 'cours.php?niveau=' . urlencode($levelSlug); ?>" class="inline-block py-2 px-4 bg-gray-500 text-white no-underline rounded-md mb-5 hover:bg-gray-600">
            ← Retour aux cours
        </a>

        <div class="border-b-4 border-green-500 pb-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($course['Title'] ?? $course['competence'] ?? 'Titre non disponible'); ?></h1>
            <div class="flex gap-3 mt-3 flex-wrap">
                <span class="inline-block py-1 px-3 bg-blue-100 text-blue-800 rounded-md text-sm font-medium">📚 <?php echo htmlspecialchars($course['Subject'] ?? $course['subject'] ?? 'Matière'); ?></span>
                <span class="inline-block py-1 px-3 bg-blue-100 text-blue-800 rounded-md text-sm font-medium">🎓 <?php echo htmlspecialchars($course['Level'] ?? $course['level'] ?? 'Niveau'); ?></span>
                <?php if (isset($course['CourseNumber'])): ?>
                    <span class="inline-block py-1 px-3 bg-blue-100 text-blue-800 rounded-md text-sm font-medium">📝 Cours n°<?php echo $course['CourseNumber']; ?></span>
                <?php endif; ?>
                <?php if (isset($course['Duration'])): ?>
                    <span class="inline-block py-1 px-3 bg-blue-100 text-blue-800 rounded-md text-sm font-medium">⏱️ <?php echo $course['Duration']; ?> min</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($course['Description'])): ?>
                <p class="mt-4 text-gray-600">
                    <?php echo htmlspecialchars($course['Description']); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="leading-relaxed text-base">
            <?php
    // 1. Contenu JSON structuré (nouveau format)
    if (!empty($course['Content']) && is_string($course['Content'])) {
        $contentData = json_decode($course['Content'], true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($contentData)) {
            // Afficher le contenu JSON structuré

            // Introduction
            if (!empty($contentData['introduction'])) {
                echo '<div class="my-6">';
                echo '<p class="leading-relaxed text-base">' . nl2br(htmlspecialchars($contentData['introduction'])) . '</p>';
                echo '</div>';
            }

            // Objectifs
            if (!empty($contentData['objectives']) && is_array($contentData['objectives'])) {
                echo '<div class="my-6">';
                echo '<h2 class="text-gray-800 text-xl mb-4 pb-2 border-b-2 border-gray-200">🎯 Objectifs</h2>';
                echo '<ul class="pl-8">';
                foreach ($contentData['objectives'] as $objective) {
                    echo '<li class="mb-2">' . htmlspecialchars($objective) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }

            // Sections
            if (!empty($contentData['sections']) && is_array($contentData['sections'])) {
                foreach ($contentData['sections'] as $section) {
                    echo '<div class="my-6">';
                    echo '<h2 class="text-gray-800 text-xl mb-4 pb-2 border-b-2 border-gray-200">' . htmlspecialchars($section['title'] ?? 'Section') . '</h2>';

                    if (!empty($section['content'])) {
                        echo '<p class="leading-relaxed text-base mb-4">' . nl2br(htmlspecialchars($section['content'])) . '</p>';
                    }

                    // Exemples
                    if (!empty($section['examples']) && is_array($section['examples'])) {
                        echo '<div class="space-y-3">';
                        foreach ($section['examples'] as $example) {
                            echo '<div class="bg-gray-50 p-4 rounded-lg">';
                            if (!empty($example['direct'])) {
                                echo '<p><strong>Direct:</strong> ' . htmlspecialchars($example['direct']) . '</p>';
                            }
                            if (!empty($example['indirect'])) {
                                echo '<p><strong>Indirect:</strong> ' . htmlspecialchars($example['indirect']) . '</p>';
                            }
                            if (!empty($example['explanation'])) {
                                echo '<p class="italic">' . htmlspecialchars($example['explanation']) . '</em></p>';
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    }

                    // Table
                    if (!empty($section['table']) && is_array($section['table'])) {
                        echo '<table class="w-full border-collapse my-5">';
                        if (!empty($section['table']['headers'])) {
                            echo '<thead><tr>';
                            foreach ($section['table']['headers'] as $header) {
                                echo '<th class="border border-gray-300 p-3 text-left bg-gray-50 font-semibold">' . htmlspecialchars($header) . '</th>';
                            }
                            echo '</tr></thead>';
                        }
                        if (!empty($section['table']['rows'])) {
                            echo '<tbody>';
                            foreach ($section['table']['rows'] as $row) {
                                echo '<tr>';
                                foreach ($row as $cell) {
                                    echo '<td class="border border-gray-300 p-3">' . htmlspecialchars($cell) . '</td>';
                                }
                                echo '</tr>';
                            }
                            echo '</tbody>';
                        }
                        echo '</table>';
                    }

                    echo '</div>';
                }
            }

            // Points clés
            if (!empty($contentData['key_points']) && is_array($contentData['key_points'])) {
                echo '<div class="my-6 bg-green-50 p-5 rounded-lg border-l-4 border-green-500">';
                echo '<h3 class="mt-0 text-green-800 text-lg mb-3 flex items-center gap-3">💡 Points clés à retenir</h3>';
                echo '<ul class="pl-6">';
                foreach ($contentData['key_points'] as $point) {
                    echo '<li class="mb-2">' . htmlspecialchars($point) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }

            // Résumé
            if (!empty($contentData['summary'])) {
                echo '<div class="my-6">';
                echo '<h2 class="text-gray-800 text-xl mb-4 pb-2 border-b-2 border-gray-200">📝 Résumé</h2>';
                echo '<p class="leading-relaxed text-base">' . nl2br(htmlspecialchars($contentData['summary'])) . '</p>';
                echo '</div>';
            }

        } else {
            // Pas du JSON valide, afficher comme HTML brut
            echo $course['Content'];
        }
    }
    // 2. Markdown content (Legacy)
    elseif (isset($course['markdown']) && !empty($course['markdown']['htmlContent'])) {
        echo $course['markdown']['htmlContent'];
    }
    // 3. Database Content (Old format with key_point, explanation, example)
    elseif (!empty($course['explanation'])) {
        echo '<div class="my-6">';
        echo $course['explanation'];
        echo '</div>';

        if (!empty($course['key_point'])) {
            echo '<div class="my-6 bg-green-50 p-5 rounded-lg border-l-4 border-green-500">';
            echo '<h3 class="mt-0 text-green-800 text-lg mb-3 flex items-center gap-3">💡 À retenir</h3>';
            echo '<p class="mb-0 text-lg text-green-900">' . nl2br(htmlspecialchars($course['key_point'])) . '</p>';
            echo '</div>';
        }

        if (!empty($course['example'])) {
            echo '<div class="my-6 bg-orange-50 p-5 rounded-lg border-l-4 border-orange-500">';
            echo '<h3 class="mt-0 text-orange-800 text-lg mb-3 flex items-center gap-3">📝 Exemples et Exercices résolus</h3>';
            echo $course['example'];
            echo '</div>';
        }
    }
    // 4. Fallback to raw markdown
    elseif (isset($course['markdown']) && !empty($course['markdown']['rawContent'])) {
        echo '<pre>' . htmlspecialchars($course['markdown']['rawContent']) . '</pre>';
    }
    // 5. Fallback to Description
    elseif (!empty($course['Description'])) {
        echo '<p>' . nl2br(htmlspecialchars($course['Description'])) . '</p>';
    } else {
        echo '<p><em>Contenu du cours non disponible.</em></p>';
    }
?>
        </div>

        <?php if (!empty($linkedExercises)): ?>
        <div class="mt-10 p-5 bg-gray-50 rounded-lg">
            <h2 class="text-xl font-semibold mb-3">✏️ Exercices associés (<?php echo count($linkedExercises); ?>)</h2>
            <p class="text-gray-700 mb-4">Teste tes connaissances avec ces exercices liés à ce cours :</p>

            <?php foreach ($linkedExercises as $exercise): ?>
                <div class="bg-white p-4 mb-3 rounded-md border-l-4 border-orange-500 transition-transform duration-200 hover:translate-x-1 hover:shadow-md">
                    <a href="<?php echo function_exists('site_url')
            ? site_url('system/view_exercise', ['id' => $exercise['Id']])
            : 'exercices.php?id=' . $exercise['Id']; ?>" class="no-underline text-gray-800 font-medium hover:text-blue-600">
                        <?php echo htmlspecialchars($exercise['Title']); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
        include_once dirname(__DIR__, 2) . '/includes/footer.php';
    } ?>

    <?php if ($sessionId): ?>
    <script>
    // ========================================
    // TRACKING DE LA SESSION D'ÉTUDE
    // ========================================

    let sessionEnded = false;
    const SESSION_ID = <?= $sessionId ?>;

    // Terminer la session quand l'utilisateur quitte la page
    window.addEventListener('beforeunload', (e) => {
        if (!sessionEnded) {
            endStudySession();
        }
    });

    // Terminer aussi sur pagehide (meilleur support mobile)
    window.addEventListener('pagehide', (e) => {
        if (!sessionEnded) {
            endStudySession();
        }
    });

    // Terminer la session après 30 minutes d'inactivité
    let inactivityTimer;
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            console.log('Session terminée par inactivité');
            endStudySession();
        }, 30 * 60 * 1000); // 30 minutes
    }

    // Détecter l'activité de l'utilisateur
    ['mousedown', 'keypress', 'scroll', 'touchstart'].forEach(eventType => {
        document.addEventListener(eventType, resetInactivityTimer, true);
    });
    resetInactivityTimer();

    // Fonction pour terminer la session
    function endStudySession() {
        if (sessionEnded) return;
        sessionEnded = true;

        const data = {
            session_id: SESSION_ID,
            completion_rate: calculateScrollPercentage()
        };

        // Utiliser sendBeacon pour garantir l'envoi même si la page se ferme
        const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
        const beaconSent = navigator.sendBeacon(
            '<?= site_url('api/end_session') ?>',
            blob
        );

        // Fallback avec fetch si sendBeacon n'est pas supporté
        if (!beaconSent) {
            fetch('<?= site_url('api/end_session') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
                keepalive: true
            }).catch(err => console.error('Erreur fin de session:', err));
        }
    }

    // Calculer le pourcentage de scroll
    function calculateScrollPercentage() {
        const scrolled = window.scrollY;
        const total = document.documentElement.scrollHeight - window.innerHeight;
        if (total <= 0) return 100;
        return Math.min(100, Math.max(0, Math.round((scrolled / total) * 100)));
    }

    // Envoyer une mise à jour toutes les 2 minutes (heartbeat)
    setInterval(() => {
        if (!sessionEnded) {
            fetch('<?= site_url('api/update_session') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: SESSION_ID,
                    completion_rate: calculateScrollPercentage(),
                    is_active: true
                })
            }).catch(err => console.error('Erreur update session:', err));
        }
    }, 2 * 60 * 1000); // Toutes les 2 minutes
    </script>
    <?php endif; ?>
</body>
</html>
