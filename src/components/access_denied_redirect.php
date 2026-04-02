<?php
/**
 * Composant : access_denied_redirect.php
 * Affiche un message d'accès refusé avec décompte et redirection automatique.
 * Usage : include_once __DIR__.'/access_denied_redirect.php';
 * Paramètres : $targetDashboardUrl (URL dashboard), $userLevel (niveau actuel), $requiredLevel (niveau requis)
 */

$targetDashboardUrl = $targetDashboardUrl ?? '/index.php?page=eleve/dashboard';
$userLevel = $userLevel ?? '6ème';
$requiredLevel = $requiredLevel ?? 'BAC';
$seconds = $seconds ?? 10;
?>
<div class="access-denied-redirect bg-slate-100 border border-slate-300 rounded-lg p-6 max-w-xl mx-auto mt-12 shadow-md text-center">
    <p class="text-2xl font-semibold text-slate-700 mb-2">🔒 Accès refusé</p>
    <p class="text-lg text-slate-600 mb-2">Ce contenu est destiné aux élèves de niveau <strong><?= htmlspecialchars($requiredLevel) ?></strong>.</p>
    <p class="text-md text-slate-600 mb-2">Vous êtes actuellement en niveau <strong><?= htmlspecialchars($userLevel) ?></strong>.</p>
    <p class="text-md text-slate-600 mb-4">Pour débloquer ce contenu, progressez dans votre parcours actuel ou contactez votre coach.</p>
    <p class="countdown-info text-base font-medium bg-blue-50 text-blue-700 inline-block px-4 py-2 rounded mb-2">
        Redirection automatique vers votre dashboard dans <span id="countdown-access-denied"><?= $seconds ?></span> secondes...
    </p>
</div>
<script>
(function() {
    let seconds = <?= $seconds ?>;
    const countdown = document.getElementById('countdown-access-denied');
    const interval = setInterval(() => {
        seconds--;
        countdown.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(interval);
            window.location.href = "<?= htmlspecialchars($targetDashboardUrl) ?>";
        }
    }, 1000);
})();
</script>
