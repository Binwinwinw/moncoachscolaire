<?php
$page_title = 'Cours Seconde - Lycée - MonCoachScolaire';
$page_css = 'lycee/seconde/cours-seconde.css';

if (!isset($pdo)) {
    require_once dirname(__DIR__, 4) . '/config/config.php';
}
if (is_file(dirname(__DIR__, 4) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 4) . '/config/site_boot.php';
}
if (is_file(dirname(__DIR__, 4) . '/includes/level_navigation.php')) {
    require_once dirname(__DIR__, 4) . '/includes/level_navigation.php';
}
if (is_file(dirname(__DIR__, 4) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 4) . '/includes/admin_auth.php';
}
if (is_file(dirname(__DIR__, 4) . '/includes/demo_security.php')) {
    require_once dirname(__DIR__, 4) . '/includes/demo_security.php';
}

$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();
$has_access = !empty($is_logged_in) || $is_admin || $is_demo;
$user_level = 'Seconde';
$level_normalized = normalize_level_for_url($user_level);
$subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais', 'Philosophie'];
?>

<main class="main-content cours-main">
    <?php if (function_exists('render_level_navigation')) {
        echo render_level_navigation('Seconde', 'cours');
    } ?>
    <div class="cours-header">
        <h1>📚 Cours Seconde - Lycée</h1>
        <p class="subtitle">Bienvenue au lycée ! Découvre de nouvelles matières et approfondis tes connaissances !</p>
        <div class="cours-navigation">
            <a href="<?php echo site_url('exercices'); ?>" class="nav-btn nav-exercices">📝 Exercices</a>
            <a href="<?php echo site_url('lycee/lycee-accueil'); ?>" class="nav-btn nav-accueil">🏠 Accueil Lycée</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard">📊 Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$has_access): ?>
        <div class="coach-preview">
            <h2>🔒 Débloque l'accès aux cours Seconde</h2>
            <p>Crée un compte gratuit pour accéder à tous les cours de Seconde !</p>
            <div class="cta-actions">
                <a href="<?php echo site_url('register'); ?>" class="coach-cta">✨ Créer mon compte</a>
                <a href="<?php echo site_url('login'); ?>" class="coach-cta secondary">🔑 Me connecter</a>
            </div>
        </div>
    <?php else: ?>
        <div class="coach-message">
            <strong>👋 Salut <?php echo htmlspecialchars($user_name ?? $_SESSION['user_name'] ?? 'Élève'); ?> !</strong><br>
            Bienvenue au lycée ! La Seconde est une année de découverte et d'orientation. Nos cours te permettront de t'adapter aux nouvelles exigences et de bien choisir ta spécialité.
            <?php if ($is_demo && !$is_admin): ?>
                <br><small>💡 Mode démo : tu as accès à <strong>un seul cours Seconde</strong>. Crée un compte pour accéder à tous les cours !</small>
            <?php endif; ?>
        </div>

        <div class="level-intro-box">
            <h3>🎯 Bienvenue au lycée !</h3>
            <p>La Seconde est une classe générale et technologique qui te permet de découvrir de nouvelles disciplines et de choisir ton orientation pour la suite. Les exigences sont plus élevées et l'autonomie est essentielle.</p>
            <ul>
                <li>📐 <strong>Mathématiques</strong> : Fonctions, vecteurs, probabilités</li>
                <li>✍️ <strong>Français</strong> : Analyse littéraire, dissertation, commentaire composé</li>
                <li>🔬 <strong>Sciences</strong> : Physique-chimie, SVT, démarche expérimentale</li>
                <li>🌍 <strong>Histoire-Géo</strong> : Période contemporaine, mondialisation, citoyenneté</li>
                <li>🇬🇧 <strong>Anglais</strong> : Expression orale continue, compréhension audio</li>
                <li>🤔 <strong>Philosophie</strong> : Introduction à la pensée philosophique (optionnel)</li>
            </ul>
        </div>

        <div class="subjects-cards">
            <?php
            $subjectsForNav = ($is_demo && !$is_admin) ? [reset($subjects)] : $subjects;
            foreach ($subjectsForNav as $subject):
                $subjectId = strtolower(str_replace([' ', '-'], ['', ''], $subject));
                $icons = ['Mathématiques' => '🧮', 'Français' => '📚', 'Sciences' => '🔬', 'Histoire-Géo' => '🏛️', 'Anglais' => '🇬🇧', 'Philosophie' => '🤔'];
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
            $icons = ['Mathématiques' => '🧮', 'Français' => '📚', 'Sciences' => '🔬', 'Histoire-Géo' => '🏛️', 'Anglais' => '🇬🇧', 'Philosophie' => '🤔'];
            $icon = $icons[$subject] ?? '📖';
        ?>
            <section id="<?php echo htmlspecialchars($subjectId); ?>" class="cours-section cours-seconde-section">
                <div class="cours-section-header">
                    <button class="btn-back-subject" onclick="toggleSubject('<?php echo htmlspecialchars($subjectId); ?>')">← Retour aux matières</button>
                    <h2><?php echo $icon; ?> <?php echo htmlspecialchars($subject); ?> - Seconde</h2>
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
                            <div class="cours-card cours-seconde-card">
                                <div class="cours-card-header">
                                    <h3><?php echo htmlspecialchars($courseTitle); ?></h3>
                                    <span class="cours-level">Seconde</span>
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
                                    <a href="<?php echo site_url('pages/cours-detail', ['id' => $exercise['Id'] ?? 0, 'matiere' => $subject, 'cours' => $exercise['Id'] ?? 0, 'niveau' => 'seconde']); ?>" class="btn-cours">📖 Voir le cours complet</a>
                                </div>
                            </div>
                        <?php
                        }
                    } else {
                        if (!($is_demo && !$is_admin && $courseCount >= 1)) {
                        ?>
                            <div class="cours-card cours-seconde-card">
                                <div class="cours-card-header">
                                    <h3>Cours <?php echo htmlspecialchars($subject); ?> - Seconde</h3>
                                    <span class="cours-level">Seconde</span>
                                </div>
                                <div class="cours-card-body">
                                    <p class="cours-description">Cours complet de <?php echo htmlspecialchars($subject); ?> pour bien démarrer ton parcours au lycée.</p>
                                    <div class="cours-objectives-preview">
                                        <h4>🎯 Au programme :</h4>
                                        <ul>
                                            <li>Notions essentielles du programme Seconde</li>
                                            <li>Méthodes de travail au lycée</li>
                                            <li>Préparation aux spécialités de Première</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="cours-card-footer">
                                    <a href="<?php echo site_url('cours', ['niveau' => 'seconde']); ?>" class="btn-cours">📖 Accéder aux cours</a>
                                </div>
                            </div>
                    <?php }
                    } ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="learning-tips-section">
            <h2>💡 Conseils pour réussir en Seconde</h2>
            <div class="tips-grid">
                <div class="tip-card">
                    <h3>🎯 Adaptation</h3>
                    <p>Le lycée demande plus d'autonomie. Organise ton travail personnel, prends des notes claires et relis tes cours régulièrement.</p>
                </div>
                <div class="tip-card">
                    <h3>🔍 Exploration</h3>
                    <p>Profite de la Seconde pour découvrir différentes matières. Cela t'aidera à choisir tes spécialités en Première.</p>
                </div>
                <div class="tip-card">
                    <h3>📚 Méthode</h3>
                    <p>Apprends à rédiger : dissertation, commentaire, synthèse. Ces compétences te suivront jusqu'au BAC et au-delà !</p>
                </div>
                <div class="tip-card">
                    <h3>💪 Persévérance</h3>
                    <p>Les débuts peuvent être difficiles. Ne te décourage pas : demande de l'aide, travaille régulièrement, et les progrès viendront !</p>
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
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        const card = document.querySelector(`[data-subject="${subjectId}"]`);
        if (card) card.classList.add('active');
    }
    document.addEventListener('DOMContentLoaded', () => {
        if (window.location.hash) toggleSubject(window.location.hash.substring(1));
    });
</script>