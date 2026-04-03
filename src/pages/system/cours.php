<?php
// ✅ 1. VÉRIFICATIONS EN PREMIER (AVANT TOUT OUTPUT)
$page_title = 'Cours - MonCoachScolaire';
$page_css = 'pages/cours.css';

// Charger config.php pour avoir accès à $pdo (sans charger site_boot.php encore)
if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}

// Charger les fonctions de sécurité AVANT site_boot.php
if (is_file(dirname(__DIR__, 2) . '/includes/demo_security.php')) {
    require_once dirname(__DIR__, 2) . '/includes/demo_security.php';
}

// Charger admin_auth.php AVANT site_boot.php
if (is_file(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
}

// ✅ VÉRIFICATION DÉMO : REDIRECTION AVANT TOUT OUTPUT
$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();

if ($is_demo && !$is_admin) {
    // Construire l'URL manuellement (site_url() n'est pas encore disponible)
    if (function_exists('detectBaseUrl')) {
        $baseUrl = rtrim(detectBaseUrl(), '/');
    } else {
        // Fallback si la fonction n'existe pas
        $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    }

    $redirectUrl = $baseUrl . '/index.php?page=demo&demo=1&reason=' . urlencode('Seuls les utilisateurs enregistrés peuvent accéder à tous les cours');
    header('Location: ' . $redirectUrl);
    exit;
}

// Charger la fonction de navigation entre niveaux
if (is_file(dirname(__DIR__, 2) . '/includes/level_navigation.php')) {
    require_once dirname(__DIR__, 2) . '/includes/level_navigation.php';
}

// ✅ 2. MAINTENANT ON PEUT CHARGER site_boot.php (qui charge topbar.php)
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}

// Charger le système de normalisation des niveaux
require_once dirname(__DIR__, 2) . '/includes/level_normalization.php';

// Le reste du code continue normalement...
$has_access = !empty($is_logged_in) || $is_admin;

// ... reste du fichier inchangé


// Déterminer le niveau : priorité au paramètre URL, puis session (y compris demo_level), puis défaut
$user_level = '6ème'; // Défaut

// Si un paramètre niveau est passé dans l'URL, l'utiliser
if (isset($_GET['niveau']) && !empty($_GET['niveau'])) {
    $niveau_param = trim($_GET['niveau']);
    // Convertir le niveau normalisé (ex: "6eme") vers le niveau complet (ex: "6ème")
    $level_mapping = [
        '6eme' => '6ème',
        '5eme' => '5ème',
        '4eme' => '4ème',
        '3eme' => '3ème',
        'seconde' => 'Seconde',
        'premiere' => 'Première',
        'terminale' => 'Terminale',
        'bac' => 'BAC',
    ];
    $user_level = $level_mapping[$niveau_param] ?? $user_level;
} elseif ($is_demo && !empty($_SESSION['demo_level'])) {
    // Pour le compte démo, utiliser le niveau sélectionné dans la démo
    $user_level = $_SESSION['demo_level'];
} elseif (!empty($_SESSION['user_level'])) {
    // Sinon, utiliser le niveau de la session
    $user_level = $_SESSION['user_level'];
    // Normaliser le niveau si nécessaire (ex: "6eme" -> "6ème")
    if (strpos($user_level, 'eme') !== false && strpos($user_level, 'ème') === false) {
        $level_mapping = [
            '6eme' => '6ème',
            '5eme' => '5ème',
            '4eme' => '4ème',
            '3eme' => '3ème',
        ];
        $user_level = $level_mapping[$user_level] ?? $user_level;
    }
}

// Si l'URL contient un paramètre niveau, vérifier que l'utilisateur y a accès
if (isset($_GET['niveau']) && is_file(dirname(__DIR__, 2) . '/includes/level_access.php')) {
    require_once dirname(__DIR__, 2) . '/includes/level_access.php';
    // enforce_level_access_or_abort renverra 403 si accès interdit
    enforce_level_access_or_abort($user_level);
}

// Normaliser le niveau pour l'URL
$level_normalized = normalize_level_for_url($user_level);

// Normaliser le niveau de l'utilisateur pour affichage correct (6ème et non 6??me)
$user_level_display = get_level_display_name($user_level);

// Déterminer si c'est collège, lycée ou BAC
$is_college = is_college_level($user_level);
$is_lycee = is_lycee_level($user_level);
$is_bac = ($user_level === 'BAC');

// Matières disponibles selon le niveau (ou toutes si admin)
$subjects = [];
if ($is_admin) {
    // Admin a accès à toutes les matières
    $subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géographie', 'Anglais', 'Philosophie', 'Physique-Chimie', 'SVT'];
} elseif ($is_college) {
    $subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géographie', 'Anglais', 'SVT'];
} else {
    $subjects = ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géographie', 'Anglais', 'Philosophie', 'Physique-Chimie'];
}
?>


<?php $cours_background = function_exists('asset_url') ? asset_url('assets/img/background_school_material.webp') : '/public/assets/img/background_school_material.webp'; ?>
<main class="cours-main" style="background-image: url('<?= htmlspecialchars($cours_background, ENT_QUOTES) ?>'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 100vh;">
    <?php if (!empty($user_level) && $is_admin) {
        echo render_level_navigation($user_level, 'cours');
    } ?>

    <div>
        <!-- Header harmonisé -->
        <header class="cours-header">
            <h1>📚 Tes Cours <?php echo htmlspecialchars($user_level_display); ?></h1>
            <div class="subtitle">
                <?php echo $is_bac ? 'Prépare ton BAC' : 'Maîtrise tes cours'; ?> de <strong><?php echo htmlspecialchars($user_level_display); ?></strong> avec exercices intégrés.
            </div>
            <nav class="cours-navigation">
                <a href="<?php
                    if ($is_college) {
                        echo site_url('college/' . $level_normalized . '/exercices-' . $level_normalized);
                    } elseif ($is_lycee) {
                        echo site_url('lycee/' . $level_normalized . '/exercices-' . $level_normalized);
                    } elseif ($is_bac) {
                        echo site_url('bac/exercices-bac');
                    } else {
                        echo site_url('exercices');
                    }
                ?>" class="nav-btn nav-exercices"><span>📝</span> Exercices</a>
                <?php
                // Bouton accueil du niveau scolaire
                if ($is_college) {
                    echo '<a href="' . site_url('eleve/college/college-accueil') . '" class="nav-btn nav-accueil"><span>🏫</span> Accueil Collège</a>';
                } elseif ($is_lycee) {
                    echo '<a href="' . site_url('eleve/lycee/lycee-accueil') . '" class="nav-btn nav-accueil"><span>🎓</span> Accueil Lycée</a>';
                } elseif ($is_bac) {
                    echo '<a href="' . site_url('eleve/bac/bac-accueil') . '" class="nav-btn nav-accueil"><span>🎯</span> Accueil BAC</a>';
                }
                ?>
            </nav>
        </header>

        <?php if (!$has_access): ?>
            <!-- Non connecté (inchangé) -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-3xl p-12 mb-16 shadow-2xl text-center">
                <!-- ... ton code existant ... -->
            </div>
        <?php else: ?>
            <!-- Header niveau + badge harmonisé -->
            <div class="cours-section-header">
                <h2>👋 Bonjour <span class="text-blue-600"><?php echo htmlspecialchars($display_name ?? 'Élève'); ?></span> !</h2>
                <p>Voici tous tes cours pour <strong class="text-2xl text-blue-600"><?php echo htmlspecialchars($user_level_display); ?></strong></p>
                <?php if ($is_demo && !$is_admin): ?>
                    <p class="text-sm text-amber-600 mt-2 italic">💡 Mode démo : 1 cours. Crée un compte pour tout !</p>
                <?php endif; ?>
                <div class="meta-item" style="display:inline-block; margin-top:1rem;">
                    <?php echo $is_bac ? '🎯 BAC' : ($is_lycee ? '🎓 Lycée' : '🏫 Collège'); ?>
                </div>
                <span id="courseCountTotal" class="meta-item" style="display:inline-block; margin-left:1rem;">0 cours</span>
            </div>

            <!-- Recherche + filtres matières -->
            <div class="subjects-nav">
                <input type="text" id="searchInput" placeholder="Recherche un cours (ex: fractions, SVT)..." class="subject-nav-btn" style="min-width:220px;" />
                <select id="subjectFilter" class="subject-nav-btn">
                    <option value="">Toutes tes matières</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?php echo strtolower(str_replace([' ', '-'], '', $sub)); ?>"><?php echo $sub; ?></option>
                    <?php endforeach; ?>
                </select>
                <button id="clearFilters" class="subject-nav-btn">🧹 Effacer</button>
            </div>


            <!-- Recommandés (top 6 du niveau, progression < 70%) -->
            <section class="cours-section">
                <h2>✨ Recommandés pour toi <span class="meta-item">Priorité progression</span></h2>
                <div id="recommendedCourses" class="cours-grid cours-grid-3cols">
                    <!-- Chargé via JS -->
                </div>
            </section>

            <!-- Tous les cours du niveau -->
            <section class="cours-section">
                <h2>📖 Tous tes cours <?php echo htmlspecialchars($user_level_display); ?></h2>
                <div id="allCoursesGrid" class="cours-grid cours-grid-3cols">
                    <!-- Chargé via PHP/JS -->
                </div>
            </section>

            <!-- Conseils adaptés (ton code existant) -->
            <?php if ($is_bac): /* ton section conseils BAC */ endif; ?>
        <?php endif; ?>
    </div>
</main>

<script>
// Charger UNIQUEMENT les cours du niveau de l'élève
let allCourses = <?php
// ✅ CHARGEMENT BDD : UNIQUEMENT niveau connecté
$courses = [];
if (isset($pdo) && function_exists('getCoursesBySubjectAndLevel')) {
    foreach ($subjects as $subject) {
        $levelCourses = getCoursesBySubjectAndLevel($subject, $user_level);
        foreach ($levelCourses as $course) {
            $course['level'] = $user_level_display;
            $course['icon'] = $icons[$subject] ?? '📖';
            $course['type'] = 'Cours + ' . (isset($linkedExercises) ? count($linkedExercises) . ' ex.' : 'Quiz');
            $course['duration'] = '15-30 min'; // À calculer
            $course['progress'] = rand(0, 100); // À remplacer par vraie progression
            $course['url'] = site_url('view_course', ['id' => $course['Id']]);
            $course['cta'] = 'Commencer';
            $courses[] = $course;
        }
    }
}
echo json_encode($courses ?? []);
?>;

document.addEventListener('DOMContentLoaded', () => {
    const totalCount = allCourses.length;
    document.getElementById('courseCountTotal').textContent = `${totalCount} cours`;

    // Recommandés : progression < 70% ou nouveaux
    const recommended = allCourses
        .filter(c => c.progress < 70)
        .sort((a, b) => a.progress - b.progress)
        .slice(0, 6);

    document.getElementById('recommendedCourses').innerHTML = recommended.map(createCourseCard).join('');
    renderCourses(allCourses);

    // Filtres (recherche + matières seulement)
    document.getElementById('searchInput').addEventListener('input', filterCourses);
    document.getElementById('subjectFilter').addEventListener('change', filterCourses);
    document.getElementById('clearFilters').onclick = () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('subjectFilter').value = '';
        filterCourses();
    };
});

function filterCourses() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const subject = document.getElementById('subjectFilter').value.toLowerCase();

    const filtered = allCourses.filter(course =>
        course.Title.toLowerCase().includes(search) && // ou course.title si modifié
        (!subject || course.subject?.toLowerCase().includes(subject))
    );

    renderCourses(filtered);
}

// createCourseCard() et renderCourses() identiques à précédent
function createCourseCard(course) { /* même code que avant, avec level fixe */ }
function renderCourses(courses) {
    document.getElementById('allCoursesGrid').innerHTML = courses.map(createCourseCard).join('');
}
</script>
