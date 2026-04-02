<?php

/**
 * Gardien d'accès par niveau scolaire
 * Affiche un message d'indisponibilité si l'élève connecté n'est pas du bon niveau.
 *
 * Usage:
 *   render_level_access_guard($required_level, 'exercices');
 *   // Return: 'allowed' | 'blocked' | 'not_logged_in'
 */

/**
 * Vérifie et affiche un message si l'accès est refusé
 *
 * @param string $required_level Niveau requis (ex: '6ème', 'Seconde', etc.)
 * @param string $section Type de section (ex: 'exercices', 'cours')
 * @param array $related_levels Niveaux connexes pour proposer des liens alternatifs
 * @return string État d'accès: 'allowed' | 'blocked' | 'not_logged_in'
 */
function render_level_access_guard($required_level, $section = 'exercices', $related_levels = [])
{
    // Charger la session et les helpers si nécessaire
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        }
    }
    if (!function_exists('isAdmin')) {
        if (is_file(dirname(__DIR__) . '/includes/admin_auth.php')) {
            require_once dirname(__DIR__) . '/includes/admin_auth.php';
        }
    }
    if (!function_exists('site_url')) {
        if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
            require_once dirname(__DIR__, 2) . '/config/site_boot.php';
        }
    }
    // Charger la normalisation des niveaux
    if (!function_exists('normalize_school_level')) {
        if (is_file(dirname(__DIR__) . '/includes/level_normalization.php')) {
            require_once dirname(__DIR__) . '/includes/level_normalization.php';
        }
    }

    $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
    $user_level = $_SESSION['user_level'] ?? '';
    $user_role = $_SESSION['user_role'] ?? '';

    // Vérifier si c'est un admin (plusieurs méthodes)
    $is_admin = false;
    if (function_exists('isAdmin')) {
        $is_admin = isAdmin();
    }
    // Vérifier aussi le rôle directement dans la session
    if (!$is_admin && in_array($user_role, ['admin', 'administrator'], true)) {
        $is_admin = true;
    }

    // Admin et visiteurs peuvent voir tout
    if ($is_admin || !$is_logged_in) {
        return 'allowed';
    }

    // Élève connecté: vérifier qu'il est du bon niveau (avec normalisation)
    if (function_exists('levels_match') && levels_match($user_level, $required_level)) {
        return 'allowed';
    } elseif ($user_level === $required_level) {
        // Fallback si la fonction de normalisation n'est pas disponible
        return 'allowed';
    }

    // Accès refusé - afficher le nom d'affichage propre
    $user_level_display = function_exists('get_level_display_name') ? get_level_display_name($user_level) : $user_level;
    $required_level_display = function_exists('get_level_display_name') ? get_level_display_name($required_level) : $required_level;

    echo '<div style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-left: 4px solid #ef4444; padding: 2rem; margin: 2rem auto; border-radius: 8px; max-width: 600px; text-align: center;">';
    echo '<h2 style="color: #dc2626; margin-top: 0;">❌ Les matières ne sont pas disponibles pour ce niveau</h2>';
    echo '<p style="color: #7f1d1d; font-size: 1.1rem; margin: 1rem 0;">';
    echo 'Tu es connecté en tant qu\'élève de <strong>' . htmlspecialchars($user_level_display ?: 'N/A') . '</strong>.<br>';
    echo 'Cette section est réservée au niveau <strong>' . htmlspecialchars($required_level_display) . '</strong>.';
    echo '</p>';

    // Proposer les liens de retour
    echo '<div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">';
    echo '<a class="btn btn-outline" href="' . (function_exists('site_url') ? site_url('eleve/dashboard') : '#') . '" style="background: #f3f4f6; border: 2px solid #6b7280; color: #374151; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600;">📊 Revenir au dashboard</a>';

    // Proposer un lien vers les exercices du bon niveau si c'est un collégien ou lycéen
    if (!empty($user_level)) {
        if (function_exists('normalize_level_for_url')) {
            $level_normalized = normalize_level_for_url($user_level);
            $target_url = '';

            // Détecter la section (collège/lycée/bac) avec normalisation
            if (function_exists('is_college_level') && is_college_level($user_level)) {
                $target_url = function_exists('site_url') ? site_url('college/' . $level_normalized . '/' . $section . '-' . $level_normalized) : '#';
            } elseif (function_exists('is_lycee_level') && is_lycee_level($user_level)) {
                $target_url = function_exists('site_url') ? site_url('lycee/' . $level_normalized . '/' . $section . '-' . $level_normalized) : '#';
            }

            if ($target_url !== '#') {
                $user_level_display_btn = function_exists('get_level_display_name') ? get_level_display_name($user_level) : $user_level;
                echo '<a class="btn btn-secondary" href="' . $target_url . '" style="background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 600;">🧩 Mes ' . htmlspecialchars($section) . ' (' . htmlspecialchars($user_level_display_btn) . ')</a>';
            }
        }
    }

    echo '</div>';
    echo '</div>';

    return 'blocked';
}

/**
 * Variante silencieuse : retourne juste le statut sans afficher
 */
function check_level_access($required_level)
{
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        }
    }
    if (!function_exists('isAdmin')) {
        if (is_file(dirname(__DIR__) . '/includes/admin_auth.php')) {
            require_once dirname(__DIR__) . '/includes/admin_auth.php';
        }
    }
    // Charger la normalisation des niveaux
    if (!function_exists('normalize_school_level')) {
        if (is_file(dirname(__DIR__) . '/includes/level_normalization.php')) {
            require_once dirname(__DIR__) . '/includes/level_normalization.php';
        }
    }

    $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
    $user_level = $_SESSION['user_level'] ?? '';
    $is_admin = function_exists('isAdmin') && isAdmin();

    if ($is_admin || !$is_logged_in) {
        return true;
    }

    // Comparaison avec normalisation
    if (function_exists('levels_match')) {
        return levels_match($user_level, $required_level);
    }

    return $user_level === $required_level;
}
