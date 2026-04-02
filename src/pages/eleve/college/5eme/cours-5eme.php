<?php
$page_title = 'Cours 5ème - Collège - MonCoachScolaire';
$page_css = 'college/5eme/cours-5eme.css';

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
$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();
$has_access = !empty($is_logged_in) || $is_admin || $is_demo;

$user_level = '5ème';
$level_normalized = normalize_level_for_url($user_level);

// Matières disponibles pour la 5ème
$subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais'];
?>

<main class="main-content cours-main">
    <?php
    if (function_exists('render_level_navigation')) {
        echo render_level_navigation('5ème', 'cours');
    }
?>
    <div class="cours-header">
        <h1>📚 Cours 5ème - Collège</h1>
        <p class="subtitle">Approfondis tes connaissances et développe de nouvelles compétences !</p>

        <div class="cours-navigation">
            <a href="<?php echo site_url('exercices'); ?>" class="nav-btn nav-exercices">📝 Exercices</a>
            <a href="<?php echo site_url('college/college-accueil'); ?>" class="nav-btn nav-accueil">🏠 Accueil Collège</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href("<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard">📊 Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$has_access): ?>
        <div class="coach-preview">
            <h2>🔒 Débloque l'accès aux cours 5ème</h2>
            <p>Crée un compte gratuit pour accéder à tous les cours de 5ème !</p>
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
            Bienvenue dans tes cours de 5ème ! Continue de progresser avec des cours adaptés qui te permettront d'aller plus loin dans chaque matière.
            <?php if ($is_demo && !$is_admin): ?>
            <br><small style="color: #666; font-style: italic;">💡 Mode démo : tu as accès à <strong>un seul cours 5ème</strong>. Crée un compte pour accéder à tous les cours !</small>
            <?php endif; ?>
        </div>

        <div class="level-intro-box">
            <h3>🎯 Objectifs 5ème</h3>
            <p>La 5ème consolide les acquis de 6ème et introduit de nouvelles notions plus complexes. Tu vas approfondir tes connaissances et développer ton autonomie.</p>
            <ul>
                <li>🔢 <strong>Mathématiques</strong> : Nombres relatifs, proportionnalité, triangles</li>
                <li>✍️ <strong>Français</strong> : Figures de style, récits d'aventure, argumentation</li>
                <li>⚗️ <strong>Sciences</strong> : Circuit électrique, respiration, digestion</li>
                <li>🗺️ <strong>Histoire-Géo</strong> : Moyen Âge, continents et océans</li>
                <li>🇬🇧 <strong>Anglais</strong> : Prétérit, comparatifs, expression orale</li>
            </ul>
        </div>

        <div class="subjects-cards">
            <?php
        $subjectsForNav = ($is_demo && !$is_admin) ? [reset($subjects)] : $subjects;
foreach ($subjectsForNav as $subject):
    $subjectId = strtolower(str_replace([' ', '-'], ['', ''], $subject));
    $icons = ['Mathématiques' => '🧮', 'Français' => '📚', 'Sciences' => '🔬', 'Histoire-Géo' => '🏛️', 'Anglais' => '🇬🇧'];
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

        <?php
        $subjectsToDisplay = ($is_demo && !$is_admin) ? [reset($subjects)] : $subjects;
foreach ($subjectsToDisplay as $subject):
    $subjectId = strtolower(str_replace([' ', '-'], ['', ''], $subject));
    $icons = ['Mathématiques' => '🧮', 'Français' => '📚', 'Sciences' => '🔬', 'Histoire-Géo' => '🏛️', 'Anglais' => '🇬🇧'];
    $icon = $icons[$subject] ?? '📖';
    ?>
            <section id="<?php echo htmlspecialchars($subjectId); ?>" class="cours-section cours-5eme-section" style="display: none;">
                <div class="cours-section-header">
                    <button class="btn-back-subject" onclick="toggleSubject('<?php echo htmlspecialchars($subjectId); ?>')">← Retour aux matières</button>
                    <h2><?php echo $icon; ?> <?php echo htmlspecialchars($subject); ?> - 5ème</h2>
                </div>

                <div class="cours-grid cours-grid-3cols">
                    <?php
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
                            <div class="cours-card cours-5eme-card">
                                <div class="cours-card-header">
                                    <h3><?php echo htmlspecialchars($courseTitle); ?></h3>
                                    <span class="cours-level">5ème</span>
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
                                        <h4>🎯 Objectifs :</h4>
                                        <ul>
                                            <?php foreach (array_slice($courseContent['objectives'], 0, 3) as $obj): ?>
                                                <li><?php echo htmlspecialchars($obj); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="cours-card-footer">
                                    <a href="<?php echo site_url('pages/cours-detail', ['id' => $exercise['Id'] ?? 0, 'matiere' => $subject, 'cours' => $exercise['Id'] ?? 0, 'niveau' => '5eme']); ?>" class="btn-cours">📖 Voir le cours complet</a>
                                </div>
                            </div>
                    <?php
        }
    } else {
        if (!($is_demo && !$is_admin && $courseCount >= 1)) {
            ?>
                        <div class="cours-card cours-5eme-card">
                            <div class="cours-card-header">
                                <h3>Cours <?php echo htmlspecialchars($subject); ?> - 5ème</h3>
                                <span class="cours-level">5ème</span>
                            </div>
                            <div class="cours-card-body">
                                <p class="cours-description">Cours de <?php echo htmlspecialchars($subject); ?> pour consolider tes acquis de 6ème et approfondir de nouvelles notions en 5ème.</p>
                                <div class="cours-objectives-preview">
                                    <h4>🎯 Au programme :</h4>
                                    <ul>
                                        <li>Approfondissement des notions de 6ème</li>
                                        <li>Nouvelles compétences du programme 5ème</li>
                                        <li>Méthodes et exercices d'application</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cours-card-footer">
                                <a href="<?php echo site_url('cours', ['niveau' => '5eme']); ?>" class="btn-cours">📖 Accéder aux cours</a>
                            </div>
                        </div>
                    <?php }
        } ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="learning-tips-section">
            <h2>💡 Conseils pour réussir en 5ème</h2>
            <div class="tips-grid">
                <div class="tip-card">
                    <h3>📝 Approfondissement</h3>
                    <p>Les notions deviennent plus complexes. Prends le temps de bien comprendre avant de passer à la suite.</p>
                </div>
                <div class="tip-card">
                    <h3>🎯 Méthode de travail</h3>
                    <p>Apprends à faire des fiches de révision claires et synthétiques. C'est une compétence qui te servira longtemps !</p>
                </div>
                <div class="tip-card">
                    <h3>💪 Autonomie</h3>
                    <p>Essaie de chercher par toi-même avant de demander de l'aide. Cela renforce ta compréhension et ta confiance.</p>
                </div>
                <div class="tip-card">
                    <h3>⏱️ Gestion du temps</h3>
                    <p>Planifie tes devoirs et révisions. Un agenda bien tenu est ton meilleur allié pour ne rien oublier !</p>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<script>
function toggleSubject(subjectId) {
    document.querySelectorAll('.cours-section').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.subject-card').forEach(c => c.classList.remove('active'));
    const target = document.getElementById(subjectId);
    if (target) {
        target.style.display = 'block';
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    const card = document.querySelector(`[data-subject="${subjectId}"]`);
    if (card) card.classList.add('active');
}
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash) toggleSubject(window.location.hash.substring(1));
});
</script>
