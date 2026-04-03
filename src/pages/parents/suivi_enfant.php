<?php
/**
 * Page de suivi d'un enfant pour les parents ou administrateurs
 * Affiche la progression, les statistiques et l'activité d'un élève
 *
 * IMPORTANT: Les vérifications critiques doivent se faire AVANT que le router
 * n'inclue cette page. On utilise donc un système de pré-vérification.
 */

// ============================================================================
// SECTION 1 : PRÉ-VÉRIFICATIONS (avant que le router inclue la topbar)
// ============================================================================

// Cette section s'exécute avant l'inclusion de la topbar par le router
// On définit des variables que le router peut vérifier

// Si ces vérifications échouent, on définit une variable pour que le router
// puisse rediriger AVANT d'inclure la topbar

// ...existing code...

// Inclure le helper de redirection sécurisée si absent
if (!function_exists('safe_redirect')) {
    $redirect_helpers = dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
    if (is_file($redirect_helpers)) {
        require_once $redirect_helpers;
    }
}

// Vérifier l'authentification
$is_admin = function_exists('isAdmin') && isAdmin();
$is_authenticated = isset($_SESSION['parent_id']) || $is_admin;

// Si non authentifié, rediriger MAINTENANT (avant la topbar)
if (!$is_authenticated) {
    if (!headers_sent()) {
        header('Location: ' . site_url('login'));
        exit;
    }
}

// Récupérer l'ID enfant de façon sécurisée (GET)

$enfant_id = isset($_GET['id']) ? intval($_GET['id']) : null;
// Si aucun enfant sélectionné, afficher la section des plans parentaux
if (!$enfant_id) {
    $show_plans_parentaux = true;
    // Initialiser variables pour éviter les warnings
    $enfantName = 'Enfant';
    $niveau_scolaire_display = 'Non défini';
    // Récupérer la liste des enfants du parent
    $nb_enfants = 0;
    $liste_enfants = [];
    if (isset($pdo) && isset($_SESSION['parent_id'])) {
        $stmt = $pdo->prepare("SELECT Id, Prenom, Nom, Username FROM users WHERE ParentId = ? AND Role = 'student' ORDER BY Prenom, Nom");
        $stmt->execute([$_SESSION['parent_id']]);
        $liste_enfants = $stmt->fetchAll();
        $nb_enfants = count($liste_enfants);
    }
} else {
    $show_plans_parentaux = false;
}

// Vérifier la connexion BDD
if (!isset($pdo) || !$pdo) {
    // Définir une variable d'erreur pour affichage après la topbar
    $db_error = true;
    $page_title = 'Erreur - MonCoachScolaire';
    $page_class = 'suivi-enfant-page error-page';
} else {
    $db_error = false;

    // ============================================================================
    // SECTION 2 : RÉCUPÉRATION DES DONNÉES (si BDD disponible)
    // ============================================================================

    // Vérifier que l'enfant existe et appartient au parent (ou admin)
    if ($is_admin) {
        $stmt = $pdo->prepare("
            SELECT Id, Username, Email, UserLevel, Prenom as prenom, Nom as nom
            FROM users
            WHERE Id = ? AND Role = 'student'
            LIMIT 1
        ");
        $stmt->execute([$enfant_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT Id, Username, Email, UserLevel, Prenom as prenom, Nom as nom
            FROM users
            WHERE Id = ? AND ParentId = ? AND Role = 'student'
            LIMIT 1
        ");
        $stmt->execute([$enfant_id, $_SESSION['parent_id']]);
    }
    $enfant = $stmt->fetch();

    // Si l'enfant n'existe pas, rediriger
    if (!$enfant) {
        if (!headers_sent()) {
            header('Location: ' . site_url('parents/dashboard_parent'));
            exit;
        }
        // Si headers déjà envoyés, définir une erreur
        $enfant_not_found = true;
    } else {
        $enfant_not_found = false;

        // Récupérer la progression
        $progression = [];
        try {
            $progressStmt = $pdo->prepare("
                SELECT XP, CurrentPosition, UpdatedAt as date
                FROM UserProgress
                WHERE UserId = ?
                ORDER BY UpdatedAt DESC
            ");
            $progressStmt->execute([$enfant_id]);
            $progression = $progressStmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erreur récupération progression: " . $e->getMessage());
        }

        // Récupérer les statistiques d'exercices
        $stats_exercices = [
            'total' => 0,
            'reussis' => 0,
            'en_cours' => 0,
            'taux_reussite' => 0,
        ];
        try {
            $statsStmt = $pdo->prepare("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN Status = 'completed' THEN 1 ELSE 0 END) as reussis,
                    SUM(CASE WHEN Status = 'in_progress' THEN 1 ELSE 0 END) as en_cours
                FROM UserExercises
                WHERE UserId = ?
            ");
            $statsStmt->execute([$enfant_id]);
            $stats = $statsStmt->fetch();
            if ($stats) {
                $stats_exercices['total'] = (int) $stats['total'];
                $stats_exercices['reussis'] = (int) $stats['reussis'];
                $stats_exercices['en_cours'] = (int) $stats['en_cours'];
                if ($stats_exercices['total'] > 0) {
                    $stats_exercices['taux_reussite'] = round(($stats_exercices['reussis'] / $stats_exercices['total']) * 100, 1);
                }
            }
        } catch (Exception $e) {
            error_log("Erreur récupération stats exercices: " . $e->getMessage());
        }

        // Récupérer les activités récentes
        $activites_recentes = [];
        try {
            $activitesStmt = $pdo->prepare("
                SELECT 'exercice' as type, Title as titre, Status as statut, UpdatedAt as date
                FROM UserExercises
                WHERE UserId = ?
                ORDER BY UpdatedAt DESC
                LIMIT 10
            ");
            $activitesStmt->execute([$enfant_id]);
            $activites_recentes = $activitesStmt->fetchAll();
        } catch (Exception $e) {
            error_log("Erreur récupération activités: " . $e->getMessage());
        }

        // Préparer les données pour l'affichage
        $enfantName = trim(($enfant['prenom'] ?? '') . ' ' . ($enfant['nom'] ?? ''));
        if (empty($enfantName)) {
            $enfantName = $enfant['Username'] ?? 'Enfant';
        }

        $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($enfantName) . '&background=1e293b&color=fff&size=128';

        // Calculer les statistiques de progression
        $xp_total = 0;
        $last_activity = 'Aucune activité';
        $current_position = 'Non définie';

        if (!empty($progression)) {
            foreach ($progression as $prog) {
                $xp_total += (int) ($prog['XP'] ?? 0);
            }

            $last_prog = $progression[0];
            if (!empty($last_prog['date'])) {
                try {
                    $date_obj = new DateTime($last_prog['date']);
                    $last_activity = $date_obj->format('d/m/Y à H:i');
                } catch (Exception $e) {
                    $last_activity = htmlspecialchars($last_prog['date']);
                }
            }

            if (!empty($last_prog['CurrentPosition'])) {
                $current_position = htmlspecialchars($last_prog['CurrentPosition']);
            }
        }

        $niveau = floor($xp_total / 100) + 1;
        $xp_pour_niveau_suivant = (($niveau) * 100) - $xp_total;

        // Charger les fonctions de normalisation des niveaux
        if (is_file(dirname(__DIR__, 2) . '/includes/level_normalization.php')) {
            require_once dirname(__DIR__, 2) . '/includes/level_normalization.php';
        }

        $niveau_scolaire_display = function_exists('get_level_display_name')
            ? get_level_display_name($enfant['UserLevel'] ?? '')
            : ($enfant['UserLevel'] ?? 'Non défini');

        // Définir les métadonnées de la page
        $page_title = 'Suivi de ' . $enfantName . ' - MonCoachScolaire';
    }
}

$page_class = 'suivi-enfant-page';
$page_css = 'suivi_enfant.css';

// ============================================================================
// SECTION 3 : AFFICHAGE HTML (après inclusion de la topbar par le router)
// ============================================================================

// Si erreur BDD, afficher le message et arrêter
if (isset($db_error) && $db_error) {
    ?>
    <div class="max-w-3xl mx-auto p-6 mt-8">
        <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
            <h2 class="text-2xl font-bold text-red-700 mb-2">⚠️ Service temporairement indisponible</h2>
            <p class="text-red-600 mb-4">La base de données est actuellement indisponible. Impossible d'afficher la progression.</p>
            <a href="<?php echo site_url('parents/dashboard_parent'); ?>" class="inline-block bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
                ← Retour au tableau de bord
            </a>
        </div>
    </div>
    <?php
    return; // Arrêter l'exécution ici
}

// Si enfant non trouvé, afficher un message

if (isset($enfant_not_found) && $enfant_not_found) {
    $show_plans_parentaux = true;
}

// Affichage normal de la page
?>


<?php if (!empty($show_plans_parentaux)): ?>
        <!-- Barre de navigation haut de page -->
        <nav class="flex flex-wrap gap-3 justify-center mb-8 mt-2">
            <a href="<?php echo site_url('landingpage'); ?>" class="px-5 py-2.5 rounded-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-100 text-sm font-semibold shadow transition">
                🏠 Accueil
            </a>
            <a href="<?php echo site_url('parents/dashboard_parent'); ?>" class="px-5 py-2.5 rounded-full bg-indigo-100 hover:bg-indigo-200 text-indigo-900 border border-indigo-200 text-sm font-semibold shadow transition">
                📊 Dashboard Parent
            </a>
            <!-- Bouton vers parents/parents et texte 'Espace Parent' supprimés -->
            </a>
        </nav>
    <?php $suivi_bg = function_exists('asset_url') ? asset_url('assets/img/background_school_material.webp') : '/assets/img/background_school_material.webp'; ?>
    <main class="min-h-screen bg-cover bg-center bg-no-repeat bg-fixed font-sans">
    <!-- Nav Breadcrumbs -->
    <nav class="max-w-6xl mx-auto mb-8 flex items-center gap-4 text-sm text-gray-600">
        <a href="<?= site_url('parents/dashboard_parent') ?>" class="hover:text-indigo-600 transition">📊 Dashboard</a>
        <span>/</span>
        <span class="font-semibold text-indigo-700">👤 <?= htmlspecialchars($enfantName) ?></span>
    </nav>

    <!-- Hero Enfant -->
    <header class="max-w-5xl mx-auto mb-16 text-center">
        <div class="relative inline-block mb-8">
            <img src="<?= htmlspecialchars($avatar_url) ?>" alt="<?= htmlspecialchars($enfantName) ?>" class="w-32 h-32 rounded-3xl shadow-2xl border-6 border-white ring-8 ring-indigo-200/50 mx-auto object-cover hover:scale-105 transition-transform duration-300">
            <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-gradient-to-br from-emerald-400 to-green-500 rounded-2xl flex items-center justify-center shadow-2xl text-white text-xl font-bold">⭐</div>
        </div>
        <div>
            <h1 class="text-5xl lg:text-6xl font-black bg-gradient-to-r from-rose-500 via-pink-500 to-purple-500 bg-clip-text text-transparent mb-4">
                Suivi <?= htmlspecialchars($enfantName) ?>
            </h1>
            <p class="text-2xl text-gray-600 font-semibold mb-2">Niveau : <span class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-2xl font-bold"><?= htmlspecialchars($niveau_scolaire_display) ?></span></p>
            <?php if (!isset($xp_total)) {
                $xp_total = 0;
            } ?>
            <div class="flex gap-4 justify-center mt-6">
                <div class="text-center">
                    <div class="text-3xl font-black text-emerald-600" title="Points d'expérience accumulés"><?= number_format($xp_total, 0, ',', ' ') ?></div>
                    <div class="text-sm text-gray-600">XP Gagnés</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-black text-purple-600" title="Nombre de badges obtenus">
                        <?php if (isset($stats_exercices['reussis'])): ?>
                            <?= (int) $stats_exercices['reussis'] ?>
                        <?php else: ?>
                            0
                        <?php endif; ?>
                        <span class="text-base text-gray-400">/<?= (int) ($stats_exercices['total'] ?? 0) ?></span>
                    </div>
                    <div class="text-sm text-gray-600">Badges</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-black text-amber-600" title="Taux de réussite sur les exercices">
                        <?= isset($stats_exercices['taux_reussite']) ? $stats_exercices['taux_reussite'] . '%' : '0%' ?>
                    </div>
                    <div class="text-sm text-gray-600">Taux Succès</div>
                </div>
            </div>

            <!-- Feedback positif -->
            <?php if (($stats_exercices['taux_reussite'] ?? 0) >= 80): ?>
                <div class="mt-6 text-lg text-green-700 font-semibold flex items-center justify-center gap-2" aria-live="polite">
                    <span class="text-2xl">🎉</span> Bravo&nbsp;! Progression remarquable cette semaine&nbsp;!
                </div>
            <?php elseif (($stats_exercices['reussis'] ?? 0) > 0): ?>
                <div class="mt-6 text-lg text-blue-700 font-semibold flex items-center justify-center gap-2" aria-live="polite">
                    <span class="text-2xl">👏</span> Continue comme ça, chaque exercice compte&nbsp;!
                </div>
            <?php endif; ?>

            <!-- Objectifs personnalisés -->
            <div class="mt-4 flex flex-col items-center">
                <div class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-2xl font-bold mb-2" aria-label="Objectif personnalisé">
                    <?php
                    if (($stats_exercices['total'] ?? 0) < 5) {
                        echo "Objectif : Terminer 5 exercices cette semaine !";
                    } elseif (($stats_exercices['taux_reussite'] ?? 0) < 60) {
                        echo "Objectif : Atteindre 60% de réussite sur les exercices.";
                    } else {
                        echo "Objectif : Gagner un nouveau badge cette semaine !";
                    }
?>
                </div>
            </div>
        </div>
    </header>

    <?php if ($nb_enfants > 0): ?>
        <!-- Intro Associés -->
        <div class="max-w-4xl mx-auto mb-16 mcs-card-bg rounded-3xl shadow-2xl p-8 border border-white/50 text-center">
            <div class="inline-flex items-center gap-3 bg-gradient-to-r from-yellow-400 to-amber-500 text-white px-6 py-3 rounded-2xl font-bold text-lg shadow-xl mb-6">
                <span class="text-2xl">👨‍👩‍👧‍👦</span> Autres enfants (<?= $nb_enfants ?>)
            </div>
            <ul class="flex flex-wrap gap-4 justify-center">
                <?php foreach ($liste_enfants as $enfant): ?>
                    <li class="group">
                        <a href="?id=<?= $enfant['user_id'] ?? '' ?>" class="w-20 h-20 bg-gradient-to-br from-gray-200 to-gray-300 rounded-2xl flex items-center justify-center text-xl shadow-lg hover:shadow-2xl hover:scale-110 transition-all duration-300 font-semibold hover:bg-indigo-400 hover:text-white">
                            <?= substr(htmlspecialchars($enfant['Prenom'] ?? ''), 0, 1) . substr(htmlspecialchars($enfant['Nom'] ?? ''), 0, 1) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Plans Personnalisés (Grille Interactive) -->
    <section class="max-w-6xl mx-auto">
        <h2 class="text-4xl font-black mb-12 text-center bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
            🚀 Plans d'Accompagnement Actifs
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
            <!-- Plan 1: Dashboard Interactif -->
            <div class="group mcs-card-bg rounded-3xl shadow-2xl p-10 border border-white/50 hover:shadow-3xl hover:-translate-y-4 transition-all duration-500 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-2xl group-hover:scale-110 transition-transform duration-300">
                        <span class="text-2xl">📊</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-6 text-center">Dashboard Interactif</h3>
                    <p class="text-lg text-gray-700 mb-8 leading-relaxed">Badges live, objectifs dynamiques, stats en temps réel pour booster la motivation quotidienne.</p>
                    <div class="w-full rounded-full h-4 mb-6 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-4 rounded-full shadow-lg transition-all duration-1000 animate-pulse"></div>
                    </div>
                    <p class="text-sm text-blue-600 font-semibold mb-8 text-center">Actif • +247 XP ce mois</p>
                    <button class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-2xl hover:shadow-3xl py-5 px-8 rounded-2xl font-bold text-xl text-white transition-all duration-300 transform hover:-translate-y-1">
                        ⚙️ Personnaliser
                    </button>
                </div>
            </div>

            <!-- Plan 2: Grille Niveau/Matière -->
            <div class="group mcs-card-bg rounded-3xl shadow-2xl p-10 border border-white/50 hover:shadow-3xl hover:-translate-y-4 transition-all duration-500 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-500/5 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-2xl group-hover:scale-110 transition-transform duration-300">
                        <span class="text-2xl">🟩</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-6 text-center">Grille Niveaux</h3>
                    <p class="text-lg text-gray-700 mb-8 leading-relaxed">Sessions guidées par niveau/matière, exercices ciblés pour combler les lacunes précises.</p>
                    <div class="w-full rounded-full h-4 mb-6 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-4 rounded-full shadow-lg transition-all duration-1000"></div>
                    </div>
                    <p class="text-sm text-green-600 font-semibold mb-8 text-center">94% complété</p>
                    <button class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 shadow-2xl hover:shadow-3xl py-5 px-8 rounded-2xl font-bold text-xl text-white transition-all duration-300 transform hover:-translate-y-1">
                        🎯 Lancer Session
                    </button>
                </div>
            </div>

            <!-- Plan 3: Plan Action -->
            <div class="group mcs-card-bg rounded-3xl shadow-2xl p-10 border border-white/50 hover:shadow-3xl hover:-translate-y-4 transition-all duration-500 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-500/5 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-2xl group-hover:scale-110 transition-transform duration-300">
                        <span class="text-2xl">🎯</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-6 text-center">Plan Hebdo</h3>
                    <p class="text-lg text-gray-700 mb-8 leading-relaxed">Objectifs smart, routines adaptées, rappels automatisés pour un rythme gagnant.</p>
                    <div class="flex gap-2 mb-6">
                        <div class="flex-1 rounded-xl h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-3 rounded-xl shadow-lg"></div>
                        </div>
                        <span class="text-sm font-bold text-amber-700 min-w-[40px]">65%</span>
                    </div>
                    <button class="w-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-2xl hover:shadow-3xl py-5 px-8 rounded-2xl font-bold text-xl text-white transition-all duration-300 transform hover:-translate-y-1">
                        📅 Routine
                    </button>
                </div>
            </div>

            <!-- Plan 4: Accompagnement -->
            <div class="group mcs-card-bg rounded-3xl shadow-2xl p-10 border border-white/50 hover:shadow-3xl hover:-translate-y-4 transition-all duration-500 overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-violet-500/5 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-400 to-violet-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-2xl group-hover:scale-110 transition-transform duration-300">
                        <span class="text-2xl">🤝</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-6 text-center">Messages</h3>
                    <p class="text-lg text-gray-700 mb-8 leading-relaxed">Motivation live, feedbacks enseignants, challenges partagés pour impliquer à fond.</p>
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-sm">
                            <span>Nouveaux</span><span class="font-bold text-purple-600">3</span>
                        </div>
                        <div class="w-full rounded-full h-2">
                            <div class="bg-gradient-to-r from-purple-500 to-violet-500 h-2 rounded-full"></div>
                        </div>
                    </div>
                    <button class="w-full bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 shadow-2xl hover:shadow-3xl py-5 px-8 rounded-2xl font-bold text-xl text-white transition-all duration-300 transform hover:-translate-y-1">
                        💬 Écrire
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="max-w-6xl mx-auto mt-20 pt-16 border-t border-white/50">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Bouton vers parents/parents supprimé -->
                <span class="text-4xl block mb-4 group-hover:scale-110 transition-transform">👨‍👩‍👧‍👦</span>
                <h4 class="text-2xl font-bold mb-2">Multi-Enfants</h4>
                <p>Retour pilotage global</p>
            </a>
            <div class="p-8 rounded-3xl mcs-card-bg shadow-2xl border border-white/50 text-center hover:shadow-3xl transition-all duration-300">
                <span class="text-4xl block mb-4">📈</span>
                <h4 class="text-2xl font-bold mb-2 text-gray-800">Rapport PDF</h4>
                <p class="text-gray-600">Télécharger bilan complet</p>
            </div>
            <div class="p-8 rounded-3xl mcs-card-bg shadow-2xl border border-white/50 text-center hover:shadow-3xl transition-all duration-300">
                <span class="text-4xl block mb-4">🎯</span>
                <h4 class="text-2xl font-bold mb-2 text-gray-800">Objectifs</h4>
                <p class="text-gray-600">Définir nouveaux challenges</p>
            </div>
        </div>
    </section>
</main>

<?php endif; ?>

