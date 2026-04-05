<?php
// $enfants, $progressions, $dernierExos, $matieresFortes attendus
?>
<section>
    <h2 class="text-2xl font-bold mb-4 text-indigo-700">Enfants suivis</h2>
    <?php if (empty($enfants)): ?>
        <div class="rounded-2xl p-8 text-center text-gray-500 shadow">Aucun enfant rattaché à ce compte pour l’instant.<br>Ajoutez un enfant pour suivre sa progression réelle.</div>
    <?php else: ?>
        <div class="space-y-4 mb-8">
            <?php foreach ($enfants as $enfant) {
                include __DIR__ . '/child-card.php';
            } ?>
        </div>
    <?php endif; ?>

    <h2 class="text-2xl font-bold mb-4 text-indigo-700">Progression scolaire</h2>
    <?php if (empty($progressions)): ?>
        <div class="rounded-2xl p-6 text-sm text-gray-500 shadow mb-8">La progression scolaire apparaîtra ici dès que vos enfants auront terminé leurs premiers quiz.</div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <?php foreach ($progressions as $prog): ?>
                <div class="rounded-2xl p-6 shadow-lg flex flex-col gap-2">
                    <p class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($prog['nom']) ?></p>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                        <div class="bg-emerald-500 h-3 rounded-full transition-all duration-300" style="width: <?= max(0, min(100, (int) ($prog['pourcent'] ?? 0))) ?>%;"></div>
                    </div>
                    <p class="text-sm text-gray-500"><?= (int) ($prog['pourcent'] ?? 0) ?>% du parcours</p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3 class="text-lg font-bold mb-2 text-indigo-600">Derniers exercices réalisés</h3>
    <?php if (empty($dernierExos)): ?>
        <div class="rounded-2xl p-6 text-sm text-gray-500 shadow mb-8">Aucun exercice récent à afficher pour le moment.</div>
    <?php else: ?>
        <ul class="mb-8">
            <?php foreach ($dernierExos as $exo): ?>
                <li class="flex items-center gap-2 text-sm text-gray-700 mb-1">
                    <span class="font-semibold"><?= htmlspecialchars($exo['matiere']) ?></span>
                    <span class="text-gray-400">•</span>
                    <span><?= htmlspecialchars($exo['date']) ?></span>
                    <span class="ml-auto <?= ($exo['resultat'] ?? '') === 'Réussi' ? 'text-emerald-600' : 'text-orange-600' ?>">
                        <?= htmlspecialchars($exo['resultat'] ?? '') ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h3 class="text-lg font-bold mb-2 text-indigo-600">Matières fortes / à renforcer</h3>
    <?php if (empty($matieresFortes)): ?>
        <div class="rounded-2xl p-6 text-sm text-gray-500 shadow">Les matières les plus solides apparaîtront ici quand des résultats réels seront disponibles.</div>
    <?php else: ?>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($matieresFortes as $m): ?>
                <?php $score = (float) ($m['score'] ?? 0); ?>
                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $score >= 70 ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' ?>">
                    <?= htmlspecialchars($m['nom'] ?? 'Matière') ?> <?= $score >= 70 ? '✓' : '⚠️' ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

