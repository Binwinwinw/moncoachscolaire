<?php
// register.php — Inscription MonCoachScolaire (élève / parent)

// 1. SESSION AVANT TOUT OUTPUT
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('register.php: impossible de démarrer la session — headers déjà envoyés.');
        }
    }
}

// 2. MÉTA / CSS PAGE
$page_title = 'Inscription - MonCoachScolaire';
$page_css   = 'register.css';

// 3. CONFIG + DB + site_url()
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/connection.php';
if (is_file(__DIR__ . '/../includes/login_security.php')) {
    require_once __DIR__ . '/../includes/login_security.php';
}

if (!function_exists('site_url')) {
    $siteBoot = __DIR__ . '/../config/site_boot.php';
    if (is_file($siteBoot)) {
        require_once $siteBoot;
    }
}

// 4. Les migrations de structure DB doivent être appliquées hors du flux de requête public.
if (!function_exists('checkUsersTableColumns')) {
    function checkUsersTableColumns(PDO $pdo): bool
    {
        try {
            $requiredColumns = ['Nom', 'Prenom', 'Telephone', 'ParentId'];
            $existingColumns = [];
            $stmt = $pdo->query('SHOW COLUMNS FROM `users`');
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $existingColumns[] = $row['Field'] ?? '';
            }

            foreach ($requiredColumns as $column) {
                if (!in_array($column, $existingColumns, true)) {
                    error_log("register.php: missing users column {$column}; run DB migration before parent registration.");
                    return false;
                }
            }

            return true;
        } catch (Throwable $e) {
            error_log('register.php: unable to validate users schema: ' . $e->getMessage());
            return false;
        }
    }
}

// 5. REDIRECTION DASHBOARD SELON NIVEAU
function getDashboardUrlForLevel(string $level): string
{
    $normalized = strtolower($level);

    // Collège
    if (in_array($normalized, ['6ème', '6eme', '6e'], true)) {
        return site_url('eleve/college/6eme/exercices-6eme', ['welcome' => '1']);
    }
    if (in_array($normalized, ['5ème', '5eme', '5e'], true)) {
        return site_url('eleve/college/5eme/exercices-5eme', ['welcome' => '1']);
    }
    if (in_array($normalized, ['4ème', '4eme', '4e'], true)) {
        return site_url('eleve/college/4eme/exercices-4eme', ['welcome' => '1']);
    }
    if (in_array($normalized, ['3ème', '3eme', '3e'], true)) {
        return site_url('eleve/college/3eme/exercices-3eme', ['welcome' => '1']);
    }

    // Lycée
    if (in_array($normalized, ['seconde'], true)) {
        return site_url('eleve/lycee/2nde/exercices-seconde', ['welcome' => '1']);
    }
    if (in_array($normalized, ['première', 'premiere'], true)) {
        return site_url('eleve/lycee/1ere/exercices-premiere', ['welcome' => '1']);
    }
    if (in_array($normalized, ['terminale'], true)) {
        return site_url('eleve/lycee/terminale/exercices-terminale', ['welcome' => '1']);
    }

    // BAC
    if (in_array($normalized, ['bac'], true)) {
        return site_url('cours', ['niveau' => 'bac', 'welcome' => '1']);
    }

    // fallback
    return site_url('eleve/dashboard', ['welcome' => '1']);
}

// 6. SORTIR DU MODE DÉMO SI PRÉSENT
if (!empty($_SESSION['is_demo'])) {
    unset($_SESSION['is_demo']);

    if (isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] === 0) {
        unset($_SESSION['user_id']);
    }
    if (isset($_SESSION['user_name'])
        && in_array($_SESSION['user_name'], ['demo', 'Visiteur Démo'], true)
    ) {
        unset($_SESSION['user_name']);
    }
}

$error            = null;
$show_student_form = false;
$show_parent_form  = false;

// 7. TYPE DE FORMULAIRE (GET / POST)
$form_type = $_GET['type'] ?? ($_POST['form_type'] ?? null);
$show_student_form = ($form_type === 'student');
$show_parent_form  = ($form_type === 'parent');

$csrfToken = function_exists('generateCSRFToken')
    ? generateCSRFToken()
    : ((string) ($_SESSION['csrf_token'] ?? ''));

// 8. TRAITEMENT POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedCsrf = trim((string) ($_POST['csrf_token'] ?? ''));
    $csrfValid = function_exists('verifyCSRFToken')
        ? verifyCSRFToken($postedCsrf)
        : ($postedCsrf !== '' && hash_equals($csrfToken, $postedCsrf));

    if (!$csrfValid) {
        $error = 'Jeton de sécurité invalide. Merci de recharger la page.';
    }

    $form_type_post = $_POST['form_type'] ?? '';

    // === FORMULAIRE ÉLÈVE ===
    if ($error === null && $form_type_post === 'student') {
        $show_student_form = true;

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $age      = (int) ($_POST['age'] ?? 0);
        $classe   = trim($_POST['classe'] ?? '');

        if ($username === '' || mb_strlen($username) < 3) {
            $error = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
        } elseif ($password === '' || mb_strlen($password) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères.";
        } elseif ($age < 10 || $age > 18) {
            $error = "L'âge doit être entre 10 et 18 ans.";
        } elseif ($classe === '') {
            $error = "Veuillez sélectionner une classe.";
        } else {
            $dbReadOnly = isset($dbReadOnly)
                ? $dbReadOnly
                : filter_var(getenv('DB_READ_ONLY') ?: 'false', FILTER_VALIDATE_BOOLEAN);

            if ($dbReadOnly) {
                $error = "La création de compte est temporairement désactivée. Veuillez réessayer plus tard.";
            } elseif (isset($pdo) && $pdo instanceof PDO) {
                try {
                    // Username unique (on utilise l'email fictif uniquement pour compat)
                    $checkStmt = $pdo->prepare("SELECT Id FROM users WHERE Username = ? LIMIT 1");
                    $checkStmt->execute([$username]);
                    if ($checkStmt->fetch()) {
                        $error = "Ce nom d'utilisateur est déjà pris. Veuillez en choisir un autre.";
                    } else {
                        $email        = $username . '@example.com';
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT); // stockage sécurisé [web:221]
                        $role         = 'student';

                        $insertStmt = $pdo->prepare("
                            INSERT INTO users (Username, Email, PasswordHash, Role, UserLevel)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $insertStmt->execute([$username, $email, $passwordHash, $role, $classe]);

                        $userId = (int) $pdo->lastInsertId();
                        if ($userId <= 0) {
                            throw new PDOException("Impossible de récupérer l'ID de l'utilisateur créé.");
                        }

                        // Session
                        $_SESSION['user_id']    = $userId;
                        $_SESSION['user_name']  = $username;
                        $_SESSION['user_level'] = $classe;
                        $_SESSION['user_role']  = $role;
                        $_SESSION['logged_in']  = true;
                        unset($_SESSION['is_demo']);

                        // Progression (optionnel)
                        try {
                            $progressStmt = $pdo->prepare("
                                INSERT INTO UserProgress (UserId, XP, CurrentPosition)
                                VALUES (?, 0, 1)
                            ");
                            $progressStmt->execute([$userId]);
                        } catch (PDOException $e) {
                            error_log("UserProgress erreur: " . $e->getMessage());
                        }

                        session_regenerate_id(true);

                        $redirectUrl = getDashboardUrlForLevel($classe);
                        header('Location: ' . $redirectUrl);
                        exit;
                    }
                } catch (PDOException $e) {
                    error_log("Erreur inscription élève: " . $e->getMessage());
                    $error = "Une erreur est survenue lors de l'inscription. Merci de réessayer plus tard.";
                    if (getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'local') {
                        $error .= " (Code " . $e->getCode() . ")";
                    }
                }
            } else {
                $error = "Base de données indisponible, veuillez réessayer plus tard.";
                error_log("register.php: \$pdo null lors inscription élève.");
            }
        }
    }

    // === FORMULAIRE PARENT ===
    if ($error === null && $form_type_post === 'parent') {
        $show_parent_form = true;

        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $nom       = trim($_POST['nom'] ?? '');
        $prenom    = trim($_POST['prenom'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Veuillez entrer une adresse email valide.";
        } elseif ($password === '' || mb_strlen($password) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères.";
        } else {
            $dbReadOnly = isset($dbReadOnly)
                ? $dbReadOnly
                : filter_var(getenv('DB_READ_ONLY') ?: 'false', FILTER_VALIDATE_BOOLEAN);

            if ($dbReadOnly) {
                $error = "La création de compte est temporairement désactivée. Veuillez réessayer plus tard.";
            } elseif (isset($pdo) && $pdo instanceof PDO) {
                try {
                    // Email unique
                    $check = $pdo->prepare("SELECT Id FROM users WHERE Email = ? LIMIT 1");
                    $check->execute([$email]);
                    if ($check->fetch()) {
                        $error = "Cette adresse email est déjà utilisée. Veuillez vous connecter ou choisir une autre adresse.";
                    } else {
                        if (!checkUsersTableColumns($pdo)) {
                            $error = "La structure de la base de données n'est pas à jour pour l'inscription parent. Merci de contacter l'administrateur.";
                        } else {
                            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                            $role         = 'parent';
                            $usernameBase = $prenom !== '' ? strtolower($prenom) : explode('@', $email)[0];
                            $username     = $usernameBase;

                            // Username unique
                            $counter = 1;
                            while (true) {
                                $checkU = $pdo->prepare("SELECT Id FROM users WHERE Username = ? LIMIT 1");
                                $checkU->execute([$username]);
                                if ($checkU->fetch()) {
                                    $username = $usernameBase . $counter;
                                    $counter++;
                                } else {
                                    break;
                                }
                            }

                            $insertStmt = $pdo->prepare("
                                INSERT INTO users (Username, Email, PasswordHash, Role, Nom, Prenom, Telephone, UserLevel, ParentId)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)
                            ");
                            $insertStmt->execute([
                                $username,
                                $email,
                                $passwordHash,
                                $role,
                                $nom ?: null,
                                $prenom ?: null,
                                $telephone ?: null,
                                'parent',
                            ]);

                            $parentId = (int) $pdo->lastInsertId();
                            if ($parentId <= 0) {
                                throw new PDOException("Impossible de récupérer l'ID du parent créé.");
                            }

                            $_SESSION['parent_id'] = $parentId;
                            $_SESSION['user_id']   = $parentId;
                            $_SESSION['user_name'] = $prenom !== '' ? $prenom : $username;
                            $_SESSION['user_role'] = 'parent';
                            $_SESSION['logged_in'] = true;
                            unset($_SESSION['is_demo']);

                            session_regenerate_id(true);

                            $redirectUrl = site_url('parents/dashboard_parent');
                            header('Location: ' . $redirectUrl);
                            exit;
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Erreur inscription parent: " . $e->getMessage());
                    $error = "Une erreur est survenue lors de l'inscription. Merci de réessayer plus tard.";
                    if (getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'local') {
                        $error .= " (Code " . $e->getCode() . ")";
                    }
                }
            } else {
                $error = "Base de données indisponible, veuillez réessayer plus tard.";
                error_log("register.php: \$pdo null lors inscription parent.");
            }
        }
    }
}
?>

<?php
// Thème neutre (topbar grise, identique à la landing)
if (is_file(__DIR__ . '/../includes/app_theme_bootstrap.php')) {
    require_once __DIR__ . '/../includes/app_theme_bootstrap.php';
}
if (function_exists('bootstrap_app_theme')) {
    bootstrap_app_theme(null, null);
}
$auth_theme_tier = $GLOBALS['app_theme']['tier'] ?? 'neutral';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <!-- Tailwind + style global -->
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/theme-level.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/tailwind.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/pages/' . $page_css); ?>">
</head>
<body class="app-bg theme-<?php echo htmlspecialchars($auth_theme_tier, ENT_QUOTES, 'UTF-8'); ?> register-page">
<?php
if (is_file(__DIR__ . '/../includes/topbar.php')) {
    include_once __DIR__ . '/../includes/topbar.php';
}
?>
<main class="min-h-screen bg-transparent flex items-center justify-center px-4 py-8">
    <div class="max-w-4xl w-full bg-white/95 rounded-xl shadow-xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-600 mb-2">✨ Créer un compte</h1>
            <p class="text-gray-600 text-lg">
                Rejoins la communauté
                <a href="<?php echo site_url('landingpage'); ?>" class="text-blue-600 hover:text-blue-800">
                    MonCoachScolaire
                </a>
                et commence ton aventure éducative !
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                ❌ <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (!$show_student_form && !$show_parent_form): ?>
            <div class="my-8">
                <h2 class="text-center text-2xl font-bold text-blue-600 mb-8">
                    Choisis ton type de compte
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <button type="button"
                            class="bg-white border border-gray-300 rounded-xl p-8 cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-blue-500 hover:bg-blue-50 text-center flex flex-col items-center gap-3"
                            onclick="showForm('student')">
                        <span class="text-5xl mb-2">🎓</span>
                        <span class="text-xl font-bold text-slate-800">Élève</span>
                        <span class="text-sm text-slate-600">Pour les élèves qui veulent progresser</span>
                    </button>
                    <button type="button"
                            class="bg-white border border-gray-300 rounded-xl p-8 cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-green-500 hover:bg-green-50 text-center flex flex-col items-center gap-3"
                            onclick="showForm('parent')">
                        <span class="text-5xl mb-2">👨‍👩‍👧‍👦</span>
                        <span class="text-xl font-bold text-slate-800">Parents</span>
                        <span class="text-sm text-slate-600">Pour suivre la progression de vos enfants</span>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($show_student_form): ?>
            <div id="student-form-wrapper" class="animate-fade-in">
                <div class="mb-6">
                    <h3 class="font-semibold text-lg mb-2">🎯 Avantages de ton inscription</h3>
                    <ul class="list-disc list-inside text-sm text-slate-700">
                        <li>Cours adaptés à ton niveau</li>
                        <li>Suivi personnalisé de tes progrès</li>
                        <li>Exercices ludiques et interactifs</li>
                        <li>Accès gratuit à toutes les ressources</li>
                    </ul>
                </div>

                <form action="<?php echo site_url('register'); ?>" method="POST"
                      class="register-form grid grid-cols-1 md:grid-cols-2 gap-6" id="student-form">
                    <input type="hidden" name="form_type" value="student">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="flex flex-col gap-2">
                        <label for="username">👤 Nom d'utilisateur</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            minlength="3"
                            placeholder="Choisis un pseudo cool"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        >
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="age">🎂 Ton âge</label>
                        <input
                            type="number"
                            id="age"
                            name="age"
                            required
                            min="10"
                            max="18"
                            placeholder="Ton âge"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            value="<?php echo isset($_POST['age']) ? htmlspecialchars((string) $_POST['age'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        >
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="classe">📚 Ta classe</label>
                        <select
                            id="classe"
                            name="classe"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="">Sélectionne ta classe</option>
                            <?php
                            $classes = ['6ème','5ème','4ème','3ème','Seconde','Première','Terminale','BAC'];
            $labels  = [
                '6ème'      => '6ème - Aventurier',
                '5ème'      => '5ème - Explorateur',
                '4ème'      => '4ème - Découvreur',
                '3ème'      => '3ème - Innovateur',
                'Seconde'   => 'Seconde - Explorateur Lycée',
                'Première'  => 'Première - Stratège',
                'Terminale' => 'Terminale - Finisher',
                'BAC'       => 'BAC - Expert',
            ];
            $selectedClasse = $_POST['classe'] ?? '';
            foreach ($classes as $c) {
                $sel = ($selectedClasse === $c) ? 'selected' : '';
                echo '<option value="' . htmlspecialchars($c, ENT_QUOTES, 'UTF-8') . "\" {$sel}>"
                    . htmlspecialchars($labels[$c], ENT_QUOTES, 'UTF-8')
                    . '</option>';
            }
            ?>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password">🔒 Mot de passe <span class="text-red-500">*</span></label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            minlength="8"
                            placeholder="Au moins 8 caractères"
                            aria-describedby="password-error password-help"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >
                        <span class="text-xs text-slate-500" id="password-help">
                            Le mot de passe doit contenir au moins 8 caractères.
                        </span>
                        <span class="text-xs text-red-600" id="password-error" role="alert"></span>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-3 px-6 rounded-lg font-semibold transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/30 md:col-span-2">
                        🚀 Créer mon compte
                    </button>
                </form>

                <div class="text-center mt-8 pt-8 border-t border-gray-200 md:col-span-2">
                    <a href="<?php echo site_url('login'); ?>">🔑 Déjà un compte ? Se connecter</a><br>
                    <button type="button" class="text-sm text-blue-600 hover:text-blue-800 mt-2" onclick="showForm(null)">
                        ← Retour au choix
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($show_parent_form): ?>
            <div id="parent-form-wrapper">
                <div class="mb-6">
                    <!-- Titre 'Avantages de l'espace Parents' supprimé -->
                    <ul class="list-disc list-inside text-sm text-slate-700">
                        <li>Suivi de la progression de vos enfants</li>
                        <li>Accès aux résultats et statistiques</li>
                        <li>Gestion du profil de vos enfants</li>
                    </ul>
                </div>

                <form action="<?php echo site_url('register'); ?>" method="POST"
                      class="register-form grid grid-cols-1 md:grid-cols-2 gap-6" id="parent-form">
                    <input type="hidden" name="form_type" value="parent">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="flex flex-col gap-2 md:col-span-2">
                        <label for="email">📧 Adresse email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            placeholder="votre.email@exemple.com"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        >
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="prenom">👤 Prénom</label>
                        <input
                            type="text"
                            id="prenom"
                            name="prenom"
                            placeholder="Votre prénom"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        >
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="nom">👤 Nom</label>
                        <input
                            type="text"
                            id="nom"
                            name="nom"
                            placeholder="Votre nom"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        >
                    </div>

                    <div class="flex flex-col gap-2 md:col-span-2">
                        <label for="telephone">📱 Téléphone (optionnel)</label>
                        <input
                            type="tel"
                            id="telephone"
                            name="telephone"
                            placeholder="06 12 34 56 78"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        >
                    </div>

                    <div class="flex flex-col gap-2 md:col-span-2">
                        <label for="password_parent">🔒 Mot de passe <span class="text-red-500">*</span></label>
                        <input
                            type="password"
                            id="password_parent"
                            name="password"
                            required
                            minlength="8"
                            placeholder="Au moins 8 caractères"
                            aria-describedby="password_parent-error password_parent-help"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >
                        <span class="text-xs text-slate-500" id="password_parent-help">
                            Le mot de passe doit contenir au moins 8 caractères.
                        </span>
                        <span class="text-xs text-red-600" id="password_parent-error" role="alert"></span>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white py-3 px-6 rounded-lg font-semibold transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/30 md:col-span-2">
                        🚀 Créer mon compte parent
                    </button>
                </form>

                <div class="text-center mt-8 pt-8 border-t border-gray-200 md:col-span-2">
                    <a href="<?php echo site_url('login'); ?>">🔑 Déjà un compte ? Se connecter</a><br>
                    <button type="button" class="text-sm text-blue-600 hover:text-blue-800 mt-2" onclick="showForm(null)">
                        ← Retour au choix
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php if (is_file(__DIR__ . '/../includes/footer.php')) {
    include __DIR__ . '/../includes/footer.php';
} ?>
<script>
function showForm(type) {
    if (type === 'student') {
        window.location.href = '<?php echo site_url('register', ['type' => 'student']); ?>';
    } else if (type === 'parent') {
        window.location.href = '<?php echo site_url('register', ['type' => 'parent']); ?>';
    } else {
        window.location.href = '<?php echo site_url('register'); ?>';
    }
}

// validation JS conservée (élève + parent) — tu peux garder ton bloc existant ici si tu veux
</script>
</body>
</html>
