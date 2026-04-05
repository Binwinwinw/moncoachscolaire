<?php
// Protection session et vérification admin
require_once dirname(__DIR__, 2) . '/config/site_boot.php';
require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';

$baseAccueil = site_url('landingpage');

if (!isAdmin()) {
	safe_redirect(site_url('login', ['redirect' => 'admin/stats']));
}

// Connexion DB
require_once dirname(__DIR__, 2) . '/config/config.php';
if (!isset($pdo) || !$pdo) {
    die('<div class="text-red-600 font-bold">Erreur de connexion à la base de données.</div>');
}

// Récupérer les stats principales (exemple : nombre d’utilisateurs, d’exercices, de cours, de feedbacks)
$stats = [
    'Utilisateurs' => 0,
    'Exercices' => 0,
    'Cours' => 0,
    'Feedbacks' => 0,
];

// Stats utilisateurs
try {
    $stats['Utilisateurs'] = (int) ($pdo->query('SELECT COUNT(*) FROM users')->fetchColumn());
} catch (Exception $e) {
    $stats['Utilisateurs'] = 0;
}
// Stats exercices
try {
    $stats['Exercices'] = (int) ($pdo->query('SELECT COUNT(*) FROM exercices')->fetchColumn());
} catch (Exception $e) {
    $stats['Exercices'] = 0;
}
// Stats cours
try {
    $stats['Cours'] = (int) ($pdo->query('SELECT COUNT(*) FROM cours')->fetchColumn());
} catch (Exception $e) {
    $stats['Cours'] = 0;
}
// Stats feedbacks
try {
    $stats['Feedbacks'] = (int) ($pdo->query('SELECT COUNT(*) FROM feedbacks')->fetchColumn());
} catch (Exception $e) {
    $stats['Feedbacks'] = 0;
}

// Dernier exercice ajouté
try {
    $last_exercice = $pdo->query('SELECT MAX(created_at) FROM exercices')->fetchColumn();
} catch (Exception $e) {
    $last_exercice = null;
}

// Exercices par niveau
try {
    $exos_by_level = $pdo->query('SELECT niveau, COUNT(*) as total FROM exercices GROUP BY niveau')->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $exos_by_level = [];
}

// Utilisateurs par rôle
try {
    $users_by_role = $pdo->query('SELECT role, COUNT(*) as total FROM users GROUP BY role')->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users_by_role = [];
}

?>
<main class="admin-main-content max-w-5xl mx-auto px-4 py-8">
	<div class="flex justify-between items-center mb-6">
		<a href="<?php echo htmlspecialchars($baseAccueil); ?>" class="btn-admin-primary flex items-center gap-2">
			<span aria-hidden="true">🏠</span>
			<span>Accueil admin</span>
		</a>
	</div>

	<h1 class="text-3xl font-bold mb-6 text-blue-900 flex items-center gap-2">
		<span>📊</span> Statistiques du projet
	</h1>

	<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
		<?php foreach ($stats as $label => $value): ?>
			<div class="stat-card bg-white rounded-xl shadow p-6 flex flex-col items-center">
				<div class="text-4xl font-bold text-blue-700 mb-2"><?php echo htmlspecialchars($value); ?></div>
				<div class="text-lg text-blue-900 font-semibold"><?php echo htmlspecialchars($label); ?></div>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
		<div class="bg-white rounded-xl shadow p-6">
			<h2 class="text-xl font-bold mb-4 text-blue-800">Exercices par niveau</h2>
			<ul class="divide-y divide-blue-100">
				<?php foreach ($exos_by_level as $row): ?>
					<li class="py-2 flex justify-between">
						<span class="font-medium"><?php echo htmlspecialchars($row['niveau']); ?></span>
						<span class="text-blue-700 font-bold"><?php echo (int) $row['total']; ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<div class="bg-white rounded-xl shadow p-6">
			<h2 class="text-xl font-bold mb-4 text-blue-800">Utilisateurs par rôle</h2>
			<ul class="divide-y divide-blue-100">
				<?php foreach ($users_by_role as $row): ?>
					<li class="py-2 flex justify-between">
						<span class="font-medium"><?php echo htmlspecialchars($row['role']); ?></span>
						<span class="text-blue-700 font-bold"><?php echo (int) $row['total']; ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="flex flex-wrap gap-4 mb-8">
		<button class="btn-admin-primary" onclick="openModal('modal-details')">Voir détails</button>
		<span class="feedback-success hidden" id="feedback-success">✅ Statistiques actualisées avec succès !</span>
	</div>

	<!-- Modal détails -->
	<div id="modal-details" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true">
		<div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
			<button onclick="closeModal('modal-details')" class="absolute top-4 right-4 text-gray-400 hover:text-blue-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
			<h2 class="text-2xl font-bold text-blue-700 mb-4 flex items-center gap-2"><span>📈</span> Détail des statistiques</h2>
			<ul class="list-disc list-inside text-gray-700 mb-4">
				<li>Dernier exercice ajouté : <strong><?php echo htmlspecialchars($last_exercice ?: 'N/A'); ?></strong></li>
				<li>Total utilisateurs : <strong><?php echo (int) $stats['Utilisateurs']; ?></strong></li>
				<li>Total exercices : <strong><?php echo (int) $stats['Exercices']; ?></strong></li>
				<li>Total cours : <strong><?php echo (int) $stats['Cours']; ?></strong></li>
				<li>Total feedbacks : <strong><?php echo (int) $stats['Feedbacks']; ?></strong></li>
			</ul>
			<button class="btn-admin-primary w-full" onclick="closeModal('modal-details')">Fermer</button>
		</div>
	</div>
</main>

<script>
function openModal(id) {
	document.getElementById(id).classList.remove('hidden');
	setTimeout(function() {
		document.getElementById(id).querySelector('button[aria-label="Fermer"]').focus();
	}, 100);
}
function closeModal(id) {
	document.getElementById(id).classList.add('hidden');
}
// Feedback animé après action
function showFeedback() {
	var el = document.getElementById('feedback-success');
	el.classList.remove('hidden');
	setTimeout(function() { el.classList.add('hidden'); }, 2000);
}
</script>
