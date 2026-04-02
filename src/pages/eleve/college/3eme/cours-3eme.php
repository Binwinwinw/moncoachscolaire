<?php
$page_title = 'Cours 3ème - Brevet - MonCoachScolaire';
$page_css = 'college/3eme/cours-3eme.css';

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
$user_level = '3ème';
$level_normalized = normalize_level_for_url($user_level);
$subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais'];
?>

<main class="main-content cours-main">
    <?php if (function_exists('render_level_navigation')) {
        echo render_level_navigation('3ème', 'cours');
    } ?>
    <div class="cours-header">
        <h1>📚 Cours 3ème - Préparation Brevet</h1>
        <p class="subtitle">L'année du brevet ! Prépare-toi efficacement avec nos cours complets !</p>
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
            <h2>🔒 Débloque l'accès aux cours 3ème</h2>
            <p>Crée un compte gratuit pour accéder à tous les cours de 3ème et réussir ton brevet !</p>
            <div class="cta-actions">
                <a href="<?php echo site_url('register'); ?>" class="coach-cta">✨ Créer mon compte</a>
                <a href="<?php echo site_url('login'); ?>" class="coach-cta secondary">🔑 Me connecter</a>
            </div>
        </div>
    <?php else: ?>
        <div class="coach-message">
            <strong>👋 Salut <?php echo htmlspecialchars($user_name ?? $_SESSION['user_name'] ?? 'Élève'); ?> !</strong><br>
            C'est l'année du brevet ! Nos cours de 3ème te permettront de maîtriser toutes les notions du programme et de te préparer sereinement à l'examen.
            <?php if ($is_demo && !$is_admin): ?>
            <br><small style="color: #666; font-style: italic;">💡 Mode démo : tu as accès à <strong>un seul cours 3ème</strong>. Crée un compte pour accéder à tous les cours et réussir ton brevet !</small>
            <?php endif; ?>
        </div>

        <div class="level-intro-box">
            <h3>🎯 Objectif Brevet !</h3>
            <p>La 3ème marque la fin du collège et ton premier examen national. Ces cours couvrent l'intégralité du programme et t'aident à préparer efficacement les épreuves du DNB.</p>
            <ul>
                <li>🔢 <strong>Mathématiques</strong> : Fonctions, trigonométrie, statistiques avancées</li>
                <li>✍️ <strong>Français</strong> : Argumentation, réécriture, dictée, rédaction</li>
                <li>⚗️ <strong>Sciences</strong> : Chimie, énergies, génétique, évolution</li>
                <li>🌍 <strong>Histoire-Géo</strong> : Guerres mondiales, géopolitique, EMC</li>
                <li>🇬🇧 <strong>Anglais</strong> : Expression orale, compréhension, culture anglophone</li>
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
            <section id="<?php echo htmlspecialchars($subjectId); ?>" class="cours-section cours-3eme-section" style="display: none;">
                <div class="cours-section-header">
                    <button class="btn-back-subject" onclick="toggleSubject('<?php echo htmlspecialchars($subjectId); ?>')">← Retour aux matières</button>
                    <h2><?php echo $icon; ?> <?php echo htmlspecialchars($subject); ?> - 3ème / Brevet</h2>
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
                            <div class="cours-card cours-3eme-card">
                                <div class="cours-card-header">
                                    <h3><?php echo htmlspecialchars($courseTitle); ?></h3>
                                    <span class="cours-level">3ème</span>
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
                                        <h4>🎯 Objectifs Brevet :</h4>
                                        <ul>
                                            <?php foreach (array_slice($courseContent['objectives'], 0, 3) as $obj): ?>
                                                <li><?php echo htmlspecialchars($obj); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="cours-brevet-badge"><span>🏅 Préparation Brevet</span></div>
                                </div>
                                <div class="cours-card-footer">
                                    <a href="<?php echo site_url('pages/cours-detail', ['id' => $exercise['Id'] ?? 0, 'matiere' => $subject, 'cours' => $exercise['Id'] ?? 0, 'niveau' => '3eme']); ?>" class="btn-cours">📖 Voir le cours complet</a>
                                </div>
                            </div>
                    <?php
        }
    } else {
        if (!($is_demo && !$is_admin && $courseCount >= 1)) {
            ?>
                        <div class="cours-card cours-3eme-card">
                            <div class="cours-card-header">
                                <h3>Cours <?php echo htmlspecialchars($subject); ?> - Brevet</h3>
                                <span class="cours-level">3ème</span>
                            </div>
                            <div class="cours-card-body">
                                <p class="cours-description">Cours complet de <?php echo htmlspecialchars($subject); ?> pour réussir ton brevet des collèges.</p>
                                <div class="cours-objectives-preview">
                                    <h4>🎯 Programme Brevet :</h4>
                                    <ul>
                                        <li>Toutes les notions du programme 3ème</li>
                                        <li>Méthodologie des épreuves du brevet</li>
                                        <li>Exercices types et annales corrigées</li>
                                    </ul>
                                </div>
                                <div class="cours-brevet-badge"><span>🏅 Préparation Brevet</span></div>
                            </div>
                            <div class="cours-card-footer">
                                <a href="<?php echo site_url('cours', ['niveau' => '3eme']); ?>" class="btn-cours">📖 Accéder aux cours</a>
                            </div>
                        </div>
                    <?php }
        } ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="learning-tips-section">
            <h2>💡 Conseils pour réussir ton Brevet</h2>
            <div class="tips-grid">
                <div class="tip-card"><h3>📅 Planification</h3><p>Commence tes révisions tôt. Établis un planning équilibré qui couvre toutes les matières sans te surcharger.</p></div>
                <div class="tip-card"><h3>📝 Fiches de révision</h3><p>Crée des fiches synthétiques pour chaque chapitre. C'est le meilleur moyen de mémoriser et de réviser efficacement.</p></div>
                <div class="tip-card"><h3>🎯 Entraînement</h3><p>Fais des sujets blancs et des annales. Plus tu t'entraînes, plus tu seras à l'aise le jour J !</p></div>
                <div class="tip-card"><h3>😌 Gestion du stress</h3><p>Respire, fais des pauses, dors suffisamment. Un esprit reposé apprend mieux qu'un esprit fatigué. Tu vas y arriver !</p></div>
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
