<?php
// Grille enfants (cartes démo si vide)
$defaultAvatar = function_exists('asset_url') ? asset_url('assets/img/avatar-default.png') : '/assets/img/avatar-default.png';
$build_avatar_url = function($path) {
    if (function_exists('asset_url')) {
        // accepter n'importe quel chemin relatif ou /assets/...
        $path = ltrim((string)$path, '/');
        return asset_url($path);
    }
    return $path;
};
?>
<section class="mb-10">
    <h2 class="text-xl md:text-2xl font-bold text-indigo-800 mb-5 flex items-center gap-2">
        <span class="text-lg">👨‍👩‍👧‍👦</span> Vos enfants suivis
    </h2>
    <?php if (empty($enfants)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php
            $demoEnfants = [
                [
                    'prenom' => 'Emma',
                    'niveau' => '6e',
                    'avatar' => $build_avatar_url('assets/img/avatar-demo-6e.png'),
                    'etat' => 'Démo',
                ],
                [
                    'prenom' => 'Lucas',
                    'niveau' => '3e',
                    'avatar' => $build_avatar_url('assets/img/avatar-demo-3e.png'),
                    'etat' => 'Démo',
                ],
            ];
        foreach ($demoEnfants as $enfant): ?>
                <div class="rounded-2xl shadow p-6 flex flex-col items-center hover:scale-[1.02] transition-transform">
                    <?php $avatar = isset($enfant['avatar']) ? $build_avatar_url($enfant['avatar']) : $defaultAvatar; ?>
                    <img src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="Avatar" class="w-16 h-16 rounded-full mb-2 border-2 border-indigo-100">
                    <div class="font-semibold text-base text-indigo-900 mb-1"><?= htmlspecialchars($enfant['prenom']) ?> <span class="text-xs text-gray-400 align-top">(<?= $enfant['niveau'] ?>)</span></div>
                    <div class="text-xs text-gray-500 mb-2">Compte démo</div>
                    <a href="<?= site_url('parents/ajouter_enfant') ?>" class="mt-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-full font-semibold border border-indigo-100 shadow-sm hover:bg-indigo-100">Ajouter un enfant</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($enfants as $enfant): ?>
                <div class="rounded-2xl shadow p-6 flex flex-col items-center hover:scale-[1.02] transition-transform">
                    <?php $avatar = isset($enfant['avatar']) ? $build_avatar_url($enfant['avatar']) : $defaultAvatar; ?>
                    <img src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="Avatar" class="w-16 h-16 rounded-full mb-2 border-2 border-indigo-100">
                    <div class="font-semibold text-base text-indigo-900 mb-1"><?= htmlspecialchars($enfant['prenom'] ?? $enfant['Prenom'] ?? 'Enfant') ?> <span class="text-xs text-gray-400 align-top">(<?= htmlspecialchars($enfant['niveau'] ?? $enfant['Niveau'] ?? '') ?>)</span></div>
                    <div class="text-xs text-gray-500 mb-2">Suivi actif</div>
                    <a href="<?= site_url('parents/suivi_enfant') ?>?id=<?= $enfant['user_id'] ?? $enfant['enfant_id'] ?? '' ?>" class="mt-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-full font-semibold border border-indigo-100 shadow-sm hover:bg-indigo-100">Voir le détail</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
