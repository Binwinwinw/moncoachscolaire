<?php
/**
 * login.php - MonCoachScolaire.fr (FINAL PROD 2026)
 * Structure register.php + toute votre logique métier
 */

// ========== 1. PROTECTION GLOBALE ==========
define('SKIP_SESSION_CHECK', true);
define('LOGIN_PAGE_ACTIVE', true);

// ========== 2. SESSION SÉCURISÉE ==========
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('login.php: impossible de démarrer session - headers envoyés');
        }
    }
}

// ========== 3. VARIABLES PAGE ==========
$page_title = 'Connexion - MonCoachScolaire';
$page_css = 'login.css';

// ========== 4. CHARGER CONFIG + SÉCURITÉ ==========
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/login_security.php';
require_once __DIR__ . '/../includes/security_logger.php';
require_once __DIR__ . '/../includes/dashboard_extensions.php';
// Level access helpers
if (is_file(__DIR__ . '/../includes/level_access.php')) {
    require_once __DIR__ . '/../includes/level_access.php';
}

// ========== 5. CHARGER site_boot UNIQUEMENT si nécessaire (CLÉ!) ==========
if (!function_exists('site_url')) {
    $siteBoot = __DIR__ . '/../config/site_boot.php';
    if (is_file($siteBoot)) {
        require_once $siteBoot;
    }
}

$normalize_role = static function ($role) {
    $role = strtolower(trim((string) $role));
    if (in_array($role, ['parent', 'parents'], true)) {
        return 'parent';
    }
    if (in_array($role, ['admin', 'administrator'], true)) {
        return 'admin';
    }
    return $role !== '' ? $role : 'student';
};

// ========== 6. REDIRECTION SI DÉJÀ CONNECTÉ ==========
if (!empty($_SESSION['logged_in']) && (!empty($_SESSION['user_id']) || !empty($_SESSION['parent_id']))) {
    $role = $normalize_role($_SESSION['user_role'] ?? '');
    $level = $_SESSION['user_level'] ?? '';
    require_once __DIR__ . '/../includes/level_normalization.php';
    $level_normalized = function_exists('normalize_school_level') ? normalize_school_level($level) : $level;
    $is_college = function_exists('is_college_level') ? is_college_level($level) : false;
    if ($role === 'admin') {
        header('Location: ' . site_url('admin/dashboard_admin'));
    } elseif ($role === 'parent' || !empty($_SESSION['parent_id'])) {
        header('Location: ' . site_url('parents/dashboard_parent'));
    } elseif ($is_college) {
        header('Location: ' . site_url('eleve/college/college-accueil'));
    } elseif (function_exists('is_lycee_level') && is_lycee_level($level)) {
        header('Location: ' . site_url('eleve/lycee/lycee-accueil'));
    } elseif (stripos($level, 'bac') !== false) {
        header('Location: ' . site_url('eleve/bac/bac-accueil'));
    } else {
        header('Location: ' . site_url('eleve/dashboard'));
    }
    exit;
}

// ========== 7. NETTOYAGE SESSION DÉMO ==========
if (!empty($_SESSION['is_demo'])) {
    unset($_SESSION['is_demo']);
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === 0) {
        unset($_SESSION['user_id']);
    }
    if (isset($_SESSION['user_name']) && ($_SESSION['user_name'] === 'demo' || $_SESSION['user_name'] === 'Visiteur Démo')) {
        unset($_SESSION['user_name']);
    }
    if (isset($_SESSION['logged_in']) && empty($_SESSION['user_id'])) {
        unset($_SESSION['logged_in']);
    }
}

// ========== 8. VARIABLES FORMULAIRE ==========
$error = null;
$success = null;
$auth_reason = null;
$host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$is_local_host = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false);
$show_auth_debug = (getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'local' || $is_local_host);
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$formAction = !empty($requestUri)
    ? $requestUri
    : (function_exists('site_url') ? site_url('login') : ($_SERVER['PHP_SELF'] ?? 'index.php?page=login'));
$auth_debug_snapshot = [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
    'post_has_username' => isset($_POST['username']),
    'post_has_password' => isset($_POST['password']),
    'post_has_csrf' => isset($_POST['csrf_token']),
    'post_processed' => isset($GLOBALS['__login_post_processed']),
    'session_id' => session_id() ?: 'none',
    'logged_in' => !empty($_SESSION['logged_in']),
    'user_id' => $_SESSION['user_id'] ?? null,
    'parent_id' => $_SESSION['parent_id'] ?? null,
    'user_role' => $_SESSION['user_role'] ?? null,
    'auth_reason' => null,
    'current_uri' => $_SERVER['REQUEST_URI'] ?? '',
    'form_action' => $formAction,
    'last_attempt' => $_SESSION['__login_last_attempt'] ?? null,
    'debug_version' => 'login-debug-v3',
];
$csrf_token = generateCSRFToken();
$securityLogger = ensureSecurityLogger($pdo ?? null);
if (!isset($_SESSION['request_id']) || $_SESSION['request_id'] === '') {
    $_SESSION['request_id'] = bin2hex(random_bytes(8));
}

error_log("LOGIN.PHP: Method=" . $_SERVER['REQUEST_METHOD'] . ", POST processed=" . (isset($GLOBALS['__login_post_processed']) ? 'YES' : 'NO'));

$genericAuthError = "Identifiants invalides. Veuillez vérifier vos informations.";

// ========== 9. TRAITEMENT POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($GLOBALS['__login_post_processed'])) {
    $GLOBALS['__login_post_processed'] = true;
    $_SESSION['__login_last_attempt'] = [
        'time' => date('Y-m-d H:i:s'),
        'phase' => 'post_received',
        'reason' => 'processing',
        'hint' => substr((string) ($_POST['username'] ?? ''), 0, 6),
    ];

    error_log("LOGIN.PHP: Début traitement POST - Username: " . ($_POST['username'] ?? 'NOT SET'));
    error_log("LOGIN POST: Début du traitement - POST data: " . json_encode(array_keys($_POST)));

    $submitted_token = $_POST['csrf_token'] ?? '';

    error_log("LOGIN POST: tentative de vérification CSRF");

    if (!verifyCSRFToken($submitted_token)) {
        error_log("LOGIN POST: Échec de la vérification CSRF");
        $auth_reason = 'csrf_invalid';
        $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
        $error = "Erreur de sécurité. Veuillez réessayer.";
        if ($securityLogger instanceof SecurityLogger) {
            $securityLogger->log('auth_login_failure', [
                'scope' => 'login',
                'result' => 'failure',
                'identifier' => $username ?? null,
                'failure_reason' => 'csrf_invalid',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'request_id' => $_SESSION['request_id'] ?? null,
            ]);
        }
    } else {
        $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        error_log("LOGIN POST: Username récupéré: " . substr($username, 0, 10) . "...");

        $usernameValidation = [
            'valid' => (!empty($username) && strlen($username) >= 3 && strlen($username) <= 255),
            'error' => 'Le nom d\'utilisateur (ou email/téléphone) est requis et doit contenir entre 3 et 255 caractères.',
        ];
        $passwordValidation = ['valid' => !empty($password), 'error' => 'Le mot de passe est requis.'];

        if (!$usernameValidation['valid']) {
            error_log("LOGIN POST: Validation username échouée: " . ($usernameValidation['error'] ?? 'Erreur inconnue'));
            $auth_reason = 'username_invalid';
            $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
            $error = $usernameValidation['error'];
            if ($securityLogger instanceof SecurityLogger) {
                $securityLogger->log('auth_login_failure', [
                    'scope' => 'login',
                    'result' => 'failure',
                    'identifier' => $username,
                    'failure_reason' => 'username_invalid',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'request_id' => $_SESSION['request_id'] ?? null,
                ]);
            }
        } elseif (!$passwordValidation['valid']) {
            error_log("LOGIN POST: Validation password échouée: " . ($passwordValidation['error'] ?? 'Erreur inconnue'));
            $auth_reason = 'password_empty';
            $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
            $error = $passwordValidation['error'];
            if ($securityLogger instanceof SecurityLogger) {
                $securityLogger->log('auth_login_failure', [
                    'scope' => 'login',
                    'result' => 'failure',
                    'identifier' => $username,
                    'failure_reason' => 'password_empty',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'request_id' => $_SESSION['request_id'] ?? null,
                ]);
            }
        } else {
            $rateLimit = checkLoginAttempts($username, 'login');
            if (!$rateLimit['allowed']) {
                $auth_reason = 'rate_limited';
                $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
                $error = $rateLimit['message'];
                if ($securityLogger instanceof SecurityLogger) {
                    $securityLogger->log('auth_blocked', [
                        'scope' => 'login',
                        'result' => 'blocked',
                        'identifier' => $username,
                        'failure_reason' => 'rate_limited',
                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                        'request_id' => $_SESSION['request_id'] ?? null,
                        'metadata' => ['remaining' => $rateLimit['remaining'] ?? null],
                    ]);
                }
            } else {
                $maintenanceFile = __DIR__ . '/.maintenance.json';
                $maintenanceEnabled = false;

                if (file_exists($maintenanceFile)) {
                    $maintenanceData = json_decode(file_get_contents($maintenanceFile), true);
                    if ($maintenanceData && isset($maintenanceData['enabled']) && $maintenanceData['enabled'] === true) {
                        $maintenanceEnabled = true;
                    }
                }

                if (isset($pdo) && $pdo) {
                    // DÉBUT bloc try/catch corrigé
                    try {
                        $authenticated = false;
                        $userFound = null;
                        $userType = null;

                        $stmt = $pdo->prepare("
                            SELECT Id, Username, Email, PasswordHash, Role, UserLevel
                            FROM users
                               WHERE Username = ? OR Email = ? OR Telephone = ?
                            ORDER BY
                                CASE
                                    WHEN LOWER(Role) = 'parent' THEN 0
                                    WHEN LOWER(Role) = 'admin' THEN 1
                                    ELSE 2
                                END,
                                Id DESC
                            LIMIT 1
                        ");
                        $stmt->execute([$username, $username, $username]);
                        $user = $stmt->fetch();

                        if ($user) {
                            $userFound = $user;
                            $userType = $normalize_role($user['Role'] ?? 'student');
                        } else {
                            $stmtParent = $pdo->prepare("
                                SELECT Id as id, Email as email, PasswordHash, Nom as nom, Prenom as prenom, Username
                                FROM users
                                WHERE (Email = ? OR Username = ? OR Telephone = ?) AND LOWER(TRIM(Role)) IN ('parent','parents')
                                LIMIT 1
                            ");
                            $stmtParent->execute([$username, $username, $username]);
                            $parent = $stmtParent->fetch();
                            if ($parent) {
                                $userFound = $parent;
                                $userType = 'parent';
                            }
                        }

                        if ($userFound) {
                            $passwordValid = false;
                            $passwordHash = $userFound['PasswordHash'] ?? '';

                            if ($userType === 'parent') {
                                error_log("DEBUG LOGIN PARENT: tentative de connexion (parent)");
                            }

                            if (is_string($passwordHash) && $passwordHash !== '' && strpos($passwordHash, '$') === 0) {
                                $passwordValid = password_verify($password, $passwordHash);
                                if ($userType === 'parent') {
                                    error_log("DEBUG LOGIN PARENT: Résultat password_verify: " . ($passwordValid ? 'OK' : 'ECHEC'));
                                }
                            } elseif (is_string($passwordHash) && $passwordHash !== '') {
                                error_log("LOGIN: hash de mot de passe non supporté pour l'utilisateur " . ($username ?? 'inconnu'));
                            }

                            if ($passwordValid) {
                                if ($maintenanceEnabled) {
                                    $isAdmin = ($userType === 'admin' || $normalize_role($userFound['Role'] ?? '') === 'admin');
                                    if (!$isAdmin) {
                                        recordFailedAttempt($username, 'login');
                                        $auth_reason = 'maintenance_blocked';
                                        $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
                                        $error = "🔧 Site en maintenance. Seuls les administrateurs peuvent se connecter.";
                                        error_log("LOGIN: Mode maintenance - Connexion refusée pour " . $username);
                                    }
                                }

                                if (!$error) {
                                    $auth_reason = 'authenticated';
                                    $_SESSION['__login_last_attempt']['phase'] = 'authenticated';
                                    $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
                                    $resolvedUserId = null;
                                    if (isset($userFound['Id'])) {
                                        $resolvedUserId = (int) $userFound['Id'];
                                    } elseif (isset($userFound['id'])) {
                                        $resolvedUserId = (int) $userFound['id'];
                                    } elseif (isset($userFound['ID'])) {
                                        $resolvedUserId = (int) $userFound['ID'];
                                    }

                                    if ($userType === 'parent') {
                                        $parentId = $resolvedUserId;
                                        if (empty($parentId)) {
                                            throw new RuntimeException('parent_id_missing_after_auth');
                                        }
                                        $_SESSION['parent_id'] = (int) $parentId;
                                        $_SESSION['user_id'] = (int) $parentId;
                                        $_SESSION['user_role'] = 'parent';
                                        $_SESSION['logged_in'] = true;
                                        $user_name = ($userFound['prenom'] ?? '') . ' ' . ($userFound['nom'] ?? '');
                                        $_SESSION['user_name'] = trim($user_name) ?: ($userFound['Username'] ?? $userFound['email']);
                                    } else {
                                        $_SESSION['user_id'] = (int) $resolvedUserId;
                                        $_SESSION['user_name'] = $userFound['Username'];
                                        $_SESSION['user_level'] = $userFound['UserLevel'] ?? '6ème';
                                        // Stocker aussi l'ordre numérique du niveau pour vérifications rapides
                                        if (function_exists('get_level_order')) {
                                            $_SESSION['user_level_order'] = get_level_order($_SESSION['user_level']);
                                        }
                                        $_SESSION['user_role'] = $normalize_role($userFound['Role'] ?? 'student');
                                        $_SESSION['logged_in'] = true;

                                        if (($userFound['Username'] ?? '') === 'demo' || ($userFound['Email'] ?? '') === 'demo@example.com') {
                                            $_SESSION['is_demo'] = true;
                                        }
                                    }

                                    $_SESSION['__login_last_attempt']['phase'] = 'session_set';
                                    $_SESSION['__login_last_attempt']['user_id'] = $_SESSION['user_id'] ?? null;
                                    $_SESSION['__login_last_attempt']['parent_id'] = $_SESSION['parent_id'] ?? null;
                                    session_regenerate_id(true);

                                    resetLoginAttempts($username, 'login');
                                    if ($securityLogger instanceof SecurityLogger) {
                                        $securityLogger->log('auth_login_success', [
                                            'scope' => 'login',
                                            'result' => 'success',
                                            'user_id' => $_SESSION['user_id'] ?? null,
                                            'identifier' => $username,
                                            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                                            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                                            'request_id' => $_SESSION['request_id'] ?? null,
                                            'metadata' => ['role' => $_SESSION['user_role'] ?? null],
                                        ]);
                                    }

                                    if ($userType !== 'parent' && function_exists('updateLoginStreak')) {
                                        updateLoginStreak($_SESSION['user_id']);
                                    }

                                    // Redirection immédiate après authentification réussie
                                    $redirect = 'eleve/dashboard';
                                    $user_role_now = $normalize_role($_SESSION['user_role'] ?? 'student');
                                    $user_level_now = $_SESSION['user_level'] ?? '';
                                    $level_norm_now = function_exists('normalize_school_level')
                                        ? normalize_school_level($user_level_now)
                                        : strtolower((string) $user_level_now);

                                    if ($user_role_now === 'admin') {
                                        $redirect = 'admin/dashboard_admin';
                                    } elseif ($user_role_now === 'parent' || !empty($_SESSION['parent_id'])) {
                                        $redirect = 'parents/dashboard_parent';
                                    } else {
                                        $redirect = 'eleve/dashboard';
                                    }

                                    $_SESSION['__login_last_attempt']['phase'] = 'redirect_immediate';
                                    $_SESSION['__login_last_attempt']['redirect'] = $redirect;
                                    session_write_close();
                                    // Correction : toujours générer une URL avec ?page= pour le routeur
                                    $redir_url = function_exists('site_url') ? site_url($redirect) : '/public/index.php?page=' . urlencode($redirect);
                                    header('Location: ' . $redir_url);
                                    exit;
                                }
                            } else {
                                recordFailedAttempt($username, 'login');
                                $auth_reason = 'password_mismatch';
                                $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
                                $error = $genericAuthError;
                                if ($securityLogger instanceof SecurityLogger) {
                                    $securityLogger->log('auth_login_failure', [
                                        'scope' => 'login',
                                        'result' => 'failure',
                                        'identifier' => $username,
                                        'failure_reason' => 'invalid_credentials',
                                        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                                        'request_id' => $_SESSION['request_id'] ?? null,
                                    ]);
                                }
                            }
                        } else {
                            recordFailedAttempt($username, 'login');
                            $auth_reason = 'user_not_found';
                            $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
                            $error = $genericAuthError;
                            if ($securityLogger instanceof SecurityLogger) {
                                $securityLogger->log('auth_login_failure', [
                                    'scope' => 'login',
                                    'result' => 'failure',
                                    'identifier' => $username,
                                    'failure_reason' => 'invalid_credentials',
                                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                                    'request_id' => $_SESSION['request_id'] ?? null,
                                ]);
                            }
                        }
                    } catch (PDOException $e) {
                        error_log("Erreur login: " . $e->getMessage());
                        $auth_reason = 'pdo_exception';
                        $_SESSION['__login_last_attempt']['phase'] = 'exception';
                        $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
                        $error = "Une erreur est survenue. Veuillez réessayer plus tard.";
                    } catch (Throwable $e) {
                        error_log("Erreur login (throwable): " . $e->getMessage());
                        $auth_reason = 'auth_runtime_exception';
                        $_SESSION['__login_last_attempt']['phase'] = 'exception';
                        $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
                        $error = "Une erreur est survenue. Veuillez réessayer plus tard.";
                    }
                    // FIN bloc try/catch corrigé
                } else {
                    $auth_reason = 'db_unavailable';
                    $_SESSION['__login_last_attempt']['reason'] = $auth_reason;
                    $error = "La base de données est temporairement indisponible. Veuillez réessayer plus tard.";
                }
            }
        }
    }
}

if ($show_auth_debug && $auth_reason) {
    error_log('LOGIN DEBUG REASON: ' . $auth_reason);
}

if ($show_auth_debug) {
    $auth_debug_snapshot['post_processed'] = isset($GLOBALS['__login_post_processed']);
    $auth_debug_snapshot['logged_in'] = !empty($_SESSION['logged_in']);
    $auth_debug_snapshot['user_id'] = $_SESSION['user_id'] ?? null;
    $auth_debug_snapshot['parent_id'] = $_SESSION['parent_id'] ?? null;
    $auth_debug_snapshot['user_role'] = $_SESSION['user_role'] ?? null;
    $auth_debug_snapshot['auth_reason'] = $auth_reason ?? 'none';
}

// ========== DEBUG SESSION LOGIN ==========
$show_debug_block = $show_auth_debug && (isset($_GET['debug']) && $_GET['debug'] == '1');
if ($show_debug_block) {
    echo '<div>';
    echo '<strong>DEBUG SESSION</strong><br>';
    echo 'session_id: ' . session_id() . '<br>';
    echo 'logged_in: ' . (isset($_SESSION['logged_in']) ? var_export($_SESSION['logged_in'], true) : 'NON DÉFINI') . '<br>';
    echo 'user_id: ' . (isset($_SESSION['user_id']) ? var_export($_SESSION['user_id'], true) : 'NON DÉFINI') . '<br>';
    echo 'parent_id: ' . (isset($_SESSION['parent_id']) ? var_export($_SESSION['parent_id'], true) : 'NON DÉFINI') . '<br>';
    echo 'user_role: ' . (isset($_SESSION['user_role']) ? var_export($_SESSION['user_role'], true) : 'NON DÉFINI') . '<br>';
    echo 'user_level: ' . (isset($_SESSION['user_level']) ? var_export($_SESSION['user_level'], true) : 'NON DÉFINI') . '<br>';
    echo 'is_demo: ' . (isset($_SESSION['is_demo']) ? var_export($_SESSION['is_demo'], true) : 'NON DÉFINI') . '<br>';
    echo 'PHP_SELF: ' . htmlspecialchars($_SERVER['PHP_SELF'] ?? '') . '<br>';
    echo 'REQUEST_URI: ' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') . '<br>';
    echo '</div>';
}
// ========== 10. DOUBLE CHECK CONNEXION ==========
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $redirect = $_GET['redirect'] ?? null;
    $user_role = $normalize_role($_SESSION['user_role'] ?? 'student');
    $user_level = $_SESSION['user_level'] ?? '';
    $level_norm = function_exists('normalize_school_level') ? normalize_school_level($user_level) : strtolower($user_level);
    $college_levels = ['6eme','5eme','4eme','3eme'];
    $lycee_levels = ['Seconde', 'Premiere', 'Terminale'];
    if (!$redirect) {
        if ($user_role === 'admin') {
            $redirect = 'admin/dashboard_admin';
        } elseif ($user_role === 'parent' || !empty($_SESSION['parent_id'])) {
            $redirect = 'parents/dashboard_parent';
        } else {
            $redirect = 'eleve/dashboard';
        }
    }
    session_write_close();
    header('Location: ' . site_url($redirect));
    exit;
}

// Thème neutre (topbar grise, identique à la landing)
if (is_file(__DIR__ . '/../includes/app_theme_bootstrap.php')) {
    require_once __DIR__ . '/../includes/app_theme_bootstrap.php';
}
if (function_exists('bootstrap_app_theme')) {
    bootstrap_app_theme(null, null);
}
$auth_theme_tier = $GLOBALS['app_theme']['tier'] ?? 'neutral';

// ========== 11. HTML COMPLET (comme register.php, SANS topbar.php) ==========
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php
    // CSS
    $root = rtrim($baseUrl ?? '', '/');
if (!$root) {
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    if ($scriptDir && $scriptDir !== '.' && $scriptDir !== '/') {
        $root = $scriptDir;
    } else {
        $root = '';
    }
}
$cssStyle = asset_url('assets/css/style.css');
$cssPage = asset_url('assets/css/pages/' . $page_css);
?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssStyle, ENT_QUOTES); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(asset_url('assets/css/theme-level.css'), ENT_QUOTES); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPage, ENT_QUOTES); ?>">
    <?php
if (function_exists('detectBaseUrl')) {
    $jsBaseUrl = detectBaseUrl();
} else {
    $jsBaseUrl = isset($baseUrl) ? $baseUrl : '';
}
echo "<script>window.baseUrl = " . json_encode($jsBaseUrl, JSON_UNESCAPED_SLASHES) . ";</script>\n";
?>
</head>

<body class="app-bg theme-<?php echo htmlspecialchars($auth_theme_tier, ENT_QUOTES, 'UTF-8'); ?> login-page">
<?php
// Afficher la topbar sur la page de connexion
if (is_file(__DIR__ . '/../includes/topbar.php')) {
    include_once __DIR__ . '/../includes/topbar.php';
}
?>

<main class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.12),_transparent_55%),linear-gradient(135deg,_#f8fbff_0%,_#eef4ff_100%)] px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-4xl items-center">
        <div class="w-full rounded-[2rem] border border-slate-200/80 bg-white/95 p-6 shadow-[0_30px_80px_-30px_rgba(15,23,42,0.35)] backdrop-blur sm:p-8 lg:p-10">
            <div class="text-center mb-8">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-2xl shadow-sm">🔐</div>
                <h1 class="text-3xl font-bold text-blue-600 mb-2">Connexion</h1>
                <p class="text-gray-600 text-lg">Accède à ton espace personnel <a href="<?php echo site_url('landingpage'); ?>" class="font-semibold text-blue-600 transition hover:text-blue-800">MonCoachScolaire</a></p>
            </div>

        <div class="text-center p-4 bg-blue-50 rounded-lg mb-6">
            <p class="text-blue-800 text-sm mb-0">
                💡 <strong>Conseil :</strong> Utilise ton <strong>nom d'utilisateur</strong> ou ton <strong>adresse e-mail</strong> pour te connecter
            </p>
        </div>

        <?php if (!empty($show_auth_debug)): ?>
            <!-- Bloc debug auth désactivé -->
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-500 rounded-lg p-4 mb-6 flex items-center gap-3 text-red-800 animate-pulse" id="error-message" role="alert">
                <span class="text-xl">❌</span>
                <span><?php echo escapeOutput($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($show_auth_debug) && !empty($auth_reason)): ?>
            <div class="bg-slate-100 border border-slate-300 rounded-lg p-3 mb-6 text-slate-700 text-xs">
                Debug auth: <strong><?php echo escapeOutput($auth_reason); ?></strong>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-500 rounded-lg p-4 mb-6 flex items-center gap-3 text-green-800">
                <span class="text-xl">✅</span>
                <span><?php echo escapeOutput($success); ?></span>
            </div>
        <?php endif; ?>

        <?php ?>
        <form action="<?php echo htmlspecialchars($formAction); ?>" method="POST" class="flex flex-col gap-6" id="login-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="flex flex-col mb-4">
                <label for="username" class="font-semibold text-gray-700 mb-2 text-base">👤 Nom d'utilisateur ou Email <span class="text-red-500 font-bold ml-1">*</span></label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    autocomplete="username"
                    placeholder="Ton nom d'utilisateur ou email"
                    class="px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100<?php echo isset($error) && (empty($_POST['username']) || strpos($error, 'nom d\'utilisateur') !== false || strpos($error, 'email') !== false) ? ' error' : ''; ?>"
                    minlength="3"
                    maxlength="255"
                    value="<?php echo isset($_POST['username']) ? escapeOutput($_POST['username']) : ''; ?>"
                    aria-describedby="username-error username-help"
                    aria-invalid="<?php echo isset($error) && (empty($_POST['username']) || strpos($error, 'nom d\'utilisateur') !== false || strpos($error, 'email') !== false) ? 'true' : 'false'; ?>"
                >
                <span class="text-gray-500 text-xs mt-1 italic block" id="username-help">Entre 3 et 255 caractères</span>
                <span class="text-red-600 text-sm mt-1 min-h-5 block" id="username-error" role="alert"></span>
            </div>

            <div class="flex flex-col mb-4">
                <label for="password" class="font-semibold text-gray-700 mb-2 text-base">🔒 Mot de passe <span class="text-red-500 font-bold ml-1">*</span></label>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Ton mot de passe (minimum 8 caractères)"
                        class="w-full px-4 py-3 pr-12 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        aria-describedby="password-error password-help"
                        aria-invalid="false"
                    >
                    <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-transparent border-none cursor-pointer p-2 text-xl opacity-60 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center" id="toggle-password" aria-label="Afficher/masquer le mot de passe">
                        <span class="eye-icon eye-icon-hidden" id="eye-icon">👁️</span>
                    </button>
                </div>
                <span class="text-xs text-gray-500 mt-1 italic" id="password-help">Minimum 8 caractères</span>
                <span class="text-sm text-red-600 mt-1 min-h-5 block" id="password-error" role="alert"></span>
            </div>

            <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-500 text-white border-none py-4 px-8 rounded-lg text-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 mt-4" id="submit-btn">
                <span class="btn-text">Se connecter</span>
            </button>
        </form>

        <div class="mt-8 border-t border-gray-200 pt-8 text-center">
            <a href="<?php echo site_url('forgot_password'); ?>" class="mr-4 font-medium text-blue-600 transition hover:text-blue-800">🔑 Mot de passe oublié&nbsp;?</a><br>
            <a href="<?php echo site_url('register'); ?>" class="font-medium text-blue-600 transition hover:text-blue-800">✨ Pas encore de compte ? S'inscrire</a>
        </div>
        </div>
    </div>
</main>

<script>
// Gestion du bouton loading sans bloquer la soumission serveur
(function() {
    const form = document.getElementById('login-form');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
    const btnLoading = submitBtn ? submitBtn.querySelector('.btn-loading') : null;
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const username = document.getElementById('username')?.value?.trim() || '';
        const password = document.getElementById('password')?.value?.trim() || '';

        // Ne jamais bloquer côté JS: le backend valide et renvoie le message d'erreur exact.
        if (username.length < 3 || password.length < 1) {
            console.warn('LOGIN: soumission envoyée malgré validation client incomplète (validation serveur active).');
        }

        // Afficher l'état loading uniquement lors du submit effectif
        if (btnText && btnLoading) {
            btnText.style.display = 'none';
            btnLoading.classList.remove('hidden');
        }
    });

    // Toggle password visibility
    const togglePassword = document.getElementById('toggle-password');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd && icon) {
                if (pwd.type === 'password') {
                    pwd.type = 'text';
                    icon.classList.remove('eye-icon-hidden');
                } else {
                    pwd.type = 'password';
                    icon.classList.add('eye-icon-hidden');
                }
            }
        });
    }
})();
</script>

<?php
// Afficher le footer sur la page de connexion
if (is_file(__DIR__ . '/../includes/footer.php')) {
    include_once __DIR__ . '/../includes/footer.php';
}
?>
</body>
</html>

