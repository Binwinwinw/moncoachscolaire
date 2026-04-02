<?php
$page_title = 'Cours BAC - Préparation Intensive - MonCoachScolaire';
$page_css = 'bac/cours-bac.css';

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

$user_level = 'BAC';
$level_normalized = normalize_level_for_url($user_level);

// Matières disponibles pour le BAC
$subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais', 'Philosophie'];
?>

<main class="main-content cours-main">
    <?php
    // Initialiser $is_admin pour la navigation (admin/dev uniquement)
    $is_admin = function_exists('isAdmin') && isAdmin();
if (function_exists('render_level_navigation')) {
    echo render_level_navigation('BAC', 'cours');
}
?>
    <div class="cours-header">
        <h1>📚 Cours BAC - Préparation Intensive</h1>
        <p class="subtitle">Fiches de révision, points clés et méthodes efficaces pour réussir le BAC !</p>

        <!-- Boutons de navigation -->
        <div class="cours-navigation">
            <a href="<?php echo site_url('exercices'); ?>" class="nav-btn nav-exercices">📝 Exercices</a>
            <a href="<?php echo site_url('bac/bac-accueil'); ?>" class="nav-btn nav-accueil">🏠 Accueil BAC</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href("<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard">📊 Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$has_access): ?>
        <div class="coach-preview">
            <h2>🔒 Débloque l'accès aux cours BAC</h2>
            <p>Crée un compte gratuit pour accéder à tous les cours de révision et fiches de synthèse pour le BAC !</p>
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
            Bienvenue dans tes cours de préparation au BAC ! Ici, tu trouveras des fiches de synthèse complètes,
            des points clés pour chaque matière, et des méthodes efficaces pour réviser et réussir ton examen.
            <?php if ($is_demo && !$is_admin): ?>
            <br><small style="color: #666; font-style: italic;">💡 Mode démo : tu as accès à <strong>un seul cours BAC</strong>. Crée un compte pour accéder à tous les cours de révision !</small>
            <?php endif; ?>
        </div>

        <!-- Introduction spécifique BAC -->
        <div class="level-intro-box">
            <h3>🎯 Objectif : Réussir le BAC</h3>
            <p>Ces cours sont spécialement conçus pour la <strong>préparation à l'examen du BAC</strong>.
            Contrairement aux cours de l'année de Terminale, ils se concentrent sur :</p>
            <ul>
                <li>📋 <strong>Fiches de synthèse</strong> : L'essentiel à retenir pour chaque matière</li>
                <li>🔑 <strong>Points clés</strong> : Les notions fondamentales à maîtriser absolument</li>
                <li>📝 <strong>Méthodes de révision</strong> : Techniques efficaces pour optimiser tes révisions</li>
                <li>⚡ <strong>Révisions express</strong> : Contenu condensé pour une préparation intensive</li>
                <li>🎯 <strong>Orientation examen</strong> : Focus sur ce qui sera demandé le jour J</li>
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
        'Philosophie' => '🤔',
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
        $subjectsToDisplay = ($is_demo && !$is_admin) ? [reset($subjects)] : $subjects;
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
        'Philosophie' => '🤔',
    ];
    $icon = $icons[$subject] ?? '📖';
    ?>
            <section id="<?php echo htmlspecialchars($subjectId); ?>" class="cours-section cours-bac-section" style="display: none;">
                <div class="cours-section-header">
                    <button class="btn-back-subject" onclick="toggleSubject('<?php echo htmlspecialchars($subjectId); ?>')">← Retour aux matières</button>
                    <h2>
                        <?php echo $icon; ?>
                        <?php echo htmlspecialchars($subject); ?> - BAC
                    </h2>
                </div>

                <div class="cours-grid cours-grid-3cols">
                    <?php
            require_once __DIR__ . '/../../../includes/exercice_loader.php';
    require_once __DIR__ . '/../../../includes/course_content.php';

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
                            <div class="cours-card cours-bac-card">
                                <div class="cours-card-header">
                                    <h3><?php echo htmlspecialchars($courseTitle); ?></h3>
                                    <span class="cours-level">BAC</span>
                                </div>
                                <div class="cours-card-body">
                                    <p class="cours-description"><?php echo htmlspecialchars($coursePreview); ?></p>
                                    <?php if ($firstLesson): ?>
                                    <div class="cours-content-preview">
                                        <h4>📖 Tu vas apprendre :</h4>
                                        <p><strong><?php echo htmlspecialchars($firstLesson['title']); ?></strong></p>
                                        <p><?php echo htmlspecialchars(substr($firstLesson['content'], 0, 200)); ?>...</p>
                                    </div>
                                    <?php endif; ?>
                                    <div class="cours-objectives-preview">
                                        <h4>🎯 Objectifs BAC :</h4>
                                        <ul>
                                            <?php foreach (array_slice($courseContent['objectives'], 0, 3) as $obj): ?>
                                                <li><?php echo htmlspecialchars($obj); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="cours-bac-badge"><span>📋 Fiche de révision</span></div>
                                </div>
                                <div class="cours-card-footer">
                                    <a href="<?php echo site_url('pages/cours-detail', ['id' => $exercise['Id'] ?? 0, 'matiere' => $subject, 'cours' => $exercise['Id'] ?? 0, 'niveau' => 'bac']); ?>" class="btn-cours">📖 Voir la fiche complète</a>
                                </div>
                            </div>
                    <?php
        }
    }
    ?>
                </div>
            </section>
        <?php endforeach; ?>

    <?php endif; ?>
</main>

<script>
// Fonction pour basculer l'affichage des matières
function toggleSubject(subjectId) {
    // Masquer toutes les sections de cours
    const allSections = document.querySelectorAll('.cours-section');
    allSections.forEach(section => {
        section.style.display = 'none';
    });

    // Masquer toutes les cartes de matières
    const allSubjectCards = document.querySelectorAll('.subject-card');
    allSubjectCards.forEach(card => {
        card.classList.remove('active');
    });

    // Afficher la section correspondante
    const targetSection = document.getElementById(subjectId);
    if (targetSection) {
        targetSection.style.display = 'block';
        // Scroll vers la section
        targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Marquer la carte comme active
    const targetCard = document.querySelector(`[data-subject="${subjectId}"]`);
    if (targetCard) {
        targetCard.classList.add('active');
    }
}

// Au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Si une matière est dans l'URL (hash), l'afficher
    if (window.location.hash) {
        const subjectId = window.location.hash.substring(1);
        toggleSubject(subjectId);
    }
});
</script>
