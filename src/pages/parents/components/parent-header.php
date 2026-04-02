<?php
$parentName = $parent['Prenom'] ?? $parent['Nom'] ?? $parent['Username'] ?? 'Parent';
?>
<div class="mcs-card-bg rounded-3xl shadow-2xl p-6 mb-8 flex flex-col items-center text-center">
    <div class="w-20 h-20 bg-gradient-to-br from-indigo-400 to-blue-500 rounded-2xl flex items-center justify-center mb-4">
        <span class="text-4xl">👨‍👩‍👧‍👦</span>
    </div>
    <h1 class="text-3xl font-black bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent mb-2">
        Bienvenue, <?= htmlspecialchars($parentName) ?> !
    </h1>
    <p class="text-base text-gray-700 mb-4">Suivez la progression de vos enfants, encouragez-les et découvrez des conseils personnalisés pour les accompagner au quotidien.</p>
    <div class="flex flex-col gap-3 w-full">
        <a href="<?= site_url('parents/suivi_enfant') ?>" class="btn-elite bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold py-2 shadow hover:from-emerald-600 hover:to-green-700 transition" aria-label="Voir le détail des progrès de vos enfants">Voir le détail des progrès</a>
        <!-- Lien vers parents/parents supprimé : Gérer le compte -->
    </div>
</div>
