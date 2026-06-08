<?php
// ✅ 1. VÉRIFICATIONS SÉCURITÉ (AVANT TOUT OUTPUT)
$page_title = 'Cours - MonCoachScolaire';
$page_css = 'pages/cours.css';

// Charger config.php pour avoir accès à $pdo
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

// Définir les variables de sécurité APRÈS les includes (pour qu'elles soient disponibles AVANT site_boot.php)
$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();

// ✅ 2. CHARGER site_boot.php (qui charge topbar.php)
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}

// Charger la fonction de navigation entre niveaux
if (is_file(dirname(__DIR__, 2) . '/includes/level_navigation.php')) {
    require_once dirname(__DIR__, 2) . '/includes/level_navigation.php';
}

// Charger le système de normalisation des niveaux
require_once dirname(__DIR__, 2) . '/includes/level_normalization.php';

// Le reste du code continue normalement...
$has_access = !empty($is_logged_in) || $is_admin || $is_demo;

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


<?php
$cours_background = function_exists('asset_url')
    ? asset_url('assets/img/background_school_material.webp')
    : (function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '') . '/assets/img/background_school_material.webp';
?>
<main class="cours-main">
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
                    echo '<a href="' . site_url('eleve/dashboard') . '" class="nav-btn nav-accueil"><span>🏫</span> Accueil Collège</a>';
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
                <p>Voici tous tes cours pour <strong><?php echo htmlspecialchars($user_level_display); ?></strong></p>
                <?php if ($is_demo && !$is_admin): ?>
                    <p class="text-sm text-amber-600 mt-2 italic">💡 Mode démo : 1 cours. Crée un compte pour tout !</p>
                <?php endif; ?>
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <span class="meta-item"><?php echo $is_bac ? '🎯 BAC' : ($is_lycee ? '🎓 Lycée' : '🏫 Collège'); ?></span>
                    <span id="courseCountTotal" class="meta-item">0 cours</span>
                </div>
            </div>

            <!-- Recherche + filtres matières -->
            <div class="subjects-nav">
                <input type="text" id="searchInput" placeholder="Recherche un cours (ex: fractions, SVT)..." class="subject-nav-btn" />
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
// Mapping des objectifs pédagogiques par matière
const objectivesBySubject = {
    'mathématiques': ['Maîtriser les concepts clés', 'Appliquer les méthodes', 'Vérifier sa compréhension'],
    'français': ['Analyser les textes', 'Enrichir son vocabulaire', 'Développer son esprit critique'],
    'sciences': ['Comprendre les phénomènes', 'Maîtriser les concepts', 'Appliquer à des cas réels'],
    'histoire-géographie': ['Contextualiser les événements', 'Analyser les sources', 'Relier les concepts'],
    'anglais': ['Améliorer la compréhension', 'Pratiquer l\'expression', 'Enrichir le vocabulaire'],
    'philosophie': ['Analyser les arguments', 'Développer la réflexion', 'Construire des perspectives'],
    'physique-chimie': ['Comprendre les lois', 'Maîtriser les calculs', 'Appliquer aux expériences'],
    'svt': ['Comprendre les vivants', 'Analyser les processus', 'Appliquer aux cas réels']
};

// Charger UNIQUEMENT les cours du niveau de l'élève
let allCourses = <?php
// ✅ CHARGEMENT BDD : UNIQUEMENT niveau connecté
$courses = [];

// En mode démo, ajouter quelques cours de démonstration
if ($is_demo && empty($courses)) {
    $courses = [
        [
            'Id' => 1,
            'Title' => 'Les fractions en mathématiques',
            'Description' => 'Comprendre et maîtriser les fractions, opérations et calculs.',
            'subject' => 'Mathématiques',
            'level' => '6ème',
            'icon' => '🔢',
            'duration' => '20 min',
            'progress' => 45,
            'exercises_linked' => ['ex1', 'ex2', 'ex3']
        ],
        [
            'Id' => 2,
            'Title' => 'Histoire ancienne: La Grèce antique',
            'Description' => 'Découvrir la civilisation grecque, Athènes et la démocratie.',
            'subject' => 'Histoire-Géographie',
            'level' => '6ème',
            'icon' => '🏛️',
            'duration' => '25 min',
            'progress' => 20,
            'exercises_linked' => ['ex1', 'ex2']
        ],
        [
            'Id' => 3,
            'Title' => 'L\'eau et les états de la matière',
            'Description' => 'Comprendre les états solide, liquide et gazeux avec l\'eau.',
            'subject' => 'Sciences',
            'level' => '6ème',
            'icon' => '💧',
            'duration' => '15 min',
            'progress' => 0,
            'exercises_linked' => ['ex1', 'ex2', 'ex3', 'ex4']
        ]
    ];
}

// Charger depuis la BDD si disponible
if (!$is_demo && isset($pdo) && function_exists('getCoursesBySubjectAndLevel')) {
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

// Enrichir les cours avec les objectifs après chargement JSON
allCourses = allCourses.map(course => {
    const subjectKey = (course.subject || 'sciences').toLowerCase().replace(/[-\s]/g, '');
    if (!course.objectives || course.objectives.length === 0) {
        // Chercher dans la mapping par correspondance de clé
        for (let key in objectivesBySubject) {
            if (subjectKey.includes(key.replace(/[-\s]/g, ''))) {
                course.objectives = objectivesBySubject[key];
                break;
            }
        }
        // Fallback si pas trouvé
        if (!course.objectives) {
            course.objectives = objectivesBySubject['mathématiques'];
        }
    }
    return course;
});

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
    const searchInput = document.getElementById('searchInput');
    const subjectFilter = document.getElementById('subjectFilter');
    const clearBtn = document.getElementById('clearFilters');
    const resultsAnnounce = document.getElementById('searchResults') || (() => {
        const el = document.createElement('div');
        el.id = 'searchResults';
        el.setAttribute('role', 'status');
        el.setAttribute('aria-live', 'polite');
        el.setAttribute('aria-atomic', 'true');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        return el;
    })();

    function announceResults() {
        const count = document.querySelectorAll('.cours-card').length;
        resultsAnnounce.textContent = `${count} cours trouvé${count > 1 ? 's' : ''}`;
    }

    searchInput.addEventListener('input', () => {
        filterCourses();
        announceResults();
    });
    subjectFilter.addEventListener('change', () => {
        filterCourses();
        announceResults();
    });
    clearBtn.onclick = () => {
        searchInput.value = '';
        subjectFilter.value = '';
        searchInput.focus();
        filterCourses();
        announceResults();
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

// createCourseCard() — Nouvelle version avec objectifs pédagogiques
function createCourseCard(course) {
    // Récupérer les objectifs (max 3)
    const objectives = course.objectives ? course.objectives.slice(0, 3) : ['Maîtriser les concepts clés', 'Appliquer les méthodes', 'Vérifier sa compréhension'];
    const objectivesHTML = objectives.map(obj => `<li>${escapeHtml(obj)}</li>`).join('');

    // Durée et exercices
    const duration = course.duration || '15-30 min';
    const exercisesCount = course.exercises_linked ? course.exercises_linked.length : 3;

    return `
        <div class="cours-card">
            <div class="cours-card-header">
                <div class="cours-card-icon">${course.icon || '📖'}</div>
                <div class="cours-card-title-group">
                    <h3 class="cours-card-header">${escapeHtml(course.Title || course.title || 'Sans titre')}</h3>
                </div>
            </div>

            <div class="cours-card-body">
                <p class="cours-description">${escapeHtml(course.Description || course.description || ('Cours de ' + escapeHtml(course.subject || 'sciences')))}</p>

                <div class="cours-objectives">
                    <div class="cours-objectives-title">Objectifs</div>
                    <ul class="cours-objectives-list">
                        ${objectivesHTML}
                    </ul>
                </div>

                <div class="cours-meta">
                    <span class="meta-item duration">⏱ ${duration}</span>
                    <span class="meta-item exercises">📝 ${exercisesCount} exercices</span>
                </div>
            </div>

            <div class="cours-actions">
                <a href="${sanitizeUrl(course.url)}" class="btn-cours">Commencer</a>
                <a href="${sanitizeUrl(course.url)}" class="btn-exercice">Exercices →</a>
            </div>
        </div>
    `;
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Fonction pour sécuriser les URLs (prévenir XSS javascript:)
function sanitizeUrl(url) {
    if (!url) return '#';
    url = String(url).trim();
    // Vérifier qu'on ne commence pas par javascript: ou data:
    if (/^(javascript|data|vbscript|file):/i.test(url)) {
        return '#';
    }
    // Échapper les quotes
    return url.replace(/"/g, '&quot;');
}

function renderCourses(courses) {
    document.getElementById('allCoursesGrid').innerHTML = courses.map(createCourseCard).join('');
}
</script>

