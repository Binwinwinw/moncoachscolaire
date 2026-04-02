<?php
// Fil notifications scrollable (démo si vide)
?>
<section class="mb-10">
    <h2 class="text-xl md:text-2xl font-bold text-indigo-800 mb-5 flex items-center gap-2">
        <span class="text-lg">🔔</span> Alertes & notifications
    </h2>
    <div class="max-h-64 overflow-y-auto pr-2">
        <ul class="space-y-2">
            <?php
            $feed = $notifications;
if (empty($feed)) {
    $feed = [
        [ 'icone' => '✅', 'texte' => "Bienvenue sur MonCoachScolaire !", 'type' => 'succès' ],
        [ 'icone' => '📅', 'texte' => "Aucun enfant rattaché. Ajoutez-en pour suivre leur progression !", 'type' => 'info' ],
        [ 'icone' => '💡', 'texte' => "Découvrez nos guides pour accompagner vos enfants.", 'type' => 'info' ],
    ];
}
foreach ($feed as $notif): ?>
                <li class="flex items-center gap-2 rounded-xl p-3 shadow-sm border border-gray-100">
                    <span class="text-lg"><?= $notif['icone'] ?? '🔔' ?></span>
                    <span class="flex-1 text-xs md:text-sm text-gray-700"><?= htmlspecialchars($notif['texte']) ?></span>
                    <span class="text-xs font-semibold <?= ($notif['type'] ?? '') === 'succès' ? 'text-emerald-600' : 'text-orange-600' ?>">
                        <?= htmlspecialchars($notif['type'] ?? '') ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
