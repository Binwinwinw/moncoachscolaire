<?php
$page_title = 'Cours 6ème - Collège - MonCoachScolaire';
$page_css = 'college/6eme/cours-6eme.css';

// Charger les fichiers nécessaires
if (!isset($pdo)) {
    if (is_file(dirname(__DIR__, 4) . '/config/config.php')) {
        require_once dirname(__DIR__, 4) . '/config/config.php';
    }
}
if (is_file(dirname(__DIR__, 4) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 4) . '/config/site_boot.php';
}

// Charger la fonction de navigation entre niveaux
if (is_file(dirname(__DIR__, 4) . '/includes/level_navigation.php')) {
    require_once dirname(__DIR__, 4) . '/includes/level_navigation.php';
}

// Charger admin_auth.php pour vérifier si l'utilisateur est admin
if (is_file(__DIR__ . '/../../../includes/admin_auth.php')) {
    require_once __DIR__ . '/../../../includes/admin_auth.php';
}

// Charger les fonctions de sécurité pour le compte démo
if (is_file(__DIR__ . '/../../../includes/demo_security.php')) {
    require_once __DIR__ . '/../../../includes/demo_security.php';
}

// Vérifier si l'utilisateur est connecté OU s'il est admin
// Le compte démo a aussi accès mais avec limitation (voir plus bas)
$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();
$has_access = !empty($is_logged_in) || $is_admin || $is_demo;

$user_level = '6ème';
$level_normalized = normalize_level_for_url($user_level);

// Matières disponibles pour la 6ème
$subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais'];
?>

<main class="main-content cours-main">
    <?php
    // Initialiser $is_admin pour la navigation (admin/dev uniquement)
    $is_admin = function_exists('isAdmin') && isAdmin();
    if (function_exists('render_level_navigation')) {
        echo render_level_navigation('6ème', 'cours');
    }
    ?>
    <div class="cours-header">
        <h1>📚 Cours 6ème - Collège</h1>
        <p class="subtitle">Découvre les bases pour bien démarrer ton année de 6ème !</p>

        <!-- Boutons de navigation -->
        <div class="cours-navigation">
            <a href="<?php echo site_url('exercices'); ?>" class="nav-btn nav-exercices">📝 Exercices</a>
            <a href="<?php echo site_url('college/college-accueil'); ?>" class="nav-btn nav-accueil">🏠 Accueil Collège</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard">📊 Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$has_access): ?>
        <div class="coach-preview">
            <h2>🔒 Débloque l'accès aux cours 6ème</h2>
            <p>Crée un compte gratuit pour accéder à tous les cours de 6ème !</p>
            <div class="cta-actions">
                <a href="<?php echo site_url('register'); ?>" class="coach-cta">✨ Créer mon compte</a>
                <a href="<?php echo site_url('login'); ?>" class="coach-cta secondary">🔑 Me connecter</a>
            </div>
        </div>
    <?php else: ?>
        <div class="coach-message">
            <strong>👋 Salut <?php
                                $display_name = $user_name ?? $_SESSION['user_name'] ?? 'Élève';
                                echo htmlspecialchars($display_name);
                                ?> !</strong><br>
            Bienvenue dans tes cours de 6ème ! Ici, tu trouveras des cours clairs et illustrés
            pour bien comprendre les notions de base dans toutes les matières.
            <?php if ($is_demo && !$is_admin): ?>
                <br><small>💡 Mode démo : tu as accès à <strong>un seul cours 6ème</strong>. Crée un compte pour accéder à tous les cours !</small>
            <?php endif; ?>
        </div>

        <!-- Introduction spécifique 6ème -->
        <div class="level-intro-box">
            <h3>🎯 Bienvenue en 6ème !</h3>
            <p>L'entrée au collège est une étape importante ! Ces cours sont conçus pour t'accompagner dans ta découverte de nouvelles matières et consolider les bases acquises à l'école primaire.</p>
            <ul>
                <li>📐 <strong>Mathématiques</strong> : Nombres décimaux, fractions simples, géométrie de base</li>
                <li>📚 <strong>Français</strong> : Grammaire, conjugaison, expression écrite</li>
                <li>🔬 <strong>Sciences</strong> : Découverte de la matière, du vivant et de l'environnement</li>
                <li>🏛️ <strong>Histoire-Géo</strong> : Antiquité, repères géographiques</li>
                <li>🇬🇧 <strong>Anglais</strong> : Vocabulaire de base, présent simple</li>
            </ul>
        </div>

        <!-- Cartes de matières cliquables -->
        <div class="subjects-cards">
            <?php
            // Pour le compte démo, n'afficher que la première matière
            $subjectsForNav = ($is_demo && !$is_admin) ? [reset($subjects)] : $subjects;
            foreach ($subjectsForNav as $subject):
                $subjectId = strtolower(str_replace([' ', '-'], ['', ''], $subject));
                $icons = [
                    'Mathématiques' => '🧮',
                    'Français' => '📚',
                    'Sciences' => '🔬',
                    'Histoire-Géo' => '🏛️',
                    'Anglais' => '🇬🇧',
                ];
                $icon = $icons[$subject] ?? '📖';
            ?>
                <div class="subject-card" data-subject="<?php echo htmlspecialchars($subjectId); ?>" onclick="toggleSubject('<?php echo htmlspecialchars($subjectId); ?>')">
                    <div class="subject-card-icon"><?php echo $icon; ?></div>
                    <h3 class="subject-card-title"><?php echo htmlspecialchars($subject); ?></h3>
                    <p class="subject-card-desc">Clique pour voir les cours disponibles</p>
                    <div class="subject-card-arrow">→</div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sections de cours par matière -->
        <?php
        // Pour le compte démo, n'afficher qu'une seule matière avec un seul cours
        $subjectsToDisplay = $subjects;
        if ($is_demo && !$is_admin) {
            $subjectsToDisplay = [reset($subjects)];
        }
        foreach ($subjectsToDisplay as $subject):
        ?>
            <?php
            $subjectId = strtolower(str_replace([' ', '-'], ['', ''], $subject));
            $icons = [
                'Mathématiques' => '🧮',
                'Français' => '📚',
                'Sciences' => '🔬',
                'Histoire-Géo' => '🏛️',
                'Anglais' => '🇬🇧',
            ];
            $icon = $icons[$subject] ?? '📖';
            ?>
            <section id="<?php echo htmlspecialchars($subjectId); ?>" class="cours-section cours-6eme-section">
                <div class="cours-section-header">
                    <button class="btn-back-subject" onclick="toggleSubject('<?php echo htmlspecialchars($subjectId); ?>')">← Retour aux matières</button>
                    <h2>
                        <?php echo $icon; ?>
                        <?php echo htmlspecialchars($subject); ?> - 6ème
                    </h2>
                </div>

                <div class="cours-grid cours-grid-3cols">
                    <?php
                    // Charger les exercices pour cette matière et niveau pour créer des cours correspondants
                    if (is_file(__DIR__ . '/../../../includes/exercice_loader.php')) {
                        require_once __DIR__ . '/../../../includes/exercice_loader.php';
                    }
                    if (is_file(__DIR__ . '/../../../includes/course_content.php')) {
                        require_once __DIR__ . '/../../../includes/course_content.php';
                    }

                    $exercises = [];
                    $hasExercises = false;

                    try {
                        if (function_exists('getExercisesByLevel') && isset($pdo) && $pdo) {
                            $exercises = getExercisesByLevel($user_level, $subject, 10);

                            if (empty($exercises) && $subject) {
                                $allExercises = getAllExercises(['subject' => $subject]);
                                $exercises = array_slice($allExercises, 0, 5);
                            }

                            $hasExercises = !empty($exercises);
                        }
                    } catch (Exception $e) {
                        error_log("Erreur chargement exercices: " . $e->getMessage());
                    }

                    $maxCourses = ($is_demo && !$is_admin) ? 1 : null;
                    $courseCount = 0;

                    if ($hasExercises) {
                        foreach ($exercises as $exercise) {
                            if ($is_demo && !$is_admin && $courseCount >= 1) {
                                break;
                            }
                            $courseCount++;

                            $courseContent = generateCourseContent($exercise, $subject, $user_level);
                            $courseTitle = $courseContent['title'];
                            $coursePreview = substr($courseContent['introduction'], 0, 150) . '...';

                            $firstLesson = !empty($courseContent['lessons']) ? $courseContent['lessons'][0] : null;
                    ?>
                            <div class="cours-card cours-6eme-card">
                                <div class="cours-card-header">
                                    <h3><?php echo htmlspecialchars($courseTitle); ?></h3>
                                    <span class="cours-level">6ème</span>
                                </div>
                                <div class="cours-card-body">
                                    <p class="cours-description">
                                        <?php echo htmlspecialchars($coursePreview); ?>
                                    </p>
                                    <?php if ($firstLesson): ?>
                                        <div class="cours-content-preview">
                                            <h4>📖 Tu vas apprendre :</h4>
                                            <p><strong><?php echo htmlspecialchars($firstLesson['title']); ?></strong></p>
                                            <p><?php echo htmlspecialchars(substr($firstLesson['content'], 0, 200)); ?>...</p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="cours-objectives-preview">
                                        <h4>🎯 Objectifs :</h4>
                                        <ul>
                                            <?php foreach (array_slice($courseContent['objectives'], 0, 3) as $obj): ?>
                                                <li><?php echo htmlspecialchars($obj); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="cours-card-footer">
                                    <a href="<?php echo site_url('pages/cours-detail', [
                                                    'id' => $exercise['Id'] ?? 0,
                                                    'matiere' => $subject,
                                                    'cours' => $exercise['Id'] ?? 0,
                                                    'niveau' => '6eme',
                                                ]); ?>" class="btn-cours">📖 Voir le cours complet</a>
                                </div>
                            </div>
                        <?php
                        }
                    } else {
                        if (!($is_demo && !$is_admin && $courseCount >= 1)) {
                        ?>
                            <div class="cours-card cours-6eme-card">
                                <div class="cours-card-header">
                                    <h3>Cours <?php echo htmlspecialchars($subject); ?> - 6ème</h3>
                                    <span class="cours-level">6ème</span>
                                </div>
                                <div class="cours-card-body">
                                    <p class="cours-description">
                                        Cours complet de <?php echo htmlspecialchars($subject); ?> pour bien démarrer ta 6ème.
                                        Notions de base, explications claires et exercices d'application.
                                    </p>
                                    <div class="cours-objectives-preview">
                                        <h4>🎯 Au programme :</h4>
                                        <ul>
                                            <li>Notions fondamentales du programme 6ème</li>
                                            <li>Explications détaillées avec exemples</li>
                                            <li>Méthodes et astuces pour progresser</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="cours-card-footer">
                                    <a href="<?php echo site_url('cours', ['niveau' => '6eme']); ?>" class="btn-cours">📖 Accéder aux cours</a>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </section>
        <?php endforeach; ?>

        <!-- Section conseils d'apprentissage -->
        <section class="learning-tips-section">
            <h2>💡 Conseils pour bien démarrer en 6ème</h2>
            <div class="tips-grid">
                <div class="tip-card">
                    <h3>📚 Organisation</h3>
                    <p>Prends l'habitude de bien ranger tes cahiers et de faire tes devoirs régulièrement. L'organisation est la clé de la réussite !</p>
                </div>
                <div class="tip-card">
                    <h3>📝 Méthode</h3>
                    <p>Lis bien tes cours, surligne l'essentiel, et fais des fiches. N'hésite pas à demander de l'aide si tu ne comprends pas.</p>
                </div>
                <div class="tip-card">
                    <h3>⏰ Régularité</h3>
                    <p>Travaille un peu chaque jour plutôt que beaucoup d'un coup. 20 minutes par jour valent mieux qu'une heure une fois par semaine !</p>
                </div>
                <div class="tip-card">
                    <h3>🎯 Participation</h3>
                    <p>Lève la main en classe, pose des questions. Plus tu participes, mieux tu retiens et comprends !</p>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<script>
    // Fonction pour basculer l'affichage des matières
    function toggleSubject(subjectId) {
        const allSections = document.querySelectorAll('.cours-section');
        allSections.forEach(section => {
            section.style.display = 'none';
        });

        const allSubjectCards = document.querySelectorAll('.subject-card');
        allSubjectCards.forEach(card => {
            card.classList.remove('active');
        });

        const targetSection = document.getElementById(subjectId);
        if (targetSection) {
            targetSection.style.display = 'block';
            targetSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        const targetCard = document.querySelector(`[data-subject="${subjectId}"]`);
        if (targetCard) {
            targetCard.classList.add('active');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash) {
            const subjectId = window.location.hash.substring(1);
            toggleSubject(subjectId);
        }
    });
</script>