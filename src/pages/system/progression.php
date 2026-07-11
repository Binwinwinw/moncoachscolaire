<?php
$page_title = "Le Labo des Génies";
$page_css = 'pages/progression.css';

// Démarrer la session de façon robuste
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Charger les dépendances de façon robuste
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
} elseif (is_file(__DIR__ . '/bootstrap/site_boot.php')) {
    require_once __DIR__ . '/bootstrap/site_boot.php';
}

if (is_file(dirname(__DIR__, 2) . '/includes/gamification.php')) {
    require_once dirname(__DIR__, 2) . '/includes/gamification.php';
}
if (is_file(dirname(__DIR__, 2) . '/includes/progress_helpers.php')) {
    require_once dirname(__DIR__, 2) . '/includes/progress_helpers.php';
}
if (is_file(dirname(__DIR__, 2) . '/includes/progress_display.php')) {
    require_once dirname(__DIR__, 2) . '/includes/progress_display.php';
}
if (is_file(dirname(__DIR__, 2) . '/includes/demo_security.php')) {
    require_once dirname(__DIR__, 2) . '/includes/demo_security.php';
}

// Charger admin_auth.php pour vérifier si l'utilisateur est admin
if (is_file(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
}

// Définir si l'utilisateur est connecté
$is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);

// Vérifier authentification OU si admin
$is_admin = function_exists('isAdmin') && isAdmin();
if (!$is_logged_in && !$is_admin) {
    header('Location: ' . site_url('login'));
    exit;
}

// SÉCURITÉ : Le compte démo peut voir un aperçu mais pas la progression réelle
// Rediriger vers la page de démo si c'est un compte démo
if (isDemoUser()) {
    header('Location: ' . site_url('demo') . '?demo=1&section=odyssee');
    exit;
}

// S'assurer que la connexion à la BDD est disponible
if (!isset($pdo) || !$pdo) {
    require_once dirname(__DIR__, 2) . '/database/connection.php';
}

// Permettre l'accès via user_id en paramètre pour les parents OU les admins
$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : (int) $_SESSION['user_id'];

// Si user_id est fourni en paramètre, vérifier les permissions
if (isset($_GET['user_id'])) {
    if ($is_admin) {
        // Admin : accès autorisé à tous les utilisateurs
        // Pas de vérification nécessaire
    } elseif (!empty($_SESSION['parent_id'])) {
        // Parent : vérifier que l'enfant appartient bien au parent (via ParentId dans users)
        if (isset($pdo) && $pdo instanceof PDO) {
            try {
                $checkStmt = $pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM users
                    WHERE Id = ? AND ParentId = ? AND Role = 'student'
                ");
                $checkStmt->execute([$userId, $_SESSION['parent_id']]);
                $check = $checkStmt->fetch();

                if (($check['count'] ?? 0) == 0) {
                    // L'enfant n'appartient pas à ce parent
                    header('Location: ' . site_url('parents/dashboard_parent'));
                    exit;
                }
            } catch (Exception $e) {
                error_log("Erreur vérification parent-enfant: " . $e->getMessage());
                header('Location: ' . site_url('parents/dashboard_parent'));
                exit;
            }
        } else {
            error_log('progression.php: connexion PDO indisponible pour la vérification parent-enfant.');
            header('Location: ' . site_url('parents/dashboard_parent'));
            exit;
        }
    } else {
        // Si user_id est fourni mais pas de parent_id ni admin, rediriger
        header('Location: ' . site_url('login'));
        exit;
    }
}

// Charger la progression depuis la BDD si disponible
$xp = 0;
$level = 1;
$levelName = 'Apprenti Scientifique';
$position = 1;
$cristaux = 0;
$exercisesCompleted = 0;
$badgesCount = 0;
$powersCount = 0;
$totalPowers = 8;
$progressData = null;
$bySubject = [];
$badges = [];
$progressPercentage = 0;

if (isset($pdo) && $pdo) {
    try {
        // Charger la progression complète
        $progressData = getUserProgress($userId);

        if ($progressData) {
            $xp = (int) ($progressData['xp'] ?? 0);
            $level = calculateUserLevel($xp);
            $levelName = getLevelName($level);
            $position = calculateGamePosition($xp);
            $cristaux = (int) ($progressData['cristaux'] ?? 0);
            $exercisesCompleted = (int) ($progressData['exercises_completed'] ?? 0);
            $badgesCount = (int) ($progressData['badges_count'] ?? 0);
            $bySubject = $progressData['by_subject'] ?? [];
            $badges = $progressData['badges'] ?? [];
        }

        // Compter les pouvoirs
        $powersCount = getUnlockedPowersCount($userId);
        $totalPowers = getTotalPowersCount();

        // Calculer le pourcentage vers le prochain niveau
        $progressPercentage = getProgressPercentage($xp, $level);

    } catch (Exception $e) {
        error_log("Erreur chargement progression: " . $e->getMessage());
    }
}

// Préparer les données pour JavaScript
$jsProgressData = [
    'xp' => $xp,
    'level' => $level,
    'levelName' => $levelName,
    'position' => $position,
    'cristaux' => $cristaux,
    'exercisesCompleted' => $exercisesCompleted,
    'badgesCount' => $badgesCount,
    'powersCount' => $powersCount,
    'totalPowers' => $totalPowers,
    'progressPercentage' => $progressPercentage,
    'bySubject' => $bySubject,
    'badges' => $badges,
];
?>

<?php
// Afficher le menu parent si le parent est connecté
if (!empty($_SESSION['parent_id'])) {
    if (is_file(dirname(__DIR__, 2) . '/includes/menu_parent.php')) {
        include dirname(__DIR__, 2) . '/includes/menu_parent.php';
    }
}
?>
<main class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-6 md:px-6 lg:px-8">
    <?php
    // Bouton de retour contextuel
    if (!empty($_SESSION['parent_id'])) {
        echo '<a href="' . site_url('parents/dashboard_parent') . '" class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">← Retour au tableau de bord parent</a>';
    } else {
        echo '<a href="' . site_url('eleve/dashboard') . '" class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">← Retour à mon espace</a>';
    }
?>
    <div class="rounded-[2rem] bg-gradient-to-br from-slate-900 via-blue-800 to-purple-700 p-8 text-white shadow-2xl">
        <div class="mb-8 text-center">
            <h1 class="mb-3 text-4xl font-bold sm:text-5xl">🔬 Le Labo des Génies</h1>
            <p class="mb-8 text-lg text-slate-100">Transforme-toi en scientifique et découvre les secrets de la connaissance</p>

            <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white/15 backdrop-blur-md rounded-2xl p-6 text-center border-2 border-white/20 transition-all duration-300 hover:transform hover:-translate-y-1 hover:shadow-xl">
                    <div class="text-sm opacity-90 mb-2 uppercase tracking-wider">Niveau Actuel</div>
                    <div class="text-3xl font-bold mb-2" id="current-level"><?php echo htmlspecialchars($levelName); ?></div>
                    <div class="text-sm opacity-80">Niveau <?php echo $level; ?></div>
                </div>
                <div class="bg-white/15 backdrop-blur-md rounded-2xl p-6 text-center border-2 border-white/20 transition-all duration-300 hover:transform hover:-translate-y-1 hover:shadow-xl">
                    <div class="text-sm opacity-90 mb-2 uppercase tracking-wider">Station d'Expérimentation</div>
                    <div class="text-3xl font-bold mb-2" id="current-position">Station <?php echo $position; ?></div>
                    <div class="text-sm opacity-80">sur 64</div>
                </div>
                <div class="bg-white/15 backdrop-blur-md rounded-2xl p-6 text-center border-2 border-white/20 transition-all duration-300 hover:transform hover:-translate-y-1 hover:shadow-xl">
                    <div class="text-sm opacity-90 mb-2 uppercase tracking-wider">Points d'Expérience</div>
                    <div class="text-3xl font-bold mb-2" id="xp-points"><?php echo $xp; ?></div>
                    <div class="w-full h-2 bg-white/20 rounded-full mb-2 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-full transition-all duration-500" id="xp-progress-fill"></div>
                    </div>
                    <div class="text-sm opacity-80" id="xp-next-level">
                        <?php echo getXPForNextLevel($level) - $xp; ?> points jusqu'au niveau <?php echo $level + 1; ?>
                    </div>
                </div>
                <div class="bg-white/15 backdrop-blur-md rounded-2xl p-6 text-center border-2 border-white/20 transition-all duration-300 hover:transform hover:-translate-y-1 hover:shadow-xl">
                    <div class="text-sm opacity-90 mb-2 uppercase tracking-wider">Outils de Labo Débloqués</div>
                    <div class="text-3xl font-bold mb-2" id="powers-count"><?php echo $powersCount; ?>/<?php echo $totalPowers; ?></div>
                </div>
            </div>
        </div>

        <!-- Statistiques détaillées -->
        <div class="mb-8 rounded-[1.5rem] bg-white p-8 shadow-lg">
            <?php if (isset($pdo) && $pdo && $progressData): ?>
                <?php renderDetailedProgress($userId); ?>
            <?php else: ?>
                <div class="text-center p-8 text-gray-500">
                    <p>📊 <strong>Base de données non disponible</strong></p>
                    <p>La progression est sauvegardée localement et sera synchronisée automatiquement.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Laboratoire virtuel -->
        <div class="mt-12 rounded-[1.5rem] border border-white/20 bg-white/10 p-6 shadow-inner backdrop-blur-md">
            <h2 class="mb-6 text-center text-3xl font-bold text-white">🔬 Ton Laboratoire</h2>
            <div class="relative mx-auto aspect-square w-full max-w-2xl rounded-[1.5rem] border-4 border-white/30 bg-white/10 p-3 shadow-inner backdrop-blur-md">
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-4/5 h-4/5 border-4 border-dashed border-white/30 rounded-full pointer-events-none"></div>
                <div class="relative w-full h-full grid grid-cols-8 grid-rows-8 gap-1 z-10" id="board-squares"></div>
                <div class="absolute w-10 h-10 bg-gradient-to-br from-yellow-400 to-yellow-500 border-4 border-white rounded-full shadow-lg z-50 transform -translate-x-1/2 -translate-y-1/2 transition-all duration-500 opacity-0 scale-0" id="player-token"></div>
            </div>
            <p class="text-center text-white mt-6 opacity-90 text-base">
                Chaque exercice complété te fait progresser dans le laboratoire. Continue tes expériences pour devenir un génie scientifique !
            </p>
        </div>
    </div>
</main>

<script>
// Données de progression depuis PHP
const progressData = <?php echo json_encode($jsProgressData); ?>;

// Initialiser le plateau de jeu
(function() {
    const boardSquares = document.getElementById('board-squares');
    const playerToken = document.getElementById('player-token');

    if (!boardSquares || !playerToken) return;

    // Créer 64 cases (8x8 grid)
    const totalSquares = 64;
    const squaresPerRow = 8;

    for (let i = 0; i < totalSquares; i++) {
        const square = document.createElement('div');
        square.className = 'bg-white/10 rounded-md border border-white/20 transition-all duration-300 cursor-pointer relative hover:bg-white/20 hover:scale-110 hover:z-10';
        square.dataset.position = i + 1;

        if (i + 1 <= progressData.position) {
            square.classList.add('bg-green-500/40', 'border-green-500/60');
        }

        if (i + 1 === progressData.position) {
            square.classList.add('bg-yellow-500/60', 'border-yellow-400', 'shadow-lg', 'shadow-yellow-400/80', 'animate-pulse');
        }

        // Positionner sur une grille 8x8
        const row = Math.floor(i / squaresPerRow);
        const col = i % squaresPerRow;
        square.style.gridRow = row + 1;
        square.style.gridColumn = col + 1;

        boardSquares.appendChild(square);
    }

    // Positionner le token
    const position = progressData.position;
    const row = Math.floor((position - 1) / squaresPerRow);
    const col = (position - 1) % squaresPerRow;

    // Calculer la position en pourcentage (grille 8x8)
    const xPercent = (col / (squaresPerRow - 1)) * 100;
    const yPercent = (row / (squaresPerRow - 1)) * 100;

    playerToken.style.left = xPercent + '%';
    playerToken.style.top = yPercent + '%';

    // Animation d'apparition
    setTimeout(() => {
        playerToken.style.opacity = '1';
        playerToken.style.transform = 'scale(1)';
    }, 500);

    // Écouter les événements de progression depuis les exercices
    document.addEventListener('exerciseCompleted', function(event) {
        const detail = event.detail;

        // Mettre à jour les affichages
        if (detail.xp !== undefined) {
            const currentXP = parseInt(document.getElementById('xp-points').textContent) || 0;
            const newXP = currentXP + detail.xp;
            document.getElementById('xp-points').textContent = newXP;

            // Recalculer le niveau et la position
            // TODO: Appeler une API pour récupérer les nouvelles valeurs
        }
    });

    // Synchroniser localStorage si nécessaire
    if (typeof window.InteractiveExercises?.syncLocalStorage === 'function') {
        window.InteractiveExercises.syncLocalStorage();
    }
})();
</script>

