<?php
/*
 * Page publique des révisions IA (route ?page=revisions)
 */

$page_title = 'Révisions IA - MonCoachScolaire';
$page_css = 'cours.css';
$page_class = 'page-revisions';

if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}

if (is_file(dirname(__DIR__, 2) . '/includes/login_security.php')) {
    require_once dirname(__DIR__, 2) . '/includes/login_security.php';
}

$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();
$has_access = !empty($is_logged_in) || $is_admin || $is_demo;

if (!$has_access) {
    header('Location: ' . (function_exists('site_url') ? site_url('login') : 'index.php?page=login'));
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/ai_course_generator.php';

$revisions = [];
if (!empty($_SESSION['user_id']) && function_exists('getAiRevisionsForUser')) {
    $revisions = getAiRevisionsForUser((int) $_SESSION['user_id']);
}

if (!is_array($revisions)) {
    $revisions = [];
}

if (file_exists(dirname(__DIR__, 2) . '/includes/topbar.php')) {
    include_once dirname(__DIR__, 2) . '/includes/topbar.php';
}
?>

<main class="main-content mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="rounded-[2rem] border border-slate-200/70 bg-white/90 p-8 shadow-[0_24px_70px_-30px_rgba(15,23,42,0.28)] backdrop-blur-sm">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-4xl font-bold text-slate-900">📚 Mes révisions IA</h1>
                <p class="mt-2 text-slate-600">Retrouve tes cours et quiz générés par l'IA, sauvegardés dans ta bibliothèque.</p>
            </div>
            <a href="<?php echo function_exists('site_url') ? site_url('system/cours') : 'index.php?page=cours'; ?>" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-3 text-white font-semibold shadow hover:bg-emerald-700 transition">
                ← Retour aux cours IA
            </a>
        </div>

        <?php if (empty($revisions)): ?>
            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-700">
                <p class="text-xl font-semibold">Aucune révision IA encore sauvegardée.</p>
                <p class="mt-3">Génère un cours ou un quiz depuis les pages Exercices ou Cours pour le retrouver ici.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($revisions as $revision): ?>
                    <?php
                    $type = htmlspecialchars($revision['Type'] ?? 'unknown', ENT_QUOTES, 'UTF-8');
                    $title = htmlspecialchars($revision['Title'] ?? 'Révision IA', ENT_QUOTES, 'UTF-8');
                    $subject = htmlspecialchars($revision['Subject'] ?? '', ENT_QUOTES, 'UTF-8');
                    $level = htmlspecialchars($revision['Level'] ?? '', ENT_QUOTES, 'UTF-8');
                    $createdAt = htmlspecialchars($revision['CreatedAt'] ?? '', ENT_QUOTES, 'UTF-8');
                    $courseId = isset($revision['CourseId']) ? (int) $revision['CourseId'] : 0;
                    $exerciseIds = [];
                    if (!empty($revision['ExerciseIds'])) {
                        $decoded = json_decode($revision['ExerciseIds'], true);
                        if (is_array($decoded)) {
                            $exerciseIds = $decoded;
                        }
                    }
                    $details = [];
                    if ($subject !== '') {
                        $details[] = $subject;
                    }
                    if ($level !== '') {
                        $details[] = $level;
                    }
                    ?>
                    <article class="rounded-3xl border border-slate-200 p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-sm font-semibold text-indigo-700"><?php echo $type === 'course' ? 'Cours IA' : ($type === 'quiz' ? 'Quiz IA' : 'Révision IA'); ?></span>
                                <h2 class="mt-3 text-2xl font-bold text-slate-900"><?php echo $title; ?></h2>
                                <p class="mt-2 text-sm text-slate-500"><?php echo implode(' • ', $details); ?></p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <?php if ($courseId > 0): ?>
                                    <a href="<?php echo function_exists('site_url') ? site_url('view_course', ['id' => $courseId]) : 'index.php?page=view_course&id=' . $courseId; ?>" class="inline-flex items-center gap-2 rounded-full bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 transition">Voir le cours</a>
                                <?php endif; ?>
                                <?php if (!empty($exerciseIds)): ?>
                                    <?php $firstExerciseId = (int) $exerciseIds[0]; ?>
                                    <a href="<?php echo function_exists('site_url') ? site_url('view_exercise', ['id' => $firstExerciseId]) : 'index.php?page=view_exercise&id=' . $firstExerciseId; ?>" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Voir l'exercice IA</a>
                                    <?php if (count($exerciseIds) > 1): ?>
                                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">+ <?php echo count($exerciseIds) - 1; ?> autres exercices</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-3 text-sm text-slate-600">
                            <span>Enregistré le <?php echo $createdAt; ?></span>
                            <?php if ($revisionId = (int) ($revision['Id'] ?? 0)): ?>
                                <span>ID révision <?php echo $revisionId; ?></span>
                            <?php endif; ?>
                            <?php if (!empty($revision['Metadata'])): ?>
                                <?php $metadata = json_decode($revision['Metadata'], true); ?>
                                <?php if (is_array($metadata) && !empty($metadata['source'])): ?>
                                    <span>Source : <?php echo htmlspecialchars($metadata['source'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>