<?php

// Helper functions for level-based access control
require_once __DIR__ . '/level_normalization.php';

/**
 * Mapping of normalized level keys to an integer order (1 = CP, increasing -> Terminale)
 * @return array<string,int>
 */
function get_levels_order_map()
{
    return [
        // Primary
        'cp' => 1,
        'ce1' => 2,
        'ce2' => 3,
        'cm1' => 4,
        'cm2' => 5,
        // Collège
        '6eme' => 6,
        '5eme' => 7,
        '4eme' => 8,
        '3eme' => 9,
        // Lycée
        'seconde' => 10,
        'premiere' => 11,
        // Bac / Terminale
        'terminale' => 12,
    ];
}

/**
 * Normalize a level value into a key compatible with the orders map
 */
function normalize_level_key($level)
{
    if (empty($level)) {
        return '';
    }
    $norm = normalize_school_level($level);
    $k = mb_strtolower($norm);
    // Remove spaces and special chars
    $k = str_replace([' ', 'è', 'é', 'ê', "'", "-"], ['', 'e', 'e', 'e', '', ''], $k);

    // Map some common primary variants
    $variants = [
        // Primaire
        'cp' => 'cp',
        'ce1' => 'ce1',
        'ce-1' => 'ce1',
        'ce2' => 'ce2',
        'cm1' => 'cm1',
        'cm-1' => 'cm1',
        'cm2' => 'cm2',
        'cm-2' => 'cm2',
        // Collège
        '6eme' => '6eme',
        '6ème' => '6eme',
        'college' => '6eme',
        'collège' => '6eme',
        'collége' => '6eme',
        'collèges' => '6eme',
        '5eme' => '5eme',
        '5ème' => '5eme',
        '4eme' => '4eme',
        '4ème' => '4eme',
        '3eme' => '3eme',
        '3ème' => '3eme',
        // Lycée
        'seconde' => 'seconde',
        '2nde' => 'seconde',
        '2nd' => 'seconde',
        'lycee' => 'seconde',
        'lycée' => 'seconde',
        'lycees' => 'seconde',
        'lycées' => 'seconde',
        'premiere' => 'premiere',
        'première' => 'premiere',
        '1ere' => 'premiere',
        '1ère' => 'premiere',
        'terminale' => 'terminale',
        'tale' => 'terminale',
        // Bac
        'bac' => 'terminale',
        'BAC' => 'terminale',
        // Prépa
        'prepa' => 'terminale',
        'prépa' => 'terminale',
        // Primaire générique
        'primaire' => 'cp',
        'école' => 'cp',
        'ecole' => 'cp',
        'elementaire' => 'cp',
        'maternelle' => 'cp',
    ];

    if (isset($variants[$k])) {
        return $variants[$k];
    }
    return $k;
}

/**
 * Get numeric order for a given level name/string. Returns null if unknown
 * @param string $level
 * @return int|null
 */
function get_level_order($level)
{
    $map = get_levels_order_map();
    $key = normalize_level_key($level);
    if (!$key) {
        return null;
    }
    return $map[$key] ?? null;
}

/**
 * Get the current user's level order from session if available
 * @return int|null
 */
function get_user_level_order()
{
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        }
    }

    if (!empty($_SESSION['user_level_order'])) {
        return (int) $_SESSION['user_level_order'];
    }

    if (!empty($_SESSION['user_level'])) {
        $ord = get_level_order($_SESSION['user_level']);
        if ($ord !== null) {
            // Cache in session for subsequent requests
            $_SESSION['user_level_order'] = $ord;
            return $ord;
        }
    }

    return null;
}

/**
 * Determine if the current user can access a required level
 * @param string $requiredLevel
 * @return bool
 */
function can_current_user_access_level($requiredLevel)
{
    // Admin bypass
    if (($_SESSION['user_role'] ?? null) === 'admin'
        || (function_exists('isAdmin') && isAdmin())
    ) {
        return true;
    }

    // Demo account bypass (demo users should have read access to all levels)
    if ((function_exists('isDemoUser') && isDemoUser())
        || (function_exists('is_demo_user') && is_demo_user())
    ) {
        return true;
    }
    if (!empty($_SESSION['is_demo'])) {
        return true;
    }

    // Si l'utilisateur n'est pas connecté (pas de user_id), accès toujours autorisé (visiteur)
    if (empty($_SESSION['user_id'])) {
        return true;
    }

    $reqOrder = get_level_order($requiredLevel);
    if ($reqOrder === null) {
        return false;
    } // Unknown required level: deny

    $userOrder = get_user_level_order();
    if ($userOrder === null) {
        return false;
    } // Unknown user level: deny

    return $userOrder >= $reqOrder;
}

/**
 * Enforce access or abort with 403 and a friendly message (French)
 * @param string $requiredLevel
 */
function enforce_level_access_or_abort($requiredLevel)
{
    if (!can_current_user_access_level($requiredLevel)) {
        $userLevelName = $_SESSION['user_level'] ?? 'inconnu';
        $msg = "\n🔒 Accès refusé\n\nCe contenu est destiné aux élèves de niveau " . htmlspecialchars($requiredLevel) . ".\nVous êtes actuellement en niveau " . htmlspecialchars($userLevelName) . ".\n\nPour débloquer ce contenu, progressez dans votre parcours actuel ou contactez votre coach.";
        // Log the attempt
        $uid = $_SESSION['user_id'] ?? 0;
        error_log(sprintf("SECURITY: Level access denied. user_id=%s user_level=%s required=%s", $uid, $userLevelName, $requiredLevel));
        // Return 403
        http_response_code(403);
        // Simple HTML message
        echo '<div>';
        echo '<h1>🔒 Accès refusé</h1>';
        echo '<p>Ce contenu est destiné aux élèves de niveau <strong>' . htmlspecialchars($requiredLevel) . '</strong>.</p>';
        echo '<p>Vous êtes actuellement en niveau <strong>' . htmlspecialchars($userLevelName) . '</strong>.</p>';
        echo '<p>Pour débloquer ce contenu, progressez dans votre parcours actuel ou contactez votre coach.</p>';
        echo '</div>';
        exit;
    }
}
