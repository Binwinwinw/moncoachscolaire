<?php
// Hero onboarding premium (état vide)
$hero_video_src = function_exists('asset_url') ? asset_url('assets/video/parent-hero-demo.mp4') : '/assets/video/parent-hero-demo.mp4';
$hero_img_src = function_exists('asset_url') ? asset_url('assets/img/parent-famille-demo.png') : '/assets/img/parent-famille-demo.png';
?>
<section class="relative flex flex-col items-center justify-center min-h-[340px] md:min-h-[420px] rounded-3xl shadow-lg overflow-hidden mb-8">
    <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover opacity-10 pointer-events-none">
        <source src="<?= htmlspecialchars($hero_video_src, ENT_QUOTES) ?>" type="video/mp4">
    </video>
    <div class="relative z-10 flex flex-col items-center justify-center py-10 px-4 md:px-12">
        <h1 class="text-3xl md:text-5xl font-bold text-indigo-900 mb-3">Bienvenue sur MonCoachScolaire</h1>
        <p class="text-base md:text-xl text-gray-700 mb-5 max-w-xl text-center">Suivez la progression de vos enfants, encouragez-les et découvrez des conseils personnalisés pour les accompagner au quotidien.</p>
        <a href="<?= site_url('parents/ajouter_enfant') ?>" class="inline-block bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-base px-7 py-3 rounded-full shadow transition-all duration-200 mb-4 border border-indigo-200">+ Ajouter mon enfant</a>
        <div class="flex flex-col md:flex-row gap-4 items-center mt-2">
            <img src="<?= htmlspecialchars($hero_img_src, ENT_QUOTES) ?>" alt="Famille" class="w-20 h-20 rounded-full shadow border-2 border-white/80">
            <div class="text-gray-700 text-sm md:text-base max-w-xs">
                <span class="font-semibold">Nouveau&nbsp;?</span> Regardez notre vidéo tutoriel (30s) ou découvrez les témoignages de parents satisfaits.
            </div>
        </div>
    </div>
</section>

