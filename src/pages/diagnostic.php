<?php
// diagnostic.php — Diagnostic initial par notion (élève)
// Routified: works with public/index.php router, no standalone HTML generation

$page_title = 'Diagnostic initial - MonCoachScolaire';
$page_css = 'diagnostic.css';
$page_class = 'diagnostic-page';

// Vérifier PDO
if (!isset($pdo)) {
    if (is_file(dirname(__DIR__, 2) . '/config/config.php')) {
        require_once dirname(__DIR__, 2) . '/config/config.php';
    }
    if (is_file(dirname(__DIR__, 2) . '/database/connection.php')) {
        require_once dirname(__DIR__, 2) . '/database/connection.php';
    }
}

// Construire les chemins d'API proprement
// Déterminer la base URL racine du projet (sans /public, sans query params)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = dirname($_SERVER['SCRIPT_NAME']); // Ex: /moncoachscolaire/public

// Remonter d'un niveau si on est dans /public
$basePath = preg_replace('#/public$#', '', $scriptPath);
$baseUrl = $protocol . '://' . $host . $basePath;

// IMPORTANT: Make $baseUrl global pour asset_url()
$GLOBALS['baseUrl'] = $baseUrl;

// Le chemin API absolu pour le JS
$apiBasePath = $baseUrl . '/src/api';

// 🔍 Déterminer le niveau et la matière de l'utilisateur connecté
$user_level = $_SESSION['user_level'] ?? null;
$user_subject = $_SESSION['user_subject'] ?? null;
$user_prenom = $_SESSION['user_name'] ?? 'Élève';

// Si la session n'a pas le niveau, fallback BDD
if (!$user_level && isset($_SESSION['user_id']) && isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare("SELECT level, preferred_subject FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userData) {
            $user_level = $userData['level'] ?? '6eme';
            $user_subject = $userData['preferred_subject'] ?? null;
        }
    } catch (Exception $e) {
        error_log("Diagnostic user query error: " . $e->getMessage());
    }
}

// Normaliser le niveau (fonctionne pour collège, lycée, bac)
if (function_exists('normalize_school_level')) {
    $user_level = normalize_school_level($user_level);
}

// Permettre l'override via URL (?level=..., ?subject=...)
$request_level = $_GET['level'] ?? $user_level ?? '6eme';
$request_subject = $_GET['subject'] ?? $user_subject;

// Stocker en session pour debug
$_SESSION['debug_diagnostic'] = [
    'user_level' => $user_level,
    'user_subject' => $user_subject,
    'request_level' => $request_level,
    'request_subject' => $request_subject,
    'user_id' => $_SESSION['user_id'] ?? null,
];
?>

    <main class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex justify-start mb-8">
                        <a href="<?php echo $basePath; ?>/public/index.php?page=eleve/dashboard" class="inline-flex items-center gap-3 px-6 py-3 back-btn font-bold transition-all text-lg">
                            <span class="text-xl">←</span>
                            <span>Retour au dashboard élève</span>
                        </a>
                </div>
        <div class="flex flex-col items-center justify-center mb-16">
            <div class="flex items-center gap-6 mb-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-xl border-4 border-white">
                    <span class="text-white text-4xl font-black">👤</span>
                </div>
                <div class="text-left">
                    <h1 class="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-blue-700 via-purple-600 to-indigo-600 bg-clip-text text-transparent drop-shadow-lg mb-2">
                        <span class="text-black">Bonjour <?php echo htmlspecialchars($user_prenom); ?> !</span>
                    </h1>
                    <div class="flex items-center gap-3">
                        <span class="px-4 py-2 rounded-full bg-blue-100 text-blue-800 font-bold shadow text-lg">
                            Niveau&nbsp;: <?php echo htmlspecialchars($request_level); ?>
                        </span>
                        <?php if ($request_subject): ?>
                        <span class="px-4 py-2 rounded-full bg-indigo-100 text-indigo-800 font-bold shadow text-lg">
                            <?php echo htmlspecialchars($request_subject); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <p class="text-xl md:text-2xl text-gray-800 font-semibold mb-2">Voici les quiz personnalisés, adaptés à ton profil.</p>
            <p class="text-lg text-gray-600 mb-2">Progresse à ton rythme et découvre tes points forts !</p>
        </div>
        <div id="diagnostic-app" class="w-full">
            <!-- Ici s'affichera le quiz par notion (chargé dynamiquement) -->
            <p class="text-center text-gray-600 py-20">
                <span class="inline-block animate-spin rounded-full w-12 h-12 border-4 border-blue-200 border-t-blue-600 mb-4"></span>
                Chargement diagnostics...
            </p>
        </div>
    </main>

    <?php if (is_file(dirname(__DIR__, 2) . '/components/course_modal.php')) {
        require_once dirname(__DIR__, 2) . '/components/course_modal.php';
    } ?>

    <?php if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
        require_once dirname(__DIR__, 2) . '/includes/footer.php';
    } ?>

    <script>
        window.basePath = <?php echo json_encode($baseUrl); ?>;
        window.apiBasePath = <?php echo json_encode($apiBasePath); ?>;
        window.userLevel = <?php echo json_encode($user_level); ?>;
        window.userSubject = <?php echo json_encode($user_subject); ?>;
        console.log('🔧 Diagnostic config:', {
            apiBasePath: window.apiBasePath,
            userLevel: window.userLevel,
            userSubject: window.userSubject,
            requestLevel: '<?php echo htmlspecialchars($request_level); ?>',
            requestSubject: '<?php echo htmlspecialchars($request_subject); ?>'
        });
    </script>
    <script src="<?php echo $basePath; ?>/public/assets/js/course_modal.js"></script>
    <!-- Charger diagnostic.js directement sans router asset_url() -->
    <script src="<?php echo $basePath; ?>/public/assets/js/diagnostic.js"></script>
