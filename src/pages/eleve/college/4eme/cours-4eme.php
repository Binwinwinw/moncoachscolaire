<?php
$page_title = 'Cours 4ème - Collège - MonCoachScolaire';
$page_css = 'college/4eme/cours-4eme.css';

if (!isset($pdo)) {
    if (is_file(dirname(__DIR__, 4) . '/config/config.php')) {
        require_once dirname(__DIR__, 4) . '/config/config.php';
    }
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
$user_level = '4ème';
$level_normalized = normalize_level_for_url($user_level);
$subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais'];
?>

<main class="main-content cours-main">
    <?php if (function_exists('render_level_navigation')) {
        echo render_level_navigation('4ème', 'cours');
    } ?>
    <div class="cours-header">
        <h1>📚 Cours 4ème - Collège</h1>
        <p class="subtitle">Prépare-toi au brevet avec des cours complets et structurés !</p>
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
            <h2>🔒 Débloque l'accès aux cours 4ème</h2>
            <p>Crée un compte gratuit pour accéder à tous les cours de 4ème !</p>
            <div class="cta-actions">
                <a href="<?php echo site_url('register'); ?>" class="coach-cta">✨ Créer mon compte</a>
                <a href="<?php echo site_url('login'); ?>" class="coach-cta secondary">🔑 Me connecter</a>
            </div>
        </div>
    <?php else: ?>
        <div class="coach-message">
            <strong>👋 Salut <?php echo htmlspecialchars($user_name ?? $_SESSION['user_name'] ?? 'Élève'); ?> !</strong><br>
            Bienvenue dans tes cours de 4ème ! L'avant-dernière année du collège, c'est le moment de consolider toutes tes bases pour aborder sereinement la 3ème et le brevet.
            <?php if ($is_demo && !$is_admin): ?>
            <br><small style="color: #666; font-style: italic;">💡 Mode démo : tu as accès à <strong>un seul cours 4ème</strong>. Crée un compte pour accéder à tous les cours !</small>
            <?php endif; ?>
        </div>

        <div class="level-intro-box">
            <h3>🎯 En route vers le brevet !</h3>
            <p>La 4ème est une année charnière qui prépare directement à la 3ème et au DNB. Les notions deviennent plus abstraites et exigent rigueur et méthode.</p>
            <ul>
                <li>➗ <strong>Mathématiques</strong> : Calcul littéral, théorème de Pythagore, statistiques</li>
                <li>📖 <strong>Français</strong> : Analyse littéraire, récit complexe, discours argumentatif</li>
                <li>⚡ <strong>Sciences</strong> : Lois de l'électricité, système nerveux, chimie</li>
                <li>🌍 <strong>Histoire-Géo</strong> : XVIIIe siècle, Révolution, mondialisation</li>
                <li>🗣️ <strong>Anglais</strong> : Present perfect, conditionnel, expression écrite</li>
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
            <section id="<?php echo htmlspecialchars($subjectId); ?>" class="cours-section cours-4eme-section" style="display: none;">
                <div class="cours-section-header">
                    <button class="btn-back-subject" onclick="toggleSubject('<?php echo htmlspecialchars($subjectId); ?>')">← Retour aux matières</button>
                    <h2><?php echo $icon; ?> <?php echo htmlspecialchars($subject); ?> - 4ème</h2>
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
                            <div class="cours-card cours-4eme-card">
                                <div class="cours-card-header">
                                    <h3><?php echo htmlspecialchars($courseTitle); ?></h3>
                                    <span class="cours-level">4ème</span>
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
                                    <a href="<?php echo site_url('pages/cours-detail', ['id' => $exercise['Id'] ?? 0, 'matiere' => $subject, 'cours' => $exercise['Id'] ?? 0, 'niveau' => '4eme']); ?>" class="btn-cours">📖 Voir le cours complet</a>
                                </div>
                            </div>
                    <?php
        }
    } else {
        if (!($is_demo && !$is_admin && $courseCount >= 1)) {
            ?>
                        <div class="cours-card cours-4eme-card">
                            <div class="cours-card-header">
                                <h3>Cours <?php echo htmlspecialchars($subject); ?> - 4ème</h3>
                                <span class="cours-level">4ème</span>
                            </div>
                            <div class="cours-card-body">
                                <p class="cours-description">Cours complet de <?php echo htmlspecialchars($subject); ?> pour préparer efficacement la 3ème et le brevet.</p>
                                <div class="cours-objectives-preview">
                                    <h4>🎯 Au programme :</h4>
                                    <ul>
                                        <li>Notions essentielles du programme 4ème</li>
                                        <li>Préparation aux exigences du brevet</li>
                                        <li>Exercices d'application et méthodes</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cours-card-footer">
                                <a href="<?php echo site_url('cours', ['niveau' => '4eme']); ?>" class="btn-cours">📖 Accéder aux cours</a>
                            </div>
                        </div>
                    <?php }
        } ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="learning-tips-section">
            <h2>💡 Conseils pour exceller en 4ème</h2>
            <div class="tips-grid">
                <div class="tip-card"><h3>🎯 Rigueur</h3><p>Les concepts deviennent abstraits. Prends des notes claires, relis tes cours régulièrement et n'hésite pas à refaire les exercices.</p></div>
                <div class="tip-card"><h3>📚 Anticiper</h3><p>La 4ème prépare au brevet ! Commence à acquérir les bonnes habitudes : fiches de révision, organisation, gestion du temps.</p></div>
                <div class="tip-card"><h3>💪 Persévérance</h3><p>Certaines notions peuvent sembler difficiles. Ne baisse pas les bras : cherche, demande de l'aide, réessaye. C'est en forgeant qu'on devient forgeron !</p></div>
                <div class="tip-card"><h3>🔍 Approfondir</h3><p>Ne te contente pas du minimum. Lis des livres, regarde des vidéos éducatives, cultive ta curiosité. C'est ce qui fait la différence !</p></div>
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
