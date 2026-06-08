<?php
/**
 * dashboard_admin.php - MonCoachScolaire (FINAL PROD 2026)
 * Structure register.php + vérification admin
 */

// ========== 1. PROTECTION GLOBALE ==========
defined('SKIP_SESSION_CHECK') || define('SKIP_SESSION_CHECK', true);
defined('ADMIN_PAGE_ACTIVE') || define('ADMIN_PAGE_ACTIVE', true);

// ========== 2. SESSION SÉCURISÉE ==========
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('dashboard_admin.php: impossible de démarrer session - headers envoyés');
        }
    }
}

// ========== 3. VARIABLES PAGE ==========
$page_title = 'Dashboard Administrateur - MonCoachScolaire';
$page_css = 'dashboard-admin.css';

// ========== 4. CHARGER CONFIG ==========

// ========== 4. CHARGER CONFIG ==========
if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}

// ========== 5. CHARGER site_boot UNIQUEMENT si nécessaire ==========
if (!function_exists('site_url')) {
    $siteBoot = dirname(__DIR__, 2) . '/config/site_boot.php';
    if (is_file($siteBoot)) {
        require_once $siteBoot;
    }
}

require_once dirname(__DIR__, 2) . '/database/connection.php';
require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
require_once dirname(__DIR__, 2) . '/includes/login_security.php';

// ========== 6. VÉRIFICATION ADMIN (AVANT TOUT HTML !) ==========
requireAdmin();

// Récupérer infos admin
$adminInfo = getAdminInfo();
$adminName = $adminInfo['Username'] ?? 'Administrateur';
$csrfToken = function_exists('generateCSRFToken') ? generateCSRFToken() : null;

// ========== 7. HTML COMPLET (autonome) ==========
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php
    $root = rtrim($baseUrl ?? '', '/');
if (!$root) {
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    if ($scriptDir && $scriptDir !== '.' && $scriptDir !== '/') {
        $root = $scriptDir;
    } else {
        $root = '';
    }
}
if (function_exists('asset_url')) {
    $cssStyle = asset_url('assets/css/tailwind.css');
    $cssPage = asset_url('assets/css/pages/' . $page_css);
} else {
    $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
    $cssStyle = $assetBase . '/assets/css/tailwind.css';
    $cssPage = $assetBase . '/assets/css/pages/' . $page_css;
}
?>
    <link rel="stylesheet" href="<?php
        if (function_exists('asset_url')) {
            echo asset_url('assets/css/style.css');
        } else {
            $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
            echo htmlspecialchars($assetBase . '/assets/css/style.css', ENT_QUOTES);
        }
    ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssStyle, ENT_QUOTES); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPage, ENT_QUOTES); ?>">
    <?php
if (function_exists('detectBaseUrl')) {
    $jsBaseUrl = detectBaseUrl();
} else {
    $jsBaseUrl = isset($baseUrl) ? $baseUrl : '';
}
echo "<script>window.baseUrl = " . json_encode($jsBaseUrl, JSON_UNESCAPED_SLASHES) . ";</script>\n";
echo "<script>window.csrfToken = " . json_encode($csrfToken) . ";</script>\n";
echo "<script>window.currentUserId = " . json_encode($_SESSION['user_id'] ?? 0) . ";</script>\n";
?>
</head>

<body class="app-bg admin-dashboard" data-user-id="<?php echo (int) ($_SESSION['user_id'] ?? 0); ?>">

<?php
// Afficher la topbar sur le dashboard admin (forcer l'affichage même si SKIP_TOPBAR global)
$hide_topbar = false;
if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
    include dirname(__DIR__, 2) . '/includes/topbar.php';
}
?>

<main class="max-w-7xl mx-auto my-8 p-6 rounded-2xl shadow-lg">
    <header class="mb-8 border-b pb-4">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Dashboard Administrateur</h1>
        <p class="text-slate-600">Bienvenue <?php echo htmlspecialchars($adminName, ENT_QUOTES); ?>.</p>
    </header>

    <!-- Modal Création/Édition Utilisateur -->
    <div id="user-modal" class="modal fixed top-0 left-0 w-full h-full flex items-center justify-center bg-black bg-opacity-40 z-50" role="dialog" aria-modal="true" aria-labelledby="user-modal-title" aria-hidden="true">
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-xl">
            <div class="flex justify-between items-center mb-4">
                <h3 id="user-modal-title" class="text-xl font-semibold">Nouvel Utilisateur</h3>
                <button class="text-gray-500 hover:text-gray-700 text-2xl font-bold" onclick="closeUserModal()">&times;</button>
            </div>
            <form id="user-form" class="space-y-4">
                <input type="hidden" id="user-id" name="userId">
                <div><label class="block mb-1 font-medium" for="user-username">Nom d'utilisateur *</label><input type="text" id="user-username" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400" /></div>
                <div><label class="block mb-1 font-medium" for="user-email">Email *</label><input type="email" id="user-email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400" /></div>
                <div><label class="block mb-1 font-medium" for="user-password">Mot de passe *</label><input type="password" id="user-password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400" /></div>
                <div class="flex gap-4">
                    <div class="flex-1"><label class="block mb-1 font-medium" for="user-role">Rôle *</label><select id="user-role" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400"><option value="student">Élève</option><option value="parent">Parent</option><option value="admin">Administrateur</option></select></div>
                    <div class="flex-1" id="user-level-group"><label class="block mb-1 font-medium" for="user-level">Niveau</label><select id="user-level" name="level" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400"><option value="6eme">6ème</option><option value="5eme">5ème</option><option value="4eme">4ème</option><option value="3eme">3ème</option><option value="2nde">Seconde</option><option value="1ere">Première</option><option value="terminale">Terminale</option></select></div>
                </div>
                <div class="flex gap-2 mt-4 justify-end">
                    <button type="button" class="bg-gray-400 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-500 transition" onclick="closeUserModal()">Annuler</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg shadow hover:bg-green-700 transition">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Navigation Admin -->
    <nav class="admin-nav">
        <button class="admin-nav-item active" data-section="overview">
            <span class="nav-icon">📊</span>
            Vue d'ensemble
        </button>
        <button class="admin-nav-item" data-section="exercises">
            <span class="nav-icon">📚</span>
            Gestion des Exercices
        </button>
        <button class="admin-nav-item" data-section="quality">
            <span class="nav-icon">🛡️</span>
            Qualité exercices
        </button>
        <button class="admin-nav-item" data-section="users">
            <span class="nav-icon">👥</span>
            Utilisateurs
        </button>
        <button class="admin-nav-item" data-section="logs">
            <span class="nav-icon">📝</span>
            Logs
        </button>
        <button class="admin-nav-item" data-section="debug">
            <span class="nav-icon">🔧</span>
            Debug
        </button>
        <button class="admin-nav-item" data-section="parents">
            <span class="nav-icon">👨‍👩‍👧‍👦</span>
            Parents-Élèves
        </button>
        <button class="admin-nav-item" data-section="export">
            <span class="nav-icon">📤</span>
            Export avancé
        </button>
        <button class="admin-nav-item" data-section="system">
            <span class="nav-icon">💻</span>
            Système
        </button>
    </nav>

    <!-- Contenu Principal -->
    <div class="admin-content">

        <!-- Section: Gestion des Exercices (NOUVEAU) -->
        <?php
        $exSection = realpath(dirname(__FILE__) . '/dashboard_admin_gde.php');
if ($exSection && is_file($exSection)) {
    include_once $exSection;
} else {
    echo '<div class="admin-section"><div class="alert-box alert-danger"><strong>Section exercices introuvable :</strong> dashboard_admin_gde.php</div></div>';
}
?>

        <!-- Section: Export avancé -->
        <section id="section-export" class="admin-section">
            <div class="section-header">
                <h2>Export avancé des données</h2>
                <div class="section-actions">
                    <select id="export-type" class="admin-select">
                        <option value="users">Utilisateurs</option>
                        <option value="exercices">Exercices</option>
                        <option value="progression">Progression</option>
                        <option value="logs">Logs</option>
                    </select>
                    <select id="export-format" class="admin-select">
                        <option value="csv">CSV</option>
                        <option value="json">JSON</option>
                        <option value="pdf">PDF</option>
                    </select>
                    <button class="btn-admin-primary" id="btn-export-download">📤 Télécharger</button>
                </div>
            </div>
            <div class="export-info">
                <p>Sélectionnez le type de données et le format souhaité pour exporter les informations administratives.</p>
                <div id="export-status"></div>
            </div>
        </section>

        <!-- Section: Vue d'ensemble -->
        <section id="section-overview" class="admin-section active">
            <div class="section-header">
                <h2>Vue d'ensemble</h2>
                <button class="btn-refresh" onclick="refreshStats()">
                    <span>🔄</span> Actualiser
                </button>
            </div>

            <!-- Cartes de statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-users-total">-</div>
                        <div class="stat-label">Utilisateurs Total</div>
                        <div class="stat-detail">
                            <span id="stat-users-students">-</span> élèves •
                            <span id="stat-users-admins">-</span> admins
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-new-users-7d">-</div>
                        <div class="stat-label">Nouveaux (7j)</div>
                        <div class="stat-detail">
                            <span id="stat-new-users-30d">-</span> sur 30 jours
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-total-xp">-</div>
                        <div class="stat-label">XP Total</div>
                        <div class="stat-detail">
                            Moyenne: <span id="stat-avg-xp">-</span> XP
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-exercises-total">-</div>
                        <div class="stat-label">Réponses Exercices</div>
                        <div class="stat-detail">
                            Score moyen: <span id="stat-avg-score">-</span>%
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-logs-24h">-</div>
                        <div class="stat-label">Actions Admin (24h)</div>
                        <div class="stat-detail">
                            Total: <span id="stat-logs-total">-</span> actions
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💻</div>
                    <div class="stat-content">
                        <div class="stat-value" id="stat-memory">-</div>
                        <div class="stat-label">Mémoire PHP</div>
                        <div class="stat-detail">
                            Pic: <span id="stat-memory-peak">-</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🕒</div>
                    <div class="stat-content">
                        <div class="stat-label">Activité Récente</div>
                        <div class="stat-detail" id="recent-activity">
                            <div class="activity-loading">Chargement...</div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💡</div>
                    <div class="stat-content">
                        <div class="stat-label">État du Système</div>
                        <div class="stat-detail" id="system-status">
                            <div class="status-loading">Vérification...</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Qualité des exercices -->
        <section id="section-quality" class="admin-section">
            <div class="section-header">
                <h2>Qualité des exercices (live)</h2>
                <div class="section-actions">
                    <select id="quality-filter-level" class="admin-select">
                        <option value="">Tous les niveaux</option>
                    </select>
                    <select id="quality-filter-subject" class="admin-select">
                        <option value="">Toutes les matières</option>
                    </select>
                    <button class="btn-admin-secondary" id="quality-export-csv">📤 Export CSV</button>
                    <button class="btn-admin-secondary" id="quality-export-json">📤 Export JSON</button>
                    <button class="btn-refresh" id="quality-refresh">
                        <span>🔄</span> Actualiser
                    </button>
                </div>
            </div>

            <div class="quality-summary">
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-value" id="quality-total-active">-</div>
                        <div class="stat-label">Actifs</div>
                        <div class="stat-detail">Exercices actifs filtrés</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-content">
                        <div class="stat-value" id="quality-total-short">-</div>
                        <div class="stat-label">Réponses courtes</div>
                        <div class="stat-detail">&lt; 30 caractères</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">❌</div>
                    <div class="stat-content">
                        <div class="stat-value" id="quality-total-empty">-</div>
                        <div class="stat-label">Réponses vides</div>
                        <div class="stat-detail">Content/Answer manquants</div>
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="admin-table" id="quality-table">
                    <thead>
                        <tr>
                            <th>Niveau</th>
                            <th>Matière</th>
                            <th>Total</th>
                            <th>Actifs</th>
                            <th>Courtes (&lt;30)</th>
                            <th>Vides</th>
                            <th>Réponse (min / moy / max)</th>
                            <th>Cours liés</th>
                            <th>Alertes</th>
                        </tr>
                    </thead>
                    <tbody id="quality-table-body">
                        <tr><td colspan="9" class="text-muted">Chargement...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section: Gestion Utilisateurs -->
        <section id="section-users" class="admin-section">
            <div class="section-header">
                <h2>Gestion des Utilisateurs</h2>
                <div class="section-actions">
                    <input type="text" id="user-search" class="input-search" placeholder="Rechercher un utilisateur...">
                    <select id="user-role-filter" class="select-filter">
                        <option value="">Tous les rôles</option>
                        <option value="student">Élèves</option>
                        <option value="parent">Parents</option>
                        <option value="admin">Administrateurs</option>
                    </select>
                    <button class="btn-admin-primary" onclick="showCreateUserModal()" type="button">➕ Nouvel Utilisateur</button>
                </div>
            </div>

            <div class="users-table-container">
                <table class="admin-table" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Niveau</th>
                            <th>XP Total</th>
                            <th>Inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <tr>
                            <td colspan="8" class="loading-cell">Chargement...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination" id="users-pagination"></div>
        </section>

        <!-- Section: Logs -->
        <section id="section-logs" class="admin-section">
            <div class="section-header">
                <h2>Logs Système</h2>
                <div class="section-actions">
                    <select id="log-type-filter" class="select-filter">
                        <option value="all">Tous les logs</option>
                        <option value="admin">Actions Admin</option>
                        <option value="error">Erreurs</option>
                        <option value="system">Système</option>
                    </select>
                    <button class="btn-refresh" onclick="refreshLogs()">
                        <span>🔄</span> Actualiser
                    </button>
                </div>
            </div>

            <div class="logs-container" id="logs-container">
                <div class="loading-message">Chargement des logs...</div>
            </div>
        </section>

        <!-- Section: Debug -->
        <section id="section-debug" class="admin-section">
            <div class="section-header">
                <h2>Outils de Debug</h2>
            </div>

            <div class="debug-grid">
                <div class="debug-card">
                    <h3>Informations Système</h3>
                    <button class="btn-admin-secondary" onclick="event.preventDefault(); event.stopPropagation(); loadDebugInfo('info'); return false;">
                        Charger Info Système
                    </button>
                    <pre id="debug-info" class="debug-output"></pre>
                </div>

                <div class="debug-card">
                    <h3>Variables d'Environnement</h3>
                    <button class="btn-admin-secondary" onclick="event.preventDefault(); event.stopPropagation(); loadDebugInfo('env'); return false;">
                        Charger Variables ENV
                    </button>
                    <pre id="debug-env" class="debug-output"></pre>
                </div>

                <div class="debug-card">
                    <h3>État Base de Données</h3>
                    <button class="btn-admin-secondary" onclick="event.preventDefault(); event.stopPropagation(); loadDebugInfo('db'); return false;">
                        Charger Info DB
                    </button>
                    <pre id="debug-db" class="debug-output"></pre>
                </div>

                <div class="debug-card">
                    <h3>Session</h3>
                    <button class="btn-admin-secondary" onclick="event.preventDefault(); event.stopPropagation(); loadDebugInfo('session'); return false;">
                        Charger Info Session
                    </button>
                    <pre id="debug-session" class="debug-output"></pre>
                </div>

                <div class="debug-card">
                    <h3>Actions</h3>
                    <button class="btn-admin-secondary" onclick="clearCache()">
                        Nettoyer Cache
                    </button>
                    <div id="debug-action-result" class="debug-result"></div>
                </div>

                <div class="debug-card">
                    <h3>Email / SMTP</h3>
                    <form id="debug-email-form" onsubmit="event.preventDefault(); sendDebugEmail(); return false;">
                        <label for="debug-email-to">Envoyer un email de test à :</label>
                        <input type="email" id="debug-email-to" name="to" placeholder="destinataire@example.com" required>
                        <button type="submit" class="btn-admin-secondary">Envoyer Email Test</button>
                    </form>
                    <div>
                        <button class="btn-admin-secondary" onclick="loadDebugInfo('smtp'); return false;">Afficher config SMTP</button>
                    </div>
                    <pre id="debug-smtp" class="debug-output"></pre>
                    <div>
                        <button class="btn-admin-secondary" onclick="loadDebugInfo('smtp_log'); return false;">Afficher dernier log PHPMailer</button>
                    </div>
                    <pre id="debug-smtp-log" class="debug-output"></pre>
                </div>
            </div>
        </section>

        <!-- Section: Gestion Parents-Élèves -->
        <section id="section-parents" class="admin-section">
            <div class="section-header">
                <h2>Gestion Parents-Élèves</h2>
                <div class="section-actions">
                    <button class="btn-refresh" onclick="refreshParentsData()">
                        <span>🔄</span> Actualiser
                    </button>
                </div>
            </div>

            <div class="parents-overview">
                <div class="overview-stats">
                    <div class="stat-mini-card">
                        <div class="stat-mini-icon">👨‍👩‍👧‍👦</div>
                        <div class="stat-mini-content">
                            <div class="stat-mini-value" id="stat-parents-total">-</div>
                            <div class="stat-mini-label">Parents</div>
                        </div>
                    </div>
                    <div class="stat-mini-card">
                        <div class="stat-mini-icon">👥</div>
                        <div class="stat-mini-content">
                            <div class="stat-mini-value" id="stat-students-total">-</div>
                            <div class="stat-mini-label">Élèves</div>
                        </div>
                    </div>
                    <div class="stat-mini-card">
                        <div class="stat-mini-icon">🔗</div>
                        <div class="stat-mini-content">
                            <div class="stat-mini-value" id="stat-attachments-total">-</div>
                            <div class="stat-mini-label">Rattachements</div>
                        </div>
                    </div>
                    <div class="stat-mini-card">
                        <div class="stat-mini-icon">🔓</div>
                        <div class="stat-mini-content">
                            <div class="stat-mini-value" id="stat-unattached-total">-</div>
                            <div class="stat-mini-label">Élèves non rattachés</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="parents-tabs">
                <button class="tab-button active" data-tab="parents-list">
                    <span>👨‍👩‍👧‍👦</span> Liste des Parents
                </button>
                <button class="tab-button" data-tab="students-list">
                    <span>👥</span> Liste des Élèves
                </button>
                <button class="tab-button" data-tab="attach-student">
                    <span>🔗</span> Rattacher un Élève
                </button>
            </div>

            <div class="parents-tab-content">
                <div id="tab-parents-list" class="tab-panel active">
                    <div class="parents-list-container">
                        <div id="parents-list" class="parents-list">
                            <div class="loading-message">Chargement des parents...</div>
                        </div>
                    </div>
                </div>

                <div id="tab-students-list" class="tab-panel">
                    <div class="students-list-container">
                        <div class="filter-bar">
                            <select id="student-filter-parent" class="select-filter">
                                <option value="">Tous les élèves</option>
                                <option value="unattached">Élèves non rattachés</option>
                            </select>
                        </div>
                        <div id="students-list" class="students-list">
                            <div class="loading-message">Chargement des élèves...</div>
                        </div>
                    </div>
                </div>

                <div id="tab-attach-student" class="tab-panel">
                    <div class="attach-form-container">
                        <form id="attach-student-form" class="attach-form">
                            <div class="form-group">
                                <label for="attach-student-select">Sélectionner un élève *</label>
                                <select id="attach-student-select" name="student_id" required>
                                    <option value="">-- Choisir un élève --</option>
                                </select>
                                <small>Seuls les élèves non rattachés sont affichés</small>
                            </div>

                            <div class="form-group">
                                <label for="attach-parent-select">Sélectionner un parent *</label>
                                <select id="attach-parent-select" name="parent_id" required>
                                    <option value="">-- Choisir un parent --</option>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-admin-primary">
                                    <span>🔗</span> Rattacher
                                </button>
                                <button type="button" class="btn-admin-secondary" onclick="resetAttachForm()">
                                    Réinitialiser
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Système -->
        <section id="section-system" class="admin-section">
            <div class="section-header">
                <h2>Configuration Système</h2>
            </div>

            <!-- Grille flexible pour les cartes de configuration -->
            <div class="system-config">

                <!-- Carte 1: Mode Debug -->
                <div class="config-card">
                    <h3>🐛 Mode Debug</h3>
                    <div class="config-item">
                        <label>
                            <input type="checkbox" id="debug-mode-toggle">
                            Activer le mode debug
                        </label>
                        <p class="config-description">
                            Affiche les erreurs PHP et les informations de debug
                        </p>
                    </div>
                </div>

                <!-- Carte 2: Maintenance -->
                <div class="config-card">
                    <h3>🔧 Maintenance</h3>
                    <div class="config-item">
                        <label>
                            <input type="checkbox" id="maintenance-mode-toggle">
                            Mode maintenance
                        </label>
                        <p class="config-description">
                            Désactive l'accès public au site (admin uniquement)
                        </p>
                    </div>
                </div>

                <!-- Carte 3: Notifications -->
                <div class="config-card">
                    <h3>🔔 Notifications</h3>
                    <div class="config-item">
                        <label>
                            <input type="checkbox" id="notifications-toggle" checked>
                            Notifications en temps réel
                        </label>
                        <p class="config-description">
                            Recevoir des notifications pour les actions importantes
                        </p>
                    </div>
                </div>

                <!-- Carte 4: Intégration API tierces -->
                <div class="config-card">
                    <h3>🔗 Intégration API tierces</h3>
                    <p class="config-description">
                        Configurer et tester les connecteurs externes (webhooks, API).
                    </p>
                    <div class="module-actions">
                        <button class="btn-admin-secondary" id="btn-api-integration">Synchroniser</button>
                        <a class="btn-admin-secondary btn-outline" href="index.php?page=admin/system/integrations">Aller à la page</a>
                    </div>
                </div>

                <!-- Carte 5: Historique actions admin -->
                <div class="config-card">
                    <h3>📜 Historique actions admin</h3>
                    <p class="config-description">
                        Consulter l'audit trail et exporter les actions administratives.
                    </p>
                    <div class="module-actions">
                        <button class="btn-admin-secondary" id="btn-audit-history">Actualiser</button>
                        <a class="btn-admin-secondary btn-outline" href="index.php?page=admin/system/audit">Aller à la page</a>
                    </div>
                </div>

                <!-- Carte 6: Analyse qualité exercices -->
                <div class="config-card">
                    <h3>🧪 Analyse qualité exercices</h3>
                    <p class="config-description">
                        Tableau de bord qualité, tendances et export des problèmes détectés.
                    </p>
                    <div class="module-actions">
                        <button class="btn-admin-secondary" id="btn-quality-analyze">Analyser</button>
                        <a class="btn-admin-secondary btn-outline" href="index.php?page=admin/system/exercise_quality">Aller à la page</a>
                    </div>
                </div>

                <!-- Carte 7: Ressources pédagogiques -->
                <div class="config-card">
                    <h3>📚 Ressources pédagogiques</h3>
                    <p class="config-description">
                        Importer, valider et attacher des ressources pédagogiques aux exercices.
                    </p>
                    <div class="module-actions">
                        <button class="btn-admin-secondary" id="btn-resources-import">Importer</button>
                        <button class="btn-admin-secondary" id="btn-resources-export">Exporter</button>
                        <a class="btn-admin-secondary btn-outline" href="index.php?page=admin/system/resources">Aller à la page</a>
                    </div>
                </div>

                <!-- Carte 8: Monitoring sécurité -->
                <div class="config-card">
                    <h3>🔒 Monitoring sécurité</h3>
                    <p class="config-description">
                        Alertes, tentatives de connexion et statut de sécurité.
                    </p>
                    <div class="module-actions">
                        <button class="btn-admin-secondary" id="btn-security-refresh">Actualiser</button>
                        <a class="btn-admin-secondary btn-outline" href="index.php?page=admin/system/security">Aller à la page</a>
                    </div>
                </div>

                <!-- Carte 9: Reporting personnalisé -->
                <div class="config-card">
                    <h3>📊 Reporting personnalisé</h3>
                    <p class="config-description">
                        Composer et planifier des rapports exportables (CSV / PDF).
                    </p>
                    <div class="module-actions">
                        <button class="btn-admin-secondary" id="btn-report-generate">Générer rapport</button>
                        <a class="btn-admin-secondary btn-outline" href="index.php?page=admin/system/reporting">Aller à la page</a>
                    </div>
                </div>
        </section>
    </div>
</main>

<!-- Scripts -->
<script>
// Fonctions modales
function showCreateUserModal() {
    var modal = document.getElementById('user-modal');
    if (modal) {
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
    }
    document.body.classList.add('modal-open');
    document.getElementById('user-form').reset();
    toggleLevelSelect();
    // Sauvegarder le bouton actif pour y renvoyer le focus
    modalTrigger = document.activeElement;
    // Donner le focus au premier champ
    document.getElementById('user-username').focus();
}

// Fermer le modal à la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var modal = document.getElementById('user-modal');
        if (modal && modal.classList.contains('active')) {
            closeUserModal();
        }
    }
});

// Référence au bouton qui a ouvert le modal (pour y renvoyer le focus)
var modalTrigger = null;

function closeUserModal() {
    var modal = document.getElementById('user-modal');
    if (modal) {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
    }
    document.body.classList.remove('modal-open');
    // Renvoyer le focus au bouton déclencheur
    if (modalTrigger) {
        modalTrigger.focus();
        modalTrigger = null;
    }
}

function toggleLevelSelect() {
    var role = document.getElementById('user-role').value;
    var levelGroup = document.getElementById('user-level-group');
    if (role === 'student') {
        levelGroup.style.display = '';
        document.getElementById('user-level').disabled = false;
    } else {
        levelGroup.style.display = 'none';
        document.getElementById('user-level').disabled = true;
    }
}

// Event listeners
document.getElementById('user-role').addEventListener('change', toggleLevelSelect);

document.getElementById('user-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);

    try {
        let apiUrl = (window.baseUrl ? window.baseUrl.replace(/\/$/, '') : '') + '/api/admin/admin_create_user';
        console.log('[ADMIN] Soumission formulaire utilisateur, URL API :', apiUrl);

        data.append('csrf_token', window.csrfToken || '');
        const resp = await fetch(apiUrl, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { 'X-CSRF-Token': window.csrfToken || '' }
        });

        if (!resp.ok) {
            let msg = 'Erreur HTTP ' + resp.status;
            try {
                const errJson = await resp.json();
                msg = errJson.message || msg;
            } catch {}
            alert(msg);
            return;
        }

        const result = await resp.json();
        if (result.success) {
            alert('Utilisateur créé avec succès.');
            closeUserModal();
            window.location.reload();
        } else {
            alert(result.message || 'Erreur lors de la création.');
        }
    } catch (err) {
        alert('Erreur réseau ou serveur : ' + (err && err.message ? err.message : err));
    }
});

console.log('🔧 window.baseUrl défini:', window.baseUrl);
</script>

<script>
// Navigation admin : affiche la section correspondante au clic sur le bouton
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.admin-nav-item[data-section]');
    const sections = document.querySelectorAll('.admin-section');
    navItems.forEach(btn => {
        btn.addEventListener('click', function() {
            navItems.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            sections.forEach(sec => sec.classList.remove('active'));
            const sectionId = 'section-' + btn.getAttribute('data-section');
            const target = document.getElementById(sectionId);
            if (target) target.classList.add('active');
        });
    });
    if (!document.querySelector('.admin-section.active') && sections.length) {
        sections[0].classList.add('active');
    }

    // Logique d'action pour chaque bouton système (cards)
    document.getElementById('btn-api-integration')?.addEventListener('click', function() {
        // Action synchronisation API
        document.getElementById('api-status').innerHTML = 'Synchronisation en cours...';
        fetch(typeof apiUrl === 'function' ? apiUrl('log_action') : '?page=api/admin/log_action', { method: 'POST', headers: {'Content-Type':'application/json', 'X-CSRF-Token': window.csrfToken || ''}, body: JSON.stringify({action:'api_sync', details:'Synchronisation API', resource:'integrations', csrf_token: window.csrfToken || ''}) })
            .then(() => { document.getElementById('api-status').innerHTML = 'Synchronisation terminée.'; })
            .catch(() => { document.getElementById('api-status').innerHTML = 'Erreur lors de la synchronisation.'; });
    });
    document.getElementById('btn-audit-history')?.addEventListener('click', function() {
        // Action actualiser audit
        if (typeof loadAdminAudit === 'function') loadAdminAudit();
    });
    document.getElementById('btn-notifications')?.addEventListener('click', function() {
        // Action actualiser notifications
        if (typeof loadNotifications === 'function') loadNotifications();
    });
    document.getElementById('btn-quality-analyze')?.addEventListener('click', function() {
        // Action analyser qualité exercices
        if (typeof analyzeQuality === 'function') analyzeQuality();
    });
    document.getElementById('btn-resources-import')?.addEventListener('click', function() {
        // Action importer ressources
        if (typeof importResources === 'function') importResources();
    });
    document.getElementById('btn-resources-export')?.addEventListener('click', function() {
        // Action exporter ressources
        if (typeof exportResources === 'function') exportResources();
    });
    document.getElementById('btn-security-refresh')?.addEventListener('click', function() {
        // Action actualiser sécurité
        if (typeof refreshSecurity === 'function') refreshSecurity();
    });
    document.getElementById('btn-report-generate')?.addEventListener('click', function() {
        // Action générer rapport
        if (typeof generateReport === 'function') generateReport();
    });
});
</script>
<script>
// --- GLOBAL API CONFIG (Fixe ReferenceError: apiUrl is not defined) ---
// Define baseUrl globally so admin-dashboard.js works correctly
if (!window.baseUrl) window.baseUrl = '<?php echo rtrim(site_url(""), "index.php?page="); ?>';
</script>
<script src="<?php
    if (function_exists('asset_url')) {
        echo asset_url('assets/js/admin-dashboard.js');
    } else {
        $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
        echo htmlspecialchars($assetBase . '/assets/js/admin-dashboard.js', ENT_QUOTES);
    }
?>"></script>


<?php
// Afficher le footer sur le dashboard admin
$footerPath = dirname(__DIR__, 2) . '/includes/footer.php';
if (is_file($footerPath)) {
    include_once $footerPath;
} else {
    echo "</body>\n</html>";
}
?>

