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
$selectedRevision = null;
$selectedContent = null;
$revisionId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$sessionUserId = (int) ($_SESSION['user_id'] ?? 0);

if ($revisionId && $sessionUserId > 0) {
    $selectedRevision = getAiRevisionForUser((int) $revisionId, $sessionUserId);
    if ($selectedRevision === null) {
        http_response_code(404);
    } else {
        $selectedContent = decodeAiRevisionContent($selectedRevision);
    }
} elseif ($sessionUserId > 0 && function_exists('getAiRevisionsForUser')) {
    $revisions = getAiRevisionsForUser($sessionUserId);
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
                <p class="mt-2 text-slate-600">Retrouve tes cours et quiz générés par l'IA dans tes révisions privées.</p>
            </div>
            <a href="<?php echo $selectedRevision !== null && function_exists('site_url') ? site_url('revisions') : (function_exists('site_url') ? site_url('system/cours') : 'index.php?page=cours'); ?>" class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-5 py-3 text-white font-semibold shadow hover:bg-emerald-700 transition">
                <?php echo $selectedRevision !== null ? '← Retour aux révisions' : '← Retour aux cours IA'; ?>
            </a>
        </div>

        <?php if ($revisionId && $selectedRevision === null): ?>
            <div class="rounded-3xl border border-slate-300 bg-slate-50 p-8 text-center text-slate-700" role="alert">
                <p class="text-xl font-semibold">Révision introuvable.</p>
                <p class="mt-3">Cette révision n'existe pas ou ne t'appartient pas.</p>
            </div>
        <?php elseif ($selectedRevision !== null): ?>
            <?php
            $selectedType = (string) ($selectedRevision['Type'] ?? 'unknown');
            $selectedTitle = htmlspecialchars((string) ($selectedRevision['Title'] ?? 'Révision IA'), ENT_QUOTES, 'UTF-8');
            $selectedSubject = htmlspecialchars((string) ($selectedRevision['Subject'] ?? ''), ENT_QUOTES, 'UTF-8');
            $selectedLevel = htmlspecialchars((string) ($selectedRevision['Level'] ?? ''), ENT_QUOTES, 'UTF-8');
            ?>
            <article class="space-y-6" data-revision-id="<?php echo (int) $selectedRevision['Id']; ?>">
                <header class="border-b border-slate-200 pb-5">
                    <span class="text-sm font-semibold text-indigo-700"><?php echo $selectedType === 'course' ? 'Cours IA' : ($selectedType === 'quiz' ? 'Quiz IA' : 'Révision IA'); ?></span>
                    <h2 class="mt-2 text-3xl font-bold text-slate-900"><?php echo $selectedTitle; ?></h2>
                    <p class="mt-2 text-slate-600"><?php echo implode(' · ', array_filter([$selectedSubject, $selectedLevel])); ?></p>
                </header>

                <?php if (!is_array($selectedContent)): ?>
                    <p class="rounded-xl bg-amber-50 p-4 text-amber-900" role="alert">Le contenu de cette révision est indisponible.</p>
                <?php elseif ($selectedType === 'course'): ?>
                    <?php if (!empty($selectedContent['introduction'])): ?>
                        <p class="text-lg leading-8 text-slate-700"><?php echo nl2br(htmlspecialchars((string) $selectedContent['introduction'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($selectedContent['objectives']) && is_array($selectedContent['objectives'])): ?>
                        <section>
                            <h3 class="text-xl font-bold text-slate-900">Objectifs</h3>
                            <ul class="mt-3 list-disc space-y-2 pl-6 text-slate-700">
                                <?php foreach ($selectedContent['objectives'] as $objective): ?>
                                    <li><?php echo htmlspecialchars((string) $objective, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>
                    <?php foreach (($selectedContent['sections'] ?? []) as $section): ?>
                        <?php if (is_array($section)): ?>
                            <section class="border-t border-slate-200 pt-5">
                                <h3 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars((string) ($section['title'] ?? 'Section'), ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="mt-3 whitespace-pre-line leading-7 text-slate-700"><?php echo htmlspecialchars((string) ($section['content'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php foreach (($section['examples'] ?? []) as $example): ?>
                                    <?php if (is_array($example)): ?>
                                        <div class="mt-4 border-l-4 border-emerald-500 bg-emerald-50 p-4 text-slate-700">
                                            <p><strong>Exemple :</strong> <?php echo htmlspecialchars((string) ($example['input'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mt-1"><strong>Résultat :</strong> <?php echo htmlspecialchars((string) ($example['output'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                            <?php if (!empty($example['explanation'])): ?><p class="mt-1"><?php echo htmlspecialchars((string) $example['explanation'], ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </section>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!empty($selectedContent['summary'])): ?>
                        <section class="border-t border-slate-200 pt-5">
                            <h3 class="text-xl font-bold text-slate-900">Résumé</h3>
                            <p class="mt-3 whitespace-pre-line leading-7 text-slate-700"><?php echo htmlspecialchars((string) $selectedContent['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </section>
                    <?php endif; ?>
                <?php elseif ($selectedType === 'quiz'): ?>
                    <ol class="space-y-6">
                        <?php foreach (($selectedContent['questions'] ?? []) as $questionIndex => $question): ?>
                            <?php if (is_array($question)): ?>
                                <li class="border-t border-slate-200 pt-5">
                                    <h3 class="text-lg font-bold text-slate-900"><?php echo ($questionIndex + 1) . '. ' . htmlspecialchars((string) ($question['question'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <ul class="mt-3 space-y-2 text-slate-700">
                                        <?php foreach (($question['choices'] ?? []) as $choice): ?>
                                            <?php if (is_array($choice)): ?>
                                                <li><?php echo htmlspecialchars((string) ($choice['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </article>
        <?php elseif (empty($revisions)): ?>
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
                                <?php if (!empty($revision['Content']) && ($revisionId = (int) ($revision['Id'] ?? 0)) > 0): ?>
                                    <a href="<?php echo function_exists('site_url') ? site_url('revisions', ['id' => $revisionId]) : 'index.php?page=revisions&id=' . $revisionId; ?>" class="inline-flex items-center gap-2 rounded-full bg-indigo-700 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-800 transition">Voir le brouillon</a>
                                <?php endif; ?>
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