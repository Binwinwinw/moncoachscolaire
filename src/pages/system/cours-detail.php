<?php
$page_title = 'Détail du Cours - MonCoachScolaire';
$page_css = 'pages/cours.css';

// Charger les fichiers nécessaires
if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}

// Charger le système de normalisation des niveaux pour gérer les problèmes d'encodage UTF-8
require_once dirname(__DIR__, 2) . '/includes/level_normalization.php';

// Initialiser les variables de session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger admin_auth.php pour vérifier si l'utilisateur est admin
if (is_file(__DIR__ . '/../../includes/admin_auth.php')) {
    require_once __DIR__ . '/../../includes/admin_auth.php';
}

// Vérifier si l'utilisateur est connecté OU s'il est admin
$is_admin = function_exists('isAdmin') && isAdmin();
$has_access = !empty($is_logged_in) || $is_admin;
$user_level = $_SESSION['user_level'] ?? '6ème';

// Normaliser le niveau de l'utilisateur pour affichage correct
$user_level_display = get_level_display_name($user_level);

// Récupérer les paramètres
$exercise_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$matiere = isset($_GET['matiere']) ? $_GET['matiere'] : '';
$cours_id = isset($_GET['cours']) ? $_GET['cours'] : '';

if (!$has_access) {
    header('Location: ' . site_url('login'));
    exit;
}

// Si admin, permettre l'accès à tous les niveaux et matières
if ($is_admin) {
    // Admin peut voir tous les cours, pas de restriction de niveau
    $user_level = $_GET['niveau'] ?? $user_level ?? '6ème';
}

// Charger l'exercice et générer le contenu du cours
$courseContent = null;
$exercise = null;
$subject = $matiere;

try {
    require_once __DIR__ . '/../../includes/exercice_loader.php';
    require_once __DIR__ . '/../../includes/course_content.php';
    require_once __DIR__ . '/../../includes/course_display.php';
    require_once __DIR__ . '/../../includes/resource_display.php';

    // Si on a un ID d'exercice, charger l'exercice
    if ($exercise_id > 0 && function_exists('getExerciseById')) {
        $exercise = getExerciseById($exercise_id);
        if ($exercise) {
            $subject = $exercise['Subject'] ?? $matiere;
            $level = $exercise['Level'] ?? $user_level;
            $courseContent = generateCourseContent($exercise, $subject, $level);
        }
    }

    // Si on a un ID de cours par défaut, générer le contenu
    if (!$courseContent && !empty($cours_id) && !empty($matiere)) {
        $defaultCourses = generateDefaultCoursesForSubject($matiere, $user_level);
        $selectedCourse = null;
        foreach ($defaultCourses as $dc) {
            if ($dc['id'] === $cours_id) {
                $selectedCourse = $dc;
                break;
            }
        }

        if ($selectedCourse) {
            // Générer un contenu de cours à partir du cours par défaut
            $courseContent = [
                'title' => $selectedCourse['title'],
                'introduction' => generateIntroduction($matiere, $user_level, $selectedCourse['title']) . ' ' . $selectedCourse['description'],
                'objectives' => $selectedCourse['objectives'],
                'lessons' => [
                    [
                        'title' => $selectedCourse['preview']['title'] ?? 'Introduction',
                        'content' => $selectedCourse['preview']['content'] ?? $selectedCourse['description'],
                        'examples' => [],
                    ],
                ],
                'examples' => generateExamples($matiere, ''),
                'summary' => generateSummary($matiere, $selectedCourse['title']),
            ];
        }
    }

    // Si toujours pas de contenu et qu'on a une matière, créer un cours générique
    if (!$courseContent && !empty($matiere)) {
        $courseContent = [
            'title' => "Cours de $matiere - $user_level",
            'introduction' => generateIntroduction($matiere, $user_level, "Cours de $matiere"),
            'objectives' => generateObjectives($matiere, "Cours de $matiere"),
            'lessons' => generateDefaultLessons($matiere, $user_level),
            'examples' => generateExamples($matiere, ''),
            'summary' => generateSummary($matiere, "Cours de $matiere"),
        ];
    }
} catch (Exception $e) {
    error_log("Erreur chargement cours détail: " . $e->getMessage());
}

// Si pas de contenu, rediriger vers la liste des cours
if (!$courseContent) {
    header('Location: ' . site_url('cours'));
    exit;
}

// S'assurer que $subject est défini
if (empty($subject)) {
    $subject = $matiere;
}

$level_normalized = normalize_level_for_url($user_level);
$is_college = is_college_level($user_level);
?>

<main class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="<?php echo site_url('cours'); ?>" class="inline-block text-blue-600 no-underline mb-4 font-semibold hover:underline">← Retour aux cours</a>
        <h1 class="text-blue-800 text-3xl mb-2">📚 <?php echo htmlspecialchars($courseContent['title']); ?></h1>
        <p class="flex gap-4 text-slate-600">
            <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-md text-sm font-semibold"><?php echo htmlspecialchars($subject); ?></span>
            <span class="bg-blue-100 text-blue-800 py-1 px-3 rounded-md text-sm font-semibold"><?php echo htmlspecialchars($user_level_display); ?></span>
        </p>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-5 my-5 rounded-lg italic">
        <strong class="text-blue-600 text-lg">👋 Salut <?php echo htmlspecialchars($user_name ?? 'Élève'); ?> !</strong><br>
        <?php echo htmlspecialchars($courseContent['introduction']); ?>
    </div>

    <!-- Objectifs -->
    <section class="my-12">
        <h2 class="text-blue-800 text-2xl mb-6 border-l-4 border-blue-500 pl-4">🎯 Objectifs d'Apprentissage</h2>
        <ul class="list-none p-0">
            <?php foreach ($courseContent['objectives'] as $objective): ?>
                <li class="bg-blue-50 p-4 mb-3 rounded-lg border-l-4 border-blue-500"><?php echo htmlspecialchars($objective); ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- Leçons -->
    <section class="my-12">
        <h2 class="text-blue-800 text-2xl mb-6 border-l-4 border-blue-500 pl-4">📖 Leçons</h2>
        <?php foreach ($courseContent['lessons'] as $index => $lesson): ?>
            <div class="bg-white border-2 border-gray-200 rounded-xl p-8 mb-6">
                <h3 class="text-slate-800 text-2xl mb-4 flex items-center gap-4">
                    <span class="bg-blue-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg"><?php echo $index + 1; ?></span>
                    <?php echo htmlspecialchars($lesson['title']); ?>
                </h3>
                <div class="text-slate-700 leading-relaxed">
                    <p class="mb-4"><?php echo nl2br(htmlspecialchars($lesson['content'])); ?></p>

                    <?php if (!empty($lesson['examples'])): ?>
                        <div class="bg-gray-50 p-6 rounded-lg mt-4">
                            <h4 class="text-blue-800 mb-3">💡 Exemples :</h4>
                            <ul class="m-0 pl-6">
                                <?php foreach ($lesson['examples'] as $example): ?>
                                    <li class="mb-2"><?php echo htmlspecialchars($example); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Exemples Pratiques -->
    <?php if (!empty($courseContent['examples'])): ?>
    <section class="my-12">
        <h2 class="text-blue-800 text-2xl mb-6 border-l-4 border-blue-500 pl-4">💡 Exemples Pratiques</h2>
        <?php foreach ($courseContent['examples'] as $example): ?>
            <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-8 mb-6">
                <h3 class="text-amber-800 text-xl mb-4"><?php echo htmlspecialchars($example['title']); ?></h3>
                <p class="text-slate-800 leading-relaxed mb-4"><?php echo htmlspecialchars($example['content']); ?></p>
                <?php if (!empty($example['step_by_step'])): ?>
                    <div class="bg-white p-6 rounded-lg mt-4">
                        <h4 class="text-amber-800 mb-3">Étapes de résolution :</h4>
                        <ol class="m-0 pl-6">
                            <?php foreach ($example['step_by_step'] as $step): ?>
                                <li class="mb-2 text-slate-700"><?php echo htmlspecialchars($step); ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- Résumé -->
    <section class="my-12">
        <h2 class="text-blue-800 text-2xl mb-6 border-l-4 border-blue-500 pl-4">📝 Résumé</h2>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-500 p-8 rounded-xl">
            <p class="text-slate-800 leading-relaxed whitespace-pre-line"><?php echo nl2br(htmlspecialchars($courseContent['summary'])); ?></p>
        </div>
    </section>

    <!-- Ressources Externes -->
    <?php
    // Récupérer les ressources du cours si on a un exercice lié
    $courseResources = [];
if ($exercise && isset($exercise['Id'])) {
    $courseResources = getExerciseExternalResources($exercise['Id']);
}
?>
    <?php if (!empty($courseResources)): ?>
    <section class="my-12">
        <h2 class="text-blue-800 text-2xl mb-6 border-l-4 border-blue-500 pl-4">🔗 Ressources Complémentaires</h2>
        <p class="text-slate-600 mb-6">Voici des ressources externes pour approfondir tes connaissances :</p>
        <?php echo displayCourseResourcesHtml(1); // Afficher les ressources du premier cours trouvé?>
    </section>
    <?php endif; ?>

    <!-- Actions -->
    <section class="my-12">
        <div class="flex gap-4 flex-wrap justify-center mt-8">
            <a href="<?php
            if ($is_college) {
                echo site_url('college/' . $level_normalized . '/exercices-' . $level_normalized);
            } else {
                echo site_url('lycee/' . $level_normalized . '/exercices-' . $level_normalized);
            }
?>" class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white py-4 px-8 rounded-lg no-underline font-semibold text-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                📝 Faire l'exercice maintenant
            </a>
            <a href="<?php echo site_url('cours'); ?>" class="bg-blue-500 text-white py-4 px-8 rounded-lg no-underline font-semibold transition-all duration-300 hover:bg-blue-600 hover:-translate-y-1 hover:shadow-lg">
                📚 Voir tous les cours
            </a>
        </div>
    </section>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-5 my-5 rounded-lg italic">
        <strong class="text-blue-600 text-lg">💡 Conseil :</strong> Relis ce cours autant de fois que nécessaire. La compréhension est la clé de la réussite !
    </div>
</main>

