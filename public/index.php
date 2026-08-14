<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Redirection automatique : /index.php?page=parents => /index.php?page=parents/parents
if (isset($_GET['page']) && trim($_GET['page'], '/ ') === 'parents') {
    $redirectBase = isset($baseUrl) ? rtrim($baseUrl, '/') : '';
    // Redirection automatique supprimée : /index.php?page=parents
    // $target = $redirectBase . '/index.php?page=parents/parents';
    $target = $redirectBase . '/index.php?page=parents'; // Ensure it stays on the same page
    header('Location: ' . $target, true, 302);
    exit;
}
// index.php (router / renderer)

// IMPORTANT: Déterminer automatiquement APP_ENV basé sur le hostname
// Cela force production quand moncoachscolaire.fr est accédé
$hostname = $_SERVER['HTTP_HOST'] ?? '';
$isProduction = (strpos($hostname, 'moncoachscolaire.fr') !== false && strpos($hostname, 'localhost') === false);
if ($isProduction && !getenv('APP_ENV')) {
    putenv('APP_ENV=production');
}

// IMPORTANT: Activer le buffering des sorties pour éviter les problèmes de headers
// C'est CRITIQUE pour que les redirections et la gestion des sessions fonctionnent
// AVANT que du HTML soit envoyé au navigateur
if (ob_get_level() === 0) {
    ob_start();
}

$root = dirname(__DIR__); // Project root (public/ is current dir)
// Marquer l'exécution via le routeur pour éviter les redirections internes en boucle
if (!defined('IN_ROUTER')) {
    define('IN_ROUTER', true);
}

// ============================================
// VÉRIFICATION MODE MAINTENANCE (PRIORITAIRE)
// ============================================
// Vérifier le mode maintenance AVANT tout autre traitement
// Exceptions :
// - Les admins peuvent toujours accéder
// - Les pages login/register sont accessibles pour permettre la connexion admin
$maintenanceFile = $root . '/.maintenance.json';
$maintenanceEnabled = false;

if (file_exists($maintenanceFile)) {
    $maintenanceData = json_decode(file_get_contents($maintenanceFile), true);
    if ($maintenanceData && isset($maintenanceData['enabled']) && $maintenanceData['enabled'] === true) {
        $maintenanceEnabled = true;
    }
}

// Si maintenance activée, vérifier si l'utilisateur est admin
if ($maintenanceEnabled) {
    // Démarrer la session pour vérifier le rôle (sécurisé)
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('index.php (maintenance): impossible de démarrer la session — headers déjà envoyés.');
        }
    }

    // Récupérer la page demandée AVANT de charger config.php
    $currentPage = trim((string)($_GET['page'] ?? 'landingpage'), '/');

    // EXCEPTION : Permettre l'accès aux pages login, logout et maintenance
    // - login : pour que les admins puissent se connecter même en maintenance
    // - logout : pour que les utilisateurs puissent se déconnecter même en maintenance
    // - maintenance : la page elle-même
    // NOTE : register est BLOQUÉ en maintenance pour empêcher la création de comptes
    $allowedPagesDuringMaintenance = ['login', 'logout', 'logout_parents', 'maintenance'];

    if (!in_array($currentPage, $allowedPagesDuringMaintenance)) {
        // Charger les fichiers nécessaires pour vérifier si admin
        if (is_file($root . '/src/config/config.php')) {
            require_once $root . '/src/config/config.php';
        }
        if (is_file($root . '/src/includes/admin_auth.php')) {
            require_once $root . '/src/includes/admin_auth.php';
        }

        // Vérifier si l'utilisateur est admin
        $isAdmin = false;
        if (function_exists('isAdmin')) {
            $isAdmin = isAdmin();
        }

        // Si ce n'est pas un admin, rediriger vers la page de maintenance
        if (!$isAdmin) {
            // Rediriger vers la page de maintenance
            $maintenanceUrl = (isset($baseUrl) ? rtrim($baseUrl, '/') : '') . '/index.php?page=maintenance';
            header('Location: ' . $maintenanceUrl);
            exit;
        }
    }
}

// ============================================
// ============================================
// ROUTAGE API (PRIORITAIRE)
// ============================================
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$pageParamRaw = $_GET['page'] ?? '';
// Nettoyer le paramètre page (trim slashes et espaces)
$pageParam = trim((string)$pageParamRaw, '/ ');

// DEBUG: Voir ce que le routeur reçoit vraiment
error_log("🔍 ROUTER CHECK: pageParam='$pageParam' | URI='$requestUri'");


// Détecter si c'est une requête API
// On accepte : index.php?page=api/xxx ou /api/xxx directement (supporte les sous-dossiers)
if ((strpos($pageParam, 'api/') === 0) || (preg_match('#/api/(.+)$#', parse_url($requestUri, PHP_URL_PATH)))) {

    // Extraction du chemin API
    if (strpos($pageParam, 'api/') === 0) {
        $apiPath = substr($pageParam, 4); // Retire 'api/'
    } else {
        // Regex corrigée pour les sous-dossiers (enlève le préfixe avant /api/)
        preg_match('#/api/(.+)$#', parse_url($requestUri, PHP_URL_PATH), $matches);
        $apiPath = $matches[1] ?? '';
    }

    // Initialiser $apiFile pour éviter le warning PHP
    $apiFile = '';
    // DEBUG avancé pour traçage API
    error_log("🔧 ROUTER API: pageParam='" . $pageParam . "' | apiPath='" . $apiPath . "' | apiFile='" . $apiFile . "'");
    // Normalisation supplémentaire du chemin (trim, slashes)
    $apiPath = trim(str_replace('\\', '/', $apiPath), '/');
    // Compatibilité: certaines pages appellent page=api/get_exercises?action=...
    // On retire la query du chemin et on réinjecte ses paramètres dans $_GET.
    if (strpos($apiPath, '?') !== false) {
        [$apiPathOnly, $apiExtraQuery] = explode('?', $apiPath, 2);
        $apiPath = $apiPathOnly;
        $apiExtraParams = [];
        parse_str($apiExtraQuery, $apiExtraParams);
        foreach ($apiExtraParams as $k => $v) {
            if (!isset($_GET[$k])) {
                $_GET[$k] = $v;
            }
        }
    }
    $apiFile = $root . '/src/api/' . $apiPath . '.php';
    error_log("🔧 ROUTER API: Normalized apiPath='" . $apiPath . "' | apiFile='" . $apiFile . "'");

    // Eviter les traversées de dossier (LFI protection)
    if (strpos($apiPath, '..') !== false || strpos($apiPath, '\\') !== false) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }

    // Normaliser les slashes pour Windows (\ -> /)
    $apiPath = str_replace('\\', '/', $apiPath);

    // Construire le chemin vers le fichier API dans src/api/
    $apiFile = $root . '/src/api/' . $apiPath . '.php';

    // DEBUG
    error_log("🔧 ROUTER API: Checking file: $apiFile");

    // Vérifier que le fichier existe
    if (file_exists($apiFile) && is_file($apiFile)) {
        // Vérification de sécurité : le fichier doit être dans src/api/
        $realApiFile = realpath($apiFile);
        $realSrcApi = realpath($root . '/src/api');

        // Normaliser pour comparaison (Windows-safe)
        $normApiFile = str_replace('\\', '/', strtolower($realApiFile ?: $apiFile));
        $normSrcApi = str_replace('\\', '/', strtolower($realSrcApi ?: ($root . '/src/api')));

        if (strpos($normApiFile, $normSrcApi) === 0 || strpos($normApiFile, '/src/api/') !== false) {
            error_log("✅ ROUTER API: File found and validated: $apiFile");

            // Charger les dépendances nécessaires pour les API
            if (is_file($root . '/src/config/config.php')) {
                require_once $root . '/src/config/config.php';
            }
            if (is_file($root . '/src/database/connection.php')) {
                require_once $root . '/src/database/connection.php';
            }

            // Exécuter le fichier API
            require $apiFile;
            exit; // Les API ne retournent jamais de HTML
        } else {
            error_log("❌ ROUTER API: Security check failed for: $apiFile");
        }
    } else {
        error_log("❌ ROUTER API: File not found: $apiFile");
    }

    // API introuvable
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'API endpoint not found',
        'path' => $apiPath,
        'file' => $apiFile
    ]);
    exit;
}

// Récupérer le paramètre page et nettoyer les paramètres supplémentaires mal formatés
$pageRawRaw = (string)($_GET['page'] ?? 'landingpage');

// Log d'entrée du routeur
error_log("ROUTER ENTRY: Method=" . $_SERVER['REQUEST_METHOD'] . ", pageRaw=" . $pageRawRaw . ", POST keys=" . json_encode(array_keys($_POST)));


// Si page=demo?demo=1 a été fourni (mauvaise syntaxe), récupérer les paramètres après ?
if (strpos($pageRawRaw, '?') !== false) {
    list($pageRawClean, $pageExtraQuery) = explode('?', $pageRawRaw, 2);
    parse_str($pageExtraQuery, $pageExtraParams);
    // Injecter les paramètres manquants dans $_GET (sans écraser ceux déjà présents)
    foreach ($pageExtraParams as $k => $v) {
        if (!isset($_GET[$k])) {
            $_GET[$k] = $v;
        }
    }
    $pageRaw = $pageRawClean;
} else {
    $pageRaw = $pageRawRaw;
}

$pageRaw = trim($pageRaw, '/');
// Normaliser les doubles slashes (ex: college//exercices- -> college/exercices-)
$pageRaw = preg_replace('#/+#', '/', $pageRaw);

// Si la landingpage est demandée via le routeur, s'assurer que le CSS de page
// et la classe body sont définis AVANT la génération du head pour que
// la feuille `public/assets/css/pages/landingpage.css` soit liée correctement.
if ($pageRaw === 'landingpage') {
    if (empty($page_css)) $page_css = 'landingpage.css';
    if (empty($page_class)) $page_class = 'landing-page';
}

// ============================================
// REDIRECTIONS LEGACY (anciens URLs)
// ============================================
// Redirection de bac/cours-bac vers cours?niveau=bac
if ($pageRaw === 'bac/cours-bac') {
    if (is_file($root . '/src/config/config.php')) {
        require_once $root . '/src/config/config.php';
    }
    $redirect_url = site_url('cours', ['niveau' => 'bac']);
    header('Location: ' . $redirect_url, true, 301);
    exit;
}

// ============================================
// FAST-PATH : LOGOUT (redirection immédiate)
// ============================================
// Détecter et traiter logout directement sans générer d'HTML
// Cela garantit que la redirection fonctionne même avec le buffering
if (in_array($pageRaw, ['logout', 'logout_parents'])) {
    // Démarrer la session si nécessaire
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('index.php (logout fast-path): impossible de démarrer la session — headers déjà envoyés.');
        }
    }

    // Charger config pour site_url()
    if (is_file($root . '/src/config/config.php')) {
        require_once $root . '/src/config/config.php';
    }

    // Inclure et exécuter logout.php directement
    $logoutFile = $root . '/src/pages/' . $pageRaw . '.php';
    if (is_file($logoutFile)) {
        // Vider le buffer avant la redirection pour éviter les problèmes de headers
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        // Inclure logout.php qui fera la redirection via header() et exit
        include $logoutFile;
        // Si on arrive ici, il y a eu un problème et on ne devrait pas continuer
        exit('Erreur lors de la déconnexion.');
    }
}

// SÉCURITÉ : Bloquer l'accès aux fichiers sensibles via ?page=
// Cette protection empêche d'accéder à des fichiers sensibles via le routeur
$blockedPages = [
    '.env',
    '.env.production',
    '.env.local',
    '.env.test',
    '.env.development',
    'config.php',
    'debug_env_loading.php',
    '.htaccess',
    '.htaccessbak',
    '.htaccesscopy',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    '.gitignore',
    '.git',
    '.gitconfig'
];

// Vérifier si la page demandée correspond exactement à un fichier bloqué
foreach ($blockedPages as $blocked) {
    if ($pageRaw === $blocked) {
        http_response_code(403);
        die('Accès refusé. Fichier protégé.');
    }
}

// Bloquer toutes les tentatives d'accès aux fichiers .env (quel que soit le nom)
if (preg_match('/\.env/i', $pageRaw)) {
    http_response_code(403);
    die('Accès refusé. Fichier protégé.');
}

// Bloquer les tentatives d'accès aux fichiers de configuration
if (preg_match('/^(config|\.htaccess|composer|package)/i', $pageRaw)) {
    http_response_code(403);
    die('Accès refusé. Fichier protégé.');
}

// TRAITEMENT PRIORITAIRE : Pour les pages login/register avec POST, traiter AVANT tout
// Cela permet aux redirections de fonctionner sans aucun header envoyé
// Détecter aussi les POST de login/register même si page= n'est pas dans l'URL
// (cas où le formulaire envoie vers index.php sans paramètre)
$isLoginPost = ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['username']) && isset($_POST['password']) &&
    isset($_POST['csrf_token']));
$isRegisterPost = ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['username']) && isset($_POST['password']) &&
    isset($_POST['classe']));

// Déterminer quelle page traiter pour le POST
$postPage = null;
if (in_array($pageRaw, ['login', 'register'])) {
    $postPage = $pageRaw;
} elseif ($isLoginPost) {
    $postPage = 'login';
} elseif ($isRegisterPost) {
    $postPage = 'register';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $postPage && in_array($postPage, ['login', 'register'])) {
    // Log pour debug
    error_log("ROUTER: POST détecté pour page=" . $postPage . ", username=" . ($_POST['username'] ?? 'N/A'));

    // Charger config.php et site_boot.php pour avoir toutes les fonctions nécessaires
    if (is_file($root . '/src/config/config.php')) require_once $root . '/src/config/config.php';
    if (is_file($root . '/src/config/site_boot.php')) include_once $root . '/src/config/site_boot.php';

    // Démarrer la session si nécessaire (site_boot.php le fait déjà, mais au cas où)
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('index.php (POST): impossible de démarrer la session — headers déjà envoyés.');
        }
    }

    // Trouver le fichier login.php ou register.php
    // Priorité: src/pages/, puis racine pour compatibilité
    $candidates = [
        $root . '/src/pages/' . $postPage . '.php',
        $root . '/' . $postPage . '.php',
    ];

    $found = null;
    foreach ($candidates as $cand) {
        if (is_file($cand)) {
            $found = $cand;
            break;
        }
    }

    error_log("ROUTER: Fichier trouvé pour POST: " . ($found ?? 'NONE'));

    if ($found) {
        // Marquer que le POST a été traité pour éviter le doublon
        // Note: login.php définit lui-même $GLOBALS['__login_post_processed']

        // Inclure le fichier qui traitera le POST et fera la redirection si succès
        chdir(dirname($found));
        include $found;
        chdir($root);

        // Fallback: si le login a authentifié la session mais n'a pas émis de redirection,
        // forcer la redirection ici pour éviter l'impression de "rien ne se passe".
        if (
            $postPage === 'login'
            && !headers_sent()
            && !empty($_SESSION['logged_in'])
            && (
                !empty($_SESSION['user_id'])
                || !empty($_SESSION['parent_id'])
                || in_array(strtolower((string)($_SESSION['user_role'] ?? '')), ['parent', 'parents'], true)
            )
        ) {
            $role = strtolower((string)($_SESSION['user_role'] ?? 'student'));
            $user_level = $_SESSION['user_level'] ?? '';
            require_once __DIR__ . '/../src/includes/level_normalization.php';
            $level_norm = function_exists('normalize_school_level') ? normalize_school_level($user_level) : strtolower((string)$user_level);
            $redirect = 'eleve/dashboard';
            if ($role === 'admin' || $role === 'administrator') {
                $redirect = 'admin/dashboard_admin';
            } elseif ($role === 'parent' || !empty($_SESSION['parent_id'])) {
                $redirect = 'parents/dashboard_parent';
            } elseif (function_exists('is_lycee_level') && is_lycee_level($user_level)) {
                $redirect = 'eleve/lycee/lycee-accueil';
            } elseif (function_exists('is_college_level') && is_college_level($user_level)) {
                $redirect = 'eleve/college/college-accueil';
            }
            header('Location: ' . (function_exists('site_url') ? site_url($redirect) : '/index.php?page=' . $redirect));
            exit;
        }

        // IMPORTANT: éviter une seconde exécution de login/register dans le flux normal.
        // Le fichier inclus gère déjà soit la redirection (succès), soit le rendu de la page (échec).
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        exit;
    }
}

// Dashboard normal : accessible aux élèves connectés
// Le dashboard.php vérifie déjà l'authentification utilisateur
// Pas de vérification spéciale nécessaire ici

// ÉTAPE 1 : Nettoyer les caractères corrompus en premier (UTF-8 mal décodé → ???)
// Exemple: college/4??me/exercices-4??me -> college/4eme/exercices-4eme
// Ce nettoyage DOIT se faire AVANT la normalisation des accents pour éviter les faux positifs
$pageClean = $pageRaw;
$corruptionFound = false;

// Supprimer tous les points d'interrogation consécutifs
if (strpos($pageClean, '?') !== false && strpos($pageClean, '??') !== false) {
    // Supprimer les ?? (corruption typique)
    $pageClean = str_replace('??', '', $pageClean);
    $corruptionFound = true;

    // Après suppression des ??, ajouter 'e' aux patterns comme "4me" → "4eme"
    $pageClean = preg_replace('/([6543])me/', '\1eme', $pageClean);
}


// ÉTAPE 2 : Normaliser les URLs avec accents vers les URLs sans accents pour cohérence
// Exemple: college/6ème/exercices-6ème -> college/6eme/exercices-6eme
// Utiliser la même fonction que site_boot.php pour cohérence
if (function_exists('normalize_level_for_url')) {
    // Extraire et normaliser les niveaux dans le chemin
    $normalizedPage = $pageClean;
    $replacements = [
        '6ème' => '6eme',
        '5ème' => '5eme',
        '4ème' => '4eme',
        '3ème' => '3eme',
        'Première' => 'premiere',
        'première' => 'premiere',
        'Seconde' => 'seconde',  // Normaliser aussi Seconde pour cohérence
        'Terminale' => 'terminale'  // Normaliser aussi Terminale pour cohérence
    ];
    // Vérifier si des remplacements sont nécessaires
    $needsNormalization = false;
    foreach ($replacements as $withAccent => $withoutAccent) {
        // Rechercher avec différentes casses et positions dans le chemin
        if (stripos($pageClean, $withAccent) !== false) {
            $normalizedPage = str_ireplace($withAccent, $withoutAccent, $pageClean);
            $needsNormalization = true;
        }
    }
} else {
    // Fallback si la fonction n'existe pas
    $normalizedPage = $pageClean;
    $replacements = [
        '6ème' => '6eme',
        '5ème' => '5eme',
        '4ème' => '4eme',
        '3ème' => '3eme',
        'Première' => 'premiere',
        'première' => 'premiere',
        'Seconde' => 'seconde',
        'Terminale' => 'terminale'
    ];
    $needsNormalization = false;
    foreach ($replacements as $withAccent => $withoutAccent) {
        if (stripos($pageClean, $withAccent) !== false) {
            $normalizedPage = str_ireplace($withAccent, $withoutAccent, $pageClean);
            $needsNormalization = true;
        }
    }
}

// Si la page a été corrompue OU normalisée et est différente, rediriger
if (($corruptionFound || $needsNormalization) && $normalizedPage !== $pageRaw) {
    $redirectBase = isset($baseUrl) ? rtrim($baseUrl, '/') : '';
    $target = $redirectBase . '/index.php?page=' . urlencode($normalizedPage);
    if (!empty($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'page=') === false) {
        $target .= '&' . $_SERVER['QUERY_STRING'];
    } elseif (!empty($_SERVER['QUERY_STRING'])) {
        // Remplacer page= dans query string
        $qs = preg_replace('/page=[^&]*/', 'page=' . urlencode($normalizedPage), $_SERVER['QUERY_STRING']);
        $target = $redirectBase . '/index.php?' . $qs;
    }
    // Use 301 permanent redirect pour les URLs normalisées
    header('Location: ' . $target, true, 301);
    exit;
}
$pageRaw = $normalizedPage;

// Map des alias lisibles vers les dossiers existants (ex: "seconde" -> "2nde", "premiere" -> "1ere")
// Cela permet d'avoir des URLs user-friendly tout en conservant la structure des dossiers.

// IMPORTANT: Ajouter le préfixe 'eleve/' pour les pages college/ et lycee/ si absent
// Cela permet aux URLs /college/*, /lycee/*, /bac/* de fonctionner comme /eleve/college/*, etc.
if (preg_match('#^(college|lycee|bac)/#', $pageRaw) && !preg_match('#^eleve/#', $pageRaw)) {
    $pageRaw = 'eleve/' . $pageRaw;
}

// Normalisation des niveaux lycée (seconde -> 2nde, premiere -> 1ere)
// Gère les cas avec ou sans préfixe 'eleve/'
if (strpos($pageRaw, 'lycee/') !== false) {
    // Remplacer les segments de chemin
    $pageRaw = str_replace('lycee/seconde/', 'lycee/2nde/', $pageRaw);
    $pageRaw = str_replace('lycee/premiere/', 'lycee/1ere/', $pageRaw);

    // Remplacer les suffixes de fichiers
    $pageRaw = str_replace('exercices-seconde', 'exercices-2nde', $pageRaw);
    $pageRaw = str_replace('exercices-premiere', 'exercices-1ere', $pageRaw);

    // Gérer les fins de chaîne (si l'URL se termine par le niveau)
    if (substr($pageRaw, -13) === 'lycee/seconde') {
        $pageRaw = substr($pageRaw, 0, -13) . 'lycee/2nde';
    }
    if (substr($pageRaw, -14) === 'lycee/premiere') {
        $pageRaw = substr($pageRaw, 0, -14) . 'lycee/1ere';
    }
}

if ($pageRaw === 'index') {
    $redirectBase = isset($baseUrl) ? rtrim($baseUrl, '/') : '';
    $target = $redirectBase . '/index.php?page=landingpage';
    // Use 302 temporary redirect to avoid caching during development
    header('Location: ' . $target, true, 302);
    exit;
}

// Aliases simples pour certaines pages publiques
// - 'contact' est une ancienne URL utilisée sans préfixe ; la page réelle est sous 'users/contact'
if ($pageRaw === 'contact') {
    // Rediriger silencieusement vers le chemin interne canonique
    $pageRaw = 'users/contact';
}

// Alias admin legacy : `admin/dashboard` -> `admin/dashboard_admin`
if ($pageRaw === 'admin/dashboard') {
    $pageRaw = 'admin/dashboard_admin';
}

// Route spéciale pour la page de test des exercices (dev/tools/tests/exercise_test_page.php)
if (isset($_GET['page']) && $_GET['page'] === 'test_exercises') {
    require_once $root . '/dev/tools/tests/exercise_test_page.php';
    exit;
}

// Construire les candidats pour la recherche de fichier
// Priorité: src/pages/ (nouvelle structure), puis racine/pages/ (legacy)
$candidates = [
    // Pages are now in src/pages/ directory (PRIORITÉ)
    $root . '/src/pages/' . $pageRaw . '.php',
    $root . '/src/pages/' . $pageRaw . '/index.php',
    $root . '/src/pages/' . $pageRaw . '.html',
    // Check in src/pages/system/ for core pages (demo, cours, exercices...)
    $root . '/src/pages/system/' . $pageRaw . '.php',
    $root . '/src/pages/system/' . $pageRaw . '/index.php',
    // Legacy: racine du projet
    $root . '/' . $pageRaw . '.php',
    $root . '/' . $pageRaw . '/index.php',
    $root . '/' . $pageRaw,
    $root . '/' . $pageRaw . '.html',
    // Legacy paths for backwards compatibility
    $root . '/pages/' . $pageRaw . '.php',
    $root . '/pages/' . $pageRaw . '/index.php',
    $root . '/pages/' . $pageRaw . '.html',
];

// Special handling for API endpoints located in src/api/
if (strpos($pageRaw, 'api/') === 0) {
    // Add candidates in src/ folder (so src/api/... works)
    // Priorité absolue pour les APIs
    array_unshift($candidates, $root . '/src/' . $pageRaw . '.php');
}

// Si la page contient des slashes (ex: college/6eme/exercices-6eme),
// essayer aussi de chercher directement dans src/pages/
if (strpos($pageRaw, '/') !== false) {
    // Ajouter les candidats avec le chemin complet dans src/pages/
    $candidates[] = $root . '/src/pages/' . $pageRaw . '.php';
    $candidates[] = $root . '/src/pages/' . $pageRaw . '/index.php';
    $candidates[] = $root . '/src/pages/' . $pageRaw . '.html';
    // Legacy paths
    $candidates[] = $root . '/pages/' . $pageRaw . '.php';
    $candidates[] = $root . '/pages/' . $pageRaw . '/index.php';
    $candidates[] = $root . '/pages/' . $pageRaw . '.html';
}

$found = null;
foreach ($candidates as $cand) {
    if (is_file($cand)) {
        $found = $cand;
        break;
    }
}

// Debug: log si une page importante n'est pas trouvée
if (!$found && in_array($pageRaw, ['dashboard_admin', 'dashboard_parent', 'landingpage'])) {
    error_log("Page '$pageRaw' not found. Candidates: " . implode(', ', $candidates));
}

// ============================================
// INCLUSION DES GUARDS ET HELPERS CENTRAUX
// ============================================
if (!$found) {
    // S'assurer que config.php est chargé pour avoir les fonctions helpers
    if (!isset($baseUrl) || !function_exists('site_url')) {
        if (is_file($root . '/config.php')) {
            require_once $root . '/config.php';
        }
    }
    if (is_file($root . '/src/includes/page_guards.php')) {
        require_once $root . '/src/includes/page_guards.php';
    }
    if (is_file($root . '/src/includes/redirect_helpers.php')) {
        require_once $root . '/src/includes/redirect_helpers.php';
    }

    // Vérifier si l'utilisateur est admin avant d'afficher la 404
    // Les admins doivent pouvoir accéder à toutes les pages
    $is_admin_404 = false;
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('index.php: impossible de démarrer la session — headers déjà envoyés.');
        }
    }
    if (is_file($root . '/includes/admin_auth.php')) {
        require_once $root . '/includes/admin_auth.php';
        $is_admin_404 = function_exists('isAdmin') && isAdmin();
    }

    // Si l'admin essaie d'accéder à une page, essayer de trouver une page d'accueil appropriée
    if ($is_admin_404) {
        // Pour les admins, rediriger vers la landing page au lieu d'afficher une 404
        $homeUrl = function_exists('site_url') ? site_url('landingpage') : (rtrim((isset($baseUrl) ? $baseUrl : ''), '/') . '/index.php?page=landingpage');
        header('Location: ' . $homeUrl);
        exit;
    }

    http_response_code(404);
    $siteName = 'MonCoachScolaire';

    // Utiliser asset_url() et site_url() si disponibles
    $cssUrl = function_exists('asset_url') ? asset_url('assets/css/style.css') : (rtrim((isset($baseUrl) ? $baseUrl : ''), '/') . '/assets/css/style.css');
    $homeUrl = function_exists('site_url') ? site_url('landingpage') : (rtrim((isset($baseUrl) ? $baseUrl : ''), '/') . '/index.php?page=landingpage');

    echo "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n<meta charset=\"utf-8\">\n<meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">\n<title>404  $siteName</title>\n<link rel=\"stylesheet\" href=\"" . htmlspecialchars($cssUrl, ENT_QUOTES) . "\">\n</head>\n<body>\n";
    // Charger site_boot avant la topbar pour disposer de site_url() et autres helpers
    if (is_file($root . '/src/config/site_boot.php')) {
        include_once $root . '/src/config/site_boot.php';
    }
    if (is_file($root . '/src/includes/topbar.php')) include $root . '/src/includes/topbar.php';
    echo '<main class="main-content"><section><h1>404  Page non trouvée</h1><p>Désolé  la page demandée est introuvable.</p><p><a href="' . htmlspecialchars($homeUrl, ENT_QUOTES) . '">Retour à l\'accueil</a></p></section></main>';
    if (is_file($root . '/src/includes/footer.php')) include $root . '/src/includes/footer.php';
    echo "</body></html>";
    exit;
}

$snippet = @file_get_contents($found, false, null, 0, 8192) ?: '';
$handlesHeader = (bool) preg_match('/(?:include|require)(?:_once)?\s*(?:\(?\s*["\'][^"\']*header\.php["\']\s*\)?)/i', $snippet);
$handlesFooter = (bool) preg_match('/(?:include|require)(?:_once)?\s*(?:\(?\s*["\'][^"\']*footer\.php["\']\s*\)?)/i', $snippet);

// IMPORTANT: Ne pas wrapper les API avec du HTML
if (strpos($pageRaw, 'api/') === 0) {
    chdir(dirname($found));
    include $found;
    chdir($root);
    exit;
}

if ($handlesHeader) {
    chdir(dirname($found));
    include $found;
    chdir($root);
    exit;
}

// Pages autonomes : elles gèrent leur propre HTML et leurs redirections.
// IMPORTANT: les inclure AVANT tout rendu HTML du routeur pour éviter le double-HTML.
$pages_sensibles = ['login', 'register', 'dashboard_parent', 'parents/dashboard_parent', 'dashboard_admin', 'admin/dashboard_admin', 'view_course'];
if (in_array($pageRaw, $pages_sensibles, true)) {
    $cwd = getcwd();
    // Marqueur debug avant inclusion dashboard
    if ($pageRaw === 'eleve/dashboard') {
        error_log("ROUTER: Including dashboard élève: $found");
    }
    chdir(dirname($found));
    include $found;
    chdir($cwd);
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    exit;
}

// S'assurer que config.php est chargé pour avoir $baseUrl et les fonctions helpers
if (!isset($baseUrl)) {
    if (is_file($root . '/src/config/config.php')) {
        require_once $root . '/src/config/config.php';
    }
}

// NOW charge site_boot.php (après tous les headers())
// Cette include doit venir ici, APRÈS les redirections, et AVANT l'affichage du HTML
if (is_file($root . '/src/config/site_boot.php')) {
    include_once $root . '/src/config/site_boot.php';
}
if (is_file($root . '/src/includes/level_normalization.php')) {
    require_once $root . '/src/includes/level_normalization.php';
}
if (is_file($root . '/src/includes/page_meta.php')) {
    require_once $root . '/src/includes/page_meta.php';
}
if (is_file($root . '/src/includes/app_theme_bootstrap.php')) {
    require_once $root . '/src/includes/app_theme_bootstrap.php';
}

$pageMeta = function_exists('load_page_meta') ? load_page_meta($found) : [];
if (empty($page_css) && !empty($pageMeta['page_css'])) {
    $page_css = $pageMeta['page_css'];
}
if (empty($page_class) && !empty($pageMeta['page_class'])) {
    $page_class = $pageMeta['page_class'];
}
if (!isset($page_title) && !empty($pageMeta['page_title'])) {
    $page_title = $pageMeta['page_title'];
}
$page_theme_level = $pageMeta['page_theme_level'] ?? null;

$app_theme = function_exists('bootstrap_app_theme')
    ? bootstrap_app_theme($pageRaw, $page_theme_level)
    : ['tier' => 'neutral', 'level_key' => 'neutral', 'variant' => []];
$page_theme_level = $GLOBALS['page_theme_level'] ?? $page_theme_level;

$siteName = 'MonCoachScolaire';
$title = isset($page_title) && $page_title ? htmlspecialchars($page_title, ENT_QUOTES) . '  ' . $siteName : $siteName;



// Utiliser asset_url() si disponible, sinon fallback sur $baseUrl
if (function_exists('asset_url')) {
    $cssBaseUrl = asset_url('assets/css/style.css');
} else {
    $cssBase = isset($baseUrl) ? rtrim($baseUrl, '/') : '';
    $cssBaseUrl = $cssBase . '/assets/css/style.css';
}

// Note: pas de redirection pré-route ici — les pages gèrent
// elles-mêmes les messages d’indisponibilité en fonction du niveau.

echo "<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n<meta charset=\"utf-8\">\n<meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">\n<title>" . $title . "</title>\n";
echo "<link rel=\"stylesheet\" href=\"" . htmlspecialchars($cssBaseUrl, ENT_QUOTES) . "\">\n";
if (function_exists('asset_url')) {
    $themeLevelCss = asset_url('assets/css/theme-level.css');
} else {
    $cssBase = isset($baseUrl) ? rtrim($baseUrl, '/') : '';
    $themeLevelCss = $cssBase . '/assets/css/theme-level.css';
}
echo "<link rel=\"stylesheet\" href=\"" . htmlspecialchars($themeLevelCss, ENT_QUOTES) . "\">\n";
// Background decorative layers are managed centrally in `public/assets/css/style.css`.
// For per-page backgrounds use `body.page-...` (ex: `body.page-landing`). To disable the decorative
// background on a page, add class `no-bg` to the <body> (see `.no-bg` rules in the CSS).

if (!empty($page_css)) {
    // Normaliser le chemin fourni par la page pour cibler assets/css/pages/
    $exCommonCss = '';
    // - Si le chemin ne commence pas par "pages/", le préfixer
    // - Supporte les sous-dossiers (ex: bac/cours-bac.css -> pages/bac/cours-bac.css)
    $cssCandidate = ltrim($page_css, '/');
    if (!preg_match('~^pages/~', $cssCandidate)) {
        $pageCssPath = 'pages/' . $cssCandidate;
    } else {
        $pageCssPath = $cssCandidate;
    }

    // Utiliser asset_url() si disponible
    if (function_exists('asset_url')) {
        $href = asset_url('assets/css/' . ltrim($pageCssPath, '/'));
    } else {
        $cssBase = isset($baseUrl) ? rtrim($baseUrl, '/') : '';
        $href = $cssBase . '/assets/css/' . ltrim($pageCssPath, '/');
    }
    echo "<link rel=\"stylesheet\" href=\"" . htmlspecialchars($href, ENT_QUOTES) . "\">\n";
    if (str_contains($page_css, 'exercices') && function_exists('asset_url')) {
        $exCommonCss = asset_url('assets/css/pages/exercices-common.css');
    } elseif (str_contains($page_css, 'exercices')) {
        $cssBase = isset($baseUrl) ? rtrim($baseUrl, '/') : '';
        $exCommonCss = $cssBase . '/assets/css/pages/exercices-common.css';
    }
    if (!empty($exCommonCss)) {
        echo "<link rel=\"stylesheet\" href=\"" . htmlspecialchars($exCommonCss, ENT_QUOTES) . "\">\n";
    }
}

// Exposer la baseUrl pour JavaScript (avant la fermeture de </head>)
if (function_exists('detectBaseUrl')) {
    $jsBaseUrl = detectBaseUrl();
} else {
    $jsBaseUrl = isset($baseUrl) ? $baseUrl : '';
}
echo "<script>window.baseUrl = " . json_encode($jsBaseUrl, JSON_UNESCAPED_SLASHES) . ";</script>\n";
echo "<script>window.appTheme = " . json_encode([
    'tier' => $app_theme['tier'] ?? 'neutral',
    'levelKey' => $app_theme['level_key'] ?? 'neutral',
], JSON_UNESCAPED_SLASHES) . ";</script>\n";

// Ajouter l'ID utilisateur au body pour le système de timeout (si utilisateur connecté)
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('index.php (user id): impossible de démarrer la session — headers déjà envoyés.');
        }
    }
}
$is_authenticated = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
if ($is_authenticated) {
    $user_id = $_SESSION['user_id'] ?? 0;
    echo "<script>if(document.body){document.body.dataset.userId = " . json_encode($user_id) . ";}</script>\n";
}

echo "</head>\n";


// Correction : charger le CSS spécifique dashboard.css si la page dashboard élève est demandée (AVANT le <head>)
if (
    (isset($pageRaw) && $pageRaw === 'eleve/dashboard')
    && (empty($page_css) || $page_css === 'dashboard.css')
) {
    $page_css = 'dashboard.css';
    if (empty($page_class)) $page_class = 'dashboard-page';
}

// Ensure landing page gets its specific body class and page CSS when routed through index.php
if (!isset($page_class) && isset($pageRaw) && $pageRaw === 'landingpage') {
    $page_class = 'landing-page';
    // Ensure the landing page stylesheet is linked in the head
    if (empty($page_css)) $page_css = 'landingpage.css';
}

$themeTier = $app_theme['tier'] ?? 'neutral';
$bodyClass = trim(($page_class ?? '') . ' app-bg theme-' . $themeTier);
echo "<body class=\"" . htmlspecialchars($bodyClass, ENT_QUOTES) . "\">\n";

// S'assurer que site_boot.php est chargé AVANT la topbar pour que les variables de session soient disponibles
if (is_file($root . '/src/config/site_boot.php')) {
    include_once $root . '/src/config/site_boot.php';
}
if (is_file($root . '/src/includes/topbar.php')) {
    include_once $root . '/src/includes/topbar.php';
}
// Inclure la page trouvée
$cwd = getcwd();
chdir(dirname($found));
include $found;
chdir($cwd);

if (!$handlesFooter && is_file($root . '/src/includes/footer.php')) {
    include_once $root . '/src/includes/footer.php';
    // Le footer.php ferme déjà </body></html>, donc on ne les ajoute pas
} else {
    // Si pas de footer inclus, fermer le body et html
    echo "</body>\n</html>";
}

// Vider le buffer de sortie avant de quitter
if (ob_get_level() > 0) {
    ob_end_flush();
}
exit;
