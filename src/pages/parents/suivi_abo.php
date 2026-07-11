<?php
/**
 * src/pages/parents/suivi_abo.php
 *
 * Page parent - Suivi Abonnement
 * Produit fini prêt à intégrer.
 *
 * Hypothèses souples :
 * - Tu peux remplacer les valeurs statiques par tes vraies données DB.
 * - Le layout global (header/footer/sidebar) est déjà géré ailleurs.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Suivi abonnement parent - MonCoachScolaire';
$page_class = 'suivi-abo-page';

if (!function_exists('site_url')) {
    $siteBoot = dirname(__DIR__, 2) . '/config/site_boot.php';
    if (is_file($siteBoot)) {
        require_once $siteBoot;
    }
}

if (!function_exists('safe_redirect')) {
    $redirectHelpers = dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
    if (is_file($redirectHelpers)) {
        require_once $redirectHelpers;
    }
}

if (!function_exists('isAdmin')) {
    $adminAuth = dirname(__DIR__, 2) . '/includes/admin_auth.php';
    if (is_file($adminAuth)) {
        require_once $adminAuth;
    }
}

if (!function_exists('generateCSRFToken') || !function_exists('verifyCSRFToken')) {
    $loginSecurity = dirname(__DIR__, 2) . '/includes/login_security.php';
    if (is_file($loginSecurity)) {
        require_once $loginSecurity;
    }
}

$is_admin = function_exists('isAdmin') && isAdmin();

if (empty($_SESSION['parent_id'])
    && in_array(strtolower((string) ($_SESSION['user_role'] ?? '')), ['parent', 'parents'], true)
    && !empty($_SESSION['user_id'])) {
    $_SESSION['parent_id'] = (int) $_SESSION['user_id'];
}

$parent_session_id = (int) ($_SESSION['parent_id'] ?? $_SESSION['user_id'] ?? 0);
if (!$is_admin && $parent_session_id <= 0) {
    $loginUrl = function_exists('site_url') ? site_url('login') : '/index.php?page=login';
    if (function_exists('safe_redirect')) {
        safe_redirect($loginUrl);
    }
    if (!headers_sent()) {
        header('Location: ' . $loginUrl, true, 302);
        exit;
    }
    echo '<script>window.location.href = ' . json_encode($loginUrl) . ';</script>';
    exit;
}

$parentName = $_SESSION['user_name'] ?? $_SESSION['parent_name'] ?? 'Parent';
$childrenCount = isset($childrenCount) ? (int) $childrenCount : 1;

$currentPlan = $currentPlan ?? [
    'name' => 'Suivi',
    'status' => 'Actif',
    'price' => '19,90 € / mois',
    'renewal_date' => '15 juillet 2026',
    'children_included' => 1,
    'billing_cycle' => 'Mensuel',
    'engagement' => 'Sans engagement',
];

$plans = [
    [
        'slug' => 'decouverte',
        'name' => 'Découverte',
        'price' => '0 €',
        'period' => '/ mois',
        'tag' => 'Pour commencer',
        'highlight' => false,
        'description' => 'Une entrée simple pour découvrir la plateforme et suivre les bases.',
        'features' => [
            'Accès parent au tableau de bord',
            'Suivi d’un enfant',
            'Vue générale de la progression',
            'Accès limité aux ressources',
            'Support standard',
        ],
        'cta' => 'Choisir Découverte',
    ],
    [
        'slug' => 'suivi',
        'name' => 'Suivi',
        'price' => '19,90 €',
        'period' => '/ mois',
        'tag' => 'La plus choisie',
        'highlight' => true,
        'description' => 'La formule idéale pour accompagner régulièrement la progression scolaire.',
        'features' => [
            'Tout Découverte',
            'Suivi détaillé des progrès',
            'Historique d’activité',
            'Recommandations parent personnalisées',
            'Ressources pédagogiques étendues',
            'Support prioritaire',
        ],
        'cta' => 'Passer à Suivi',
    ],
    [
        'slug' => 'famille',
        'name' => 'Famille',
        'price' => '29,90 €',
        'period' => '/ mois',
        'tag' => 'Multi-enfants',
        'highlight' => false,
        'description' => 'Pensée pour les familles qui souhaitent piloter plusieurs enfants depuis un seul espace.',
        'features' => [
            'Tout Suivi',
            'Jusqu’à 3 enfants inclus',
            'Vue consolidée parent',
            'Comparaison douce entre enfants',
            'Alertes et rappels renforcés',
            'Accompagnement prioritaire',
        ],
        'cta' => 'Choisir Famille',
    ],
];

$faq = [
    [
        'q' => 'Puis-je changer de formule à tout moment ?',
        'a' => 'Oui, vous pouvez faire évoluer votre formule selon vos besoins. Le changement prend effet selon vos règles de facturation.',
    ],
    [
        'q' => 'Puis-je arrêter l’abonnement facilement ?',
        'a' => 'Oui, l’espace abonnement doit permettre une gestion simple et transparente, sans parcours compliqué.',
    ],
    [
        'q' => 'La formule Famille couvre combien d’enfants ?',
        'a' => 'Cette version de démonstration prévoit jusqu’à 3 enfants inclus. Tu peux ajuster cette règle selon ton offre réelle.',
    ],
    [
        'q' => 'Les parents voient-ils les progrès de leur enfant ?',
        'a' => 'Oui, la logique de cette page est justement de relier la formule choisie au niveau de suivi accessible dans l’espace parent.',
    ],
];

if (!function_exists('abo_badge_class')) {
    function abo_badge_class(string $status): string
    {
        $normalized = mb_strtolower(trim($status));
        if (in_array($normalized, ['actif', 'active', 'activee'], true)) {
            return 'bg-emerald-100 text-emerald-800 border border-emerald-200';
        }
        if (in_array($normalized, ['essai', 'trial'], true)) {
            return 'bg-slate-100 text-slate-700 border border-slate-200';
        }
        if (in_array($normalized, ['expiré', 'expire', 'expirée', 'expired'], true)) {
            return 'bg-rose-100 text-rose-800 border border-rose-200';
        }

        return 'bg-slate-100 text-slate-700 border border-slate-200';
    }
}

if (!function_exists('abo_plan_cta_class')) {
    function abo_plan_cta_class(bool $highlight): string
    {
        if ($highlight) {
            return 'inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800';
        }

        return 'inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50';
    }
}

$selfAboUrl = function_exists('site_url') ? site_url('parents/suivi_abo') : 'index.php?page=parents/suivi_abo';
$dashboardParentUrl = function_exists('site_url') ? site_url('parents/dashboard_parent') : 'index.php?page=parents/dashboard_parent';
$subscriptionActionUrl = function_exists('site_url') ? site_url('api/parents/subscription_action') : 'index.php?page=api/parents/subscription_action';
$csrfToken = function_exists('generateCSRFToken')
    ? generateCSRFToken()
    : ((string) ($_SESSION['csrf_token'] ?? ''));
$overviewCards = [
    [
        'title' => 'Lisibilité immédiate',
        'text' => 'Les différences entre formules sont visibles dès le premier écran.',
    ],
    [
        'title' => 'Gestion simplifiée',
        'text' => 'Votre statut et votre échéance principale sont regroupés au même endroit.',
    ],
    [
        'title' => 'Choix rassurant',
        'text' => 'Les bénéfices sont formulés autour du suivi scolaire concret.',
    ],
];
$comparisonRows = [
    ['feature' => 'Accès parent au dashboard', 'decouverte' => 'Oui', 'suivi' => 'Oui', 'famille' => 'Oui', 'accent' => true],
    ['feature' => 'Nombre d’enfants inclus', 'decouverte' => '1 enfant', 'suivi' => '1 enfant', 'famille' => 'Jusqu’à 3 enfants', 'accent' => false],
    ['feature' => 'Suivi détaillé des progrès', 'decouverte' => 'Limité', 'suivi' => 'Complet', 'famille' => 'Complet', 'accent' => false],
    ['feature' => 'Historique d’activité', 'decouverte' => 'Essentiel', 'suivi' => 'Oui', 'famille' => 'Oui', 'accent' => false],
    ['feature' => 'Ressources pédagogiques', 'decouverte' => 'Accès limité', 'suivi' => 'Accès étendu', 'famille' => 'Accès étendu', 'accent' => false],
    ['feature' => 'Conseils parent personnalisés', 'decouverte' => 'Non', 'suivi' => 'Oui', 'famille' => 'Oui', 'accent' => false],
    ['feature' => 'Vue consolidée multi-enfants', 'decouverte' => 'Non', 'suivi' => 'Non', 'famille' => 'Oui', 'accent' => false],
    ['feature' => 'Support prioritaire', 'decouverte' => 'Non', 'suivi' => 'Oui', 'famille' => 'Oui', 'accent' => false],
    ['feature' => 'Engagement', 'decouverte' => 'Sans engagement', 'suivi' => 'Sans engagement', 'famille' => 'Sans engagement', 'accent' => false],
];

$renewalEnabled = isset($currentPlan['auto_renew']) ? (bool) $currentPlan['auto_renew'] : true;
$paymentMethod = (string) ($currentPlan['payment_method'] ?? 'Non renseigné');
$nextBillingDate = (string) ($currentPlan['next_billing_date'] ?? ($currentPlan['renewal_date'] ?? '—'));
$nextBillingAmount = (string) ($currentPlan['next_billing_amount'] ?? ($currentPlan['price'] ?? '—'));
$subscriptionSource = 'fallback';

$billingHistory = [
    [
        'label' => 'Dernière facture',
        'value' => 'Aucune facture récente disponible',
        'status' => 'info',
    ],
    [
        'label' => 'Prochain prélèvement',
        'value' => $nextBillingDate . ' - ' . $nextBillingAmount,
        'status' => 'pending',
    ],
];

$actionDescriptions = [
    'change-plan' => [
        'title' => 'Modifier la formule',
        'desc' => 'Le changement de formule prend effet à la prochaine échéance. Vous conservez l’accès actuel jusqu’à cette date.',
    ],
    'pause' => [
        'title' => 'Mettre en pause',
        'desc' => 'La pause stoppe le renouvellement automatique pour le prochain cycle. La reprise reste possible à tout moment.',
    ],
    'cancel' => [
        'title' => 'Résilier',
        'desc' => 'La résiliation met fin au renouvellement futur. L’accès reste actif jusqu’à la fin de la période déjà réglée.',
    ],
];

$requestedAction = strtolower((string) (filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ''));
$selectedAction = $actionDescriptions[$requestedAction] ?? null;
$selectedActionPlan = strtolower((string) (filter_input(INPUT_GET, 'plan', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ''));
if (!in_array($selectedActionPlan, ['decouverte', 'suivi', 'famille'], true)) {
    $selectedActionPlan = 'suivi';
}

$operationStatus = strtolower((string) (filter_input(INPUT_GET, 'op_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ''));
$operationAction = strtolower((string) (filter_input(INPUT_GET, 'op_action', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? ''));
$operationMessage = (string) (filter_input(INPUT_GET, 'op_message', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
$operationRequestId = (string) (filter_input(INPUT_GET, 'op_request', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
$operationEffective = (string) (filter_input(INPUT_GET, 'op_effective', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
$operationPersisted = (string) (filter_input(INPUT_GET, 'op_persisted', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '0') === '1';

if (!function_exists('abo_table_exists')) {
    function abo_table_exists(PDO $pdo, string $tableName): bool
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
        $stmt->execute([$tableName]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    $dbConnection = dirname(__DIR__, 2) . '/database/connection.php';
    if (is_file($dbConnection)) {
        require_once $dbConnection;
    }
}

if (isset($pdo) && $pdo instanceof PDO && $parent_session_id > 0) {
    $subscriptionQueries = [
        "SELECT plan_name AS name, status, price_label AS price, renewal_date, billing_cycle, auto_renew, engagement, payment_method, next_billing_date, next_billing_amount FROM parent_subscriptions WHERE parent_user_id = ? ORDER BY id DESC LIMIT 1",
        "SELECT plan_name AS name, status, price, renewal_date, billing_cycle, auto_renew, engagement, payment_method, next_billing_date, next_billing_amount FROM subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT 1",
        "SELECT plan AS name, status, amount_label AS price, renewal_date, cycle AS billing_cycle, auto_renew, commitment AS engagement, payment_method, next_charge_date AS next_billing_date, next_charge_amount AS next_billing_amount FROM billing_subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT 1",
    ];

    foreach ($subscriptionQueries as $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute([$parent_session_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($row) && !empty($row)) {
                $currentPlan = array_merge($currentPlan, array_filter([
                    'name' => $row['name'] ?? null,
                    'status' => $row['status'] ?? null,
                    'price' => $row['price'] ?? null,
                    'renewal_date' => $row['renewal_date'] ?? null,
                    'billing_cycle' => $row['billing_cycle'] ?? null,
                    'engagement' => $row['engagement'] ?? null,
                    'payment_method' => $row['payment_method'] ?? null,
                    'next_billing_date' => $row['next_billing_date'] ?? null,
                    'next_billing_amount' => $row['next_billing_amount'] ?? null,
                    'auto_renew' => isset($row['auto_renew']) ? (bool) $row['auto_renew'] : null,
                ], static function ($value) {
                    return $value !== null && $value !== '';
                }));

                $renewalEnabled = isset($currentPlan['auto_renew']) ? (bool) $currentPlan['auto_renew'] : $renewalEnabled;
                $paymentMethod = (string) ($currentPlan['payment_method'] ?? $paymentMethod);
                $nextBillingDate = (string) ($currentPlan['next_billing_date'] ?? ($currentPlan['renewal_date'] ?? $nextBillingDate));
                $nextBillingAmount = (string) ($currentPlan['next_billing_amount'] ?? ($currentPlan['price'] ?? $nextBillingAmount));
                $subscriptionSource = 'database';
                break;
            }
        } catch (Throwable $e) {
            continue;
        }
    }

    $invoiceQueries = [
        "SELECT invoice_number, total_amount, status, created_at FROM parent_invoices WHERE parent_user_id = ? ORDER BY created_at DESC LIMIT 5",
        "SELECT reference AS invoice_number, amount AS total_amount, status, issued_at AS created_at FROM invoices WHERE user_id = ? ORDER BY issued_at DESC LIMIT 5",
        "SELECT reference AS invoice_number, amount AS total_amount, payment_status AS status, paid_at AS created_at FROM billing_invoices WHERE user_id = ? ORDER BY paid_at DESC LIMIT 5",
    ];

    foreach ($invoiceQueries as $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute([$parent_session_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $billingHistory = [];
                foreach ($rows as $row) {
                    $invoiceLabel = trim((string) ($row['invoice_number'] ?? 'Facture'));
                    $invoiceAmount = trim((string) ($row['total_amount'] ?? '—'));
                    $invoiceDate = trim((string) ($row['created_at'] ?? 'Date inconnue'));
                    $invoiceStatus = strtolower(trim((string) ($row['status'] ?? 'info')));
                    $billingHistory[] = [
                        'label' => $invoiceLabel,
                        'value' => $invoiceAmount . ' - ' . $invoiceDate,
                        'status' => $invoiceStatus,
                    ];
                }
                break;
            }
        } catch (Throwable $e) {
            continue;
        }
    }
}

$subscriptionStateLabel = $renewalEnabled ? 'Renouvellement automatique actif' : 'Renouvellement automatique désactivé';
$subscriptionStateClass = $renewalEnabled
    ? 'border-emerald-200 bg-emerald-100 text-emerald-800'
    : 'border-amber-200 bg-amber-100 text-amber-800';

$effectiveDateForAction = $nextBillingDate !== '' && $nextBillingDate !== '—'
    ? $nextBillingDate
    : date('Y-m-d');
?>
<main class="min-h-screen px-4 py-8 md:px-6 md:py-10">
    <a href="#abo-main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:border focus:border-slate-200 focus:bg-white focus:px-3 focus:py-2 focus:font-semibold focus:text-slate-700">
        Aller au contenu
    </a>

    <div id="abo-main-content" class="mx-auto w-full max-w-6xl space-y-6">
        <section aria-labelledby="abo-title" class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/95 shadow-[0_30px_80px_-35px_rgba(15,23,42,0.35)] backdrop-blur">
            <div class="grid gap-6 p-6 lg:grid-cols-5 lg:p-8">
                <div class="space-y-4 lg:col-span-3">
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-800">
                        Gestion abonnement parent
                    </span>
                    <h1 id="abo-title" class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">
                        Suivi abonnement
                    </h1>
                    <p class="max-w-2xl text-sm leading-7 text-slate-600 md:text-base">
                        Bonjour <?= htmlspecialchars((string) $parentName, ENT_QUOTES, 'UTF-8') ?>. Consultez votre formule actuelle,
                        comparez les offres et choisissez le niveau d’accompagnement le plus adapté au suivi scolaire de vos enfants.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="#formules" class="inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                            Voir les formules
                        </a>
                        <a href="#comparatif" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Comparer en détail
                        </a>
                        <a href="<?= htmlspecialchars($dashboardParentUrl, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                            Retour au dashboard
                        </a>
                    </div>
                </div>

                <aside aria-labelledby="abo-current-title" class="rounded-2xl border border-slate-200 bg-white/85 p-5 lg:col-span-2">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Abonnement actuel</p>
                            <h2 id="abo-current-title" class="mt-1 text-2xl font-bold text-slate-900">
                                <?= htmlspecialchars((string) ($currentPlan['name'] ?? 'Aucune formule'), ENT_QUOTES, 'UTF-8') ?>
                            </h2>
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= htmlspecialchars(abo_badge_class((string) ($currentPlan['status'] ?? 'Inconnu')), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string) ($currentPlan['status'] ?? 'Inconnu'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Tarification</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) ($currentPlan['price'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Échéance</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) ($currentPlan['renewal_date'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Cycle</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) ($currentPlan['billing_cycle'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white p-3">
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Enfants inclus</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) ($currentPlan['children_included'] ?? $childrenCount), ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                    </dl>

                    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700">
                            <span class="font-semibold text-slate-900">Moyen de paiement :</span>
                            <?= htmlspecialchars($paymentMethod, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="rounded-lg border px-3 py-2 text-xs <?= htmlspecialchars($subscriptionStateClass, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($subscriptionStateLabel, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="<?= htmlspecialchars($selfAboUrl, ENT_QUOTES, 'UTF-8') ?>#formules" class="inline-flex items-center rounded-lg border border-slate-200 bg-white/70 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-white">
                            Gérer mon abonnement
                        </a>
                        <a href="#faq" class="inline-flex items-center rounded-lg border border-slate-200 bg-white/70 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-white">
                            Questions fréquentes
                        </a>
                    </div>
                </aside>
            </div>
        </section>

        <section aria-labelledby="abo-overview-title" class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm lg:p-8">
            <h2 id="abo-overview-title" class="text-2xl font-bold tracking-tight text-slate-900">Ce que cette page permet</h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 md:text-base">
                Une bonne page d’abonnement ne doit pas seulement afficher des prix : elle doit expliquer clairement
                ce que chaque formule change dans l’expérience parent, et rendre la gestion du compte simple et transparente.
            </p>
            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <?php foreach ($overviewCards as $card): ?>
                    <article class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <h3 class="text-sm font-semibold text-emerald-900"><?= htmlspecialchars((string) $card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="mt-1 text-sm text-emerald-800"><?= htmlspecialchars((string) $card['text'], ENT_QUOTES, 'UTF-8') ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="formules" aria-labelledby="abo-plans-title" class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm lg:p-8">
            <h2 id="abo-plans-title" class="text-2xl font-bold tracking-tight text-slate-900">Choisir une formule</h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 md:text-base">
                Trois offres suffisent pour orienter la décision: une entrée simple, une formule recommandée et une formule familiale.
            </p>

            <div class="mt-5 grid gap-4 xl:grid-cols-3">
                <?php foreach ($plans as $plan): ?>
                    <?php
                        $isFeaturedPlan = !empty($plan['highlight']);
                        $planCardClass = $isFeaturedPlan ? 'border-emerald-300 bg-emerald-50 shadow-md' : 'border-slate-200 bg-white';
                        $planBadgeClass = $isFeaturedPlan ? 'border-emerald-200 bg-emerald-100 text-emerald-800' : 'border-slate-200 bg-slate-100 text-slate-700';
                    ?>
                    <article aria-labelledby="plan-<?= htmlspecialchars((string) $plan['slug'], ENT_QUOTES, 'UTF-8') ?>" class="flex h-full flex-col rounded-2xl border p-5 shadow-sm <?= htmlspecialchars($planCardClass, ENT_QUOTES, 'UTF-8') ?>">
                        <span class="inline-flex w-fit rounded-full border px-2.5 py-1 text-xs font-semibold <?= htmlspecialchars($planBadgeClass, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string) $plan['tag'], ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <h3 id="plan-<?= htmlspecialchars((string) $plan['slug'], ENT_QUOTES, 'UTF-8') ?>" class="mt-3 text-xl font-bold text-slate-900">
                            <?= htmlspecialchars((string) $plan['name'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            <?= htmlspecialchars((string) $plan['description'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="mt-4 flex items-end gap-1">
                            <span class="text-3xl font-extrabold tracking-tight text-slate-900"><?= htmlspecialchars((string) $plan['price'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="pb-1 text-sm font-medium text-slate-500"><?= htmlspecialchars((string) $plan['period'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>

                        <ul class="mt-4 space-y-2 text-sm text-slate-600" role="list">
                            <?php foreach ($plan['features'] as $feature): ?>
                                <li class="flex items-start gap-2">
                                    <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">✓</span>
                                    <span><?= htmlspecialchars((string) $feature, ENT_QUOTES, 'UTF-8') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="mt-5">
                            <?php if (($currentPlan['name'] ?? '') === $plan['name']): ?>
                                <span class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-emerald-200 bg-emerald-100 px-4 py-2.5 text-sm font-semibold text-emerald-800" aria-disabled="true">
                                    Formule actuelle
                                </span>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($selfAboUrl, ENT_QUOTES, 'UTF-8') ?>?plan=<?= urlencode((string) $plan['slug']) ?>#abo-cta-title" class="<?= htmlspecialchars(abo_plan_cta_class(!empty($plan['highlight'])), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars((string) $plan['cta'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <p class="mt-3 text-xs text-slate-500">
                Les prix et intitulés ci-dessus servent de base visuelle. Vous pourrez les relier ensuite aux vraies offres et règles de paiement.
            </p>
        </section>

        <section id="comparatif" aria-labelledby="abo-compare-title" class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm lg:p-8">
            <h2 id="abo-compare-title" class="text-2xl font-bold tracking-tight text-slate-900">Comparer les fonctionnalités</h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 md:text-base">
                Le tableau détaillé complète les cartes avec une vue précise de ce qui change selon la formule.
            </p>

            <div class="mt-5 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-[760px] w-full border-collapse text-left text-sm">
                    <caption class="px-4 py-3 text-left text-xs text-slate-500 md:px-5">
                        Comparatif des formules parent MonCoachScolaire.
                    </caption>
                    <thead class="bg-slate-100 text-slate-800">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold md:px-5">Fonctionnalité</th>
                            <th scope="col" class="px-4 py-3 font-semibold md:px-5">Découverte</th>
                            <th scope="col" class="px-4 py-3 font-semibold md:px-5">Suivi</th>
                            <th scope="col" class="px-4 py-3 font-semibold md:px-5">Famille</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white text-slate-700">
                        <?php foreach ($comparisonRows as $row): ?>
                            <?php
                                $cellAccentClass = !empty($row['accent'])
                                    ? 'text-emerald-700 font-semibold'
                                    : 'text-slate-700';
                            ?>
                            <tr class="border-t border-slate-200">
                                <th scope="row" class="px-4 py-3 font-medium md:px-5"><?= htmlspecialchars((string) $row['feature'], ENT_QUOTES, 'UTF-8') ?></th>
                                <td class="px-4 py-3 md:px-5 <?= htmlspecialchars($cellAccentClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $row['decouverte'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-3 md:px-5 <?= htmlspecialchars($cellAccentClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $row['suivi'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-4 py-3 md:px-5 <?= htmlspecialchars($cellAccentClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $row['famille'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="actions" aria-labelledby="abo-actions-title" class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm lg:p-8">
            <h2 id="abo-actions-title" class="text-2xl font-bold tracking-tight text-slate-900">Actions de gestion</h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 md:text-base">
                Gérez votre abonnement en toute transparence. Chaque action affiche clairement ses conséquences avant validation.
            </p>

            <?php if ($operationStatus === 'success' || $operationStatus === 'error'): ?>
                <?php
                    $statusPanelClass = $operationStatus === 'success'
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                        : 'border-rose-200 bg-rose-50 text-rose-900';
                    $statusTitle = $operationStatus === 'success'
                        ? 'Action abonnement prise en compte'
                        : 'Action abonnement non exécutée';
                ?>
                <div class="mt-4 rounded-2xl border p-4 <?= htmlspecialchars($statusPanelClass, ENT_QUOTES, 'UTF-8') ?>">
                    <p class="text-sm font-semibold"><?= htmlspecialchars($statusTitle, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if ($operationMessage !== ''): ?>
                        <p class="mt-1 text-sm"><?= htmlspecialchars($operationMessage, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <p class="mt-1 text-xs opacity-90">
                        Action: <?= htmlspecialchars($operationAction !== '' ? $operationAction : 'n/a', ENT_QUOTES, 'UTF-8') ?> |
                        Date d'effet: <?= htmlspecialchars($operationEffective !== '' ? $operationEffective : 'n/a', ENT_QUOTES, 'UTF-8') ?> |
                        Persistance: <?= htmlspecialchars($operationPersisted ? 'oui' : 'trace uniquement', ENT_QUOTES, 'UTF-8') ?> |
                        Requete: <?= htmlspecialchars($operationRequestId !== '' ? $operationRequestId : 'n/a', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <a href="<?= htmlspecialchars($selfAboUrl, ENT_QUOTES, 'UTF-8') ?>?action=change-plan#actions" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Modifier la formule</a>
                <a href="<?= htmlspecialchars($selfAboUrl, ENT_QUOTES, 'UTF-8') ?>?action=pause#actions" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">Mettre en pause</a>
                <a href="<?= htmlspecialchars($selfAboUrl, ENT_QUOTES, 'UTF-8') ?>?action=cancel#actions" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 transition hover:bg-rose-100">Résilier</a>
                <a href="mailto:support@moncoachscolaire.fr?subject=Support%20abonnement%20parent" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100">Contacter le support</a>
            </div>

            <?php if ($selectedAction): ?>
                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) $selectedAction['title'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mt-1 text-sm leading-6 text-slate-600"><?= htmlspecialchars((string) $selectedAction['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mt-1 text-xs text-slate-500">Date d'effet indiquee: <?= htmlspecialchars($effectiveDateForAction, ENT_QUOTES, 'UTF-8') ?></p>

                    <form method="post" action="<?= htmlspecialchars($subscriptionActionUrl, ENT_QUOTES, 'UTF-8') ?>" class="mt-3 rounded-xl border border-slate-200 bg-white p-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="redirect" value="1">
                        <input type="hidden" name="action" value="<?= htmlspecialchars($requestedAction, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="effective_date" value="<?= htmlspecialchars($effectiveDateForAction, ENT_QUOTES, 'UTF-8') ?>">

                        <?php if ($requestedAction === 'change-plan'): ?>
                            <label for="target-plan" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nouvelle formule</label>
                            <select id="target-plan" name="target_plan" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                <option value="decouverte" <?= $selectedActionPlan === 'decouverte' ? 'selected' : '' ?>>Decouverte</option>
                                <option value="suivi" <?= $selectedActionPlan === 'suivi' ? 'selected' : '' ?>>Suivi</option>
                                <option value="famille" <?= $selectedActionPlan === 'famille' ? 'selected' : '' ?>>Famille</option>
                            </select>
                        <?php endif; ?>

                        <label class="mt-3 flex items-start gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="confirmed" value="1" required class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                            <span>Je confirme cette demande et comprends ses consequences sur le renouvellement et la facturation.</span>
                        </label>

                        <button type="submit" class="mt-3 inline-flex min-h-11 items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                            Confirmer l'action
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </section>

        <section id="tracabilite" aria-labelledby="abo-tracking-title" class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm lg:p-8">
            <h2 id="abo-tracking-title" class="text-2xl font-bold tracking-tight text-slate-900">Traçabilité abonnement</h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 md:text-base">
                Source des données: <?= htmlspecialchars($subscriptionSource, ENT_QUOTES, 'UTF-8') ?>.
                Vérifiez ici le prochain prélèvement et l’historique récent de facturation.
            </p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Prochain prélèvement</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900"><?= htmlspecialchars($nextBillingDate, ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($nextBillingAmount, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Engagement</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900"><?= htmlspecialchars((string) ($currentPlan['engagement'] ?? 'Sans engagement'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>

            <ul class="mt-4 space-y-2" role="list">
                <?php foreach ($billingHistory as $event): ?>
                    <li class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                        <span class="font-semibold text-slate-900"><?= htmlspecialchars((string) $event['label'], ENT_QUOTES, 'UTF-8') ?>:</span>
                        <span class="ml-1"><?= htmlspecialchars((string) $event['value'], ENT_QUOTES, 'UTF-8') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section id="faq" aria-labelledby="abo-faq-title" class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm lg:p-8">
            <h2 id="abo-faq-title" class="text-2xl font-bold tracking-tight text-slate-900">Questions fréquentes</h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 md:text-base">
                Une FAQ courte évite les doutes inutiles avant le choix ou la modification d’une formule.
            </p>

            <div class="mt-4 space-y-3">
                <?php foreach ($faq as $item): ?>
                    <details class="group rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <summary class="cursor-pointer list-none pr-6 font-semibold text-slate-800 marker:content-none">
                            <?= htmlspecialchars((string) $item['q'], ENT_QUOTES, 'UTF-8') ?>
                            <span class="float-right text-slate-500 transition group-open:rotate-45">+</span>
                        </summary>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            <?= htmlspecialchars((string) $item['a'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </details>
                <?php endforeach; ?>
            </div>
        </section>

        <section aria-labelledby="abo-cta-title" class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm lg:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 id="abo-cta-title" class="text-2xl font-bold tracking-tight text-emerald-900">Prêt à ajuster votre formule ?</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-emerald-800 md:text-base">
                        Choisissez l’offre qui correspond au niveau de suivi souhaité, puis finalisez la mise à jour depuis votre espace parent.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="<?= htmlspecialchars($selfAboUrl, ENT_QUOTES, 'UTF-8') ?>?plan=suivi#abo-cta-title" class="inline-flex items-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                        Passer à Suivi - 19,90 € / mois
                    </a>
                    <a href="<?= htmlspecialchars($dashboardParentUrl, ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center rounded-xl border border-emerald-300 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100">
                        Retour à l’espace parent
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>
