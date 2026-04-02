<?php
// Page des Mentions Légales - MonCoachScolaire
// Cette page présente les informations légales obligatoires conformément à la loi française

$page_class = $page_class ?? 'mentions-legales-page';
$page_css = $page_css ?? 'legal-pages.css'; // CSS unifié pour toutes les pages légales
$page_title = $page_title ?? 'Mentions Légales - MonCoachScolaire';

// Bootstrap site helpers when accessed directly
$direct_access = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));
if ($direct_access) {
    $site_boot = __DIR__ . '/site_boot.php';
    if (!is_file($site_boot)) {
        $site_boot = __DIR__ . '/bootstrap/site_boot.php';
    }
    if (is_file($site_boot)) {
        require_once $site_boot;
    }

    $page_class = $page_class ?: 'mentions-legales-page';
    if (empty($page_css)) {
        $page_css = 'legal.css';
    }
    if (empty($page_title)) {
        $page_title = 'Mentions Légales - MonCoachScolaire';
    }

    ?><!doctype html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="description" content="Mentions légales du site MonCoachScolaire - Informations légales obligatoires">
        <title><?php echo htmlspecialchars($page_title); ?></title>
        <?php
        $root = rtrim($baseUrl ?? '', '/');
    if (!$root) {
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir && $scriptDir !== '.' && $scriptDir !== '/') {
            $root = $scriptDir;
        } else {
            $root = '';
        }
    }

    if ($root !== ''): ?>
          <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/tailwind.css') : ($root . '/assets/css/tailwind.css'); ?>">
          <?php if (!empty($page_css)): ?>
            <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/' . $page_css) : ($root . '/assets/css/pages/' . htmlspecialchars($page_css)); ?>">
          <?php endif; ?>
        <?php else: ?>
          <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/tailwind.css') : 'assets/css/tailwind.css'; ?>">
          <?php if (!empty($page_css)): ?>
            <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/' . $page_css) : ('assets/css/pages/' . htmlspecialchars($page_css)); ?>">
          <?php endif; ?>
        <?php endif; ?>
    </head>
    <body class="app-bg <?php echo htmlspecialchars($page_class ?? ''); ?>">
    <?php
    if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
        require_once dirname(__DIR__, 2) . '/includes/topbar.php';
    }
}

// Charger les fichiers nécessaires
if (is_file(dirname(__DIR__, 2) . '/database/connection.php')) {
    require_once dirname(__DIR__, 2) . '/database/connection.php';
}

if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}

// Gestion de session
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        }
    }
}

$current_year = date('Y');
?>

<!-- Header de la page -->
<header class="flex flex-col gap-2 items-center justify-center text-center py-8 px-4 mx-auto my-8 max-w-screen-xl bg-white/70 backdrop-blur-md rounded-xl shadow-lg" role="banner">
  <h1 class="m-0 max-w-4xl text-4xl md:text-5xl font-bold text-slate-800 leading-tight">
    ⚖️ Mentions Légales
  </h1>
  <p class="lead m-0 max-w-2xl text-lg text-slate-600 font-medium">
    Informations légales obligatoires concernant le site MonCoachScolaire
  </p>
  <p class="text-sm text-slate-500 mt-2">
    <em>Dernière mise à jour : <?php echo date('d/m/Y'); ?></em>
  </p>
</header>

<main class="legal-content mx-auto my-12 max-w-screen-lg px-4">

  <!-- Section 1 : Éditeur du site -->
  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-blue-500 pb-2">
      📋 1. Éditeur du site
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        Le site <strong>MonCoachScolaire</strong> accessible à l'adresse <strong>moncoachscolaire.fr</strong> est édité par :
      </p>

      <div class="info-box bg-blue-50 border-l-4 border-blue-500 p-4 my-4">
        <p class="mb-2"><strong>Raison sociale :</strong> MonCoachScolaire SAS</p>
        <p class="mb-2"><strong>Forme juridique :</strong> SAS</p>
        <p class="mb-2"><strong>Capital social :</strong> 10 000 euros</p>
        <p class="mb-2"><strong>Siège social :</strong> 10 rue de l'Éducation, 75010 Paris, France</p>
        <p class="mb-2"><strong>SIRET :</strong> 89912345678901</p>
        <p class="mb-2"><strong>TVA intracommunautaire :</strong> FR56899123456</p>
        <p class="mb-2"><strong>Téléphone :</strong> 01 23 45 67 89</p>
        <p class="mb-2"><strong>Email :</strong> <a href="mailto:contact@moncoachscolaire.fr" class="text-blue-600 hover:underline">contact@moncoachscolaire.fr</a></p>
      </div>

      <p class="mb-3">
        <strong>Directeur de la publication :</strong> Jeanne Martin
      </p>

      <p class="mb-3 text-sm text-slate-600">
        <em>Note : En cas d'entreprise individuelle ou auto-entrepreneur, le directeur de publication est généralement le propriétaire du site.</em>
      </p>
    </div>
  </section>

  <!-- Section 2 : Hébergement -->
  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-green-500 pb-2">
      🖥️ 2. Hébergement du site
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        Le site <strong>MonCoachScolaire</strong> est hébergé par :
      </p>

      <div class="info-box bg-green-50 border-l-4 border-green-500 p-4 my-4">
        <p class="mb-2"><strong>Hébergeur :</strong> Hostinger International Ltd.</p>
        <p class="mb-2"><strong>Adresse :</strong> 61 Lordou Vironos Street, 6023 Larnaca, Chypre</p>
        <p class="mb-2"><strong>Téléphone :</strong> +357 24 030 595</p>
        <p class="mb-2"><strong>Site web :</strong> <a href="https://www.hostinger.fr" target="_blank" rel="noopener noreferrer" class="text-green-600 hover:underline">https://www.hostinger.fr</a></p>
      </div>

      <p class="text-sm text-slate-600 italic">
        Conformément à la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique.
      </p>
    </div>
  </section>

  <!-- Section 3 : Protection des données personnelles -->
  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-purple-500 pb-2">
      🔒 3. Protection des données personnelles (RGPD)
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">

      <h3 class="text-xl font-semibold text-slate-800 mt-4 mb-3">3.1. Responsable du traitement</h3>
      <p class="mb-3">
        Le responsable du traitement des données personnelles collectées sur le site est :
        <strong>Jeanne Martin</strong>, joignable à l'adresse email suivante :
        <a href="mailto:dpo@moncoachscolaire.fr" class="text-purple-600 hover:underline">dpo@moncoachscolaire.fr</a>
      </p>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">3.2. Données collectées</h3>
      <p class="mb-3">
        Dans le cadre de l'utilisation du site MonCoachScolaire, nous sommes amenés à collecter les données personnelles suivantes :
      </p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li><strong>Comptes élèves :</strong> Nom, prénom, niveau scolaire, adresse email (si applicable), progression pédagogique</li>
        <li><strong>Comptes parents :</strong> Nom, prénom, adresse email, numéro de téléphone (si fourni)</li>
        <li><strong>Comptes administrateurs :</strong> Nom, prénom, adresse email, identifiants de connexion</li>
        <li><strong>Données de navigation :</strong> Adresse IP, pages visitées, durée de session, type de navigateur</li>
        <li><strong>Cookies :</strong> Cookies de session, cookies de préférence (voir section Cookies)</li>
      </ul>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">3.3. Finalité du traitement</h3>
      <p class="mb-3">Les données personnelles collectées sont utilisées pour :</p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li>La création et la gestion des comptes utilisateurs</li>
        <li>Le suivi de la progression pédagogique des élèves</li>
        <li>L'envoi de communications relatives au service (notifications, mises à jour)</li>
        <li>L'amélioration de nos services et de l'expérience utilisateur</li>
        <li>La sécurité et la prévention de la fraude</li>
        <li>Le respect de nos obligations légales</li>
      </ul>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">3.4. Base légale du traitement</h3>
      <p class="mb-3">Le traitement de vos données repose sur :</p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li><strong>Exécution du contrat :</strong> Pour la fourniture des services éducatifs</li>
        <li><strong>Consentement :</strong> Pour l'envoi de communications marketing (optionnel)</li>
        <li><strong>Intérêt légitime :</strong> Pour l'amélioration de nos services</li>
        <li><strong>Obligation légale :</strong> Pour la conservation de certaines données comptables</li>
      </ul>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">3.5. Protection des mineurs</h3>
      <div class="info-box bg-amber-50 border-l-4 border-amber-500 p-4 my-4">
        <p class="mb-2 font-semibold">⚠️ Attention particulière aux mineurs</p>
        <p class="mb-2">
          MonCoachScolaire s'adresse principalement à des mineurs (collégiens et lycéens). Conformément au RGPD :
        </p>
        <ul class="list-disc list-inside ml-4 space-y-1 text-sm">
          <li>L'inscription d'un mineur de moins de 15 ans requiert le consentement d'un titulaire de l'autorité parentale</li>
          <li>Les données des mineurs sont traitées avec une protection renforcée</li>
          <li>Aucune donnée personnelle superflue n'est collectée</li>
          <li>Les parents peuvent à tout moment consulter, modifier ou supprimer les données de leur enfant</li>
        </ul>
      </div>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">3.6. Durée de conservation</h3>
      <p class="mb-3">Vos données personnelles sont conservées pour la durée suivante :</p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li><strong>Données de compte actif :</strong> Pendant toute la durée d'utilisation du service</li>
        <li><strong>Données de compte inactif :</strong> 3 ans après la dernière connexion</li>
        <li><strong>Données de progression :</strong> Pendant toute la durée d'utilisation du service</li>
        <li><strong>Données de facturation :</strong> 10 ans (obligation légale)</li>
        <li><strong>Logs de connexion :</strong> 12 mois maximum</li>
      </ul>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">3.7. Vos droits</h3>
      <p class="mb-3">
        Conformément au Règlement Général sur la Protection des Données (RGPD) et à la loi Informatique et Libertés,
        vous disposez des droits suivants concernant vos données personnelles :
      </p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li><strong>Droit d'accès :</strong> Obtenir une copie de vos données personnelles</li>
        <li><strong>Droit de rectification :</strong> Corriger des données inexactes ou incomplètes</li>
        <li><strong>Droit à l'effacement :</strong> Demander la suppression de vos données</li>
        <li><strong>Droit à la limitation :</strong> Limiter le traitement de vos données</li>
        <li><strong>Droit à la portabilité :</strong> Recevoir vos données dans un format structuré</li>
        <li><strong>Droit d'opposition :</strong> Vous opposer au traitement de vos données</li>
        <li><strong>Droit de retirer votre consentement :</strong> À tout moment, sans affecter la licéité du traitement</li>
      </ul>

      <p class="mb-3">
        Pour exercer ces droits, vous pouvez nous contacter à l'adresse :
        <a href="mailto:dpo@moncoachscolaire.fr" class="text-purple-600 hover:underline font-semibold">dpo@moncoachscolaire.fr</a>
      </p>

      <p class="mb-3">
        Vous disposez également du droit d'introduire une réclamation auprès de la CNIL
        (<a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:underline">www.cnil.fr</a>).
      </p>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">3.8. Sécurité des données</h3>
      <p class="mb-3">
        Nous mettons en œuvre toutes les mesures techniques et organisationnelles appropriées pour protéger vos données personnelles contre :
      </p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li>L'accès non autorisé</li>
        <li>La divulgation, l'altération ou la destruction</li>
        <li>Les pertes accidentelles</li>
      </ul>
      <p class="mb-3">
        Les mots de passe sont chiffrés, les connexions sont sécurisées via HTTPS, et les accès à la base de données sont restreints.
      </p>
    </div>
  </section>

  <!-- Section 4 : Cookies -->
  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-orange-500 pb-2">
      🍪 4. Politique de cookies
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">

      <h3 class="text-xl font-semibold text-slate-800 mt-4 mb-3">4.1. Qu'est-ce qu'un cookie ?</h3>
      <p class="mb-3">
        Un cookie est un petit fichier texte déposé sur votre terminal (ordinateur, tablette, smartphone) lors de la visite d'un site web.
        Il permet de mémoriser des informations relatives à votre navigation.
      </p>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">4.2. Cookies utilisés sur MonCoachScolaire</h3>

      <div class="cookie-type mb-4">
        <h4 class="text-lg font-semibold text-slate-800 mb-2">🔐 Cookies strictement nécessaires (exemptés de consentement)</h4>
        <ul class="list-disc list-inside ml-4 space-y-1 text-sm">
          <li><strong>Cookies de session :</strong> Maintenir votre connexion active pendant votre visite</li>
          <li><strong>Cookies de sécurité :</strong> Protéger contre les attaques CSRF et sécuriser les formulaires</li>
          <li><strong>Cookies de préférence :</strong> Mémoriser vos choix de langue ou d'affichage</li>
        </ul>
      </div>

      <div class="cookie-type mb-4">
        <h4 class="text-lg font-semibold text-slate-800 mb-2">📊 Cookies analytiques (nécessitent votre consentement)</h4>
        <p class="text-sm mb-2">
          Ces cookies nous permettent de comprendre comment les visiteurs utilisent le site (pages visitées, temps passé, etc.)
          afin d'améliorer nos services. Nous n'utilisons que des outils respectueux de la vie privée.
        </p>
      </div>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">4.3. Gestion des cookies</h3>
      <p class="mb-3">
        Vous pouvez à tout moment gérer vos préférences de cookies via :
      </p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-2">
        <li>Le bandeau de consentement qui s'affiche lors de votre première visite</li>
        <li>Les paramètres de votre navigateur (consulter la documentation de votre navigateur)</li>
        <li>Notre page de gestion des cookies (si disponible)</li>
      </ul>

      <p class="text-sm text-slate-600 italic">
        Attention : Le refus de certains cookies peut empêcher l'utilisation de certaines fonctionnalités du site.
      </p>
    </div>
  </section>

  <!-- Section 5 : Propriété intellectuelle -->
  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-pink-500 pb-2">
      ©️ 5. Propriété intellectuelle
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">

      <h3 class="text-xl font-semibold text-slate-800 mt-4 mb-3">5.1. Contenu du site</h3>
      <p class="mb-3">
        L'ensemble du contenu du site MonCoachScolaire (structure, textes, graphismes, logos, icônes, sons, logiciels, exercices, cours, quiz, etc.)
        est la propriété exclusive de <strong>[NOM DE VOTRE SOCIÉTÉ]</strong> ou de ses partenaires,
        et est protégé par les lois françaises et internationales relatives à la propriété intellectuelle.
      </p>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">5.2. Utilisation autorisée</h3>
      <p class="mb-3">
        Toute reproduction, représentation, modification, publication, adaptation totale ou partielle du site ou de son contenu,
        par quelque procédé que ce soit, est interdite sans l'autorisation écrite préalable de l'éditeur,
        sauf pour les besoins de consultation normale du site et à des fins strictement personnelles et pédagogiques.
      </p>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">5.3. Usage pédagogique</h3>
      <div class="info-box bg-pink-50 border-l-4 border-pink-500 p-4 my-4">
        <p class="mb-2">
          <strong>✅ Usage autorisé :</strong> Les élèves inscrits peuvent utiliser les ressources pédagogiques
          (exercices, cours, quiz) pour leur usage personnel dans le cadre de leur scolarité.
        </p>
        <p class="mb-2">
          <strong>❌ Usage interdit :</strong> Toute diffusion, reproduction ou commercialisation
          des contenus pédagogiques à des tiers est strictement interdite.
        </p>
      </div>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">5.4. Marques et logos</h3>
      <p class="mb-3">
        Les marques, logos et signes distinctifs reproduits sur le site sont la propriété de
        <strong>[NOM DE VOTRE SOCIÉTÉ]</strong> ou de ses partenaires.
        Toute reproduction ou utilisation sans autorisation expresse est interdite.
      </p>
    </div>
  </section>

  <!-- Section 6 : Limitation de responsabilité -->
  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-red-500 pb-2">
      ⚠️ 6. Limitation de responsabilité
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">

      <h3 class="text-xl font-semibold text-slate-800 mt-4 mb-3">6.1. Disponibilité du site</h3>
      <p class="mb-3">
        L'éditeur s'efforce d'assurer au mieux la disponibilité et l'accessibilité du site.
        Toutefois, l'éditeur ne peut garantir que le site soit accessible de manière continue et ininterrompue,
        notamment en raison de :
      </p>
      <ul class="list-disc list-inside mb-4 ml-4 space-y-1">
        <li>Opérations de maintenance</li>
        <li>Pannes techniques</li>
        <li>Interruptions des services d'hébergement</li>
        <li>Cas de force majeure</li>
      </ul>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">6.2. Exactitude des informations</h3>
      <p class="mb-3">
        L'éditeur met tout en œuvre pour offrir des informations pédagogiques fiables et à jour.
        Cependant, l'éditeur ne peut être tenu responsable des erreurs, omissions ou résultats obtenus
        par l'utilisation des contenus pédagogiques.
      </p>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">6.3. Liens externes</h3>
      <p class="mb-3">
        Le site peut contenir des liens hypertextes vers d'autres sites web.
        L'éditeur n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu,
        leur accessibilité ou leur politique de confidentialité.
      </p>

      <h3 class="text-xl font-semibold text-slate-800 mt-6 mb-3">6.4. Virus et logiciels malveillants</h3>
      <p class="mb-3">
        L'éditeur met en œuvre les mesures de sécurité nécessaires pour protéger le site contre les virus et logiciels malveillants.
        Toutefois, l'utilisateur est responsable de la protection de son équipement contre toute forme d'intrusion ou de contamination.
      </p>
    </div>
  </section>

  <!-- Section 7 : Droit applicable et juridiction -->
  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-indigo-500 pb-2">
      ⚖️ 7. Droit applicable et juridiction compétente
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        Les présentes mentions légales sont régies par le droit français.
      </p>
      <p class="mb-3">
        En cas de litige et à défaut d'accord amiable, le litige sera porté devant les tribunaux français
        conformément aux règles de compétence en vigueur.
      </p>
      <p class="mb-3">
        Pour les litiges relatifs à la protection des données personnelles, vous pouvez également saisir la CNIL
        (<a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline">www.cnil.fr</a>).
      </p>
    </div>
  </section>

  <!-- Section 8 : Contact -->
  <section class="legal-section bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-blue-500 pb-2">
      📧 8. Nous contacter
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed">
      <p class="mb-3">
        Pour toute question concernant les présentes mentions légales, la protection de vos données personnelles,
        ou l'utilisation du site MonCoachScolaire, vous pouvez nous contacter :
      </p>

      <div class="contact-box bg-white border-l-4 border-blue-500 p-4 my-4 rounded">
        <p class="mb-2"><strong>📧 Par email :</strong> <a href="mailto:contact@moncoachscolaire.fr" class="text-blue-600 hover:underline font-semibold">contact@moncoachscolaire.fr</a></p>
        <p class="mb-2"><strong>📞 Par téléphone :</strong> 01 23 45 67 89</p>
        <p class="mb-2"><strong>✉️ Par courrier postal :</strong></p>
        <p class="ml-4 text-sm">
          MonCoachScolaire SAS<br>
          10 rue de l'Éducation<br>
          75010 Paris<br>
          France
        </p>
      </div>

      <p class="text-sm text-slate-600 italic mt-4">
        Nous nous engageons à répondre à vos demandes dans les meilleurs délais,
        et au plus tard dans un délai d'un mois à compter de la réception de votre demande.
      </p>
    </div>
  </section>

  <!-- Section 9 : Crédits -->
  <section class="legal-section bg-white rounded-xl shadow-md p-6 mb-6">
    <h2 class="text-2xl font-bold text-slate-800 mb-4 border-b-2 border-gray-500 pb-2">
      🎨 9. Crédits
    </h2>
    <div class="legal-text text-slate-700 leading-relaxed text-sm">
      <p class="mb-3">
        <strong>Conception et développement :</strong> Équipe MonCoachScolaire
      </p>
      <p class="mb-3">
        <strong>Design graphique :</strong> Studio Graphik Paris
      </p>
      <p class="mb-3">
        <strong>Crédits photos et illustrations :</strong>
      </p>
      <ul class="list-disc list-inside ml-4 space-y-1">
        <li>Unsplash, Pexels, Flaticon</li>
        <li>Illustrations personnalisées par Studio Graphik Paris</li>
      </ul>
      <p class="mb-3 mt-4">
        <strong>Technologies utilisées :</strong> PHP, HTML5, CSS3, JavaScript, MySQL, Tailwind CSS
      </p>
    </div>
  </section>

  <!-- Bandeau de retour à l'accueil -->
  <div class="text-center my-8">
    <a href="<?php echo site_url('landingpage'); ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
      ← Retour à l'accueil
    </a>
  </div>



</main>

<?php

// Inclure le footer systématiquement
if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
    include_once dirname(__DIR__, 2) . '/includes/footer.php';
}
?>
