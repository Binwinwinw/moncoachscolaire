<?php

// helper pour footer : calcule l'URL des exercices selon la session utilisateur
if (!function_exists('get_exercices_url_from_session')) {
    function get_exercices_url_from_session(): string
    {
        // s'assurer que la session est démarrée via les helpers centraux
        if (function_exists('ensure_session_started')) {
            ensure_session_started();
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
        }

        $default = function_exists('site_url') ? site_url('exercices') : '/index.php?page=exercices';

        // si pas connecté ou pas de niveau, retourner l'url par défaut
        if (empty($_SESSION['user_id']) || empty($_SESSION['logged_in']) || empty($_SESSION['user_level'])) {
            return $default;
        }

        $user_level = $_SESSION['user_level'];

        // Normalisation si disponible
        if (function_exists('normalize_level_for_url')) {
            $level_normalized = normalize_level_for_url($user_level);
        } else {
            // fallback simple : translit basique
            $level_normalized = preg_replace('~[^a-z0-9-]+~i', '-', strtolower(trim($user_level)));
        }

        // Résolution triée : collège / lycée / bac
        $college_levels = ['6eme','6ème','5eme','5ème','4eme','4ème','3eme','3ème'];
        $lycee_levels = ['seconde','premiere','première','terminale','bac'];

        $user_level_key = strtolower($user_level);

        if (in_array($user_level_key, $college_levels, true) || in_array($level_normalized, $college_levels, true)) {
            return function_exists('site_url') ? site_url('college/' . $level_normalized . '/exercices-' . $level_normalized) : '/index.php?page=college/' . $level_normalized . '/exercices-' . $level_normalized;
        }

        if (in_array($user_level_key, $lycee_levels, true) || in_array($level_normalized, $lycee_levels, true)) {
            return function_exists('site_url') ? site_url('lycee/' . $level_normalized . '/exercices-' . $level_normalized) : '/index.php?page=lycee/' . $level_normalized . '/exercices-' . $level_normalized;
        }

        if (strcasecmp($user_level, 'BAC') === 0) {
            return function_exists('site_url') ? site_url('bac/exercices-bac') : '/index.php?page=bac/exercices-bac';
        }

        return $default;
    }
}
