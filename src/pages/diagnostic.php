<?php
// diagnostic.php — Diagnostique initial par notion (élève)
// Routified: works with public/index.php router, no standalone HTML generation

$page_title = 'Diagnostique initial - MonCoachScolaire';
$page_css = 'pages/quiz.css';
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

    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-bold text-slate-800 mb-4">🧠 Diagnostique Initial</h1>
            <p class="text-xl text-slate-600 mb-3">Teste tes compétences par notion et découvre les points à renforcer.</p>

        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-3xl p-6 mb-8 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                <div class="rounded-2xl bg-white px-4 py-3 text-slate-700 shadow-sm">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Niveau</p>
                    <p class="mt-1 font-semibold"><?php echo htmlspecialchars($request_level); ?></p>
                </div>
            </div>
        </div>

        <section id="diagnostic-app" class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center sm:px-8">
                <div class="mb-4 h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-sky-600"></div>
                <p class="text-sm font-medium text-slate-700">Chargement du diagnostic…</p>
                <p class="mt-1 text-sm text-slate-500">Préparation des notions et des quiz adaptés.</p>
            </div>
        </section>
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
        window.requestLevel = <?php echo json_encode($request_level); ?>;
        window.requestSubject = <?php echo json_encode($request_subject); ?>;
        console.log('🔧 Diagnostic config:', {
            apiBasePath: window.apiBasePath,
            userLevel: window.userLevel,
            userSubject: window.userSubject,
            requestLevel: window.requestLevel,
            requestSubject: window.requestSubject,
        });
    </script>
    <script src="<?php echo $basePath; ?>/public/assets/js/course_modal.js"></script>
    <!-- Charger diagnostic.js directement sans router asset_url() -->
    <script src="<?php echo $basePath; ?>/public/assets/js/diagnostic.js"></script>
