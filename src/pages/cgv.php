<?php
// Conditions Générales de Vente / Utilisation — MonCoachScolaire

$page_class = 'cgv-page';
$page_css = 'legal-pages.css';
$page_title = 'Conditions Générales de Vente - MonCoachScolaire';

require_once dirname(__DIR__) . '/includes/legal_page_shell.php';

legal_page_bootstrap([
    'script_file' => __FILE__,
    'page_class' => 'cgv-page',
    'page_title' => 'Conditions Générales de Vente - MonCoachScolaire',
    'page_description' => 'Conditions générales de vente et d\'utilisation de la plateforme MonCoachScolaire',
    'page_css' => 'legal-pages.css',
]);

legal_page_render_header(
    '📜',
    'Conditions Générales de Vente',
    'Conditions d\'utilisation de la plateforme MonCoachScolaire',
    'cgv'
);
legal_page_main_open();
?>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-blue-500 pb-2">
      📋 Introduction
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        Bienvenue sur <strong>MonCoachScolaire</strong>. L'accès et l'utilisation de notre plateforme impliquent
        l'acceptation pleine et entière des présentes conditions d'utilisation.
      </p>
      <div class="info-box bg-blue-50 border-l-4 border-blue-500 p-4 my-4">
        <p class="mb-0"><strong>Important :</strong> En utilisant notre plateforme, vous acceptez ces conditions.
        Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre service.</p>
      </div>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-green-500 pb-2">
      🎯 1. Objet
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        <strong>MonCoachScolaire</strong> propose des contenus éducatifs et des outils de suivi pour les élèves,
        parents et enseignants. Notre mission est de fournir un environnement d'apprentissage sécurisé et efficace.
      </p>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-purple-500 pb-2">
      🔑 2. Accès au service
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li><strong>Création de compte :</strong> L'accès à certains services nécessite la création d'un compte utilisateur. Vous vous engagez à fournir des informations exactes et à jour.</li>
        <li><strong>Protection des mineurs :</strong> Les utilisateurs de moins de 15 ans doivent avoir obtenu le consentement parental pour créer un compte.</li>
        <li><strong>Comptes sécurisés :</strong> Vous êtes responsable de la confidentialité de vos identifiants et de toutes les activités réalisées sous votre compte.</li>
      </ul>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-orange-500 pb-2">
      ⚖️ 3. Responsabilités
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <h3 class="text-xl font-semibold text-slate-800 mt-4 mb-3">3.1. Responsabilités de l'utilisateur</h3>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li>Respecter la législation en vigueur et ne pas porter atteinte à l'intégrité de la plateforme</li>
        <li>Ne pas utiliser le service à des fins illégales ou non autorisées</li>
        <li>Ne pas tenter d'accéder à des zones restreintes ou de contourner les mesures de sécurité</li>
        <li>Ne pas publier de contenu offensant, diffamatoire ou contraire aux bonnes mœurs</li>
      </ul>
      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">3.2. Responsabilités de MonCoachScolaire</h3>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li>Fournir un service conforme aux descriptions présentées sur la plateforme</li>
        <li>Assurer la sécurité et la disponibilité du service dans la mesure du possible</li>
        <li>Respecter la confidentialité des données utilisateur conformément à notre
          <a href="<?php echo htmlspecialchars(site_url('confidentialite'), ENT_QUOTES); ?>" class="text-blue-600 hover:underline">politique de confidentialité</a></li>
      </ul>
      <div class="info-box bg-amber-50 border-l-4 border-amber-500 p-4 my-4">
        <p class="mb-0"><strong>Limitation de responsabilité :</strong> MonCoachScolaire ne saurait être tenu responsable des contenus publiés par les utilisateurs ou des dommages résultant de l'utilisation ou de l'impossibilité d'utiliser la plateforme.</p>
      </div>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-pink-500 pb-2">
      ©️ 4. Propriété intellectuelle
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        Tous les contenus présents sur la plateforme (textes, images, logos, vidéos, exercices) sont la propriété exclusive de
        <strong>MonCoachScolaire</strong> ou de ses partenaires et sont protégés par le droit d'auteur.
      </p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li><strong>Usage personnel :</strong> Vous pouvez consulter et utiliser les contenus uniquement à des fins éducatives personnelles.</li>
        <li><strong>Interdiction de reproduction :</strong> Toute reproduction, représentation, modification ou publication sans autorisation écrite préalable est interdite.</li>
        <li><strong>Contenus utilisateurs :</strong> Les contenus que vous publiez restent votre propriété, mais vous accordez à MonCoachScolaire une licence d'utilisation pour les besoins du service.</li>
      </ul>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-indigo-500 pb-2">
      🔒 5. Données personnelles
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        La collecte et le traitement des données personnelles sont détaillés dans notre
        <a href="<?php echo htmlspecialchars(site_url('confidentialite'), ENT_QUOTES); ?>" class="text-indigo-600 hover:underline font-semibold">Politique de confidentialité &amp; RGPD</a>.
        En utilisant notre service, vous acceptez notre politique de confidentialité.
      </p>
      <div class="info-box bg-blue-50 border-l-4 border-blue-500 p-4 my-4">
        <p class="mb-0"><strong>Consentement :</strong> Pour les mineurs de moins de 15 ans, le consentement parental est requis conformément au RGPD.</p>
      </div>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-red-500 pb-2">
      📝 6. Modification des conditions
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        <strong>MonCoachScolaire</strong> se réserve le droit de modifier les présentes conditions à tout moment.
        Les modifications entrent en vigueur dès leur publication sur la plateforme.
      </p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li>Les utilisateurs seront informés des changements majeurs par email ou via une notification visible sur la plateforme</li>
        <li>L'utilisation continue du service après la modification des conditions constitue une acceptation des nouvelles conditions</li>
        <li>Si vous n'acceptez pas les modifications, vous devez cesser d'utiliser le service</li>
      </ul>
    </div>
  </section>

  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-gray-500 pb-2">
      🚪 7. Résiliation
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        Vous pouvez résilier votre compte à tout moment depuis les paramètres de votre profil.
        MonCoachScolaire se réserve également le droit de suspendre ou résilier votre compte en cas de violation des présentes conditions.
      </p>
    </div>
  </section>

  <section class="legal-section bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-blue-500 pb-2">
      📧 8. Nous contacter
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">Pour toute question concernant ces conditions d'utilisation :</p>
      <div class="contact-box bg-white border-l-4 border-blue-500 p-4 my-4 rounded">
        <p class="mb-2"><strong>📧 Email :</strong> <a href="mailto:contact@moncoachscolaire.fr" class="text-blue-600 hover:underline font-semibold">contact@moncoachscolaire.fr</a></p>
        <p class="mb-2"><strong>📞 Téléphone :</strong> (+596) 696 30 26 60</p>
        <p class="mb-2"><strong>✉️ Adresse :</strong></p>
        <p class="ml-4 text-sm">
          MonCoachScolaire<br>
          xxxxxxxxxx<br>
          xxxxxxxxxx, xxxxxxxxxx
        </p>
      </div>
    </div>
  </section>

<?php
legal_page_main_close();
if (is_file(dirname(__DIR__) . '/includes/footer.php')) {
    include_once dirname(__DIR__) . '/includes/footer.php';
}
legal_page_finish();
