<?php
$page_title = 'Cours Terminale - Lycée - MonCoachScolaire';
$page_css = 'lycee/terminale/cours-terminale.css';

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
$user_level = 'Terminale';
$level_normalized = normalize_level_for_url($user_level);
$subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais', 'Philosophie'];
?>

<main class="main-content cours-main">
    <?php if (function_exists('render_level_navigation')) {
        echo render_level_navigation('Terminale', 'cours');
    } ?>
    <div class="cours-header">
        <h1>📚 Cours Terminale - Lycée</h1>
        <p class="subtitle">Dernière ligne droite vers le BAC ! Maîtrise tes spécialités et prépare-toi au Grand Oral !</p>
        <div class="cours-navigation">
            <a href="<?php echo site_url('exercices'); ?>" class="nav-btn nav-exercices">📝 Exercices</a>
            <a href="<?php echo site_url('cours', ['niveau' => 'bac']); ?>" class="nav-btn nav-bac">🎯 Préparation BAC</a>
            <a href="<?php echo site_url('lycee/lycee-accueil'); ?>" class="nav-btn nav-accueil">🏠 Accueil Lycée</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard">📊 Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$has_access): ?>
        <div class="coach-preview">
            <h2>🔒 Débloque l'accès aux cours Terminale</h2>
            <p>Crée un compte gratuit pour accéder à tous les cours de Terminale et réussir ton BAC !</p>
            <div class="cta-actions">
                <a href="<?php echo site_url('register'); ?>" class="coach-cta">✨ Créer mon compte</a>
                <a href="<?php echo site_url('login'); ?>" class="coach-cta secondary">🔑 Me connecter</a>
            </div>
        </div>
    <?php else: ?>
        <div class="coach-message">
            <strong>👋 Salut <?php echo htmlspecialchars($user_name ?? $_SESSION['user_name'] ?? 'Élève'); ?> !</strong><br>
            C'est l'année du BAC ! Nos cours de Terminale te permettront de maîtriser toutes les notions du programme et de te préparer efficacement aux épreuves finales et au Grand Oral.
            <?php if ($is_demo && !$is_admin): ?>
            <br><small>💡 Mode démo : tu as accès à <strong>un seul cours Terminale</strong>. Crée un compte pour accéder à tous les cours et réussir ton BAC !</small>
            <?php endif; ?>
        </div>

        <div class="level-intro-box">
            <h3>🎯 Objectif BAC !</h3>
            <p>La Terminale est l'année décisive : tu passes tes épreuves de spécialités, le Grand Oral, et la Philosophie. C'est une année intense qui demande rigueur, méthode et détermination. Chaque cours t'aide à préparer efficacement ces échéances.</p>
            <ul>
                <li>📐 <strong>Mathématiques</strong> : Fonctions avancées, intégrales, lois de probabilités</li>
                <li>🤔 <strong>Philosophie</strong> : Notions au programme, dissertation, explication de texte</li>
                <li>⚗️ <strong>Sciences</strong> : Spécialités approfondies, projets expérimentaux</li>
                <li>🗺️ <strong>Histoire-Géo</strong> : Histoire contemporaine, géopolitique mondiale, EMC</li>
                <li>🇬🇧 <strong>Anglais</strong> : Analyse de documents, synthèse, expression orale</li>
                <li>🎤 <strong>Grand Oral</strong> : Préparation des questions, argumentation, posture</li>
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
            <section id="<?php echo htmlspecialchars($subjectId); ?>" class="cours-section cours-terminale-section">
                <div class="cours-section-header">
                    <button class="btn-back-subject" onclick="toggleSubject('<?php echo htmlspecialchars($subjectId); ?>')">← Retour aux matières</button>
                    <h2><?php echo $icon; ?> <?php echo htmlspecialchars($subject); ?> - Terminale</h2>
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
                            <div class="cours-card cours-terminale-card">
                                <div class="cours-card-header">
                                    <h3><?php echo htmlspecialchars($courseTitle); ?></h3>
                                    <span class="cours-level">Terminale</span>
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
                                    <div class="cours-bac-badge"><span>🎓 Préparation BAC</span></div>
                                </div>
                                <div class="cours-card-footer">
                                    <a href="<?php echo site_url('pages/cours-detail', ['id' => $exercise['Id'] ?? 0, 'matiere' => $subject, 'cours' => $exercise['Id'] ?? 0, 'niveau' => 'terminale']); ?>" class="btn-cours">📖 Voir le cours complet</a>
                                </div>
                            </div>
                    <?php
        }
    } else {
        if (!($is_demo && !$is_admin && $courseCount >= 1)) {
            ?>
                        <div class="cours-card cours-terminale-card">
                            <div class="cours-card-header">
                                <h3>Cours <?php echo htmlspecialchars($subject); ?> - Terminale</h3>
                                <span class="cours-level">Terminale</span>
                            </div>
                            <div class="cours-card-body">
                                <p class="cours-description">Cours complet de <?php echo htmlspecialchars($subject); ?> pour maîtriser ton programme et réussir ton BAC.</p>
                                <div class="cours-objectives-preview">
                                    <h4>🎯 Programme BAC :</h4>
                                    <ul>
                                        <li>Programme complet de Terminale</li>
                                        <li>Préparation épreuves de spécialités</li>
                                        <li>Méthodologie Grand Oral et Philosophie</li>
                                    </ul>
                                </div>
                                <div class="cours-bac-badge"><span>🎓 Préparation BAC</span></div>
                            </div>
                            <div class="cours-card-footer">
                                <a href="<?php echo site_url('cours', ['niveau' => 'terminale']); ?>" class="btn-cours">📖 Accéder aux cours</a>
                            </div>
                        </div>
                    <?php }
        } ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="learning-tips-section">
            <h2>💡 Conseils pour réussir ton BAC</h2>
            <div class="tips-grid">
                <div class="tip-card"><h3>📅 Planification</h3><p>Organise tes révisions sur l'année. Prépare tes spécialités dès septembre, la Philo dès janvier. Ne laisse rien pour le dernier moment !</p></div>
                <div class="tip-card"><h3>📝 Fiches de révision</h3><p>Crée des fiches synthétiques pour chaque chapitre. Relis-les régulièrement. C'est la clé pour mémoriser et réviser efficacement.</p></div>
                <div class="tip-card"><h3>🎤 Grand Oral</h3><p>Prépare tes questions tôt, entraîne-toi à l'oral devant un miroir ou avec un proche. La maîtrise vient avec la répétition !</p></div>
                <div class="tip-card"><h3>😌 Confiance</h3><p>Tu as travaillé toute l'année. Le jour J, fais-toi confiance. Gère ton stress, reste calme, et donne le meilleur de toi-même. Tu vas réussir !</p></div>
            </div>
        </section>
    <?php endif; ?>
</main>

<script>
function toggleSubject(subjectId) {
    document.querySelectorAll('.cours-section').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.subject-card').forEach(c => c.classList.remove('active'));
    const target = document.getElementById(subjectId);
    if (target) { target.style.display = 'block'; target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    const card = document.querySelector(`[data-subject="${subjectId}"]`);
    if (card) card.classList.add('active');
}
document.addEventListener('DOMContentLoaded', () => { if (window.location.hash) toggleSubject(window.location.hash.substring(1)); });
</script>

