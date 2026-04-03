<?php
// Composant Footer réutilisable
// Attendu : variables auxiliaires optionnelles définies par le wrapper
// - $exercices_url (string) : URL du lien Exercices
// - $show_bac_link (bool) : afficher le lien BAC

$exercices_url = $exercices_url ?? (function_exists('get_exercices_url_from_session') ? get_exercices_url_from_session() : (function_exists('site_url') ? site_url('exercices') : '/index.php?page=exercices'));
$show_bac_link = isset($show_bac_link) ? (bool) $show_bac_link : false;
?>
<!-- Footer (composant) -->
<?php $footerCss = function_exists('asset_url') ? asset_url('assets/css/components/footer.css') : '/assets/css/components/footer.css'; ?>
<link rel="stylesheet" href="<?= htmlspecialchars($footerCss, ENT_QUOTES) ?>">
<footer class="site-footer" role="contentinfo" aria-label="Pied de page du site">
    <div class="site-footer-inner">
        <div class="footer-grid">

            <!-- Column 1: Brand -->
            <div class="footer-section footer-brand">
                <div class="brand-inner flex items-center gap-2">
                    <span class="logo-icon text-xl align-middle" aria-hidden="true">📚</span>
                    <h3 class="brand-title text-slate-700 font-semibold align-middle">MonCoachScolaire</h3>
                </div>
                <p class="brand-desc">Ton coach pédagogique personnalisé pour réussir du collège au bac. Apprentissage ludique, suivi individualisé, réussite garantie !</p>
            </div>

            <!-- Column 2: Quick links -->
            <div class="footer-section">
                <h4>🔗 Liens rapides</h4>
                <ul class="footer-links-list">
                    <li><a href="<?= htmlspecialchars(site_url('college/college-accueil'), ENT_QUOTES) ?>"><span class="footer-link-icon">📚</span> Collège+</a></li>
                    <li><a href="<?= htmlspecialchars(site_url('lycee/lycee-accueil'), ENT_QUOTES) ?>"><span class="footer-link-icon">🎓</span> Lycée+</a></li>
                    <li><a href="<?= htmlspecialchars(site_url('eleve/bac/bac-accueil'), ENT_QUOTES) ?>"><span class="footer-link-icon">🏆</span> Préparer le Bac</a></li>
                    <?php if ($show_bac_link): ?>
                        <li><a href="<?= htmlspecialchars(site_url('bac/bac-accueil'), ENT_QUOTES) ?>"><span class="footer-link-icon">🏆</span> Chemin du BAC</a></li>
                    <?php endif; ?>
                    <li><a href="<?= htmlspecialchars(site_url('contact'), ENT_QUOTES) ?>"><span class="footer-link-icon">📞</span> Contact</a></li>
                </ul>
            </div>

            <!-- Column 3: Resources -->
            <div class="footer-section">
                <h4>📚 Ressources</h4>
                <ul class="footer-links-list">
                    <li><a href="<?= htmlspecialchars(site_url('cours'), ENT_QUOTES) ?>"><span class="footer-link-icon">📖</span> Cours en ligne</a></li>
                    <li><a href="<?= htmlspecialchars(function_exists('site_url') ? site_url('exercices') : '/index.php?page=exercices', ENT_QUOTES) ?>"><span class="footer-link-icon">✏️</span> Exercices</a></li>
                </ul>
            </div>

            <!-- Column 4: RGPD -->
            <div class="footer-section">
                <h4>Certifié conforme au RGPD</h4>
                <p>Notre plateforme respecte scrupuleusement les réglementations européennes sur la protection des données.</p>
            </div>

        </div>

        <!-- Divider -->
        <hr class="divider" />

        <!-- Footer meta -->
        <div class="footer-meta">
            <p class="copyright">© 2025 MonCoachScolaire - Tous droits réservés</p>
            <div class="meta-links">
                <a href="<?= htmlspecialchars(site_url('mentions-legales'), ENT_QUOTES) ?>">Mentions légales</a>
                <a href="<?= htmlspecialchars(site_url('confidentialite'), ENT_QUOTES) ?>">Confidentialité</a>
                <a href="<?= htmlspecialchars(site_url('cgv'), ENT_QUOTES) ?>">CGV</a>
            </div>
        </div>
    </div>
</footer>
<!-- /Footer (composant) -->
