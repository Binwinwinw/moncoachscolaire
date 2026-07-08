<?php

if (!function_exists('legal_page_bootstrap')) {
    /**
     * Bootstrap commun des pages légales (routeur ou accès direct).
     *
     * @param array{script_file: string, page_class: string, page_title: string, page_description?: string, page_css?: string} $opts
     */
    function legal_page_bootstrap(array $opts): void
    {
        global $page_class, $page_title, $page_css, $page_description, $legal_page_direct_access;

        $page_class = $page_class ?? ($opts['page_class'] ?? 'legal-page');
        $page_css = $page_css ?? ($opts['page_css'] ?? 'legal-pages.css');
        $page_title = $page_title ?? ($opts['page_title'] ?? 'MonCoachScolaire');
        $page_description = $page_description ?? ($opts['page_description'] ?? '');

        $scriptFile = $opts['script_file'] ?? '';
        $legal_page_direct_access = $scriptFile !== ''
            && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath($scriptFile);

        if ($legal_page_direct_access) {
            $srcRoot = dirname(__DIR__);
            if (is_file($srcRoot . '/config/site_boot.php')) {
                require_once $srcRoot . '/config/site_boot.php';
            }
            if (is_file($srcRoot . '/includes/app_theme_bootstrap.php')) {
                require_once $srcRoot . '/includes/app_theme_bootstrap.php';
            }
            if (function_exists('bootstrap_app_theme')) {
                bootstrap_app_theme(null, null);
            }

            $themeTier = $GLOBALS['app_theme']['tier'] ?? 'neutral';

            ?><!doctype html>
            <html lang="fr">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width,initial-scale=1">
                <?php if ($page_description !== ''): ?>
                <meta name="description" content="<?php echo htmlspecialchars($page_description, ENT_QUOTES, 'UTF-8'); ?>">
                <?php endif; ?>
                <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
                <?php
                $styleCss = function_exists('asset_url') ? asset_url('assets/css/style.css') : 'assets/css/style.css';
            $themeCss = function_exists('asset_url') ? asset_url('assets/css/theme-level.css') : 'assets/css/theme-level.css';
            $pageCssHref = function_exists('asset_url')
                ? asset_url('assets/css/pages/' . ltrim($page_css, '/'))
                : 'assets/css/pages/' . ltrim($page_css, '/');
            ?>
                <link rel="stylesheet" href="<?php echo htmlspecialchars($styleCss, ENT_QUOTES, 'UTF-8'); ?>">
                <link rel="stylesheet" href="<?php echo htmlspecialchars($themeCss, ENT_QUOTES, 'UTF-8'); ?>">
                <link rel="stylesheet" href="<?php echo htmlspecialchars($pageCssHref, ENT_QUOTES, 'UTF-8'); ?>">
            </head>
            <body class="app-bg theme-<?php echo htmlspecialchars($themeTier, ENT_QUOTES, 'UTF-8'); ?> legal-page <?php echo htmlspecialchars($page_class, ENT_QUOTES, 'UTF-8'); ?>">
            <?php
            if (is_file($srcRoot . '/includes/topbar.php')) {
                require_once $srcRoot . '/includes/topbar.php';
            }
        }

        $srcRoot = dirname(__DIR__);
        if (is_file($srcRoot . '/database/connection.php')) {
            require_once $srcRoot . '/database/connection.php';
        }
        if (!function_exists('site_url') && is_file($srcRoot . '/config/site_boot.php')) {
            require_once $srcRoot . '/config/site_boot.php';
        }
        if (function_exists('ensure_session_started')) {
            ensure_session_started();
        } elseif (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Bandeau titre + sous-navigation entre pages légales.
     */
    function legal_page_render_header(string $icon, string $title, string $subtitle, string $currentSlug = ''): void
    {
        $links = [
            'mentions-legales' => ['label' => 'Mentions légales', 'icon' => '⚖️'],
            'confidentialite' => ['label' => 'Confidentialité', 'icon' => '🔒'],
            'cgv' => ['label' => 'CGV', 'icon' => '📜'],
        ];
        ?>
<header class="legal-page-banner flex flex-col gap-2 items-center justify-center text-center py-8 px-4 mx-auto my-8 max-w-screen-xl bg-white/70 backdrop-blur-md rounded-xl shadow-lg" role="banner">
  <h1 class="m-0 max-w-4xl text-4xl md:text-5xl font-bold text-slate-800 leading-tight">
    <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
  </h1>
  <p class="lead m-0 max-w-2xl text-lg text-slate-600 font-medium">
    <?php echo htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8'); ?>
  </p>
  <p class="text-sm text-slate-500 mt-2">
    <em>Dernière mise à jour : <?php echo date('d/m/Y'); ?></em>
  </p>
  <nav class="legal-subnav flex flex-wrap justify-center gap-2 mt-4" aria-label="Pages légales">
    <?php foreach ($links as $slug => $link):
        $isCurrent = ($slug === $currentSlug);
        $href = function_exists('site_url') ? site_url($slug) : ('index.php?page=' . $slug);
        $classes = $isCurrent
            ? 'inline-flex items-center gap-1 px-4 py-2 rounded-lg text-sm font-semibold bg-slate-700 text-white shadow'
            : 'inline-flex items-center gap-1 px-4 py-2 rounded-lg text-sm font-semibold bg-white/80 text-slate-700 border border-slate-200 hover:bg-slate-100 transition';
        ?>
    <a href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $classes; ?>"<?php echo $isCurrent ? ' aria-current="page"' : ''; ?>>
      <?php echo htmlspecialchars($link['icon'] . ' ' . $link['label'], ENT_QUOTES, 'UTF-8'); ?>
    </a>
    <?php endforeach; ?>
  </nav>
</header>
        <?php
    }

    function legal_page_main_open(): void
    {
        echo '<main class="legal-content mx-auto my-12 max-w-screen-lg px-4">' . "\n";
    }

    function legal_page_main_close(): void
    {
        $homeUrl = function_exists('site_url') ? site_url('landingpage') : 'index.php?page=landingpage';
        ?>
  <div class="text-center my-8">
    <a href="<?php echo htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn-back-home">
      ← Retour à l'accueil
    </a>
  </div>
</main>
        <?php
    }

    function legal_page_finish(): void
    {
        global $legal_page_direct_access;

        if (!empty($legal_page_direct_access)) {
            $footerPath = dirname(__DIR__) . '/includes/footer.php';
            if (!is_file($footerPath)) {
                echo "</body>\n</html>";
            }
        }
    }
}
