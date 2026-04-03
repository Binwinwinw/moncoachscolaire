<?php
$page_title = 'Cours Première - Lycée - MonCoachScolaire';
$page_css = 'lycee/premiere/cours-premiere.css';

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
$user_level = 'Première';
$level_normalized = normalize_level_for_url($user_level);
$subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais', 'Philosophie'];
?>

<main class="main-content cours-main">
    <?php if (function_exists('render_level_navigation')) {
        echo render_level_navigation('Première', 'cours');
    } ?>
    <div class="cours-header">
        <h1>📚 Cours Première - Lycée</h1>
        <p class="subtitle">Approfondis tes spécialités et prépare-toi au Bac de Français !</p>
        <div class="cours-navigation">
            <a href="<?php echo site_url('exercices'); ?>" class="nav-btn nav-exercices">📝 Exercices</a>
            <a href="<?php echo site_url('lycee/lycee-accueil'); ?>" class="nav-btn nav-accueil">🏠 Accueil Lycée</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href("<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard">📊 Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$has_access): ?>
        <div class="coach-preview">
            <h2>🔒 Débloque l'accès aux cours Première</h2>
            <p>Crée un compte gratuit pour accéder à tous les cours de Première !</p>
            <div class="cta-actions">
                <a href="<?php echo site_url('register'); ?>" class="coach-cta">✨ Créer mon compte</a>
                <a href="<?php echo site_url('login'); ?>" class="coach-cta secondary">🔑 Me connecter</a>
            </div>
        </div>
    <?php else: ?>
        <div class="coach-message">
            <strong>👋 Salut <?php echo htmlspecialchars($user_name ?? $_SESSION['user_name'] ?? 'Élève'); ?> !</strong><br>
            La Première est une année décisive : tu approfondis tes spécialités et passes ton Bac de Français. Nos cours te permettront de réussir ces deux défis !
            <?php if ($is_demo && !$is_admin): ?>
            <br><small>💡 Mode démo : tu as accès à <strong>un seul cours Première</strong>. Crée un compte pour accéder à tous les cours !</small>
            <?php endif; ?>
        </div>

        <div class="level-intro-box">
            <h3>🎯 Objectifs Première</h3>
            <p>La Première marque le début du cycle terminal. Tu travailles tes spécialités en profondeur et passes tes premières épreuves du BAC (français écrit et oral). C'est une année intense qui demande rigueur et organisation.</p>
            <ul>
                <li>🔢 <strong>Mathématiques</strong> : Dérivation, suites, probabilités conditionnelles</li>
                <li>📖 <strong>Français</strong> : Analyse d'œuvres intégrales, dissertation, commentaire, oral</li>
                <li>⚗️ <strong>Sciences</strong> : Spécialités approfondies (physique-chimie, SVT, etc.)</li>
                <li>🗺️ <strong>Histoire-Géo</strong> : République, totalitarismes, mondialisation</li>
                <li>🇬🇧 <strong>Anglais</strong> : Expression structurée, analyse de documents, civilisation</li>
                <li>🤔 <strong>Philosophie</strong> : Initiation aux notions philosophiques (optionnel)</li>
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
            <section id="<?php echo htmlspecialchars($subjectId); ?>" class="cours-section cours-premiere-section">
                <div class="cours-section-header">
                    <button class="btn-back-subject" onclick="toggleSubject('<?php echo htmlspecialchars($subjectId); ?>')">← Retour aux matières</button>
                    <h2><?php echo $icon; ?> <?php echo htmlspecialchars($subject); ?> - Première</h2>
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
                            <div class="cours-card cours-premiere-card">
                                <div class="cours-card-header">
                                    <h3><?php echo htmlspecialchars($courseTitle); ?></h3>
                                    <span class="cours-level">Première</span>
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
                                    <a href="<?php echo site_url('pages/cours-detail', ['id' => $exercise['Id'] ?? 0, 'matiere' => $subject, 'cours' => $exercise['Id'] ?? 0, 'niveau' => 'premiere']); ?>" class="btn-cours">📖 Voir le cours complet</a>
                                </div>
                            </div>
                    <?php
        }
    } else {
        if (!($is_demo && !$is_admin && $courseCount >= 1)) {
            ?>
                        <div class="cours-card cours-premiere-card">
                            <div class="cours-card-header">
                                <h3>Cours <?php echo htmlspecialchars($subject); ?> - Première</h3>
                                <span class="cours-level">Première</span>
                            </div>
                            <div class="cours-card-body">
                                <p class="cours-description">Cours complet de <?php echo htmlspecialchars($subject); ?> pour approfondir tes spécialités et réussir ton Bac de Français.</p>
                                <div class="cours-objectives-preview">
                                    <h4>🎯 Au programme :</h4>
                                    <ul>
                                        <li>Programme approfondi des spécialités</li>
                                        <li>Préparation au Bac de Français</li>
                                        <li>Méthodologie dissertation et commentaire</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cours-card-footer">
                                <a href="<?php echo site_url('cours', ['niveau' => 'premiere']); ?>" class="btn-cours">📖 Accéder aux cours</a>
                            </div>
                        </div>
                    <?php }
        } ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="learning-tips-section">
            <h2>💡 Conseils pour réussir en Première</h2>
            <div class="tips-grid">
                <div class="tip-card"><h3>📚 Spécialités</h3><p>Concentre-toi sur tes spécialités : elles comptent pour le BAC. Travaille-les régulièrement et approfondis les notions complexes.</p></div>
                <div class="tip-card"><h3>✍️ Bac de Français</h3><p>Lis les œuvres au programme, fais des fiches par texte, entraîne-toi aux exercices (commentaire, dissertation, oral). La préparation commence dès septembre !</p></div>
                <div class="tip-card"><h3>⏰ Organisation</h3><p>Planifie ton travail personnel. Entre les spécialités et le français, l'année est chargée. Anticipe et répartis tes efforts.</p></div>
                <div class="tip-card"><h3>💪 Grand oral</h3><p>Commence à réfléchir à tes questions de Grand Oral en lien avec tes spécialités. Plus tu prépares tôt, mieux c'est !</p></div>
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

