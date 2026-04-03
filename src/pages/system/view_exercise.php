<?php
/**
 * Page d'affichage d'un exercice complet
 * Route: index.php?page=view_exercise&id=X
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/exercice_loader.php';

// ✅ 1. Récupérer l'ID de l'exercice D'ABORD
$exerciseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Rediriger vers le routeur si on n'est pas déjà dedans
if (!defined('IN_ROUTER')) {
    if ($exerciseId > 0) {
        $redirectUrl = function_exists('site_url')
            ? site_url('system/view_exercise', ['id' => $exerciseId])
            : 'public/index.php?page=view_exercise&id=' . $exerciseId;
        header('Location: ' . $redirectUrl);
    } else {
        $redirectUrl = function_exists('site_url')
            ? site_url('system/exercices')
            : 'public/index.php?page=exercices';
        header('Location: ' . $redirectUrl);
    }
    exit;
}

// ✅ 2. Charger l'exercice
$exercise = getExerciseById($exerciseId);

if (!$exercise) {
    echo '<p>❌ Exercice introuvable.</p>';
    exit;
}

// ✅ 3. Vérification d'accès par niveau
if (is_file(dirname(__DIR__, 2) . '/includes/level_access.php')) {
    require_once dirname(__DIR__, 2) . '/includes/level_access.php';
    // L'exercice contient généralement le champ 'Level'
    $requiredLevel = $exercise['Level'] ?? $exercise['level'] ?? null;
    if ($requiredLevel) {
        enforce_level_access_or_abort($requiredLevel);
    }
}

// ✅ 4. Démarrer le tracking
require_once dirname(__DIR__, 2) . '/includes/study_tracker.php';
require_once dirname(__DIR__, 2) . '/includes/exercice_card.php';

$sessionId = null;
if (!empty($_SESSION['user_id']) && !empty($_SESSION['logged_in'])) {
    try {
        $sessionId = startStudySession($_SESSION['user_id'], 'exercise', $exerciseId);
        $_SESSION['current_study_session'] = $sessionId;
    } catch (Exception $e) {
        error_log('Erreur tracking exercice: ' . $e->getMessage());
    }
}

$page_title = ($exercise['Title'] ?? 'Exercice') . ' - MonCoachScolaire';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/tailwind.css') : '/public/assets/css/tailwind.css'; ?>">
</head>
<body class="app-bg">
    <?php if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
        include_once dirname(__DIR__, 2) . '/includes/topbar.php';
    } ?>

    <div class="max-w-3xl mx-auto my-8 p-6 bg-white rounded-2xl shadow-lg">
        <a href="<?php echo function_exists('site_url') ? site_url('system/exercices') : 'exercices.php'; ?>" class="inline-block px-5 py-2 bg-gray-600 text-white rounded-lg mb-5 hover:bg-gray-700 transition">
            ← Retour aux exercices
        </a>

        <div class="border-b-4 border-orange-400 pb-4 mb-6">
            <h1 class="text-2xl font-bold text-slate-800"><?= htmlspecialchars($exercise['Title']) ?></h1>
        </div>

        <?php
        // Afficher l'exercice avec la carte interactive
        renderExerciseCard($exercise, [
            'showAnswer' => false,
            'showDetails' => true,
            'interactive' => true,
        ]);
?>
    </div>

    <?php if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
        include_once dirname(__DIR__, 2) . '/includes/footer.php';
    } ?>

    <?php if ($sessionId): ?>
    <script>
    // ========================================
    // TRACKING DE LA SESSION D'EXERCICE
    // ========================================

    let sessionEnded = false;
    let exerciseScore = null;
    const SESSION_ID = <?= $sessionId ?>;

    // Écouter la soumission de l'exercice pour capturer le score
    document.addEventListener('exerciseCompleted', (e) => {
        if (e.detail && e.detail.exerciseId == <?= $exerciseId ?>) {
            exerciseScore = e.detail.score || 100;
            console.log('Exercice complété avec score:', exerciseScore);

            // Terminer la session immédiatement après complétion
            setTimeout(() => {
                endStudySession();
            }, 1000);
        }
    });

    // Terminer quand l'utilisateur quitte la page
    window.addEventListener('beforeunload', () => {
        if (!sessionEnded) {
            endStudySession();
        }
    });

    window.addEventListener('pagehide', () => {
        if (!sessionEnded) {
            endStudySession();
        }
    });

    // Inactivité de 30 minutes
    let inactivityTimer;
    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            console.log('Session terminée par inactivité');
            endStudySession();
        }, 30 * 60 * 1000);
    }

    ['mousedown', 'keypress', 'scroll', 'touchstart', 'input'].forEach(eventType => {
        document.addEventListener(eventType, resetInactivityTimer, true);
    });
    resetInactivityTimer();

    // Fonction pour terminer la session
    function endStudySession() {
        if (sessionEnded) return;
        sessionEnded = true;

        const data = {
            session_id: SESSION_ID,
            score: exerciseScore,
            completion_rate: exerciseScore !== null ? 100 : calculateScrollPercentage()
        };

        console.log('Fin de session exercice:', data);

        // sendBeacon pour garantir l'envoi
        const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
        const beaconSent = navigator.sendBeacon(
            '<?= site_url('api/end_session') ?>',
            blob
        );

        if (!beaconSent) {
            fetch('<?= site_url('api/end_session') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
                keepalive: true
            }).catch(err => console.error('Erreur fin de session:', err));
        }
    }

    function calculateScrollPercentage() {
        const scrolled = window.scrollY;
        const total = document.documentElement.scrollHeight - window.innerHeight;
        if (total <= 0) return 100;
        return Math.min(100, Math.max(0, Math.round((scrolled / total) * 100)));
    }

    // Heartbeat toutes les 2 minutes
    setInterval(() => {
        if (!sessionEnded) {
            fetch('<?= site_url('api/update_session') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: SESSION_ID,
                    completion_rate: calculateScrollPercentage(),
                    is_active: true
                })
            }).catch(err => console.error('Erreur update session:', err));
        }
    }, 2 * 60 * 1000);
    </script>
    <?php endif; ?>
</body>
</html>
