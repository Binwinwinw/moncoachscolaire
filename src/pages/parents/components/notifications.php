<?php
// $notifications, $objectifs, $conseils attendus
?>
<section class="mb-8">
    <h2 class="text-xl font-bold mb-4 text-indigo-700">Alertes & notifications</h2>
    <ul class="space-y-3 mb-6">
        <?php foreach (array_slice(is_array($notifications) ? $notifications : [], 0, 5) as $notif): ?>
            <li class="flex items-center gap-3 mcs-card-bg rounded-xl p-3 shadow">
                <span class="text-2xl"><?= $notif['icone'] ?? '🔔' ?></span>
                <span class="flex-1 text-sm"><?= htmlspecialchars($notif['texte']) ?></span>
                <span class="text-xs font-semibold <?= $notif['type'] === 'succès' ? 'text-emerald-600' : 'text-orange-600' ?>">
                    <?= htmlspecialchars($notif['type']) ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>
    <h3 class="text-lg font-bold mb-2 text-indigo-600">Objectifs de la semaine</h3>
    <ul class="mb-4">
        <?php foreach ($objectifs as $obj): ?>
            <li class="flex items-center gap-2 text-sm text-gray-700 mb-1">
                <span class="text-emerald-600">🎯</span>
                <?= htmlspecialchars($obj) ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <h3 class="text-lg font-bold mb-2 text-indigo-600">Astuces pédagogiques</h3>
    <ul>
        <?php foreach ($conseils as $c): ?>
            <li class="text-xs text-gray-500 mb-1">💡 <?= htmlspecialchars($c) ?></li>
        <?php endforeach; ?>
    </ul>
</section>
