<?php
// Page cours lycée : niveau scolaire centré
$page_title = 'Cours Lycée - MonCoachScolaire';
$page_css = 'cours-lycee.css';
$page_class = 'page-cours-lycee';

// Protection session & config
if (!isset($pdo)) {
    require_once dirname(__DIR__, 3) . '/config/config.php';
}
if (is_file(dirname(__DIR__, 3) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 3) . '/config/site_boot.php';
}

// Détection du niveau
$niveau = $_SESSION['user_level'] ?? '';
$niveau_normalise = ucfirst(strtolower(preg_replace('/[^a-z0-9]/i', '', $niveau)));

// Topbar
if (!isset($hide_topbar)) {
    $hide_topbar = false;
}
if (!$hide_topbar && file_exists(dirname(__DIR__, 3) . '/includes/topbar.php')) {
    include_once dirname(__DIR__, 3) . '/includes/topbar.php';
}
?>

<main class="main-content max-w-4xl mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">📚 Cours Lycée</h1>
        <div class="flex justify-center">
            <span class="inline-block px-6 py-3 rounded-2xl bg-blue-100 text-blue-700 font-semibold text-xl shadow-lg">
                <?php echo htmlspecialchars($niveau_normalise ?: 'Niveau inconnu'); ?>
            </span>
        </div>
    </div>
    <?php
    // Restriction visiteur : n'afficher qu'un seul cours si non connecté
    if (!isset($_SESSION['user_id'])) {
        echo '<div class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg text-center font-semibold">'
            . 'Vous êtes en mode découverte : seul un exemple de cours est affiché. <br>Connectez-vous pour accéder à tous les cours du lycée.</div>';
        // Affichage d'un seul cours exemple
        echo '<section class="mt-6">';
        echo '<div class="bg-white shadow-md rounded-lg p-6 mb-4">';
        echo '<h2 class="text-xl font-bold text-slate-700 mb-2">Exemple : Cours de Français - Terminale</h2>';
        echo '<p class="text-slate-600">Analyse de texte, méthodologie du commentaire, préparation au bac.</p>';
        echo '</div>';
        echo '</section>';
    } else {
        // Affichage dynamique : ne montrer que les cours du niveau de l'élève connecté (Seconde, Première, Terminale)
        $niveau_cours_lycee = [
            'Seconde' => [
                ['Mathématiques', 'Fonctions, statistiques, géométrie analytique.'],
                ['Français', 'Analyse de texte, écriture, argumentation.'],
                ['Histoire-Géographie', 'Révolutions, sociétés, mondialisation.'],
            ],
            'Première' => [
                ['Mathématiques', 'Suites, probabilités, fonctions avancées.'],
                ['Français', 'Méthodologie du commentaire, dissertation, oral.'],
                ['Histoire-Géographie', 'XXe siècle, conflits, géographie du développement.'],
            ],
            'Terminale' => [
                ['Mathématiques', 'Analyse, probabilités, préparation au bac.'],
                ['Philosophie', 'Notions, auteurs, dissertation, explication de texte.'],
                ['Histoire-Géographie', 'Géopolitique, mondialisation, mémoires.'],
            ],
        ];
        $niveau_user = $_SESSION['user_level'] ?? '';
        // Normalisation simple (Seconde, 2nde, Première, 1ère, Terminale)
        $niveau_map = [
            '2nde' => 'Seconde', 'seconde' => 'Seconde', 'Seconde' => 'Seconde',
            '1ere' => 'Première', '1ère' => 'Première', 'premiere' => 'Première', 'Première' => 'Première',
            'terminale' => 'Terminale', 'Terminale' => 'Terminale',
        ];
        $niveau_user_norm = strtolower(str_replace(['è','é','ê'], 'e', $niveau_user));
        $niveau_final = $niveau_map[$niveau_user] ?? $niveau_map[$niveau_user_norm] ?? '';
        echo '<section class="mt-6">';
        if ($niveau_final && isset($niveau_cours_lycee[$niveau_final])) {
            foreach ($niveau_cours_lycee[$niveau_final] as $cours) {
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
