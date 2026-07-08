<?php
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (!function_exists('site_url')) {
	$siteBoot = dirname(__DIR__, 2) . '/config/site_boot.php';
	if (is_file($siteBoot)) {
		require_once $siteBoot;
	}
}

if (!function_exists('safe_redirect')) {
	$redirectHelpers = dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
	if (is_file($redirectHelpers)) {
		require_once $redirectHelpers;
	}
}

if (!function_exists('isAdmin')) {
	$adminAuth = dirname(__DIR__, 2) . '/includes/admin_auth.php';
	if (is_file($adminAuth)) {
		require_once $adminAuth;
	}
}

require_once dirname(__DIR__, 2) . '/database/connection.php';
require_once dirname(__DIR__, 2) . '/includes/learning_repository.php';

if (is_file(dirname(__DIR__, 2) . '/includes/level_normalization.php')) {
	require_once dirname(__DIR__, 2) . '/includes/level_normalization.php';
}

$is_admin = function_exists('isAdmin') && isAdmin();

if (empty($_SESSION['parent_id'])
	&& in_array(strtolower((string) ($_SESSION['user_role'] ?? '')), ['parent', 'parents'], true)
	&& !empty($_SESSION['user_id'])) {
	$_SESSION['parent_id'] = (int) $_SESSION['user_id'];
}

$parent_session_id = (int) ($_SESSION['parent_id'] ?? $_SESSION['user_id'] ?? 0);

if (!$is_admin && $parent_session_id <= 0) {
	safe_redirect(site_url('login'));
}

$page_title = 'Suivi élève - MonCoachScolaire';
$page_class = 'suivi-enfant-page';
$page_css = 'suivi_enfant.css';

$build_inline_avatar = static function (string $label, string $accent = '#4f46e5'): string {
	$initial = strtoupper(substr(trim($label), 0, 1));
	if ($initial === '') {
		$initial = 'E';
	}

	$svg = sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96" role="img" aria-label="Avatar %1$s"><rect width="96" height="96" rx="48" fill="#eef2ff"/><circle cx="48" cy="48" r="42" fill="%2$s" opacity="0.16"/><text x="48" y="56" text-anchor="middle" font-family="Arial, sans-serif" font-size="38" font-weight="700" fill="%2$s">%1$s</text></svg>',
		htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'),
		htmlspecialchars($accent, ENT_QUOTES, 'UTF-8')
	);

	return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
};

$fetch_attached_children = static function (PDO $pdo, int $parentId): array {
	if ($parentId <= 0) {
		return [];
	}

	try {
		$stmt = $pdo->prepare(
			"SELECT u.Id, u.Username, u.Prenom, u.Nom, u.UserLevel, u.Role
			 FROM users u
			 JOIN parent_child_invites pci ON u.Id = pci.child_user_id
			 WHERE pci.parent_user_id = ? AND pci.status = 'accepted' AND u.Role = 'student'
			 ORDER BY u.Prenom, u.Nom"
		);
		$stmt->execute([$parentId]);
		$children = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!empty($children)) {
			return $children;
		}
	} catch (Exception $e) {
		error_log('suivi_enfant: erreur récupération parent_child_invites: ' . $e->getMessage());
	}

	try {
		$stmt = $pdo->prepare(
			"SELECT u.Id, u.Username, u.Prenom, u.Nom, u.UserLevel, u.Role
			 FROM users u
			 JOIN parent_enfants pe ON u.Id = pe.student_id
			 WHERE pe.parent_id = ? AND u.Role = 'student'
			 ORDER BY u.Prenom, u.Nom"
		);
		$stmt->execute([$parentId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		error_log('suivi_enfant: erreur récupération parent_enfants: ' . $e->getMessage());
	}

	return [];
};

$db_error = !isset($pdo) || !$pdo instanceof PDO;

if ($db_error) {
	?>
	<div class="max-w-3xl mx-auto p-6 mt-8">
		<div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg">
			<h2 class="text-2xl font-bold text-red-700 mb-2">Service temporairement indisponible</h2>
			<p class="text-red-600 mb-4">La base de données est actuellement indisponible. Impossible d'afficher la fiche élève.</p>
			<a href="<?php echo htmlspecialchars(site_url('parents/dashboard_parent'), ENT_QUOTES, 'UTF-8'); ?>" class="inline-block bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition">
				Retour au tableau de bord
			</a>
		</div>
	</div>
	<?php
	return;
}

$enfant_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$liste_enfants = $is_admin ? [] : $fetch_attached_children($pdo, $parent_session_id);

if (!$enfant_id && !empty($liste_enfants)) {
	$firstAttachedChild = $liste_enfants[0];
	$enfant_id = (int) ($firstAttachedChild['Id'] ?? $firstAttachedChild['id'] ?? $firstAttachedChild['user_id'] ?? $firstAttachedChild['enfant_id'] ?? 0);
}

if (!$enfant_id) {
	safe_redirect(site_url('parents/dashboard_parent'));
}
$enfant = null;

if ($is_admin) {
	$stmt = $pdo->prepare(
		"SELECT Id, Username, Email, UserLevel, Prenom AS prenom, Nom AS nom, Role
		 FROM users
		 WHERE Id = ? AND Role = 'student'
		 LIMIT 1"
	);
	$stmt->execute([$enfant_id]);
	$enfant = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} else {
	$stmt = $pdo->prepare(
		"SELECT Id, Username, Email, UserLevel, Prenom AS prenom, Nom AS nom, Role
		 FROM users
		 WHERE Id = ? AND Role = 'student' AND (
			 EXISTS (
				 SELECT 1
				 FROM parent_child_invites pci
				 WHERE pci.child_user_id = users.Id
				   AND pci.parent_user_id = ?
				   AND pci.status = 'accepted'
			 )
			 OR EXISTS (
				 SELECT 1
				 FROM parent_enfants pe
				 WHERE pe.student_id = users.Id
				   AND pe.parent_id = ?
			 )
			 OR ParentId = ?
		 )
		 LIMIT 1"
	);
	$stmt->execute([$enfant_id, $parent_session_id, $parent_session_id, $parent_session_id]);
	$enfant = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

if (!$enfant) {
	if (!empty($liste_enfants) && !$is_admin) {
		$firstAttachedChild = $liste_enfants[0];
		$fallbackChildId = (int) ($firstAttachedChild['Id'] ?? $firstAttachedChild['id'] ?? $firstAttachedChild['user_id'] ?? $firstAttachedChild['enfant_id'] ?? 0);
		if ($fallbackChildId > 0 && $fallbackChildId !== (int) $enfant_id) {
			safe_redirect(site_url('parents/suivi_enfant') . '?id=' . urlencode((string) $fallbackChildId));
		}
	}

	safe_redirect(site_url('parents/dashboard_parent'));
}

$enfantName = trim((string) (($enfant['prenom'] ?? '') . ' ' . ($enfant['nom'] ?? '')));
if ($enfantName === '') {
	$enfantName = (string) ($enfant['Username'] ?? 'Enfant');
}

$avatar_url = $build_inline_avatar($enfantName, '#1e293b');
$niveau_scolaire_display = function_exists('get_level_display_name')
	? get_level_display_name((string) ($enfant['UserLevel'] ?? ''))
	: (string) ($enfant['UserLevel'] ?? 'Non défini');

$page_title = 'Suivi de ' . $enfantName . ' - MonCoachScolaire';

$progression = [];
$stats_exercices = [
	'total' => 0,
	'reussis' => 0,
	'en_cours' => 0,
	'taux_reussite' => 0.0,
];
$quiz_stats = [
	'total_quiz' => 0,
	'avg_score' => 0.0,
	'passed_count' => 0,
	'last_quiz_at' => null,
	'recent_attempts' => 0,
];
$matieres_actives = [];
$activites_recentes = [];
$learning_path = null;
$learning_events = [];
$recommendation_context = [];
$next_step_copy = null;
$current_goal = null;
$profile = null;

try {
	$progressStmt = $pdo->prepare(
		"SELECT XP, CurrentPosition, UpdatedAt AS date
		 FROM UserProgress
		 WHERE UserId = ?
		 ORDER BY UpdatedAt DESC"
	);
	$progressStmt->execute([$enfant_id]);
	$progression = $progressStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
	error_log('suivi_enfant: erreur récupération progression: ' . $e->getMessage());
}

try {
	$statsStmt = $pdo->prepare(
		"SELECT
			COUNT(*) AS total,
			SUM(CASE WHEN Status = 'completed' THEN 1 ELSE 0 END) AS reussis,
			SUM(CASE WHEN Status = 'in_progress' THEN 1 ELSE 0 END) AS en_cours
		 FROM UserExercises
		 WHERE UserId = ?"
	);
	$statsStmt->execute([$enfant_id]);
	$stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
	$stats_exercices['total'] = (int) ($stats['total'] ?? 0);
	$stats_exercices['reussis'] = (int) ($stats['reussis'] ?? 0);
	$stats_exercices['en_cours'] = (int) ($stats['en_cours'] ?? 0);
	if ($stats_exercices['total'] > 0) {
		$stats_exercices['taux_reussite'] = round(($stats_exercices['reussis'] / $stats_exercices['total']) * 100, 1);
	}
} catch (Exception $e) {
	error_log('suivi_enfant: erreur récupération stats exercices: ' . $e->getMessage());
}

try {
	$quizStatsStmt = $pdo->prepare(
		"SELECT
			COUNT(*) AS total_quiz,
			AVG(score) AS avg_score,
			SUM(passed) AS passed_count,
			MAX(created_at) AS last_quiz_at,
			SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) THEN 1 ELSE 0 END) AS recent_attempts
		 FROM quizresult
		 WHERE user_id = ?"
	);
	$quizStatsStmt->execute([$enfant_id]);
	$quizStats = $quizStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];
	$quiz_stats['total_quiz'] = (int) ($quizStats['total_quiz'] ?? 0);
	$quiz_stats['avg_score'] = round((float) ($quizStats['avg_score'] ?? 0), 1);
	$quiz_stats['passed_count'] = (int) ($quizStats['passed_count'] ?? 0);
	$quiz_stats['last_quiz_at'] = $quizStats['last_quiz_at'] ?? null;
	$quiz_stats['recent_attempts'] = (int) ($quizStats['recent_attempts'] ?? 0);
} catch (Exception $e) {
	error_log('suivi_enfant: erreur récupération stats quiz: ' . $e->getMessage());
}

try {
	$subjectsStmt = $pdo->prepare(
		"SELECT q.subject, AVG(qr.score) AS avg_score, COUNT(*) AS attempts
		 FROM quizresult qr
		 JOIN quiz q ON qr.quiz_id = q.id
		 WHERE qr.user_id = ?
		 GROUP BY q.subject
		 ORDER BY attempts DESC, avg_score DESC
		 LIMIT 4"
	);
	$subjectsStmt->execute([$enfant_id]);
	$matieres_actives = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
	error_log('suivi_enfant: erreur récupération matières actives: ' . $e->getMessage());
}

try {
	$exerciseActivitiesStmt = $pdo->prepare(
		"SELECT Title AS label, Status AS status, UpdatedAt AS activity_date, 'exercice' AS activity_type
		 FROM UserExercises
		 WHERE UserId = ?
		 ORDER BY UpdatedAt DESC
		 LIMIT 5"
	);
	$exerciseActivitiesStmt->execute([$enfant_id]);
	$exerciseActivities = $exerciseActivitiesStmt->fetchAll(PDO::FETCH_ASSOC);

	$quizActivitiesStmt = $pdo->prepare(
		"SELECT COALESCE(q.subject, 'Général') AS label,
				CASE WHEN qr.passed = 1 THEN 'Réussi' ELSE 'À revoir' END AS status,
				qr.created_at AS activity_date,
				'quiz' AS activity_type,
				qr.score
		 FROM quizresult qr
		 LEFT JOIN quiz q ON qr.quiz_id = q.id
		 WHERE qr.user_id = ?
		 ORDER BY qr.created_at DESC
		 LIMIT 5"
	);
	$quizActivitiesStmt->execute([$enfant_id]);
	$quizActivities = $quizActivitiesStmt->fetchAll(PDO::FETCH_ASSOC);

	$activites_recentes = array_merge($exerciseActivities, $quizActivities);
	usort($activites_recentes, static function (array $left, array $right): int {
		return strtotime((string) ($right['activity_date'] ?? '')) <=> strtotime((string) ($left['activity_date'] ?? ''));
	});
	$activites_recentes = array_slice($activites_recentes, 0, 5);
} catch (Exception $e) {
	error_log('suivi_enfant: erreur récupération activités récentes: ' . $e->getMessage());
}

$learningRepository = new LearningRepository($pdo);
$profile = $learningRepository->getActiveProfileForUser($enfant_id);

if ($profile) {
	$recommendation_context = $learningRepository->buildRecommendationContext((int) $profile['id'], 'consolidation');
	$next_step_copy = $recommendation_context['next_step'] ?? null;
	$learning_dashboards = $learningRepository->getStudentDashboard((int) $profile['id']);
	$learning_path = $learning_dashboards[0] ?? null;
	$learning_events = $learningRepository->getRecentProgressEvents((int) $profile['id'], 5);
	if (!empty($learning_path['target_competency'])) {
		$current_goal = (string) $learning_path['target_competency'];
	} elseif (!empty($recommendation_context['content_unit']['title'])) {
		$current_goal = (string) $recommendation_context['content_unit']['title'];
	}
}

$xp_total = 0;
$current_position = 'Rythme à construire';
if (!empty($progression)) {
	foreach ($progression as $progressItem) {
		$xp_total += (int) ($progressItem['XP'] ?? 0);
	}

	$last_progress = $progression[0];
	if (!empty($last_progress['CurrentPosition'])) {
		$current_position = (string) $last_progress['CurrentPosition'];
	}
}

$regularity_label = 'À relancer';
if ($quiz_stats['recent_attempts'] >= 4) {
	$regularity_label = 'Très régulier';
} elseif ($quiz_stats['recent_attempts'] >= 2) {
	$regularity_label = 'Rythme installé';
} elseif ($quiz_stats['recent_attempts'] === 1) {
	$regularity_label = 'Reprise en cours';
}

$status_title = 'Démarrage à accompagner';
$status_summary = 'Le suivi commence. L’objectif est surtout d’installer une routine simple et rassurante.';

if ($quiz_stats['total_quiz'] > 0 && $quiz_stats['avg_score'] >= 80) {
	$status_title = 'Dynamique très positive';
	$status_summary = 'Les résultats récents montrent une bonne maîtrise. Le rôle du parent est surtout d’entretenir la régularité.';
} elseif ($quiz_stats['total_quiz'] > 0 && $quiz_stats['avg_score'] >= 60) {
	$status_title = 'Progression régulière';
	$status_summary = 'Les bases sont là. Quelques révisions ciblées peuvent aider à stabiliser les acquis.';
} elseif ($quiz_stats['total_quiz'] > 0) {
	$status_title = 'Consolidation nécessaire';
	$status_summary = 'Certaines notions demandent encore du soutien. Mieux vaut avancer par petites étapes bien ciblées.';
}

$attention_point = 'Aucun point bloquant détecté pour le moment.';
if ($quiz_stats['recent_attempts'] === 0) {
	$attention_point = 'Aucune activité récente n’a été repérée sur les quatorze derniers jours.';
} elseif ($quiz_stats['avg_score'] > 0 && $quiz_stats['avg_score'] < 60) {
	$attention_point = 'Les résultats restent fragiles. Une seule notion à la fois sera plus efficace qu’une reprise trop large.';
} elseif ($stats_exercices['en_cours'] > 0) {
	$attention_point = 'Des exercices sont encore en cours. Les terminer peut redonner de l’élan rapidement.';
}

$next_step_title = 'Choisir une petite étape claire';
$next_step_description = 'Une action simple et réaliste aide à relancer l’enfant sans le surcharger.';
$next_step_label = 'Voir les exercices';
$next_step_url = site_url('exercices');

if ($next_step_copy) {
	$next_step_title = (string) ($next_step_copy['title'] ?? $next_step_title);
	$next_step_description = (string) ($next_step_copy['description'] ?? $next_step_description);
	$next_step_label = (string) ($next_step_copy['label'] ?? $next_step_label);
}

if ($quiz_stats['total_quiz'] === 0 && empty($next_step_copy)) {
	$next_step_title = 'Lancer un premier repère';
	$next_step_description = 'Un mini diagnostic ou un premier exercice permet de mieux comprendre le point de départ de votre enfant.';
	$next_step_label = 'Commencer un diagnostic';
	$next_step_url = site_url('diagnostic') . '?child_id=' . urlencode((string) $enfant_id);
}

$last_activity_label = 'Aucune activité récente';
if (!empty($quiz_stats['last_quiz_at'])) {
	try {
		$lastActivityDate = new DateTime((string) $quiz_stats['last_quiz_at']);
		$last_activity_label = $lastActivityDate->format('d/m/Y à H:i');
	} catch (Exception $e) {
		$last_activity_label = (string) $quiz_stats['last_quiz_at'];
	}
}

$parent_advice = [];
if ($quiz_stats['recent_attempts'] === 0) {
	$parent_advice[] = 'Proposez un créneau court, fixe et calme pour relancer la routine sans pression.';
}
if ($quiz_stats['avg_score'] > 0 && $quiz_stats['avg_score'] < 60) {
	$parent_advice[] = 'Concentrez-vous sur une seule notion à la fois et valorisez chaque petit progrès.';
}
if (!empty($recommendation_context['content_unit']['subject'])) {
	$parent_advice[] = 'Demandez à votre enfant ce qu’il retient en ' . $recommendation_context['content_unit']['subject'] . ' avant de parler du résultat.';
}
if ($parent_advice === []) {
	$parent_advice[] = 'Gardez un échange court et positif après chaque séance pour entretenir la confiance.';
	$parent_advice[] = 'Mettez en avant l’effort régulier plus que la note finale.';
}

$other_children = array_values(array_filter(
	$liste_enfants,
	static function (array $child) use ($enfant_id): bool {
		return (int) ($child['Id'] ?? 0) !== (int) $enfant_id;
	}
));
?>

<main class="min-h-screen bg-slate-50/70 px-4 py-8">
	<div class="mx-auto max-w-6xl space-y-8">
		<nav class="flex flex-wrap items-center gap-3 text-sm text-slate-600">
			<a href="<?php echo htmlspecialchars(site_url('parents/dashboard_parent'), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center rounded-full bg-white px-4 py-2 font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50">
				Retour au dashboard parent
			</a>
			<span class="text-slate-300">/</span>
			<span class="font-semibold text-indigo-700">Fiche élève</span>
		</nav>

		<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
			<div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
				<div class="flex items-start gap-4">
					<img src="<?php echo htmlspecialchars($avatar_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($enfantName, ENT_QUOTES, 'UTF-8'); ?>" class="h-20 w-20 rounded-3xl border border-indigo-100 bg-indigo-50 object-cover shadow-sm">
					<div>
						<p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600">Fiche élève</p>
						<h1 class="mt-2 text-3xl font-bold text-slate-950"><?php echo htmlspecialchars($enfantName, ENT_QUOTES, 'UTF-8'); ?></h1>
						<div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-700">
							<span class="rounded-full bg-indigo-50 px-3 py-1 font-medium text-indigo-700"><?php echo htmlspecialchars($niveau_scolaire_display, ENT_QUOTES, 'UTF-8'); ?></span>
							<span class="rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700"><?php echo htmlspecialchars($status_title, ENT_QUOTES, 'UTF-8'); ?></span>
							<span class="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700"><?php echo htmlspecialchars($regularity_label, ENT_QUOTES, 'UTF-8'); ?></span>
						</div>
						<p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600"><?php echo htmlspecialchars($status_summary, ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
				</div>
				<div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:w-[420px]">
					<div class="rounded-2xl bg-slate-50 p-4">
						<p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Quiz</p>
						<p class="mt-2 text-2xl font-bold text-slate-900"><?php echo (int) $quiz_stats['total_quiz']; ?></p>
					</div>
					<div class="rounded-2xl bg-slate-50 p-4">
						<p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Réussite</p>
						<p class="mt-2 text-2xl font-bold text-slate-900"><?php echo htmlspecialchars((string) $quiz_stats['avg_score'], ENT_QUOTES, 'UTF-8'); ?>%</p>
					</div>
					<div class="rounded-2xl bg-slate-50 p-4">
						<p class="text-xs font-semibold uppercase tracking-wide text-slate-500">XP</p>
						<p class="mt-2 text-2xl font-bold text-slate-900"><?php echo number_format($xp_total, 0, ',', ' '); ?></p>
					</div>
					<div class="rounded-2xl bg-slate-50 p-4">
						<p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dernière activité</p>
						<p class="mt-2 text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($last_activity_label, ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
				</div>
			</div>

			<?php if (!empty($other_children)): ?>
				<div class="mt-6 border-t border-slate-100 pt-6">
					<p class="text-sm font-semibold text-slate-700">Autres enfants rattachés</p>
					<div class="mt-3 flex flex-wrap gap-2">
						<?php foreach ($other_children as $child): ?>
							<a href="<?php echo htmlspecialchars(site_url('parents/suivi_enfant') . '?id=' . urlencode((string) ($child['Id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-full bg-white px-3 py-2 text-sm font-medium text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50">
								<?php echo htmlspecialchars(trim((string) (($child['Prenom'] ?? '') . ' ' . ($child['Nom'] ?? ''))) ?: (string) ($child['Username'] ?? 'Enfant'), ENT_QUOTES, 'UTF-8'); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</section>

		<section class="grid gap-6 lg:grid-cols-[1.3fr_0.9fr]">
			<article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
				<div class="flex items-center gap-2">
					<span class="text-xl">Situation actuelle</span>
				</div>
				<div class="mt-5 grid gap-4 sm:grid-cols-2">
					<div class="rounded-2xl bg-slate-50 p-4">
						<p class="text-sm font-semibold text-slate-900">Rythme de travail</p>
						<p class="mt-2 text-sm leading-6 text-slate-600"><?php echo htmlspecialchars($regularity_label, ENT_QUOTES, 'UTF-8'); ?>. <?php echo (int) $quiz_stats['recent_attempts']; ?> activité(s) repérée(s) sur les 14 derniers jours.</p>
					</div>
					<div class="rounded-2xl bg-slate-50 p-4">
						<p class="text-sm font-semibold text-slate-900">Position actuelle</p>
						<p class="mt-2 text-sm leading-6 text-slate-600"><?php echo htmlspecialchars($current_position, ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
					<div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2">
						<p class="text-sm font-semibold text-slate-900">Point d’attention</p>
						<p class="mt-2 text-sm leading-6 text-slate-600"><?php echo htmlspecialchars($attention_point, ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
				</div>

				<div class="mt-6">
					<p class="text-sm font-semibold text-slate-900">Matières actuellement visibles</p>
					<?php if (!empty($matieres_actives)): ?>
						<div class="mt-3 flex flex-wrap gap-2">
							<?php foreach ($matieres_actives as $matiere): ?>
								<span class="rounded-full bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700">
									<?php echo htmlspecialchars((string) ($matiere['subject'] ?? 'Général'), ENT_QUOTES, 'UTF-8'); ?>
									<span class="text-indigo-400">·</span>
									<?php echo (int) ($matiere['attempts'] ?? 0); ?> activité(s)
								</span>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<p class="mt-3 text-sm text-slate-600">Aucune matière ne ressort encore clairement. Un premier exercice aidera à mieux cerner les besoins.</p>
					<?php endif; ?>
				</div>
			</article>

			<article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
				<div class="flex items-center gap-2">
					<span class="text-xl">Objectif en cours</span>
				</div>
				<div class="mt-5 space-y-4">
					<div class="rounded-2xl bg-amber-50 p-4 text-sm text-amber-900">
						<p class="font-semibold">Cap pédagogique</p>
						<p class="mt-2 leading-6"><?php echo htmlspecialchars($current_goal ?: 'Stabiliser une prochaine étape simple et réaliste.', ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
					<div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
						<p class="font-semibold text-slate-900">Exercices</p>
						<p class="mt-2"><?php echo (int) $stats_exercices['reussis']; ?> terminé(s), <?php echo (int) $stats_exercices['en_cours']; ?> en cours, <?php echo (int) $stats_exercices['total']; ?> au total.</p>
					</div>
					<?php if (!empty($learning_path)): ?>
						<div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
							<p class="font-semibold text-slate-900">Parcours actif</p>
							<p class="mt-2"><?php echo htmlspecialchars((string) ($learning_path['title'] ?? 'Parcours actif'), ENT_QUOTES, 'UTF-8'); ?></p>
							<?php if (!empty($learning_path['current_stage'])): ?>
								<p class="mt-1 text-slate-600">Étape actuelle : <?php echo htmlspecialchars((string) $learning_path['current_stage'], ENT_QUOTES, 'UTF-8'); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</article>
		</section>

		<section class="rounded-3xl border border-indigo-100 bg-white p-6 shadow-sm">
			<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
				<div class="max-w-3xl">
					<p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600">Prochaine action recommandée</p>
					<h2 class="mt-2 text-2xl font-bold text-slate-950"><?php echo htmlspecialchars($next_step_title, ENT_QUOTES, 'UTF-8'); ?></h2>
					<p class="mt-3 text-sm leading-6 text-slate-600"><?php echo htmlspecialchars($next_step_description, ENT_QUOTES, 'UTF-8'); ?></p>
					<?php if (!empty($recommendation_context['content_unit']['subject'])): ?>
						<p class="mt-3 text-sm text-slate-700">Notion à soutenir : <span class="font-semibold"><?php echo htmlspecialchars((string) $recommendation_context['content_unit']['subject'], ENT_QUOTES, 'UTF-8'); ?></span></p>
					<?php endif; ?>
				</div>
				<div class="rounded-2xl bg-indigo-50 p-4 lg:min-w-[280px]">
					<p class="text-sm font-semibold text-indigo-700">Pourquoi maintenant</p>
					<p class="mt-2 text-sm leading-6 text-slate-700">Cette étape donne au parent une action simple à encourager, sans transformer la page en tableau de bord analytique.</p>
					<div class="mt-4 flex flex-wrap gap-2">
						<a href="<?php echo htmlspecialchars($next_step_url, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
							<?php echo htmlspecialchars($next_step_label, ENT_QUOTES, 'UTF-8'); ?>
						</a>
						<a href="<?php echo htmlspecialchars(site_url('parents/dashboard_parent'), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50">
							Revenir à la synthèse
						</a>
					</div>
				</div>
			</div>
		</section>

		<section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
			<article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
				<h2 class="text-xl font-bold text-slate-950">Activité récente utile</h2>
				<?php if (!empty($activites_recentes)): ?>
					<div class="mt-5 space-y-3">
						<?php foreach ($activites_recentes as $activity): ?>
							<?php
							$activityDate = (string) ($activity['activity_date'] ?? '');
							$activityDateLabel = $activityDate;
							if ($activityDate !== '') {
								try {
									$activityDateLabel = (new DateTime($activityDate))->format('d/m/Y à H:i');
								} catch (Exception $e) {
									$activityDateLabel = $activityDate;
								}
							}
							?>
							<div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
								<div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
									<div>
										<p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars((string) ($activity['label'] ?? 'Activité'), ENT_QUOTES, 'UTF-8'); ?></p>
										<p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars((string) ($activity['status'] ?? 'En cours'), ENT_QUOTES, 'UTF-8'); ?></p>
									</div>
									<div class="text-sm text-slate-500">
										<div><?php echo htmlspecialchars((string) ($activity['activity_type'] ?? 'activité'), ENT_QUOTES, 'UTF-8'); ?></div>
										<div class="mt-1"><?php echo htmlspecialchars($activityDateLabel, ENT_QUOTES, 'UTF-8'); ?></div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else: ?>
					<p class="mt-4 text-sm leading-6 text-slate-600">Aucune activité utile n’est encore visible. La fiche se complétera naturellement dès les premiers exercices ou quiz.</p>
				<?php endif; ?>

				<?php if (!empty($learning_events)): ?>
					<div class="mt-6 border-t border-slate-100 pt-6">
						<p class="text-sm font-semibold text-slate-900">Événements du parcours</p>
						<ul class="mt-3 space-y-2 text-sm text-slate-600">
							<?php foreach ($learning_events as $event): ?>
								<li class="rounded-xl bg-slate-50 px-3 py-2">
									<?php echo htmlspecialchars((string) ($event['event_type'] ?? 'activité'), ENT_QUOTES, 'UTF-8'); ?>
									<?php if (!empty($event['created_at'])): ?>
										· <?php echo htmlspecialchars((string) $event['created_at'], ENT_QUOTES, 'UTF-8'); ?>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</article>

			<article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
				<h2 class="text-xl font-bold text-slate-950">Conseils pour le parent</h2>
				<div class="mt-5 space-y-3">
					<?php foreach ($parent_advice as $advice): ?>
						<div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm leading-6 text-slate-700">
							<?php echo htmlspecialchars($advice, ENT_QUOTES, 'UTF-8'); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</article>
		</section>
	</div>
</main>
