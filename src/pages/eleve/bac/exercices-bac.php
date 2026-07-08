<?php
$page_css = 'bac/exercices-bac.css';
$page_class = 'page-exercices-bac';
$page_theme_level = 'bac';

$srcRoot = dirname(__DIR__, 3);
if (is_file($srcRoot . '/config/site_boot.php')) {
    require_once $srcRoot . '/config/site_boot.php';
}
$bac_theme = $GLOBALS['app_theme']['variant'] ?? (function_exists('get_theme_variant_by_level') ? get_theme_variant_by_level('bac') : []);
$header_cover = htmlspecialchars($bac_theme['cover'] ?? 'bg-gradient-to-br from-amber-100 to-yellow-50', ENT_QUOTES, 'UTF-8');
$header_title = htmlspecialchars($bac_theme['title'] ?? 'text-amber-700', ENT_QUOTES, 'UTF-8');
$header_subtitle = htmlspecialchars($bac_theme['subtitle'] ?? 'text-amber-800', ENT_QUOTES, 'UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========== VERIFICATION ACCES ==========
$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();
$has_access = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']) || $is_admin || $is_demo;

if (!$has_access) {
    ?>
    <main class="main-content page-centered-container max-w-6xl mx-auto px-6 py-10">
        <div class="header text-center mb-8 rounded-xl <?php echo $header_cover; ?> p-8 shadow-lg border-b-4 border-amber-400">
            <h1 class="text-4xl font-bold <?php echo $header_title; ?>">🏆 Exercices BAC - Préparation Intensive</h1>
            <p class="subtitle text-lg <?php echo $header_subtitle; ?>">Programme 2025 | Décroche ton Baccalauréat avec confiance</p>
        </div>

        <div class="coach-preview text-center bg-white/80 rounded-2xl p-8 shadow-lg">
            <h2 class="text-2xl font-bold text-slate-800">🔒 Débloque ton Coach Scolaire Personnalisé</h2>
            <p class="text-slate-700">
                Tu vois ici un aperçu des exercices disponibles, mais pour accéder à ton coach personnel,
                à ses conseils adaptés à ton niveau, et à l'accompagnement complet, tu dois créer un compte gratuit !
            </p>

            <div class="preview-features grid grid-cols-1 md:grid-cols-2 gap-6 my-6">
                <div class="preview-feature bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-2">👨‍🏫 Coach Personnel</h3>
                    <p class="text-sm text-slate-600">Messages motivants et conseils adaptés à TON niveau et TES besoins spécifiques</p>
                </div>
                <div class="preview-feature bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-2">📊 Suivi Personnalisé</h3>
                    <p class="text-sm text-slate-600">Dashboard avec tes progrès, statistiques, et recommandations sur mesure</p>
                </div>
                <div class="preview-feature bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-2">🎯 Exercices Adaptés</h3>
                    <p class="text-sm text-slate-600">Contenu qui s'ajuste à tes forces et faiblesses pour maximiser tes progrès</p>
                </div>
                <div class="preview-feature bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-slate-800 mb-2">🏆 Récompenses & Badges</h3>
                    <p class="text-sm text-slate-600">Système de gamification pour te motiver et célébrer tes victoires</p>
                </div>
            </div>

            <p class="text-slate-700 font-semibold">💡 Le coach s'adapte à ton cursus scolaire et te donne des conseils pédagogiques personnalisés !</p>

            <div class="cta-actions flex flex-wrap gap-4 justify-center mt-6">
                 <a href="<?php echo function_exists('site_url') ? site_url('register') : 'index.php?page=register'; ?>" class="coach-cta inline-flex items-center justify-center px-6 py-3 rounded-lg bg-amber-600 text-white font-semibold hover:bg-amber-700 transition-colors">✨ Créer mon compte gratuit</a>
                 <a href="<?php echo function_exists('site_url') ? site_url('login') : 'index.php?page=login'; ?>" class="coach-cta secondary inline-flex items-center justify-center px-6 py-3 rounded-lg bg-slate-600 text-white font-semibold hover:bg-slate-700 transition-colors">🔑 Me connecter</a>
            </div>
        </div>
    </main>
<?php
    return;
}
?>
<main class="main-content page-centered-container max-w-6xl mx-auto px-6 py-10">
    <div class="header text-center mb-8 rounded-xl <?php echo $header_cover; ?> p-8 shadow-lg border-b-4 border-amber-400">
        <h1 class="text-4xl font-bold <?php echo $header_title; ?>">🏆 Exercices BAC - Préparation Intensive</h1>
        <p class="subtitle text-lg <?php echo $header_subtitle; ?>">Programme 2025 | Décroche ton Baccalauréat avec confiance</p>
    </div>
    <?php // Subject selector for BAC?>
    <?php if (!empty($is_logged_in) && function_exists('getSubjectsByLevels')): ?>
        <?php $availableSubjects = getSubjectsByLevels('Terminale');
        $selectedSubject = $_GET['subject'] ?? ''; ?>
        <div class="subject-filter max-w-4xl mx-auto mt-3 flex justify-center">
            <form method="get" id="subject-filter-form-bac">
                <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? 'bac/exercices-bac'); ?>">
            <label for="subject-select-bac" class="mr-2 font-semibold self-center text-slate-800">Choisir une matière :</label>
            <select id="subject-select-bac" name="subject" onchange="document.getElementById('subject-filter-form-bac').submit()" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-800">
                    <option value="">Toutes les matières</option>
                    <?php foreach ($availableSubjects as $sub): ?>
                        <option value="<?php echo htmlspecialchars($sub); ?>" <?php echo ($selectedSubject === $sub) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>

    <div class="coach-message">
        <strong>🏆 Ton Coach est à tes côtés pour le BAC !</strong><br>
        Le BAC, c'est l'objectif ultime ! Maîtrise, synthèse, préparation examen...<br>
        Chaque exercice te rapproche du diplôme. Tu vas devenir un expert dans toutes les matières.<br>
        L'examen du BAC n'aura plus de secrets pour toi ! Montre-moi de quoi tu es capable ! 📚
    </div>
    <div class="motivation-box">
        <h4>🎯 Ta Préparation au BAC</h4>
        <p>Maîtrise chaque matière en résolvant des exercices types !<br>Chaque compétence acquise est une marche vers le diplôme.</p>
        <div class="progress-section">
            <div class="progress-item">
                <h5>📊 Progression Globale des Exercices</h5>
                <div class="progress-bar">
                    <div class="progress-fill" id="globalProgress"></div>
                </div>
                <p><strong>Exercices terminés : <span id="progressText">0%</span></strong></p>
            </div>
        </div>
    </div>
    <!-- Conteneur dynamique pour les exercices BAC -->
    <?php $dataSubjects = !empty($availableSubjects) ? implode(',', $availableSubjects) : '';
$selectedSubjectEsc = isset($selectedSubject) ? htmlspecialchars($selectedSubject) : ''; ?>
    <div id="dynamic-exercises-container" data-dynamic-exercises data-level="Terminale" data-subjects="<?php echo htmlspecialchars($dataSubjects); ?>" data-selected-subject="<?php echo $selectedSubjectEsc; ?>"></div>
    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/dynamic-exercises.js') : 'assets/js/dynamic-exercises.js'; ?>"></script>
    <script>
        // Initialisation du système dynamique
        document.addEventListener('DOMContentLoaded', function() {
            if (window.DynamicExercises && typeof window.DynamicExercises.initAll === 'function') {
                window.DynamicExercises.initAll();
            }
        });
    </script>
    <!-- Coach WebM -->
    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/coach-webm.js') : 'assets/js/coach-webm.js'; ?>"></script>
    <style>
      .coach-overlay {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 250px;
        height: auto;
        z-index: 9999;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.5s ease-out;
      }
      .coach-overlay.active {
        opacity: 1;
        animation: slideInUp 0.6s ease-out;
      }
      .coach-overlay video {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        background: transparent;
      }
      @keyframes slideInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
      }
      @media (max-width: 480px) {
        .coach-overlay { width: 180px; bottom: 10px; right: 10px; }
      }
      .colibri-mascot-container,
      .colibri-mascot-global,
      [data-colibri],
      [data-colibri-global] { display: none !important; }
    </style>

<script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/exercises.js') : 'assets/js/exercises.js'; ?>"></script>

</main>

