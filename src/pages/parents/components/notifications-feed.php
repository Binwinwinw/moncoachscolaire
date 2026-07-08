<?php
// Fil notifications scrollable.
?>
<section class="mb-10">
    <h2 class="mb-5 flex items-center gap-2 text-xl font-bold text-slate-900 md:text-2xl">
        <span class="text-lg">🔔</span> Alertes & notifications
    </h2>
    <div class="max-h-64 overflow-y-auto pr-2">
        <ul class="space-y-2">
            <?php
            $feed = isset($notifications) && is_array($notifications) ? $notifications : [];
if (empty($feed)) {
    $feed = [
        [ 'icone' => '🔔', 'texte' => "Aucune notification pour le moment.", 'type' => 'info' ],
    ];
}
foreach ($feed as $notif): ?>
                <li class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white/90 p-3 shadow-sm">
                    <span class="text-lg"><?= $notif['icone'] ?? '🔔' ?></span>
                    <span class="flex-1 text-xs text-slate-700 md:text-sm"><?= htmlspecialchars($notif['texte']) ?></span>
                    <span class="text-xs font-semibold <?= ($notif['type'] ?? '') === 'succès' ? 'text-emerald-600' : (($notif['type'] ?? '') === 'warning' ? 'text-amber-600' : 'text-sky-600') ?>">
                        <?= htmlspecialchars($notif['type'] ?? '') ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
