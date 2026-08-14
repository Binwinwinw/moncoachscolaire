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

function has_authenticated_session(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
}

function has_valid_cli_token(?string $token): bool
{
    $configuredToken = (string) getenv('MCSPHP_CLI_API_TOKEN');
    $token = (string) $token;

    return $configuredToken !== '' && $token !== '' && hash_equals($configuredToken, $token);
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
