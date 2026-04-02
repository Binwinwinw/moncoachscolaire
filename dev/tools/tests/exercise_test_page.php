// Page de test des exercices
// (Contenu à insérer depuis le fichier source fourni)
// ...

<?php
// Page de test des exercices
// Test d’intégration du composant exercice_card
// Structure inspirée des patterns dashboard.php

// Sécurité : accès direct uniquement depuis le routeur ou usage CLI
if (!defined('IN_ROUTER') && php_sapi_name() !== 'cli') {
	// Affichage minimal si accès direct
	?><!DOCTYPE html>
	<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title>Test Exercices - MonCoachScolaire</title>
		<link rel="stylesheet" href="/moncoachscolaire/public/assets/css/style.css">
		<link rel="stylesheet" href="/moncoachscolaire/public/assets/css/pages/dashboard.css">
	</head>
	<body class="app-bg test-exercises-page">
	<?php
}

// Initialisation helpers et configuration
if (!isset($pdo)) {
	require_once dirname(__DIR__, 3) . '/src/config/config.php';
}
if (is_file(dirname(__DIR__, 3) . '/src/config/site_boot.php')) {
	require_once dirname(__DIR__, 3) . '/src/config/site_boot.php';
}
require_once dirname(__DIR__, 3) . '/src/database/connection.php';

// Inclusion du composant exercice_card
require_once dirname(__DIR__, 3) . '/src/includes/exercice_card.php';

// Exemple de rendu d’une carte d’exercice (mock minimal)
$exercice = [
	'id' => 1,
	'title' => 'Exercice Test',
	'subject' => 'Mathématiques',
	'level' => '6ème',
	'content' => 'Résous l’équation suivante : 2x + 3 = 7',
	'type' => 'question',
	'difficulty' => 'Facile',
	'answers' => ['2'],
	'explanation' => 'On soustrait 3 puis on divise par 2.',
	'status' => 'active',
	'tags' => ['équation', 'calcul'],
];

echo '<main class="p-8 max-w-2xl mx-auto">';
echo '<h1 class="text-2xl font-bold mb-6">Test de rendu carte exercice</h1>';
// Appel du composant (exemple)
if (function_exists('render_exercice_card')) {
	echo render_exercice_card($exercice);
} else {
	echo '<div class="bg-red-100 text-red-700 p-4 rounded">Composant exercice_card non disponible.</div>';
}
echo '</main>';

// Footer
if (!defined('IN_ROUTER') && php_sapi_name() !== 'cli') {
	if (is_file(dirname(__DIR__, 3) . '/src/includes/footer.php')) include_once dirname(__DIR__, 3) . '/src/includes/footer.php';
	echo '</body></html>';
}
