<?php

if (!ob_get_level()) {
    ob_start();
}
define('SKIP_SESSION_CHECK', true); // Protection anti-boucle session
/**
 * Dashboard Parent - MonCoachScolaire
 * Interface pour que les parents suivent la progression de leurs enfants
 *
 * IMPORTANT :
 * - NE RIEN AFFICHER (echo, HTML, espace, BOM, etc.) AVANT la vérification de session et la redirection !
 * - TOUS LES includes qui produisent du HTML (header, topbar, etc.) DOIVENT être APRES la redirection !
 */

// 1. Session + redirection AVANT tout HTML / header global
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEBUG SESSION - uniquement en mode debug
if (getenv('APP_DEBUG') === 'true' || getenv('APP_ENV') === 'local') {
    error_log('DEBUG DASHBOARD_PARENT: SESSION = ' . print_r($_SESSION, true));
    if (isset($_SESSION['user_id'])) {
        error_log('DEBUG DASHBOARD_PARENT: user_id = ' . $_SESSION['user_id']);
    }
    if (isset($_SESSION['parent_id'])) {
        error_log('DEBUG DASHBOARD_PARENT: parent_id = ' . $_SESSION['parent_id']);
    }
    if (isset($_SESSION['user_role'])) {
        error_log('DEBUG DASHBOARD_PARENT: user_role = ' . $_SESSION['user_role']);
    }
    if (isset($_SESSION['user_name'])) {
        error_log('DEBUG DASHBOARD_PARENT: user_name = ' . $_SESSION['user_name']);
    }
    if (isset($_SESSION['logged_in'])) {
        error_log('DEBUG DASHBOARD_PARENT: logged_in = ' . ($_SESSION['logged_in'] ? 'true' : 'false'));
    }
}

// Charger site_boot pour site_url()
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
} elseif (is_file(__DIR__ . '/bootstrap/site_boot.php')) {
    require_once __DIR__ . '/bootstrap/site_boot.php';
}

$is_admin = false;
if (is_file(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
    $is_admin = function_exists('isAdmin') && isAdmin();
}

if (empty($_SESSION['parent_id'])
    && in_array(strtolower((string) ($_SESSION['user_role'] ?? '')), ['parent', 'parents'], true)
    && !empty($_SESSION['user_id'])) {
    $_SESSION['parent_id'] = (int) $_SESSION['user_id'];
}

if (empty($_SESSION['parent_id']) && !$is_admin) {
    // Rediriger vers le dashboard adapté selon le rôle
    if (!empty($_SESSION['user_role'])) {
        $role = strtolower((string) $_SESSION['user_role']);
        if ($role === 'admin') {
            header('Location: ' . site_url('admin/dashboard_admin'));
            exit;
        } elseif ($role === 'student') {
            header('Location: ' . site_url('eleve/dashboard'));
            exit;
        }
    }
    // Sinon, forcer la connexion
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'dashboard_parent';
    header('Location: ' . site_url('login'));
    exit;
}

// 2. APRES seulement, includes qui peuvent afficher du HTML (header, topbar, etc.)

$page_title = 'Espace Parent – Suivi enfants';
$page_css = 'dashboard-parent.css'; // Garder pour compatibilité, mais palette harmonisée dans les composants

if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}

// Charger site_boot.php (inutile ici car déjà fait, mais conservé si besoin d'autres variables)
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
} elseif (is_file(__DIR__ . '/bootstrap/site_boot.php')) {
    require_once __DIR__ . '/bootstrap/site_boot.php';
}

require_once dirname(__DIR__, 2) . '/database/connection.php';

$parent_id = $_SESSION['parent_id'] ?? null;

// Récupérer les informations du parent (depuis users avec Role='parent')
// OU les infos de l'admin
$parent = null;
if ($is_admin) {
    // Admin : utiliser les infos de session admin
    $parent = [
        'Id'        => $_SESSION['user_id'] ?? null,
        'Username'  => $_SESSION['user_name'] ?? 'Administrateur',
        'Email'     => $_SESSION['user_email'] ?? '',
        'Prenom'    => $_SESSION['user_name'] ?? 'Admin',
        'Nom'       => '',
        'UserLevel' => 'Admin',
    ];
} elseif ($parent_id) {
    // Parent : récupérer les infos depuis la base
    $parentStmt = $pdo->prepare("SELECT * FROM users WHERE Id = :parent_id AND Role IN ('parent', 'parents') LIMIT 1");
    $parentStmt->execute(['parent_id' => $parent_id]);
    $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);
}

// --- Préparation des variables attendues par les composants (logique métier complète) ---
// 1. Récupérer les enfants rattachés (parent_child_invites acceptés)
$enfants = [];
if ($parent_id) {
    try {
        $stmt = $pdo->prepare("SELECT u.*, pci.accepted_at FROM users u
            JOIN parent_child_invites pci ON u.Id = pci.child_user_id
            WHERE pci.parent_user_id = :parent_id AND pci.status = 'accepted'
            ORDER BY u.Prenom, u.Nom");
        $stmt->execute(['parent_id' => $parent_id]);
        $enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('dashboard_parent: parent_child_invites table manquante ou erreur: ' . $e->getMessage());
    }
}

// Si aucun résultat, fallback legacy parent_enfants (protection compatibilité)
if (empty($enfants) && $parent_id) {
    $stmt = $pdo->prepare("SELECT u.* FROM users u
        JOIN parent_enfants pe ON u.Id = pe.student_id
        WHERE pe.parent_id = :parent_id");
    $stmt->execute(['parent_id' => $parent_id]);
    $enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 2. Si enfants, récupérer progression, exos, etc. (données réelles, pas de démo)
$progressions = [];
$dernierExos = [];
$matieresFortes = [];
$notifications = [];
$objectifs = [];
$conseils = [];
if (!empty($enfants)) {
    try {
        foreach ($enfants as $enfant) {
            $childId = $enfant['Id'] ?? $enfant['id'] ?? null;
            $childName = $enfant['Prenom'] ?? $enfant['prenom'] ?? $enfant['Username'] ?? 'Enfant';
            if (!$childId) {
                continue;
            }

            // Progression globale à partir des résultats de quiz
            $statStmt = $pdo->prepare("SELECT COUNT(*) AS total_quiz, AVG(score) AS avg_score, SUM(passed) AS passed_count FROM quizresult WHERE user_id = ?");
            $statStmt->execute([$childId]);
            $stats = $statStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $avgScore = isset($stats['avg_score']) ? round((float) $stats['avg_score'], 1) : 0;
            $avgScore = max(0, min(100, $avgScore));

            $progressions[] = [
                'nom' => $childName,
                'pourcent' => $avgScore,
                'total_quiz' => (int) ($stats['total_quiz'] ?? 0),
                'passés' => (int) ($stats['passed_count'] ?? 0),
            ];

            // Derniers exercices / diagnostics
            $lastStmt = $pdo->prepare("SELECT qr.score, qr.passed, qr.created_at, q.subject FROM quizresult qr LEFT JOIN quiz q ON qr.quiz_id = q.id WHERE qr.user_id = ? ORDER BY qr.created_at DESC LIMIT 5");
            $lastStmt->execute([$childId]);
            $lasts = $lastStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($lasts as $result) {
                $dernierExos[] = [
                    'enfant' => $childName,
                    'matiere' => $result['subject'] ?? 'Général',
                    'date' => date('d/m/Y', strtotime($result['created_at'] ?? 'now')),
                    'resultat' => ((int) ($result['passed'] ?? 0) >= 1 ? 'Réussi' : 'À revoir'),
                    'score' => isset($result['score']) ? round((float) $result['score'], 1) : null,
                ];
            }

            // Matières fortes
            $bestSubjectsStmt = $pdo->prepare("SELECT q.subject, AVG(qr.score) AS avg_score, COUNT(*) AS attempts FROM quizresult qr JOIN quiz q ON qr.quiz_id = q.id WHERE qr.user_id = ? GROUP BY q.subject ORDER BY avg_score DESC LIMIT 3");
            $bestSubjectsStmt->execute([$childId]);
            $bestSubjects = $bestSubjectsStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($bestSubjects as $subj) {
                $matieresFortes[] = [
                    'nom' => $subj['subject'] ?? 'Général',
                    'score' => round((float) ($subj['avg_score'] ?? 0), 1),
                    'exos' => (int) ($subj['attempts'] ?? 0),
                ];
            }
        }

        if (empty($progressions)) {
            $notifications[] = [ 'icone' => '⚠️', 'texte' => "Aucune donnée de progression disponible pour le moment.", 'type' => 'warning' ];
        } else {
            $notifications[] = [ 'icone' => '✅', 'texte' => "Vos enfants ont des progrès enregistrés, super !", 'type' => 'succès' ];
        }
        $objectifs = ['Vérifier les progrès chaque semaine', 'Encourager la revue des matières faibles'];
        $conseils = ['Fixez un créneau révision quotidien', 'Discutez des résultats avec votre enfant'];

    } catch (Exception $e) {
        error_log('dashboard_parent: erreur récupération progression enfants : ' . $e->getMessage());
        // Fallback visuel si la requête DB échoue
        $progressions = [
            ['nom' => 'Enfant', 'pourcent' => 70],
        ];
        $dernierExos = [
            ['matiere' => 'Mathématiques', 'date' => date('d/m/Y'), 'resultat' => 'À revoir', 'score' => 62],
        ];
        $matieresFortes = [
            ['nom' => 'Français', 'score' => 83, 'exos' => 12],
        ];
        $notifications = [ [ 'icone' => '⚠️', 'texte' => "Impossible de récupérer les données réelles. En mode dégradé.", 'type' => 'warning' ] ];
        $objectifs = ['Rafraîchir la page', 'Contacter support si problème persiste'];
        $conseils = ['Vérifier la connexion à la base de données', 'Valider le rôle parent'];
    }
} else {
    // Fallback démo si aucun enfant
    $progressions = [
        ['nom' => 'Emma (démo)', 'pourcent' => 85],
        ['nom' => 'Lucas (démo)', 'pourcent' => 72],
    ];
    $dernierExos = [
        ['enfant' => 'Emma (démo)', 'matiere' => 'Mathématiques', 'date' => date('d/m/Y', strtotime('-2 days')), 'resultat' => 'Réussi', 'score' => 92],
        ['enfant' => 'Lucas (démo)', 'matiere' => 'Français', 'date' => date('d/m/Y', strtotime('-4 days')), 'resultat' => 'À revoir', 'score' => 65],
    ];
    $matieresFortes = [
        ['nom' => 'Français', 'score' => 88, 'exos' => 12],
        ['nom' => 'Mathématiques', 'score' => 78, 'exos' => 9],
    ];
    $notifications = [
        [ 'icone' => '✅', 'texte' => "Bienvenue sur MonCoachScolaire !", 'type' => 'succès' ],
        [ 'icone' => '📅', 'texte' => "Aucun enfant rattaché. Ajoutez-en pour suivre leur progression !", 'type' => 'info' ],
        [ 'icone' => '💡', 'texte' => "Découvrez nos guides pour accompagner vos enfants.", 'type' => 'info' ],
    ];
    $objectifs = ['Découvrir la plateforme', 'Ajouter un enfant'];
    $conseils = ['Regardez la vidéo tutoriel', 'Contactez l’assistance en cas de besoin'];
}

// Sécurisation (toujours tableau)
if (!isset($notifications) || !is_array($notifications)) {
    $notifications = [];
}
if (!isset($objectifs) || !is_array($objectifs)) {
    $objectifs = [];
}
if (!isset($conseils) || !is_array($conseils)) {
    $conseils = [];
}
if (!isset($progressions) || !is_array($progressions)) {
    $progressions = [];
}
if (!isset($dernierExos) || !is_array($dernierExos)) {
    $dernierExos = [];
}
if (!isset($matieresFortes) || !is_array($matieresFortes)) {
    $matieresFortes = [];
}

// Inclusion du layout harmonisé (palette sobre, typographie pro)
require_once __DIR__ . '/partials/parent-layout.php';
