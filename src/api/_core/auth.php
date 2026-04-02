<?php

/**
 * API Core Auth Helpers
 */

function require_auth(): void
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['logged_in'])) {
        json_error('Non authentifié', 401, 'ERR_AUTH');
    }
}

function require_role(array $roles): void
{
    require_auth();
    $role = $_SESSION['user_role'] ?? null;
    if (!$role || !in_array($role, $roles, true)) {
        json_error('Accès refusé', 403, 'ERR_FORBIDDEN');
    }
}

function is_demo_user(): bool
{
    if (!empty($_SESSION['is_demo'])) {
        return true;
    }
    $userId = $_SESSION['user_id'] ?? null;
    if ((int) $userId === 0) {
        return true;
    }
    $userName = strtolower((string) ($_SESSION['user_name'] ?? ''));
    $demoNames = ['demo', 'visiteur démo', 'élève demo', 'eleve demo'];
    return in_array($userName, $demoNames, true);
}
