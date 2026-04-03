<?php
$page_title = 'Exercices Collège - MonCoachScolaire';
$page_css = 'college/exercices-college.css';

if (is_file(dirname(__DIR__, 3) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 3) . '/config/site_boot.php';
}
if (is_file(dirname(__DIR__, 2) . '/includes/exercice_loader.php')) {
    require_once dirname(__DIR__, 2) . '/includes/exercice_loader.php';
}
if (is_file(dirname(__DIR__, 2) . '/includes/level_navigation.php')) {
    require_once dirname(__DIR__, 2) . '/includes/level_navigation.php';
}

// ========== VERIFICATION ACCES ==========
$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();
$has_access = !empty($is_logged_in) || $is_admin || $is_demo;
?>
<main class="main-content max-w-6xl mx-auto px-6 py-10">
    <?php echo render_level_navigation('6ème', 'exercices'); ?>
    <div class="header text-center mb-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 text-white p-8 shadow-lg">
        <h1 class="text-4xl font-bold">📚 Exercices Collège</h1>
        <p class="subtitle text-lg text-white/90">Choisis ton niveau et commence à t'entraîner</p>
    </div>

    <?php if (!$has_access): ?>
        <!-- Aperçu exercices + panneau coach harmonisé (visiteur uniquement) -->
        <section class="exercises-section mt-8">
            <div class="exercises-grid grid gap-6">
                <!-- ...existing code... -->
            </div>
        </section>
        <div class="coach-preview text-center bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl p-8 shadow-lg mt-12">
            <h2 class="text-2xl font-bold text-slate-800">🔒 Accède à des centaines d'exercices interactifs</h2>
            <p class="text-slate-600">Découvre ci-dessous un aperçu de nos exercices, mais pour t'entraîner et progresser avec un suivi personnalisé, crée ton compte gratuit !</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8">
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">📚 Exercices Multi-niveaux</h3>
                    <p class="text-slate-600">Du collège au BAC : 6ème, 5ème, 4ème, 3ème, Seconde, Première, Terminale</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">✅ Corrections Détaillées</h3>
                    <p class="text-slate-600">Chaque exercice avec sa correction expliquée pas à pas</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">📊 Suivi de Progression</h3>
                    <p class="text-slate-600">Statistiques personnalisées pour suivre tes progrès</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">🎯 Exercices Adaptatifs</h3>
                    <p class="text-slate-600">Système qui s'adapte à ton rythme et tes difficultés</p>
                </div>
            </div>
            <p><strong>💡 Plus tu t'entraînes, plus tu progresses !</strong></p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6">
                <a href="<?php echo site_url('register'); ?>" class="inline-flex items-center gap-2 justify-center px-6 py-3 rounded-lg bg-green-600 text-white font-semibold shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 transition">✨ Créer mon compte gratuit</a>
                <a href="<?php echo site_url('login'); ?>" class="inline-flex items-center gap-2 justify-center px-6 py-3 rounded-lg bg-white text-green-700 font-semibold border border-green-300 shadow hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-300 transition">🔑 Me connecter</a>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $levels = ['6ème','5ème','4ème','3ème'];
// Selected subject from query param (optional)
$selectedSubject = isset($_GET['subject']) && strlen(trim($_GET['subject'])) ? trim($_GET['subject']) : null;

$all = [];
foreach ($levels as $lvl) {
    try {
        $exs = getExercisesByLevel($lvl, $selectedSubject);
    } catch (Exception $e) {
        $exs = [];
    }
    $all[$lvl] = $exs;
}
// Subject selector?>
    <?php if (!empty($is_logged_in) && function_exists('getSubjectsByLevels')): ?>
        <?php $availableSubjects = getSubjectsByLevels($levels); ?>
        <div class="subject-filter max-w-4xl mx-auto mt-3 flex justify-center">
            <form method="get" id="subject-filter-form">
                <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? 'college/exercices-college'); ?>">
                <label for="subject-select" class="mr-2 font-semibold self-center text-slate-800">Choisir une matière :</label>
                <select id="subject-select" name="subject" onchange="document.getElementById('subject-filter-form').submit()" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-800">
                    <option value="">Toutes les matières</option>
                    <?php foreach ($availableSubjects as $sub): ?>
                        <option value="<?php echo htmlspecialchars($sub); ?>" <?php echo ($selectedSubject === $sub) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>

    <section class="exercises-section mt-8">
        <div class="exercises-grid grid gap-6">
            <?php foreach ($all as $levelName => $exList): ?>
                <h2 class="text-2xl font-bold text-slate-800 mt-4 mb-2"><?php echo htmlspecialchars($levelName); ?></h2>
                <?php if (!empty($exList)): ?>
                    <?php foreach ($exList as $ex): ?>
                        <article class="exercise-card pro-card bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-slate-200 flex flex-col" id="exercise-<?php echo (int) $ex['Id']; ?>">
                            <div class="card-head flex items-start justify-between gap-4 mb-2">
                                <div class="flex flex-col gap-1">
                                    <div class="card-title text-xl font-bold text-green-700 mb-1 flex items-center gap-2">
                                        <span class="inline-block text-2xl">📖</span>
                                        <?php echo htmlspecialchars($ex['Title']); ?>
                                    </div>
                                    <div class="card-meta flex flex-wrap items-center gap-2 text-sm">
                                        <span class="badge badge-difficulty <?php echo(strtolower($ex['Difficulty']) === 'facile' ? 'easy' : (strtolower($ex['Difficulty']) === 'difficile' ? 'hard' : 'medium')); ?> px-2 py-1 rounded-full font-semibold text-white <?php echo(strtolower($ex['Difficulty']) === 'facile' ? 'bg-green-500' : (strtolower($ex['Difficulty']) === 'difficile' ? 'bg-red-500' : 'bg-yellow-500')); ?>">
                                            <?php echo htmlspecialchars($ex['Difficulty']); ?>
                                        </span>
                                        <span class="badge badge-subject bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-semibold">
                                            <?php echo htmlspecialchars($ex['Subject'] ?? ''); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-actions flex flex-col items-end gap-2">
                                    <button class="btn-exercise px-4 py-2 rounded-lg bg-green-600 text-white font-semibold shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 transition" data-action="start-exercise" data-id="<?php echo (int) $ex['Id']; ?>">Commencer</button>
                                    <a href="<?php echo site_url('view_exercise', ['id' => $ex['Id']]); ?>" class="btn-outline px-4 py-2 rounded-lg border border-green-300 text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-300 transition">Ouvrir</a>
                                </div>
                            </div>
                            <div class="card-content mt-2 text-slate-700 flex-1">
                                <p class="leading-relaxed text-base mb-2 line-clamp-4" style="min-height:60px;">
                                    <?php echo nl2br(htmlspecialchars(substr($ex['Content'] ?? '', 0, 400))); ?><?php if (strlen($ex['Content'] ?? '') > 400) {
                                        echo '...';
                                    } ?>
                                </p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <button class="btn-outline px-3 py-1 rounded-lg border border-blue-300 text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-300 transition" data-action="verify-exercise">Vérifier mes réponses</button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-slate-500">Aucun exercice pour ce niveau.</p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>

    <?php ?>

    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/exercises.js') : 'assets/js/exercises.js'; ?>"></script>

</main>
