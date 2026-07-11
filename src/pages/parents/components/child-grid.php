<?php
// Grille enfants
$build_inline_avatar = static function ($label, $accent = '#4f46e5') {
    $initial = strtoupper(substr(trim((string) $label), 0, 1));
    if ($initial === '') {
        $initial = 'E';
    }

    $svg = sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96" role="img" aria-label="Avatar %1$s"><rect width="96" height="96" rx="48" fill="#eef2ff"/><circle cx="48" cy="48" r="42" fill="%2$s" opacity="0.16"/><text x="48" y="56" text-anchor="middle" font-family="Arial, sans-serif" font-size="38" font-weight="700" fill="%2$s">%1$s</text></svg>',
        htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($accent, ENT_QUOTES, 'UTF-8')
    );

    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
};

$build_avatar_url = static function ($path, $label = 'E') use ($build_inline_avatar) {
    $path = trim((string) $path);
    if ($path === '') {
        return $build_inline_avatar($label);
    }

    if (preg_match('#^(https?:)?//#i', $path) === 1 || strpos($path, 'data:image/') === 0) {
        return $path;
    }

    $normalizedPath = ltrim($path, '/');
    $publicPath = dirname(__DIR__, 4) . '/public/' . preg_replace('#^public/#', '', $normalizedPath);

    if (is_file($publicPath)) {
        if (function_exists('asset_url')) {
            return asset_url(preg_replace('#^public/#', '', $normalizedPath));
        }

        return '/' . preg_replace('#^public/#', '', $normalizedPath);
    }

    return $build_inline_avatar($label);
};
?>
<section class="mb-10">
    <div class="mb-5 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-700">Vue famille</p>
            <h2 class="mt-1 flex items-center gap-2 text-xl font-bold text-slate-900 md:text-2xl">
                <span class="text-lg">👨‍👩‍👧‍👦</span> Vos enfants suivis
            </h2>
        </div>
        <p class="text-sm text-slate-600">Chaque enfant dispose d’un accès rapide vers son suivi personnalisé.</p>
    </div>
    <?php
    $enfants_affiches = [];
    foreach ((array) ($enfants ?? []) as $enfant) {
        $enfant_id = $enfant['Id'] ?? $enfant['id'] ?? $enfant['user_id'] ?? $enfant['enfant_id'] ?? null;
        $enfant_role = strtolower((string) ($enfant['Role'] ?? $enfant['role'] ?? 'student'));
        if (empty($enfant_id) || $enfant_role !== 'student') {
            continue;
        }
        $enfants_affiches[] = $enfant;
    }
    ?>
    <?php if (empty($enfants_affiches)): ?>
        <div class="rounded-[1.5rem] border border-slate-200 bg-white/90 p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-5 md:flex-row md:items-center">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 via-cyan-600 to-slate-800 text-xl font-bold text-white">
                    +
                </div>
                <div class="flex-1">
                    <h3 class="mb-1 text-lg font-bold text-slate-900">Aucun enfant rattaché pour l’instant</h3>
                    <p class="max-w-2xl text-sm leading-6 text-slate-600">Générez un code de rattachement et demandez à votre enfant de finaliser le lien pour voir sa progression dans cette section.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php $apiBase = function_exists('site_url') ? site_url('api/parent_family') : '/api/parent_family'; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($enfants_affiches as $enfant): ?>
                <div class="flex flex-col items-center rounded-[1.5rem] border border-slate-200 bg-white/90 p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md">
                    <?php $enfant_id = $enfant['Id'] ?? $enfant['id'] ?? $enfant['user_id'] ?? $enfant['enfant_id'] ?? null; ?>
                    <?php $avatar = $build_avatar_url($enfant['avatar'] ?? '', $enfant['prenom'] ?? $enfant['Prenom'] ?? $enfant['Username'] ?? 'E'); ?>
                    <img src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="Avatar" class="mb-3 h-16 w-16 rounded-full border-2 border-slate-200 object-cover">
                    <div class="mb-1 text-base font-semibold text-slate-900"><?= htmlspecialchars($enfant['prenom'] ?? $enfant['Prenom'] ?? 'Enfant') ?> <span class="align-top text-xs text-slate-500">(<?= htmlspecialchars($enfant['niveau'] ?? $enfant['Niveau'] ?? '') ?>)</span></div>
                    <div class="mb-3 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Suivi actif</div>
                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                        <a href="<?= site_url('parents/suivi_enfant') ?>?id=<?= urlencode((string) $enfant_id) ?>" class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">Voir le détail</a>
                        <button type="button" data-detach-child-id="<?= htmlspecialchars((string) $enfant_id, ENT_QUOTES, 'UTF-8') ?>" data-detach-child-name="<?= htmlspecialchars((string) ($enfant['prenom'] ?? $enfant['Prenom'] ?? $enfant['Username'] ?? 'cet élève'), ENT_QUOTES, 'UTF-8') ?>" class="rounded-full border border-rose-200 bg-white px-4 py-2 font-semibold text-rose-700 shadow-sm transition hover:bg-rose-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2">Détacher</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
        (function () {
            const apiUrl = <?php echo json_encode($apiBase, JSON_UNESCAPED_SLASHES); ?>;
            const detachButtons = document.querySelectorAll('[data-detach-child-id]');

            async function detachChild(childId) {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.csrfToken || ''
                    },
                    body: JSON.stringify({
                        action: 'detach_child',
                        child_user_id: childId
                    })
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || !payload.success) {
                    throw new Error(payload.error || 'Impossible de détacher cet élève.');
                }
            }

            detachButtons.forEach((button) => {
                button.addEventListener('click', async function () {
                    const childId = this.getAttribute('data-detach-child-id');
                    const childName = this.getAttribute('data-detach-child-name') || 'cet élève';

                    if (!childId) {
                        return;
                    }

                    const confirmed = window.confirm('Confirmer le détachement de ' + childName + ' ?');
                    if (!confirmed) {
                        return;
                    }

                    const previousLabel = this.textContent;
                    this.disabled = true;
                    this.textContent = 'Détachement...';

                    try {
                        await detachChild(childId);
                        window.location.reload();
                    } catch (error) {
                        alert(error.message || 'Une erreur est survenue.');
                        this.disabled = false;
                        this.textContent = previousLabel;
                    }
                });
            });
        })();
        </script>
    <?php endif; ?>
</section>
