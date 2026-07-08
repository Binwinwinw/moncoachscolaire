<?php
// $enfant attendu en entrée
$niveau = $enfant['UserLevel'] ?? '?';
$statut = $enfant['statut'] ?? 'Progrès réguliers'; // À calculer selon la logique métier
$avatar = strtoupper(substr($enfant['Prenom'] ?? $enfant['Username'] ?? 'E', 0, 1));
?>
<div class="rounded-2xl shadow-lg p-5 flex items-center gap-4 mb-4 hover:shadow-xl transition">
    <div class="w-14 h-14 bg-gradient-to-br from-indigo-300 to-blue-400 rounded-xl flex items-center justify-center text-2xl font-bold text-white">
        <?= htmlspecialchars($avatar) ?>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-bold text-gray-900 truncate"><?= htmlspecialchars($enfant['Prenom'] ?? $enfant['Username'] ?? 'Enfant') ?></p>
        <p class="text-sm text-gray-500">Niveau : <?= htmlspecialchars($niveau) ?></p>
        <p class="text-xs mt-1 <?= $statut === 'Progrès réguliers' ? 'text-emerald-600' : 'text-orange-600' ?>">
            <?= htmlspecialchars($statut) ?>
        </p>
    </div>
    <?php $childId = $enfant['Id'] ?? $enfant['id'] ?? $enfant['user_id'] ?? $enfant['enfant_id'] ?? null; ?>
    <a href="<?= site_url('parents/suivi_enfant') ?>?id=<?= urlencode((string) $childId) ?>" class="btn-elite bg-gradient-to-r from-indigo-500 to-blue-600 text-white font-semibold px-4 py-2 rounded-lg shadow hover:from-indigo-600 hover:to-blue-700 transition" aria-label="Voir le détail de la progression de <?= htmlspecialchars($enfant['Prenom'] ?? $enfant['Username'] ?? 'Enfant') ?>">Voir détail</a>
</div>
