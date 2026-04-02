<?php
$page_title = 'Cours Collège - MonCoachScolaire';
$page_css = 'cours-college.css';
$page_class = 'page-cours-college';

if (!isset($pdo)) {
    require_once dirname(__DIR__, 3) . '/config/config.php';
}
if (is_file(dirname(__DIR__, 3) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 3) . '/config/site_boot.php';
}

$niveau = $_SESSION['user_level'] ?? '';
$niveau_normalise = ucfirst(strtolower(preg_replace('/[^a-z0-9]/i', '', $niveau)));

?><main class="main-content max-w-4xl mx-auto px-4 py-8">
	<div class="text-center mb-8">
		<h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">📚 Cours Collège</h1>
		<div class="flex justify-center">
			<span class="inline-block px-6 py-3 rounded-2xl bg-blue-100 text-blue-700 font-semibold text-xl shadow-lg">
				<?php echo htmlspecialchars($niveau_normalise ?: 'Niveau inconnu'); ?>
			</span>
		</div>
	</div>
	<?php
    if (!isset($_SESSION['user_id'])) {
        echo '<div class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg text-center font-semibold">Vous êtes en mode découverte : seul un exemple de cours est affiché. <br>Connectez-vous pour accéder à tous les cours du collège.</div>';
        echo '<section class="mt-6">';
        echo '<div class="bg-white shadow-md rounded-lg p-6 mb-4">';
        echo '<h2 class="text-xl font-bold text-slate-700 mb-2">Exemple : Cours de Mathématiques - 6ème</h2>';
        echo '<p class="text-slate-600">Calcul, fractions, géométrie de base.</p>';
        echo '</div>';
        echo '</section>';
    } else {
        // Affichage dynamique : ne montrer que les cours du niveau de l'élève connecté
        $niveau_cours = [
            '6ème' => [
                ['Mathématiques', 'Calcul, fractions, géométrie de base.'],
                ['Français', 'Lecture, grammaire, orthographe, expression écrite.'],
                ['Histoire-Géographie', 'Premières civilisations, repères géographiques.'],
            ],
            '5ème' => [
                ['Mathématiques', 'Proportionnalité, calcul littéral, géométrie plane.'],
                ['Français', 'Grammaire, conjugaison, compréhension de texte.'],
                ['Histoire-Géographie', 'Moyen Âge, grandes découvertes, géographie humaine.'],
            ],
            '4ème' => [
                ['Mathématiques', 'Équations, fonctions, théorème de Thalès.'],
                ['Français', 'Analyse de texte, figures de style, argumentation.'],
                ['Histoire-Géographie', 'Révolutions, XIXe siècle, géographie urbaine.'],
            ],
            '3ème' => [
                ['Mathématiques', 'Trigonométrie, statistiques, fonctions, brevet.'],
                ['Français', 'Analyse littéraire, écriture d’invention, préparation brevet.'],
                ['Histoire-Géographie', 'XXe siècle, guerres mondiales, géographie contemporaine.'],
            ],
        ];
        $niveau_user = $_SESSION['user_level'] ?? '';
        // Normalisation simple (6eme, 6ème, 6EME, etc.)
        $niveau_user_norm = strtolower(str_replace(['ème','eme','ÈME','Ème','EME'], 'ème', $niveau_user));
        $niveau_map = [
            '6eme' => '6ème', '6ème' => '6ème',
            '5eme' => '5ème', '5ème' => '5ème',
            '4eme' => '4ème', '4ème' => '4ème',
            '3eme' => '3ème', '3ème' => '3ème',
        ];
        $niveau_final = $niveau_map[$niveau_user_norm] ?? '';
        echo '<section class="mt-6">';
        if ($niveau_final && isset($niveau_cours[$niveau_final])) {
            foreach ($niveau_cours[$niveau_final] as $cours) {
                echo '<div class="bg-white shadow-md rounded-lg p-6 mb-4">';
                echo '<h2 class="text-xl font-bold text-slate-700 mb-2">' . htmlspecialchars($cours[0]) . ' - ' . htmlspecialchars($niveau_final) . '</h2>';
                echo '<p class="text-slate-600">' . htmlspecialchars($cours[1]) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<div class="bg-yellow-100 text-yellow-800 rounded-lg p-4 text-center font-semibold">Aucun cours disponible pour ce niveau.</div>';
        }
        echo '</section>';
    }
?>
</main>
