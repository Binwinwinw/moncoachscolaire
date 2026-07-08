<?php
// Politique de confidentialité — MonCoachScolaire

$page_class = 'confidentialite-page';
$page_css = 'legal-pages.css';
$page_title = 'Confidentialité - MonCoachScolaire';

require_once dirname(__DIR__) . '/includes/legal_page_shell.php';

legal_page_bootstrap([
    'script_file' => __FILE__,
    'page_class' => 'confidentialite-page',
    'page_title' => 'Confidentialité - MonCoachScolaire',
    'page_description' => 'Politique de confidentialité et protection des données des élèves et enseignants sur MonCoachScolaire.fr',
    'page_css' => 'legal-pages.css',
]);

legal_page_render_header(
    '🔒',
    'Politique de Confidentialité',
    'Protection des données personnelles conformément au RGPD',
    'confidentialite'
);
legal_page_main_open();
?>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-blue-500 pb-2">
      ✅ 1. Conformité RGPD
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        MonCoachScolaire respecte intégralement le <strong>Règlement Général sur la Protection des Données (RGPD)</strong>
        et la loi Informatique &amp; Libertés.
      </p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li>Hébergeur en France (Hostinger EU)</li>
        <li>Serveur sécurisé HTTPS/TLS 1.3</li>
        <li>Données jamais vendues ni partagées à des tiers commerciaux</li>
        <li>Délai de suppression : 1 an maximum après inactivité prolongée</li>
      </ul>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-green-500 pb-2">
      📊 2. Données collectées
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 bg-white rounded-lg shadow text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-3 text-left font-bold text-slate-800 uppercase tracking-wider">Type</th>
              <th class="px-4 py-3 text-left font-bold text-slate-800 uppercase tracking-wider">Données</th>
              <th class="px-4 py-3 text-left font-bold text-slate-800 uppercase tracking-wider">Finalité</th>
              <th class="px-4 py-3 text-left font-bold text-slate-800 uppercase tracking-wider">Durée</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr>
              <td class="px-4 py-3 font-medium text-slate-900">Identité</td>
              <td class="px-4 py-3 text-slate-600">Nom, prénom, niveau scolaire</td>
              <td class="px-4 py-3 text-slate-600">Accès personnalisé aux exercices</td>
              <td class="px-4 py-3 text-slate-600">Supprimé à la demande</td>
            </tr>
            <tr class="bg-slate-50">
              <td class="px-4 py-3 font-medium text-slate-900">Technique</td>
              <td class="px-4 py-3 text-slate-600">IP anonymisée, User-Agent</td>
              <td class="px-4 py-3 text-slate-600">Sécurité &amp; statistiques anonymes</td>
              <td class="px-4 py-3 text-slate-600">30 jours</td>
            </tr>
            <tr>
              <td class="px-4 py-3 font-medium text-slate-900">Usage</td>
              <td class="px-4 py-3 text-slate-600">Exercices résolus, scores</td>
              <td class="px-4 py-3 text-slate-600">Suivi pédagogique</td>
              <td class="px-4 py-3 text-slate-600">1 an scolaire</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-orange-500 pb-2">
      🛡️ 3. Mesures de sécurité
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li>Chiffrement HTTPS complet sur l'ensemble du site</li>
        <li>Protection CSRF sur tous les formulaires</li>
        <li>Sessions sécurisées et requêtes SQL préparées (PDO)</li>
        <li>Interface responsive et accessible (objectif WCAG 2.1)</li>
      </ul>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-purple-500 pb-2">
      ⚖️ 4. Vos droits RGPD
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">Conformément au RGPD, vous disposez des droits suivants :</p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li><strong>Droit d'accès :</strong> Obtenir une copie de vos données personnelles</li>
        <li><strong>Droit de rectification :</strong> Corriger des informations inexactes</li>
        <li><strong>Droit à l'effacement :</strong> Demander la suppression de votre compte et de vos données</li>
        <li><strong>Droit d'opposition et de limitation :</strong> Limiter certains traitements</li>
        <li><strong>Droit à la portabilité :</strong> Recevoir vos données dans un format structuré</li>
      </ul>
      <div class="info-box bg-purple-50 border-l-4 border-purple-500 p-4 my-4">
        <p class="mb-2"><strong>📧 Contact DPO :</strong>
          <a href="mailto:dpo@moncoachscolaire.fr" class="text-purple-600 hover:underline font-semibold">dpo@moncoachscolaire.fr</a>
        </p>
        <p class="mb-0 text-sm text-slate-600">Réponse sous 72 h ouvrées — réclamation possible auprès de la
          <a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:underline">CNIL</a>.
        </p>
      </div>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-amber-500 pb-2">
      🍪 5. Cookies
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <h3 class="text-xl font-semibold text-slate-800 mt-4 mb-3">5.1. Cookies strictement nécessaires</h3>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li><strong>PHPSESSID :</strong> Maintien de la session utilisateur</li>
        <li><strong>csrf_token :</strong> Sécurisation des formulaires</li>
      </ul>
      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">5.2. Cookies tiers</h3>
      <p class="mb-3">
        MonCoachScolaire n'utilise pas de cookies publicitaires tiers (Google Analytics, Facebook Pixel, etc.)
        sans consentement explicite.
      </p>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-pink-500 pb-2">
      👶 6. Protection des mineurs
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <div class="info-box bg-amber-50 border-l-4 border-amber-500 p-4 my-4">
        <p class="mb-2 font-semibold">⚠️ Attention particulière aux mineurs</p>
        <ul class="list-disc list-inside ml-4 space-y-1 text-sm">
          <li>L'inscription d'un mineur de moins de 15 ans requiert le consentement d'un titulaire de l'autorité parentale</li>
          <li>Les données des mineurs sont traitées avec une protection renforcée</li>
          <li>Les parents peuvent consulter, modifier ou supprimer les données de leur enfant</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="legal-section bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-blue-500 pb-2">
      📧 7. Nous contacter
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">Pour toute question relative à vos données personnelles :</p>
      <div class="contact-box bg-white border-l-4 border-blue-500 p-4 my-4 rounded">
        <p class="mb-2"><strong>📧 DPO :</strong> <a href="mailto:dpo@moncoachscolaire.fr" class="text-blue-600 hover:underline font-semibold">dpo@moncoachscolaire.fr</a></p>
        <p class="mb-2"><strong>📧 Contact général :</strong> <a href="mailto:contact@moncoachscolaire.fr" class="text-blue-600 hover:underline font-semibold">contact@moncoachscolaire.fr</a></p>
      </div>
      <p class="text-sm text-slate-600 italic">
        Consultez également nos
        <a href="<?php echo htmlspecialchars(site_url('mentions-legales'), ENT_QUOTES); ?>" class="text-blue-600 hover:underline">mentions légales</a>
        et nos
        <a href="<?php echo htmlspecialchars(site_url('cgv'), ENT_QUOTES); ?>" class="text-blue-600 hover:underline">conditions générales</a>.
      </p>
    </div>
  </section>

<?php
legal_page_main_close();
if (is_file(dirname(__DIR__) . '/includes/footer.php')) {
    include_once dirname(__DIR__) . '/includes/footer.php';
}
legal_page_finish();
