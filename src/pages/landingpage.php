<?php
// If this file is requested directly (not included by the router), render a minimal page wrapper
// so the landing page CSS and topbar/footer are loaded and the page looks correct when opened
// as /landingpage.php in a browser.

// Ensure the page class is visible to the router extractor so the landing-specific CSS applies
$page_class = $page_class ?? 'landing-page';

// Bootstrap site helpers when accessed directly so variables like $baseUrl and helpers exist
$direct_access = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));
if ($direct_access) {
    // ─────────────────────────────────────────────
    //  landingpage.php — Vue principale (router)
    // ─────────────────────────────────────────────
    $site_boot = __DIR__ . '/site_boot.php';
    if (!is_file($site_boot)) {
        $site_boot = __DIR__ . '/bootstrap/site_boot.php';
    }
    if (is_file($site_boot)) {
        require_once $site_boot;
    }

    $page_class  = $page_class ?: 'landing-page';
    if (empty($page_css)) {
        $page_css   = 'landingpage.css';
    }
    if (empty($page_title)) {
        $page_title = 'Accueil - MonCoachScolaire';
    }

    ?><!doctype html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?php echo htmlspecialchars($page_title); ?></title>
        <?php
        $root = rtrim($baseUrl ?? '', '/');
    if (!$root) {
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir && $scriptDir !== '.' && $scriptDir !== '/') {
            $root = $scriptDir;
        } else {
            $root = '';
        }
    }
    if ($root !== ''): ?>
          <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/tailwind.css') : ($root . '/assets/css/tailwind.css'); ?>">
          <?php if (!empty($page_css)): ?>
            <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/' . $page_css) : ($root . '/assets/css/pages/' . htmlspecialchars($page_css)); ?>">
          <?php endif; ?>
        <?php else: ?>
          <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/tailwind.css') : 'assets/css/tailwind.css'; ?>">
          <?php if (!empty($page_css)): ?>
            <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/' . $page_css) : ('assets/css/pages/' . htmlspecialchars($page_css)); ?>">
          <?php endif; ?>
        <?php endif; ?>
    </head>
    <body class="app-bg <?php echo htmlspecialchars($page_class ?? ''); ?>">
    <?php
    if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
        require_once dirname(__DIR__, 2) . '/includes/topbar.php';
    }
}

if (is_file(dirname(__DIR__, 2) . '/database/connection.php')) {
    require_once dirname(__DIR__, 2) . '/database/connection.php';
}

if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}

if (is_file(dirname(__DIR__, 2) . '/includes/level_normalization.php')) {
    require_once dirname(__DIR__, 2) . '/includes/level_normalization.php';
}

$page_class = $page_class ?? 'landing-page';
$page_css   = $page_css   ?? 'landingpage.css';

if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('landingpage.php: impossible de démarrer la session — headers déjà envoyés.');
        }
    }
}

$is_logged_in        = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
$is_parent_logged_in = !empty($_SESSION['parent_id']);
$user_level          = $_SESSION['user_level'] ?? '';
$user_name           = $_SESSION['user_name'] ?? 'Élève';
$user_role           = $_SESSION['user_role'] ?? 'student';
$user_level_display  = get_level_display_name($user_level);

$is_admin = false;
if ($is_logged_in && !empty($user_role)) {
    $is_admin = in_array($user_role, ['admin', 'administrator'], true);
}
if (!$is_admin && $is_logged_in && !empty($_SESSION['user_role'])) {
    $is_admin = in_array($_SESSION['user_role'], ['admin', 'administrator'], true);
}
if (!$is_admin && file_exists(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    if (!function_exists('isAdmin')) {
        require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
    }
    if (function_exists('isAdmin')) {
        $is_admin = isAdmin();
    }
}

$is_college = is_college_level($user_level);
$is_lycee   = is_lycee_level($user_level);
?>

    <!-- ═══════════════ CSS LANDING V2 ═══════════════ -->
    <style>
    :root{
      --v:#6C3CE1; --vl:#EDE9FF; --vd:#4f27b3;
      --b:#3B82F6; --or:#F59E0B; --g:#10B981;
      --dk:#0F172A; --gr:#64748B; --gl:#F8FAFF;
      --wh:#FFFFFF; --r:16px; --rs:10px;
    }
    .lp-wrap{font-family:'Plus Jakarta Sans','Inter',system-ui,sans-serif;color:var(--dk);overflow-x:hidden;}
    /* ... CSS landing V2 complet ... */
    </style>
    <!-- ...existing code... -->
  <?php if ($is_logged_in || $is_parent_logged_in): ?>
    <!-- HERO CONNECTÉ -->
    <section class="mx-auto max-w-6xl px-4 lg:px-8 pt-10 lg:pt-16">
      <div class="rounded-3xl bg-white/10 backdrop-blur-sm shadow-lg border border-sky-100 px-6 py-8 md:px-10 md:py-10">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
          <div class="max-w-xl">
            <?php if ($is_admin): ?>
              <p class="text-sm font-semibold uppercase tracking-wide text-sky-700 mb-2">Espace administrateur</p>
              <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-3">
                Pilote tout MonCoachScolaire depuis un seul tableau de bord
              </h1>
              <p class="text-slate-700 text-base md:text-lg">
                Gère les contenus, les exercices, les élèves et les statistiques de la plateforme en quelques clics.
              </p>
            <?php elseif ($is_parent_logged_in): ?>
              <!-- Texte 'Espace parents' supprimé -->
              <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-3">
                Suivez la progression de votre enfant en toute sérénité
              </h1>
              <p class="text-slate-700 text-base md:text-lg">
                Accédez aux résultats, aux activités et aux outils de suivi pour accompagner votre enfant au quotidien.
              </p>
            <?php else: ?>
              <p class="text-sm font-semibold uppercase tracking-wide text-blue-700 mb-2">Espace élève</p>
              <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-3">
                Bienvenue <?php echo htmlspecialchars($user_name); ?> 👋
              </h1>
              <p class="text-slate-700 text-base md:text-lg">
                Tu es connecté en tant qu’élève de <strong class="font-semibold"><?php echo htmlspecialchars($user_level_display); ?></strong>.
                Retrouve ici tes exercices, tes cours et ta progression.
              </p>
            <?php endif; ?>
          </div>

          <div class="relative">
              <div class="h-32 w-32 md:h-40 md:w-40 rounded-3xl shadow-xl border border-white flex items-center justify-center">
                <span class="text-4xl md:text-5xl" aria-hidden="true">
                  <?php if ($is_admin): ?>
                    ⚙️
                  <?php elseif ($is_parent_logged_in): ?>
                    👨‍👩‍👧‍👦
                  <?php else: ?>
                    🎓
                  <?php endif; ?>
                </span>
              </div>
              <div class="absolute -bottom-3 -right-3 rounded-full bg-sky-600 text-white text-xs font-semibold px-3 py-1 shadow-lg">
                En ligne
              </div>
            </div>
          </div>
        </div>

        <!-- MESSAGE CONTEXTE -->
        <div class="mt-6">
          <?php if ($is_admin): ?>
            <div class="bg-gradient-to-br from-amber-50 to-yellow-100 border-l-4 border-amber-400 p-4 md:p-5 rounded-xl text-sm md:text-base text-slate-800">
              <strong class="block mb-1">👋 Salut <?php echo htmlspecialchars($user_name); ?> !</strong>
              Tu es connecté en tant qu’<strong>administrateur</strong>. Tu as accès à tous les niveaux, exercices et cours de l’application.
            </div>
          <?php elseif ($is_parent_logged_in): ?>
            <div class="bg-gradient-to-br from-emerald-50 to-green-100 border-l-4 border-emerald-400 p-4 md:p-5 rounded-xl text-sm md:text-base text-slate-800">
              <strong class="block mb-1">👋 Bonjour <?php echo htmlspecialchars($user_name); ?> !</strong>
              Tu es connecté en tant que <strong>parent</strong>. Accède rapidement aux ressources de suivi de tes enfants ci-dessous.
            </div>
          <?php else: ?>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border-l-4 border-blue-400 p-4 md:p-5 rounded-xl text-sm md:text-base text-slate-800">
              <strong class="block mb-1">👋 Salut <?php echo htmlspecialchars($user_name); ?> !</strong>
              Tu es connecté en tant qu’élève de <strong><?php echo htmlspecialchars($user_level_display); ?></strong>.
              Accède rapidement à tes ressources ci-dessous.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- MENUS CONNECTÉS -->
    <section class="mx-auto max-w-6xl px-4 lg:px-8 pb-12 pt-6">
      <?php if ($is_admin): ?>
        <!-- Menu Admin -->
        <div class="mb-10">
          <h2 class="text-xl font-bold text-center text-slate-900 mb-4">⚙️ Menu administrateur</h2>
          <div class="flex flex-wrap gap-3 justify-center">
            <a href="<?php echo site_url('admin/dashboard'); ?>" class="px-5 py-2.5 rounded-lg bg-blue-700 text-white text-sm font-semibold shadow hover:bg-blue-800 transition">
              🏠 Dashboard Admin
            </a>
            <a href="<?php echo site_url('admin/content'); ?>" class="px-5 py-2.5 rounded-lg border border-blue-200 text-blue-700 text-sm font-semibold shadow-sm hover:bg-blue-50 transition">
              📚 Gestion contenu
            </a>
            <a href="<?php echo site_url('admin/stats'); ?>" class="px-5 py-2.5 rounded-lg border border-blue-200 text-blue-700 text-sm font-semibold shadow-sm hover:bg-blue-50 transition">
              📊 Statistiques
            </a>
            <a href="<?php echo site_url('logout'); ?>" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold shadow-sm hover:bg-slate-50 transition">
              🚪 Déconnexion
            </a>
          </div>
        </div>

      <?php elseif ($is_parent_logged_in): ?>
        <!-- Menu Parent -->
        <div class="mb-10">
          <h2 class="text-xl font-bold text-center text-slate-900 mb-4">👨‍👩‍👧‍👦 Menu parents</h2>
          <div class="flex flex-wrap gap-3 justify-center">
            <!-- Bouton et texte '🏠 Espace parents' supprimés -->
              <!-- '🏠 Espace parents' supprimé -->
            </a>
            <a href="<?php echo site_url('parents/dashboard_parent'); ?>" class="px-5 py-2.5 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-semibold border border-emerald-200 shadow-sm hover:bg-emerald-100 transition">
              📊 Dashboard parent
            </a>
            <a href="<?php echo site_url('parents/suivi_enfant'); ?>" class="px-5 py-2.5 rounded-lg bg-blue-50 text-blue-700 text-sm font-semibold border border-blue-200 shadow-sm hover:bg-blue-100 transition">
              📈 Suivi enfant
            </a>
            <?php
        // Afficher un bouton de suivi pour chaque enfant du parent
        if (isset($pdo) && !empty($_SESSION['parent_id'])) {
            $stmt = $pdo->prepare("SELECT Id, Prenom, Nom, Username FROM users WHERE ParentId = ? AND Role = 'student' ORDER BY Prenom, Nom");
            $stmt->execute([$_SESSION['parent_id']]);
            $enfants = $stmt->fetchAll();
            foreach ($enfants as $enfant) {
                $enfantName = trim(($enfant['Prenom'] ?? '') . ' ' . ($enfant['Nom'] ?? ''));
                if (empty($enfantName)) {
                    $enfantName = $enfant['Username'] ?? 'Enfant';
                }
                $url = site_url('parents/suivi_enfant') . '&id=' . $enfant['Id'];
                echo '<a href="' . htmlspecialchars($url) . '" class="px-5 py-2.5 rounded-lg border border-emerald-200 text-emerald-700 text-sm font-semibold shadow-sm hover:bg-emerald-50 transition">📈 Suivi de ' . htmlspecialchars($enfantName) . '</a>';
            }
        }
          ?>
            <a href="<?php echo site_url('logout'); ?>" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold shadow-sm hover:bg-slate-50 transition">
              🚪 Déconnexion
            </a>
          </div>
        </div>

      <?php else: ?>
        <!-- Menu Élève -->
        <div class="mb-10">
          <h2 class="text-xl font-bold text-center text-slate-900 mb-4">👦 Menu élève</h2>
          <div class="flex flex-wrap gap-3 justify-center">
            <a href="<?php echo site_url('eleve/dashboard'); ?>" class="px-5 py-2.5 rounded-lg bg-green-700 text-white text-sm font-semibold shadow hover:bg-green-800 transition">
              🏠 Dashboard élève
            </a>
            <a href="<?php echo site_url('exercices'); ?>" class="px-5 py-2.5 rounded-lg bg-green-50 text-green-800 text-sm font-semibold border border-green-200 shadow-sm hover:bg-green-100 transition">
              ✏️ Exercices
            </a>
            <a href="<?php echo site_url('cours'); ?>" class="px-5 py-2.5 rounded-lg border border-green-200 text-green-800 text-sm font-semibold shadow-sm hover:bg-green-50 transition">
              📚 Cours
            </a>
            <a href="<?php echo site_url('diagnostic'); ?>" class="px-5 py-2.5 rounded-lg border border-green-200 text-green-800 text-sm font-semibold shadow-sm hover:bg-green-50 transition">
              🧪 Diagnostic
            </a>
            <a href="<?php echo site_url('system/progression'); ?>" class="px-5 py-2.5 rounded-lg border border-green-200 text-green-800 text-sm font-semibold shadow-sm hover:bg-green-50 transition">
              📈 Progression
            </a>
            <a href="<?php echo site_url('logout'); ?>" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-700 text-sm font-semibold shadow-sm hover:bg-slate-50 transition">
              🚪 Déconnexion
            </a>
          </div>
        </div>
      <?php endif; ?>
    </section>
  <?php else: ?>
  <!-- VISITEUR NON CONNECTÉ -->

  <!-- HEADER HERO VISITEUR -->
  <header class="flex flex-col gap-2 items-center justify-center text-center py-10 px-4 pb-14 mx-auto my-8 max-w-5xl backdrop-blur-md rounded-2xl shadow-xl relative" role="banner" aria-label="Intro" style="background-color: rgba(255, 255, 255, 0.15); background-image: none;">
    <h1 class="m-0 max-w-4xl text-4xl md:text-6xl font-bold text-slate-800 leading-tight">
      Bienvenue sur MonCoachScolaire&nbsp;!
    </h1>
    <p class="m-0 max-w-2xl text-lg text-slate-700 font-medium">
      Ton coach pédagogique personnalisé pour réussir toute ta scolarité
    </p>
  </header>

  <!-- Choix du niveau -->
  <section class="mx-auto max-w-6xl px-4 lg:px-8 pt-10 lg:pt-14">
    <div class="text-center mb-8">
      <p class="text-sm font-semibold tracking-wide text-sky-700 uppercase">
        Découvrir la plateforme
      </p>
      <h1 class="mt-2 text-3xl md:text-4xl font-extrabold text-slate-900">
        Choisis ton niveau scolaire
      </h1>

    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-4">
      <!-- Card Collège+ -->
      <article class="card-niveau rounded-2xl shadow-lg p-8 flex flex-col items-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <div class="card-media mb-5">
          <img
            src="assets/img/college/photo-1547082634-4886fd241406.avif"
            alt="Collège"
            class="rounded-full w-32 h-32 md:w-40 md:h-40 object-cover shadow-md border-4 border-green-300"
          >
        </div>
        <header class="card-header flex flex-col items-center mb-3">

          <a
            href="<?php echo site_url('eleve/college/college-accueil'); ?>"
            class="card-cta bg-green-600 text-white px-6 py-2 rounded-full text-sm font-bold shadow border border-green-600 transition-all duration-300 hover:bg-green-700 hover:shadow-lg hover:-translate-y-0.5"
          >
            Accéder à Collège+
          </a>
          <h3 class="text-2xl font-bold mt-3">Collège+</h3>
        </header>
        <div class="card-content text-sm md:text-base text-slate-700 text-center">
          <p>De la 6<sup>ème</sup> à la 3<sup>ème</sup>.</p>
          <ul class="list-disc list-inside mt-2 space-y-1">
            <li>Cours interactifs adaptés à ton âge</li>
            <li>Quiz et exercices ludiques</li>
            <li>Préparation au Brevet</li>
          </ul>
        </div>
      </article>

      <!-- Card Lycée+ -->
      <article class="card-niveau rounded-2xl shadow-lg p-8 flex flex-col items-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
        <div class="card-media mb-5">
          <img
            src="assets/img/lycee/lycee.avif"
            alt="Lycée"
            class="rounded-full w-32 h-32 md:w-40 md:h-40 object-cover shadow-md border-4 border-violet-400"
          >
        </div>
        <header class="card-header flex flex-col items-center mb-3">
          <a
            href="<?php echo site_url('eleve/lycee/lycee-accueil'); ?>"
            class="card-cta bg-gradient-to-r from-violet-700 via-purple-500 to-fuchsia-600 text-white px-6 py-2 rounded-full text-sm font-bold shadow border border-violet-700 transition-all duration-300 hover:shadow-violet-400/50 hover:-translate-y-0.5"
          >
            Accéder à Lycée+
          </a>
          <h3 class="text-2xl font-bold mt-3">Lycée+</h3>
        </header>
        <div class="card-content text-sm md:text-base text-slate-700 text-center">
          <p>De la Seconde à la Terminale.</p>
          <ul class="list-disc list-inside mt-2 space-y-1">
            <li>Cours approfondis par matière</li>
            <li>Méthodologie et organisation</li>
            <li>Préparation aux épreuves du Bac</li>
          </ul>
        </div>
      </article>

      <!-- Card BAC -->
      <article class="card-niveau rounded-2xl shadow-lg bg-gradient-to-br from-yellow-400 to-purple-600 p-8 flex flex-col items-center transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
        <div class="card-media mb-5">
          <img
            src="assets/img/bac/bac.webp"
            alt="BAC"
            class="rounded-full w-32 h-32 md:w-40 md:h-40 object-cover shadow-md border-4 border-yellow-400"
          >
        </div>
        <header class="card-header flex flex-col items-center mb-3">
          <a
            href="<?php echo site_url('eleve/bac/bac-accueil'); ?>"
            class="card-cta bg-yellow-400 text-slate-900 px-6 py-2 rounded-full text-sm font-bold shadow border border-yellow-500 transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5"
          >
            Préparer le BAC
          </a>
          <h3 class="text-2xl font-bold mt-3 text-white">Préparer le BAC</h3>
        </header>
        <div class="card-content text-sm md:text-base text-white text-center">
          <p>Préparation intensive au Baccalauréat.</p>
          <ul class="list-disc list-inside mt-2 space-y-1">
            <li>Révisions ciblées par spécialité</li>
            <li>Sujets types et corrigés</li>
            <li>Coaching personnalisé</li>
          </ul>
        </div>
      </article>
    </div>
  </section>

  <!-- CTA DÉMO -->
  <section class="mx-auto max-w-6xl px-4 lg:px-8 mt-4">
    <div class="rounded-3xl shadow-lg border border-slate-100 p-7 md:p-8 flex flex-col items-center text-center">
      <h2 class="text-2xl font-bold mb-2 text-slate-900">
        🎮 Testez MonCoachScolaire gratuitement
      </h2>
      <p class="mb-4 text-slate-700 text-sm md:text-base">
        Accédez à une sélection d'exercices interactifs, de cours et de quiz
        pour découvrir la plateforme avant de créer votre compte.
      </p>
      <a
        href="<?php echo site_url('demo', ['demo' => '1']); ?>"
        class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold shadow hover:bg-blue-700 transition js-start-demo"
      >
        ✨ Essayer la démo
      </a>
    </div>
  </section>

  <!-- POURQUOI MCS -->
  <section class="mx-auto max-w-6xl px-4 lg:px-8 my-12">
    <header class="text-center mb-6">
      <h2 class="text-2xl md:text-3xl font-bold text-slate-900">
        Pourquoi choisir MonCoachScolaire&nbsp;?
      </h2>
      <p class="mt-3 text-base md:text-lg text-slate-700 max-w-2xl mx-auto">
        Une plateforme pensée pour t'aider à comprendre tes cours, garder la motivation
        et préparer tes examens dans les meilleures conditions.
      </p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <article class="rounded-2xl shadow-lg p-6 flex flex-col items-center text-center">
        <div class="mascotte-img mb-4">
          <picture>
            <source srcset="assets/img/lycee/photo-1558021211-6d1403321394.avif" type="image/avif">
            <img
              src="assets/img/lycee/photo-1558021211-6d1403321394.avif"
              alt="Interactif"
              width="100"
              height="180"
              loading="lazy"
              class="rounded-xl object-cover"
            >
          </picture>
        </div>
        <h3 class="text-lg font-semibold mb-2">100&nbsp;% interactif</h3>
        <p class="text-sm text-slate-700">
          Des activités dynamiques, des quiz et des supports visuels pour apprendre en s'amusant.
        </p>
      </article>

      <article class="rounded-2xl shadow-lg p-6 flex flex-col items-center text-center">
        <div class="mascotte-img mb-4">
          <picture>
            <source srcset="assets/img/lycee/photo-1519389950473-47ba0277781c.avif" type="image/avif">
            <img
              src="assets/img/lycee/photo-1519389950473-47ba0277781c.avif"
              alt="Coaching"
              width="100"
              height="100"
              loading="lazy"
              class="rounded-xl object-cover"
            >
          </picture>
        </div>
        <h3 class="text-lg font-semibold mb-2">Coaching personnalisé</h3>
        <p class="text-sm text-slate-700">
          Un accompagnement sur-mesure pour progresser à ton rythme, avec des conseils adaptés à chaque élève.
        </p>
      </article>

      <article class="rounded-2xl shadow-lg p-6 flex flex-col items-center text-center">
        <div class="mascotte-img mb-4">
          <picture>
            <source srcset="assets/img/bac/photo-1706901786647-206a5cc89063.avif" type="image/avif">
            <img
              src="assets/img/bac/photo-1706901786647-206a5cc89063.avif"
              alt="Sécurité"
              width="100"
              height="100"
              loading="lazy"
              class="rounded-xl object-cover"
            >
          </picture>
        </div>
        <h3 class="text-lg font-semibold mb-2">Respect &amp; sécurité</h3>
        <p class="text-sm text-slate-700">
          Un espace bienveillant, sans collecte de données inutiles,
          pour garantir la confidentialité et la sécurité de tous.
        </p>
      </article>
    </div>
  </section>

  <!-- PARENTS / ENCADRANTS -->
  <section class="mx-auto max-w-6xl px-4 lg:px-8 mb-14">
    <h2 class="text-2xl md:text-3xl font-bold text-center text-slate-900 mb-8">
      Parents ou encadrants&nbsp;: un environnement sécurisé pour les mineurs
    </h2>

    <div class="flex flex-col items-center justify-center gap-8">
      <!-- Card centrale Protection -->
      <article class=" rounded-2xl shadow-lg p-8 max-w-xl w-full text-center flex flex-col items-center">
        <div class="mb-4">
          <svg width="90" height="90" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="60" cy="60" r="60" fill="#E3F2FD"/>
            <path d="M60 25L35 35V55C35 70 45 85 60 90C75 85 85 70 85 55V35L60 25Z" fill="#3498db" stroke="#2980b9" stroke-width="2"/>
            <circle cx="60" cy="60" r="12" fill="#2980b9"/>
            <path d="M56 60L59 63L64 58" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M50 50L45 45M70 50L75 45M50 70L45 75M70 70L75 75" stroke="#2980b9" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-emerald-700 mb-2">Protection</h3>
        <p class="text-sm md:text-base text-slate-700 mb-1">
          Aucune donnée personnelle superflue n'est collectée.
        </p>
        <p class="text-sm md:text-base text-slate-700">
          L'espace est conçu pour protéger les mineurs et respecter leur vie privée.
        </p>
      </article>

      <!-- Mini-cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 w-full max-w-4xl">
        <article class="bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm p-4 flex flex-col items-center text-center">
          <strong class="text-emerald-800 mb-1 text-sm md:text-base">Protection des données</strong>
          <p class="text-slate-700 text-xs md:text-sm">
            Nous collectons uniquement les informations strictement nécessaires au bon fonctionnement du service.
          </p>
        </article>
        <article class="bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm p-4 flex flex-col items-center text-center">
          <strong class="text-emerald-800 mb-1 text-sm md:text-base">Environnement sécurisé</strong>
          <p class="text-slate-700 text-xs md:text-sm">
            Contrôle parental intégré et modération active pour garantir un espace sûr pour les mineurs.
          </p>
        </article>
        <article class="bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm p-4 flex flex-col items-center text-center">
          <strong class="text-emerald-800 mb-1 text-sm md:text-base">Respect de la vie privée</strong>
          <p class="text-slate-700 text-xs md:text-sm">
            Aucune publicité ciblée, pas de partage de données avec des tiers, conformément au RGPD.
          </p>
        </article>
        <article class="bg-emerald-50 border border-emerald-200 rounded-xl shadow-sm p-4 flex flex-col items-center text-center">
          <strong class="text-emerald-800 mb-1 text-sm md:text-base">Transparence totale</strong>
          <p class="text-slate-700 text-xs md:text-sm">
            Vous pouvez à tout moment consulter, modifier ou supprimer les données de votre enfant.
          </p>
        </article>
      </div>
    </div>
  </section>
<?php endif; ?>

</main>


<?php // footer is provided by the router (index.php)?>
<?php
// Inclure le footer systématiquement
if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
    include_once dirname(__DIR__, 2) . '/includes/footer.php';
}
?>
