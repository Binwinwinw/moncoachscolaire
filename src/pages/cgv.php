
<?php
$page_title = 'Conditions Générales de Vente - MonCoachScolaire';
$page_class = 'cgv-page';
?><!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($page_title) ?></title>
	<link rel="stylesheet" href="<?= asset_url('assets/css/tailwind.css') ?>">
</head>
<body class="bg-gray-50 text-gray-900 <?= htmlspecialchars($page_class) ?>">
	<?php if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
	    require_once dirname(__DIR__, 2) . '/includes/topbar.php';
	} ?>
	<main class="max-w-3xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
		<div class="bg-white shadow-xl rounded-2xl p-8 md:p-12 mb-8 border border-gray-100">
			<h1 class="text-3xl md:text-4xl font-bold text-center mb-8">
				Conditions d'utilisation
			</h1>
			<p class="text-xl text-gray-600 text-center mb-8">Bienvenue sur MonCoachScolaire - Acceptez nos conditions pour utiliser notre plateforme</p>
			<section class="space-y-8">
				<h2 class="text-2xl font-semibold mt-8">Introduction</h2>
				<p>Bienvenue sur <strong>MonCoachScolaire</strong>. L'accès et l'utilisation de notre plateforme impliquent l'acceptation pleine et entière des présentes conditions d'utilisation.</p>
				<div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded mb-4">
					<p><strong>Important :</strong> En utilisant notre plateforme, vous acceptez ces conditions. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre service.</p>
				</div>
				<h2 class="text-2xl font-semibold mt-8">1. Objet</h2>
				<p><strong>MonCoachScolaire</strong> propose des contenus éducatifs et des outils de suivi pour les élèves, parents et enseignants. Notre mission est de fournir un environnement d'apprentissage sécurisé et efficace.</p>
				<h2 class="text-2xl font-semibold mt-8">2. Accès au service</h2>
				<ul class="list-disc pl-6 space-y-2">
					<li><strong>Création de compte :</strong> L'accès à certains services nécessite la création d'un compte utilisateur. Vous vous engagez à fournir des informations exactes et à jour.</li>
					<li><strong>Protection des mineurs :</strong> Les utilisateurs de moins de 15 ans doivent avoir obtenu le consentement parental pour créer un compte.</li>
					<li><strong>Comptes sécurisés :</strong> Vous êtes responsable de la confidentialité de vos identifiants et de toutes les activités réalisées sous votre compte.</li>
				</ul>
				<h2 class="text-2xl font-semibold mt-8">3. Responsabilités</h2>
				<h3 class="text-xl font-semibold mt-6">Responsabilités de l'utilisateur</h3>
				<ul class="list-disc pl-6 space-y-2">
					<li>Respecter la législation en vigueur et ne pas porter atteinte à l'intégrité de la plateforme</li>
					<li>Ne pas utiliser le service à des fins illégales ou non autorisées</li>
					<li>Ne pas tenter d'accéder à des zones restreintes ou de contourner les mesures de sécurité</li>
					<li>Ne pas publier de contenu offensant, diffamatoire ou contraire aux bonnes mœurs</li>
				</ul>
				<h3 class="text-xl font-semibold mt-6">Responsabilités de MonCoachScolaire</h3>
				<ul class="list-disc pl-6 space-y-2">
					<li>Fournir un service conforme aux descriptions présentées sur la plateforme</li>
					<li>Assurer la sécurité et la disponibilité du service dans la mesure du possible</li>
					<li>Respecter la confidentialité des données utilisateur conformément à notre politique de confidentialité</li>
				</ul>
				<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded mb-4">
					<p><strong>Limitation de responsabilité :</strong> MonCoachScolaire ne saurait être tenu responsable des contenus publiés par les utilisateurs ou des dommages résultant de l'utilisation ou de l'impossibilité d'utiliser la plateforme.</p>
				</div>
				<h2 class="text-2xl font-semibold mt-8">4. Propriété intellectuelle</h2>
				<p>Tous les contenus présents sur la plateforme (textes, images, logos, vidéos, exercices) sont la propriété exclusive de <strong>MonCoachScolaire</strong> ou de ses partenaires et sont protégés par le droit d'auteur.</p>
				<ul class="list-disc pl-6 space-y-2">
					<li><strong>Usage personnel :</strong> Vous pouvez consulter et utiliser les contenus uniquement à des fins éducatives personnelles.</li>
					<li><strong>Interdiction de reproduction :</strong> Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite sans autorisation écrite préalable.</li>
					<li><strong>Contenus utilisateurs :</strong> Les contenus que vous publiez restent votre propriété, mais vous accordez à MonCoachScolaire une licence d'utilisation pour les besoins du service.</li>
				</ul>
				<h2 class="text-2xl font-semibold mt-8">5. Données personnelles</h2>
				<p>La collecte et le traitement des données personnelles sont détaillés dans notre <a href="rgpd.html" class="text-blue-600 underline">Politique de confidentialité & RGPD</a>. En utilisant notre service, vous acceptez notre politique de confidentialité.</p>
				<div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded mb-4">
					<p><strong>Consentement :</strong> Pour les mineurs de moins de 15 ans, le consentement parental est requis conformément au RGPD.</p>
				</div>
				<h2 class="text-2xl font-semibold mt-8">6. Modification des conditions</h2>
				<p><strong>MonCoachScolaire</strong> se réserve le droit de modifier les présentes conditions à tout moment. Les modifications entrent en vigueur dès leur publication sur la plateforme.</p>
				<ul class="list-disc pl-6 space-y-2">
					<li>Les utilisateurs seront informés des changements majeurs par email ou via une notification visible sur la plateforme</li>
					<li>L'utilisation continue du service après la modification des conditions constitue une acceptation des nouvelles conditions</li>
					<li>Si vous n'acceptez pas les modifications, vous devez cesser d'utiliser le service</li>
				</ul>
				<h2 class="text-2xl font-semibold mt-8">7. Résiliation</h2>
				<p>Vous pouvez résilier votre compte à tout moment depuis les paramètres de votre profil. MonCoachScolaire se réserve également le droit de suspendre ou résilier votre compte en cas de violation des présentes conditions.</p>
				<h2 class="text-2xl font-semibold mt-8">8. Contact</h2>
				<p>Pour toute question concernant ces conditions d'utilisation, n'hésitez pas à nous contacter :</p>
				<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4 mb-4">
					<div class="bg-gray-50 border rounded p-4">
						<h4 class="font-semibold mb-2">Email</h4>
						<p><a href="mailto:contact@moncoachscolaire.fr" class="text-blue-600 underline">contact@moncoachscolaire.fr</a></p>
					</div>
					<div class="bg-gray-50 border rounded p-4">
						<h4 class="font-semibold mb-2">Téléphone</h4>
						<p>(+596) 696 30 26 60</p>
					</div>
					<div class="bg-gray-50 border rounded p-4">
						<h4 class="font-semibold mb-2">Adresse</h4>
						<p>MonCoachScolaire<br>DUCOS<br>97224 Martinique, France</p>
					</div>
				</div>
				<p class="text-sm text-gray-500 mt-8">Dernière mise à jour : 15 novembre 2023</p>
			</section>
		</div>
	</main>
	<?php if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
	    require_once dirname(__DIR__, 2) . '/includes/footer.php';
	} ?>
</body>
</html>
