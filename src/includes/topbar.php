<?php
if (!ob_get_level()) {
    ob_start();
}
// Protection contre toute sortie accidentelle avant les headers

// Correction : forcer le buffer et l’encodage UTF-8 sur la topbar
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
} else {
    error_log('topbar.php : impossible d’envoyer le header Content-Type, headers déjà envoyés.');
}

// PREVENT TOPBAR IN API REQUESTS
if (defined('IS_API_REQUEST') && IS_API_REQUEST) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    return;
}

// Éviter double inclusion (routeur + page à accès direct)
if (!empty($GLOBALS['__topbar_included'])) {
    return;
}

// topbar.php — central topbar include
// Expected optional flags defined by the caller before include:
//  - $hide_topbar : if true, do not render the topbar
//  - $hide_topbar_persona : if true, show only the site name (no persona/level)

// Ne jamais skip la topbar sur les pages admin (ADMIN_PAGE_ACTIVE)
if ((defined('ADMIN_PAGE_ACTIVE') && ADMIN_PAGE_ACTIVE) === false && (!empty($hide_topbar) || isset($GLOBALS['SKIP_TOPBAR']))) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    return;  // Skip pour login.php ET pages sensibles
}

// La session doit être démarrée par le routeur (index.php).
if (function_exists('ensure_session_started')) {
    ensure_session_started();
}

// CSRF token global (pour appels fetch)
if (file_exists(dirname(__DIR__) . '/includes/login_security.php')) {
    require_once dirname(__DIR__) . '/includes/login_security.php';
    if (function_exists('generateCSRFToken')) {
        $csrfToken = generateCSRFToken();
        echo '<script>window.csrfToken = ' . json_encode($csrfToken) . ';</script>';
    }
}

// Charger site_boot.php si pas déjà chargé pour avoir accès à site_url() et detectBaseUrl()
if (!function_exists('site_url') || !function_exists('detectBaseUrl')) {
    // Fichiers dans src/config/
    if (file_exists(dirname(__DIR__, 2) . '/config/site_boot.php')) {
        require_once dirname(__DIR__, 2) . '/config/site_boot.php';
    }
    // Charger aussi config.php pour avoir detectBaseUrl()
    if (!function_exists('detectBaseUrl') && file_exists(dirname(__DIR__, 2) . '/config/config.php')) {
        require_once dirname(__DIR__, 2) . '/config/config.php';
    }
}

// Resolve base url so topbar links work from nested pages (utiliser detectBaseUrl si disponible)
if (function_exists('detectBaseUrl')) {
    $rootHref = rtrim(detectBaseUrl(), '/');
} else {
    $rootHref = isset($baseUrl) ? rtrim($baseUrl, '/') : '';
}

// Vérifier la connexion : user_id ET logged_in doivent être présents
// Utiliser $is_logged_in de site_boot.php si disponible (plus fiable car site_boot.php est chargé avant)
if (isset($is_logged_in) && $is_logged_in) {
    // Si $is_logged_in est défini et true, utiliser cette valeur
    $is_user_logged_in = $is_logged_in;
} else {
    // Sinon, vérifier directement la session (fallback)
    $is_user_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
}
// Détection parent : vérifier le rôle 'parent' OU 'parents' (pluriel)
$is_parent_logged_in = !empty($_SESSION['user_id']) && in_array(strtolower((string) ($_SESSION['user_role'] ?? '')), ['parent', 'parents'], true);
$user_level = $_SESSION['user_level'] ?? '';
$current_theme = $_SESSION['user_theme_name'] ?? '';

// Charger la normalisation des niveaux pour affichage propre
if (!function_exists('get_level_display_name')) {
    if (file_exists(dirname(__DIR__) . '/includes/level_normalization.php')) {
        require_once dirname(__DIR__) . '/includes/level_normalization.php';
    }
}

// Normaliser le niveau pour affichage (corrige les problèmes d'encodage)
$user_level_display = function_exists('get_level_display_name') ? get_level_display_name($user_level) : $user_level;

// Vérifier si l'utilisateur est admin (nécessite admin_auth.php)
$is_admin_logged_in = false;
if ($is_user_logged_in) {
    // Charger admin_auth.php (dans src/includes)
    if (!function_exists('isAdmin')) {
        try {
            // S'assurer que config.php est chargé pour la DB et helpers
            if (!isset($pdo) && file_exists(dirname(__DIR__, 2) . '/config/config.php')) {
                require_once dirname(__DIR__, 2) . '/config/config.php';
            }
            if (file_exists(dirname(__DIR__) . '/includes/admin_auth.php')) {
                require_once dirname(__DIR__) . '/includes/admin_auth.php';
            }
        } catch (Exception $e) {
            // Continuer sans interrompre le rendu de la topbar
        }
    }
    if (function_exists('isAdmin')) {
        $is_admin_logged_in = isAdmin();
    }
}

// Déterminer l'URL du dashboard selon le type d'utilisateur
// Déterminer l'URL d'accueil selon le niveau scolaire
$dashboard_url = '';
if ($is_admin_logged_in) {
    $dashboard_url = function_exists('site_url') ? site_url('admin/dashboard_admin') : ($rootHref . '/index.php?page=dashboard_admin');
} elseif ($is_parent_logged_in) {
    $dashboard_url = function_exists('site_url') ? site_url('parents/dashboard_parent') : ($rootHref . '/index.php?page=dashboard_parent');
} elseif ($is_user_logged_in) {
    // Pour les élèves connectés, le bouton Mon espace doit toujours renvoyer vers le tableau de bord central.
    $dashboard_url = function_exists('site_url') ? site_url('eleve/dashboard') : ($rootHref . '/index.php?page=eleve/dashboard');
}

$is_demo_active = function_exists('isDemoUser') ? isDemoUser() : !empty($_SESSION['is_demo']);
$user_level_normalized = function_exists('normalize_school_level') ? normalize_school_level($user_level) : strtolower((string) $user_level);

$app_theme = $GLOBALS['app_theme'] ?? null;
if (!is_array($app_theme) || empty($app_theme['variant'])) {
    $page_theme_level = $GLOBALS['page_theme_level'] ?? null;
    $app_theme = function_exists('resolve_app_theme')
        ? resolve_app_theme($page_theme_level)
        : ['tier' => 'neutral', 'level_key' => 'neutral', 'variant' => function_exists('get_neutral_theme_variant') ? get_neutral_theme_variant() : []];
    $GLOBALS['app_theme'] = $app_theme;
}
$topbar_theme = is_array($app_theme['variant'] ?? null) ? $app_theme['variant'] : [];

// Ensure a valid topbar theme exists even if the level is malformed.
if (empty($topbar_theme) || !is_array($topbar_theme)) {
    $topbar_theme = [
        'topbar_bg' => 'bg-slate-700',
        'topbar_border' => 'border-slate-800',
        'button_dashboard' => 'bg-blue-600 text-white hover:bg-blue-700',
        'button_logout' => 'bg-slate-700 text-white hover:bg-slate-800',
        'button_demo' => 'bg-emerald-600 text-white hover:bg-emerald-700',
    ];
}

// Resolve persona label only for logged-in users and if not suppressed
if ($is_user_logged_in && empty($hide_topbar_persona)) {
    $label = 'MonCoachScolaire';

    if ($is_admin_logged_in) {
        $label = 'MonCoachScolaire - Admin';
    } elseif ($is_parent_logged_in) {
        $label = 'MonCoachScolaire - Parents';
    } else {
        $themeNames = ['6eme' => 'Aventurier', '5eme' => 'Explorateur', '4eme' => 'Ingénieur', '3eme' => 'Expert'];
        // Utiliser le niveau normalisé pour le mapping des thèmes
        $level_normalized = function_exists('normalize_school_level') ? normalize_school_level($user_level) : $user_level;
        $themeName = $current_theme ?: ($themeNames[$level_normalized] ?? 'Aventurier');
        if ($user_level_display) {
            $label = sprintf('MonCoachScolaire - %s (%s)', $themeName, $user_level_display);
        }
    }
} else {
    // default for visitors or when persona suppressed
    $label = 'MonCoachScolaire';
}

?>
<!-- Topbar -->
<header class="topbar w-full shadow-lg border-b sticky top-0 z-50 text-white <?php echo htmlspecialchars($topbar_theme['topbar_bg'] ?? 'bg-slate-700', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($topbar_theme['topbar_border'] ?? 'border-slate-800', ENT_QUOTES, 'UTF-8'); ?>" role="banner" aria-label="Barre supérieure du site">
  <div class="container topbar-inner mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16 text-white">
    <div class="topbar-left flex items-center gap-3">
      <a href="<?php echo function_exists('site_url') ? site_url('landingpage') : ($rootHref . '/index.php?page=landingpage'); ?>" class="topbar-logo inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 text-slate-700 text-xl font-bold shadow-sm" aria-label="Accueil MonCoachScolaire">
        <span class="logo-icon" aria-hidden="true">📚</span>
        <span class="sr-only">Accueil MonCoachScolaire</span>
      </a>
      <a href="<?php echo function_exists('site_url') ? site_url('landingpage') : ($rootHref . '/index.php?page=landingpage'); ?>" class="topbar-title-link inline-flex items-center gap-2" aria-label="Aller à la page d'accueil">
        <strong class="topbar-title font-semibold text-lg text-white">MonCoachScolaire</strong>
        <?php if ($is_user_logged_in && empty($hide_topbar_persona)): ?>
          <span class="topbar-role-badge inline-block ml-2 px-2 py-1 rounded text-xs font-semibold bg-white/20 text-white border border-white/30 align-middle">
            <?php
              if ($is_admin_logged_in) {
                  echo 'Admin';
              } elseif ($is_parent_logged_in) {
                  echo 'Parents';
              } else {
                  // Affichage du niveau d’aventure ou scolaire
                  $niveau = $user_level_display ?: ($user_level_normalized ?? 'Élève');
                  echo htmlspecialchars($niveau);
              }
?>
          </span>
        <?php endif; ?>
      </a>
    </div>
    <nav class="topbar-right flex items-center" role="navigation" aria-label="Actions utilisateur">
      <?php if ($is_admin_logged_in): ?>
        <ul class="topbar-actions-list flex items-center gap-2">
          <li><a href="<?php echo $dashboard_url; ?>" class="btn small inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-semibold bg-slate-800 text-white hover:bg-slate-700" aria-label="Mon espace administrateur">⚙️ Mon espace</a></li>
          <li><a href="<?php echo site_url('logout'); ?>" class="btn small inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold bg-slate-700 text-white hover:bg-slate-800" aria-label="Se déconnecter">Se déconnecter</a></li>
        </ul>
      <?php elseif ($is_parent_logged_in): ?>
        <ul class="topbar-actions-list flex items-center gap-2">
          <!-- Lien 'Mon espace parents' supprimé -->
          <li><a href="<?php echo site_url('logout'); ?>" class="btn small inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold <?php echo htmlspecialchars($topbar_theme['button_logout'] ?? 'bg-slate-700 text-white hover:bg-slate-800', ENT_QUOTES, 'UTF-8'); ?>" aria-label="Se déconnecter">Se déconnecter</a></li>
        </ul>
      <?php elseif ($is_user_logged_in): ?>
        <ul class="topbar-actions-list flex items-center gap-2">
          <li><a href="<?php echo $dashboard_url; ?>" class="btn btn-topbar-accent small inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold" aria-label="Mon espace">Mon espace</a></li>
          <li><a href="<?php echo site_url('logout'); ?>" class="btn small inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold <?php echo htmlspecialchars($topbar_theme['button_logout'] ?? 'bg-slate-700 text-white hover:bg-slate-800'); ?>" aria-label="Se déconnecter">Se déconnecter</a></li>
        </ul>
      <?php else:
          // Vérifier si l'utilisateur est en mode démo
          $is_demo_active = !empty($_SESSION['is_demo']);
          ?>
        <ul class="topbar-actions-list flex items-center gap-2">
          <?php if (!$is_demo_active): // Ne pas afficher les boutons de connexion si le mode démo est actif?>
            <li><a href="<?php echo site_url('demo') . '?demo=1'; ?>" class="btn btn-demo small js-start-demo inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-semibold <?php echo htmlspecialchars($topbar_theme['button_demo'] ?? 'bg-emerald-600 text-white hover:bg-emerald-700'); ?>" aria-label="Essayer la démo">🎮 Mode Démo</a></li>
            <li><a href="<?php echo site_url('login'); ?>" class="btn btn-login small inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold bg-slate-700 text-white hover:bg-slate-800" aria-label="Se connecter">Se connecter</a></li>
            <li><a href="<?php echo site_url('register'); ?>" class="btn btn-register small inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700" aria-label="Créer un compte">Créer un compte</a></li>
          <?php else: // En mode démo, afficher les boutons pour quitter ou créer un compte?>
            <li><a href="<?php echo site_url('logout'); ?>" class="btn btn-demo small js-quit-demo inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-semibold <?php echo htmlspecialchars($topbar_theme['button_logout'] ?? 'bg-slate-700 text-white hover:bg-slate-800'); ?>" aria-label="Quitter le mode démo">🚪 Quitter le mode démo</a></li>
            <li><a href="<?php echo site_url('register'); ?>" class="btn btn-register small inline-flex items-center px-3 py-1.5 rounded-md text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700" aria-label="Créer un compte">Créer un compte</a></li>
          <?php endif; ?>
        </ul>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php
// mark that the topbar has been rendered in case a page was manually including it
$GLOBALS['__topbar_included'] = true;

?>
<script>
// Activer le mode démo sans quitter la page : on déclenche une requête vers la page démo (?demo=1)
// puis on recharge la page courante pour rafraîchir la topbar
(function(){
  try {
    // Activer démo
    const start = document.querySelector('.js-start-demo');
    if (start) {
      start.addEventListener('click', function(e){
        try { e.preventDefault(); } catch(_){}
        const url = this.getAttribute('href');
        try { sessionStorage.setItem('demoToast', 'activated'); } catch(_){}
        fetch(url, { method: 'GET', credentials: 'same-origin' })
          .then(() => {
            // Rediriger vers la page démo après activation
            window.location.href = url.replace(/[?&]demo=1/, '');
          })
          .catch(() => {
            // En cas d'erreur, rediriger quand même
            window.location.href = url.replace(/[?&]demo=1/, '');
          });
      });
    }

    // Quitter démo
    const landingUrl = <?php echo json_encode(site_url("landingpage")); ?>;
    const quit = document.querySelector('.js-quit-demo');
    if (quit) {
      quit.addEventListener('click', function(e){
        try { e.preventDefault(); } catch(_){}
        const url = this.getAttribute('href');
        try { sessionStorage.setItem('demoToast', 'quit'); } catch(_){}
        fetch(url, { method: 'GET', credentials: 'same-origin' })
          .then(() => {
            // Rediriger vers la landing page après déconnexion
            window.location.href = landingUrl;
          })
          .catch(() => {
            // En cas d'erreur, rediriger quand même vers la landing page
            window.location.href = landingUrl;
          });
      });
    }
    // Afficher toast après reload si demandé
    try {
      const flag = sessionStorage.getItem('demoToast');
      if (flag) {
        sessionStorage.removeItem('demoToast');
        const msg = flag === 'activated' ? 'Mode démo activé' : 'Mode démo désactivé';
        const bg = flag === 'activated' ? '#16a34a' : '#334155';
        const el = document.createElement('div');
        el.textContent = '✔ ' + msg;
        el.setAttribute('role','status');
        el.style.position = 'fixed';
        el.style.top = '16px';
        el.style.right = '16px';
        el.style.zIndex = '9999';
        el.style.background = bg;
        el.style.color = '#fff';
        el.style.padding = '10px 14px';
        el.style.borderRadius = '8px';
        el.style.boxShadow = '0 6px 20px rgba(0,0,0,0.2)';
        el.style.fontSize = '14px';
        el.style.opacity = '0';
        el.style.transition = 'opacity .25s ease, transform .25s ease';
        el.style.transform = 'translateY(-6px)';
        document.body.appendChild(el);
        requestAnimationFrame(()=>{
          el.style.opacity = '1';
          el.style.transform = 'translateY(0)';
        });
        setTimeout(()=>{
          el.style.opacity = '0';
          el.style.transform = 'translateY(-6px)';
          setTimeout(()=>{ el.remove(); }, 250);
        }, 1800);
      }
    } catch(_){}
  } catch (_) {}
})();
 </script>
<?php
// Fin du buffer : flush uniquement si la topbar a été affichée
if (ob_get_level()) {
    ob_end_flush();
} ?>
