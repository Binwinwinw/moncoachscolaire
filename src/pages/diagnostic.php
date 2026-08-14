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

// Le chemin API via le routeur public pour le JS
if (function_exists('site_url')) {
    $apiBasePath = site_url('api');
} else {
    $apiBasePath = $baseUrl . '/src/api';
}

$sessionRole = strtolower((string) ($_SESSION['user_role'] ?? $_SESSION['role'] ?? ''));
$isParentDiagnosticView = in_array($sessionRole, ['parent', 'parents'], true);
$isStudentDiagnosticView = $sessionRole === 'student' || !$isParentDiagnosticView;

$linkedChildren = [];
$selectedChild = null;
$selectedChildId = filter_input(INPUT_GET, 'child_id', FILTER_VALIDATE_INT);

if ($isParentDiagnosticView && isset($pdo) && $pdo instanceof PDO) {
    $parentUserId = (int) ($_SESSION['parent_id'] ?? $_SESSION['user_id'] ?? 0);

    if ($parentUserId > 0) {
        try {
            $childStmt = $pdo->prepare(
                "SELECT u.*, pci.accepted_at
                 FROM users u
                 JOIN parent_child_invites pci ON u.Id = pci.child_user_id
                 WHERE pci.parent_user_id = :parent_id
                   AND pci.status = 'accepted'
                   AND u.Role = 'student'
                 ORDER BY COALESCE(u.Prenom, u.Username), COALESCE(u.Nom, '')"
            );
            $childStmt->execute(['parent_id' => $parentUserId]);
            $linkedChildren = $childStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('Diagnostic parent: impossible de charger les enfants rattachés: ' . $e->getMessage());
        }
    }

    if (count($linkedChildren) === 1) {
        $selectedChild = $linkedChildren[0];
        $selectedChildId = isset($selectedChild['Id']) ? (int) $selectedChild['Id'] : null;
    } elseif ($selectedChildId) {
        foreach ($linkedChildren as $childRow) {
            $childId = isset($childRow['Id']) ? (int) $childRow['Id'] : 0;
            if ($childId === (int) $selectedChildId) {
                $selectedChild = $childRow;
                break;
            }
        }
        if ($selectedChild === null) {
            $selectedChildId = null;
        }
    }
}

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

if ($selectedChild !== null) {
    $childLevel = $selectedChild['level']
        ?? $selectedChild['Level']
        ?? $selectedChild['UserLevel']
        ?? $selectedChild['Niveau']
        ?? null;
    $childSubject = $selectedChild['preferred_subject']
        ?? $selectedChild['PreferredSubject']
        ?? $selectedChild['subject']
        ?? $selectedChild['Subject']
        ?? null;
    $childName = $selectedChild['Prenom']
        ?? $selectedChild['prenom']
        ?? $selectedChild['Username']
        ?? 'Votre enfant';

    $user_level = $childLevel ?: $user_level;
    $user_subject = $childSubject ?: $user_subject;
    $user_prenom = $childName;

    if (function_exists('normalize_school_level')) {
        $user_level = normalize_school_level((string) $user_level);
    }
}

// Permettre l'override via URL (?level=..., ?subject=...)
// Ne pas forcer le filtre matière par défaut: sinon on masque des quiz valides
// lorsque la matière préférée n'a pas (encore) de contenu pour ce niveau.
$request_level = $_GET['level'] ?? $user_level ?? '6eme';
$request_subject = isset($_GET['subject']) ? (string) $_GET['subject'] : null;

$parentDiagnosticNeedsSelection = $isParentDiagnosticView && count($linkedChildren) > 1 && $selectedChild === null;
$parentDiagnosticEmptyState = $isParentDiagnosticView && count($linkedChildren) === 0;
$diagnosticCatalogAvailable = true;
$canGenerateDiagnosticQuiz = !$parentDiagnosticNeedsSelection && !$parentDiagnosticEmptyState;

$selectedChildDisplayName = null;
if ($selectedChild !== null) {
    $selectedChildDisplayName = $selectedChild['Prenom']
        ?? $selectedChild['prenom']
        ?? $selectedChild['Username']
        ?? 'Votre enfant';
}

if (!$parentDiagnosticNeedsSelection && !$parentDiagnosticEmptyState) {
    $diagnosticCatalogAvailable = diagnostic_catalog_has_entries(
        dirname(__DIR__) . '/data/quiz',
        (string) $request_level,
        (string) $request_subject,
    );
}

// Stocker en session pour debug
$_SESSION['debug_diagnostic'] = [
    'user_level' => $user_level,
    'user_subject' => $user_subject,
    'request_level' => $request_level,
    'request_subject' => $request_subject,
    'user_id' => $_SESSION['user_id'] ?? null,
    'is_parent_diagnostic_view' => $isParentDiagnosticView,
    'selected_child_id' => $selectedChildId,
];
?>

<main class="mx-auto max-w-7xl px-4 py-8 md:px-6 lg:px-8">
    <div class="mb-10 rounded-3xl border border-slate-200 bg-white/90 p-6 text-center shadow-sm md:p-8">
        <h1 class="mb-3 text-4xl font-bold text-slate-900 md:text-5xl">🧠 Diagnostique Initial</h1>
        <p class="mx-auto max-w-3xl text-lg text-slate-600 md:text-xl">
            <?php if ($isParentDiagnosticView): ?>
                Lancez un diagnostic pour un seul enfant à la fois, afin de garder un suivi clair et bien contextualisé.
            <?php else: ?>
                Teste tes compétences par notion et découvre les points à renforcer.
            <?php endif; ?>
        </p>
        <?php if ($canGenerateDiagnosticQuiz): ?>
            <div class="mt-6 flex justify-center">
                <button id="btn-diagnostic-quiz-ia" type="button" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-5 py-2.5 font-semibold text-white shadow transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2" aria-haspopup="dialog" aria-controls="modal-diagnostic-quiz-ia">
                    🤖 Quiz IA
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-8 rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Revue IA</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Revue IA du diagnostic</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Cette fonctionnalité est visible en frontend, mais le backend d’analyse n’est pas encore disponible.</p>
            </div>
            <button id="btn-open-ai-review-sidebar" type="button" class="inline-flex items-center justify-center rounded-xl bg-sky-700 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-sky-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2" aria-expanded="false" aria-controls="ai-review-sidebar">
                🧠 Ouvrir la revue IA
            </button>
        </div>
    </div>

    <?php if ($isParentDiagnosticView): ?>
        <section class="mb-8 rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm">
            <?php if ($parentDiagnosticEmptyState): ?>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Aucun enfant rattaché</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Vous devez rattacher au moins un enfant à votre compte avant de lancer un diagnostic parent.</p>
                    </div>
                    <a href="<?php echo htmlspecialchars(site_url('parents/dashboard_parent') . '#family-invite-section', ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                        Aller au rattachement
                    </a>
                </div>
            <?php elseif ($parentDiagnosticNeedsSelection): ?>
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Choix de l'enfant</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900">Choisir l’enfant pour ce diagnostic</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Le diagnostic parent est lancé pour un seul enfant à la fois. Sélectionnez l’enfant concerné avant d’afficher les diagnostics disponibles.</p>
                    </div>
                    <form method="get" action="<?php echo htmlspecialchars(site_url('diagnostic'), ENT_QUOTES, 'UTF-8'); ?>" class="w-full max-w-md rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                        <label for="diagnostic-child-id" class="mb-2 block text-sm font-semibold text-slate-700">Sélectionner un enfant</label>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <select id="diagnostic-child-id" name="child_id" class="min-h-11 flex-1 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-800 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <option value="">Choisir un enfant</option>
                                <?php foreach ($linkedChildren as $childOption): ?>
                                    <?php $childOptionId = isset($childOption['Id']) ? (int) $childOption['Id'] : 0; ?>
                                    <?php $childOptionName = $childOption['Prenom'] ?? $childOption['prenom'] ?? $childOption['Username'] ?? 'Enfant'; ?>
                                    <?php $childOptionLevel = $childOption['level'] ?? $childOption['Level'] ?? $childOption['UserLevel'] ?? $childOption['Niveau'] ?? ''; ?>
                                    <option value="<?php echo htmlspecialchars((string) $childOptionId, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars((string) $childOptionName . ($childOptionLevel !== '' ? ' - ' . $childOptionLevel : ''), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                                Voir les diagnostics
                            </button>
                        </div>
                    </form>
                </div>
            <?php elseif ($selectedChild !== null): ?>
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Contexte parent</p>
                        <h2 class="mt-2 text-2xl font-bold text-slate-900">Diagnostic en cours pour : <?php echo htmlspecialchars((string) $selectedChildDisplayName, ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Les diagnostics affichés ci-dessous correspondent au niveau scolaire de cet enfant.</p>
                    </div>
                    <?php if (count($linkedChildren) > 1): ?>
                        <a href="<?php echo htmlspecialchars(site_url('diagnostic'), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                            Changer d’enfant
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <div class="mb-8 rounded-3xl border border-emerald-200 bg-emerald-50/80 p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="rounded-2xl border border-emerald-100 bg-white px-4 py-3 text-slate-700 shadow-sm">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Profil</p>
                <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($isParentDiagnosticView ? 'Parent - ' . ($selectedChildDisplayName ?: 'sélection requise') : (string) $user_prenom, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white px-4 py-3 text-slate-700 shadow-sm">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Niveau</p>
                <p class="mt-1 font-semibold text-slate-900"><?php echo htmlspecialchars($request_level); ?></p>
            </div>
        </div>
    </div>

    <section id="diagnostic-app" class="rounded-3xl border border-slate-200 bg-white/90 shadow-sm">
        <?php if ($parentDiagnosticEmptyState): ?>
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center sm:px-8">
                <p class="text-sm font-medium text-slate-700">Le diagnostic parent sera disponible dès qu’un enfant sera rattaché à ce compte.</p>
            </div>
        <?php elseif ($parentDiagnosticNeedsSelection): ?>
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center sm:px-8">
                <p class="text-sm font-medium text-slate-700">Sélectionnez un enfant pour afficher les diagnostics disponibles.</p>
                <p class="mt-1 text-sm text-slate-500">Un seul diagnostic parent à la fois, avec un contexte enfant explicite.</p>
            </div>
        <?php elseif (!$diagnosticCatalogAvailable): ?>
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center sm:px-8">
                <p class="text-sm font-medium text-slate-700">Aucun diagnostic n’est encore disponible pour le niveau <?php echo htmlspecialchars((string) $request_level, ENT_QUOTES, 'UTF-8'); ?>.</p>
                <p class="mt-1 text-sm text-slate-500">Le catalogue diagnostic pour ce niveau est encore en préparation.</p>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center sm:px-8">
                <div class="mb-4 h-10 w-10 animate-spin rounded-full border-4 border-slate-200 border-t-sky-600"></div>
                <p class="text-sm font-medium text-slate-700">Chargement du diagnostic…</p>
                <p class="mt-1 text-sm text-slate-500">Préparation des notions et des quiz adaptés.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php if (is_file(dirname(__DIR__, 2) . '/components/course_modal.php')) {
    require_once dirname(__DIR__, 2) . '/components/course_modal.php';
} ?>

<?php if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
    require_once dirname(__DIR__, 2) . '/includes/footer.php';
} ?>

<?php if ($canGenerateDiagnosticQuiz): ?>
    <div id="modal-diagnostic-quiz-ia" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-black/50 p-4" aria-hidden="true">
        <div class="relative my-4 flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="modal-diagnostic-quiz-ia-title">
            <button id="close-modal-diagnostic-quiz-ia" type="button" class="absolute right-3 top-3 text-2xl leading-none text-slate-400 transition hover:text-slate-700" aria-label="Fermer">&times;</button>
            <h2 id="modal-diagnostic-quiz-ia-title" class="mb-2 text-2xl font-bold text-emerald-700">Générer un quiz IA</h2>
            <p class="mb-4 text-sm text-slate-600">Crée un quiz ciblé pour lancer un diagnostic supplémentaire.</p>

            <form id="form-diagnostic-quiz-ia" class="flex flex-col gap-4">
                <div id="diagnostic-quiz-niveau-container">
                    <label for="diagnostic-quiz-niveau" class="mb-1 block font-semibold">Niveau scolaire</label>
                    <select id="diagnostic-quiz-niveau" name="niveau" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="">Sélectionner…</option>
                        <option value="6eme">6ème</option>
                        <option value="5eme">5ème</option>
                        <option value="4eme">4ème</option>
                        <option value="3eme">3ème</option>
                        <option value="2nde">Seconde</option>
                        <option value="1ere">Première</option>
                        <option value="terminale">Terminale</option>
                        <option value="bac">BAC</option>
                    </select>
                </div>

                <div>
                    <label for="diagnostic-quiz-matiere" class="mb-1 block font-semibold">Matière</label>
                    <select id="diagnostic-quiz-matiere" name="matiere" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="">Sélectionner…</option>
                        <option value="Mathématiques">Mathématiques</option>
                        <option value="Français">Français</option>
                        <option value="Physique-Chimie">Physique-Chimie</option>
                        <option value="SVT">SVT</option>
                        <option value="Histoire-Géographie">Histoire-Géographie</option>
                        <option value="Anglais">Anglais</option>
                        <option value="Espagnol">Espagnol</option>
                        <option value="Philosophie">Philosophie</option>
                    </select>
                </div>

                <div>
                    <label for="diagnostic-quiz-type" class="mb-1 block font-semibold">Type de quiz (optionnel)</label>
                    <input id="diagnostic-quiz-type" name="type" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="QCM, vrai/faux, QCU…" />
                </div>

                <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2.5 font-bold text-white transition hover:bg-emerald-800">Générer le quiz</button>
            </form>

            <div id="diagnostic-quiz-ia-result" class="mt-5 min-h-0 overflow-y-auto pr-1" aria-live="polite"></div>
        </div>
    </div>

    <style>
        #modal-diagnostic-quiz-ia .qcm-question {
            margin: 1rem 0;
            padding: 0.85rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #f8fafc;
        }

        #modal-diagnostic-quiz-ia .qcm-choices {
            margin-top: 0.75rem;
            display: grid;
            gap: 0.5rem;
        }

        #modal-diagnostic-quiz-ia .qcm-choice {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            padding: 0.5rem 0.65rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.65rem;
            background: #ffffff;
            cursor: pointer;
        }

        #modal-diagnostic-quiz-ia .qcm-radio {
            margin-top: 0.2rem;
            accent-color: #047857;
        }

        #modal-diagnostic-quiz-ia .qcm-choice-text {
            line-height: 1.45;
            color: #1e293b;
        }

        #modal-diagnostic-quiz-ia .input-feedback {
            display: inline-block;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        #modal-diagnostic-quiz-ia .qcm-feedback {
            margin-top: 0.75rem;
            border-radius: 0.75rem;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1e3a8a;
        }
    </style>
<?php endif; ?>

<script>
    window.basePath = <?php echo json_encode($baseUrl); ?>;
    window.apiBasePath = <?php echo json_encode($apiBasePath); ?>;
    window.userLevel = <?php echo json_encode($user_level); ?>;
    window.userSubject = <?php echo json_encode($user_subject); ?>;
    window.requestLevel = <?php echo json_encode($request_level); ?>;
    window.requestSubject = <?php echo json_encode($request_subject); ?>;
    window.diagnosticContext = <?php echo json_encode([
                                    'role' => $isParentDiagnosticView ? 'parent' : 'student',
                                    'selectedChildId' => $selectedChildId ? (int) $selectedChildId : null,
                                    'selectedChildName' => $selectedChildDisplayName,
                                    'needsSelection' => $parentDiagnosticNeedsSelection,
                                    'hasLinkedChildren' => count($linkedChildren) > 0,
                                    'catalogAvailable' => $diagnosticCatalogAvailable,
                                    'canSwitchChild' => count($linkedChildren) > 1,
                                    'diagnosticPageUrl' => site_url('diagnostic'),
                                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    console.log('🔧 Diagnostic config:', {
        apiBasePath: window.apiBasePath,
        userLevel: window.userLevel,
        userSubject: window.userSubject,
        requestLevel: window.requestLevel,
        requestSubject: window.requestSubject,
        diagnosticContext: window.diagnosticContext,
    });
</script>
<script src="<?php echo $basePath; ?>/public/assets/js/course_modal.js"></script>
<script src="<?php echo $basePath; ?>/public/assets/js/interactive-exercises.js"></script>
<!-- Charger diagnostic.js directement sans router asset_url() -->
<script src="<?php echo $basePath; ?>/public/assets/js/diagnostic.js"></script>

<?php
function diagnostic_catalog_has_entries(string $quizDir, string $level, string $subject = ''): bool
{
    if ($quizDir === '' || !is_dir($quizDir)) {
        return false;
    }

    $targetLevel = diagnostic_catalog_normalize_text((string) (function_exists('normalize_school_level') ? normalize_school_level($level) : $level));
    $targetSubject = diagnostic_catalog_normalize_text($subject);
    $files = glob($quizDir . '/*.json');

    if (!is_array($files)) {
        return false;
    }

    foreach ($files as $filePath) {
        $raw = file_get_contents($filePath);
        if (!is_string($raw) || $raw === '') {
            continue;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            continue;
        }

        $fileLevel = $decoded['level']
            ?? $decoded['quiz']['level']
            ?? $decoded['contents']['level']
            ?? null;
        $fileSubject = $decoded['subject']
            ?? $decoded['quiz']['subject']
            ?? $decoded['contents']['subject']
            ?? '';

        $normalizedFileLevel = diagnostic_catalog_normalize_text((string) (function_exists('normalize_school_level') ? normalize_school_level((string) $fileLevel) : (string) $fileLevel));
        $normalizedFileSubject = diagnostic_catalog_normalize_text((string) $fileSubject);

        if ($targetLevel !== '' && $normalizedFileLevel !== $targetLevel) {
            continue;
        }

        if ($targetSubject !== '' && $normalizedFileSubject !== $targetSubject) {
            continue;
        }

        return true;
    }

    return false;
}

function diagnostic_catalog_normalize_text(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $value = mb_strtolower($value, 'UTF-8');
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if (is_string($ascii) && $ascii !== '') {
        $value = $ascii;
    }

    $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;

    return trim($value);
}
