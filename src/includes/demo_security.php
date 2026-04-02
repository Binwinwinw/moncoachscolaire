<?php

/**
 * Système de sécurité pour le compte démo
 *
 * Ce fichier contient toutes les fonctions de sécurité nécessaires
 * pour s'assurer que le compte démo n'a accès qu'aux fonctionnalités autorisées
 */

/**
 * Vérifie si l'utilisateur actuel est en mode démo
 *
 * @return bool True si l'utilisateur est en mode démo
 */
function isDemoUser()
{
    return !empty($_SESSION['is_demo'])
           || (!empty($_SESSION['user_id'])
            && ($_SESSION['user_id'] === 0
             || (isset($_SESSION['user_name']) && ($_SESSION['user_name'] === 'demo' || $_SESSION['user_name'] === 'Visiteur Démo'))
             || (!empty($_SESSION['user_email']) && $_SESSION['user_email'] === 'demo@example.com')));
}

/**
 * Vérifie si l'utilisateur est le compte démo spécifique dans la BDD
 *
 * @param int|null $userId ID de l'utilisateur (optionnel, utilise session si non fourni)
 * @return bool True si c'est le compte démo réel
 */
function isDemoAccount($userId = null)
{
    global $pdo;

    if ($userId === null) {
        $userId = $_SESSION['user_id'] ?? null;
    }

    if (!$userId || $userId === 0) {
        return isDemoUser(); // Session visiteur = démo
    }

    // Vérifier dans la BDD si disponible
    if ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT Email, Username FROM users WHERE Id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if ($user) {
                return ($user['Email'] === 'demo@example.com' || $user['Username'] === 'demo');
            }
        } catch (Exception $e) {
            error_log("Erreur vérification compte démo: " . $e->getMessage());
        }
    }

    // Fallback : vérifier la session
    return isDemoUser();
}

/**
 * Bloque l'accès si l'utilisateur est en mode démo
 * Redirige vers la page de démo avec un message
 *
 * @param string $reason Raison du blocage (pour le message)
 * @param string $redirectUrl URL de redirection (par défaut: page demo)
 */
function blockDemoAccess($reason = 'Cette fonctionnalité n\'est pas disponible en mode démo', $redirectUrl = null)
{
    if (isDemoUser()) {
        if ($redirectUrl === null) {
            $redirectUrl = site_url('demo') . '?demo=1&blocked=1&reason=' . urlencode($reason);
        }

        $_SESSION['demo_blocked_reason'] = $reason;
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Vérifie si une opération d'écriture est autorisée pour le compte démo
 *
 * @param string $operation Type d'opération (ex: 'save_progress', 'update_profile')
 * @return bool True si l'opération est autorisée
 */
function isDemoWriteAllowed($operation = '')
{
    // Le compte démo ne peut PAS écrire dans la BDD
    // Seules les opérations en lecture sont autorisées

    $allowedReadOnlyOperations = [
        'view_exercises',
        'view_courses',
        'view_quiz',
        'view_progression_preview',
    ];

    // Si c'est une opération en lecture seule, autoriser
    if (in_array($operation, $allowedReadOnlyOperations)) {
        return true;
    }

    // Toutes les autres opérations (écriture) sont bloquées pour le démo
    return false;
}

/**
 * Vérifie et bloque les écritures en BDD pour le compte démo
 *
 * @param string $operation Type d'opération
 * @return bool True si l'opération peut continuer, false si bloquée
 */
function checkDemoWritePermission($operation = '')
{
    if (isDemoUser()) {
        if (!isDemoWriteAllowed($operation)) {
            error_log("SECURITY: Tentative d'écriture bloquée pour compte démo - Opération: $operation");
            return false;
        }
    }
    return true;
}

/**
 * Vérifie l'accès aux pages sensibles (admin, parents, etc.)
 *
 * @param string $pageType Type de page ('admin', 'parent', 'sensitive')
 * @return bool True si l'accès est autorisé
 */
function checkDemoPageAccess($pageType = '')
{
    if (isDemoUser()) {
        $blockedPages = ['admin', 'parent', 'sensitive'];

        if (in_array($pageType, $blockedPages)) {
            blockDemoAccess('Cette page n\'est pas accessible en mode démo');
            return false;
        }
    }
    return true;
}

/**
 * Nettoie les données sensibles avant affichage pour le compte démo
 *
 * @param array $data Données à nettoyer
 * @return array Données nettoyées
 */
function sanitizeDemoData($data)
{
    if (!isDemoUser()) {
        return $data; // Pas de nettoyage nécessaire pour les vrais utilisateurs
    }

    // Retirer les données sensibles
    $sensitiveKeys = ['password', 'password_hash', 'email', 'phone', 'address', 'parent_id'];

    foreach ($sensitiveKeys as $key) {
        if (isset($data[$key])) {
            unset($data[$key]);
        }
    }

    return $data;
}

/**
 * Force le mode lecture seule pour le compte démo dans les requêtes SQL
 *
 * @param string $sql Requête SQL
 * @return string Requête SQL modifiée (ou originale si pas de modification nécessaire)
 */
function enforceDemoReadOnly($sql)
{
    if (!isDemoUser()) {
        return $sql; // Pas de modification pour les vrais utilisateurs
    }

    // Bloquer les opérations d'écriture
    $writeOperations = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'CREATE', 'TRUNCATE'];
    $sqlUpper = strtoupper(trim($sql));

    foreach ($writeOperations as $op) {
        if (strpos($sqlUpper, $op) === 0) {
            error_log("SECURITY: Tentative d'opération d'écriture SQL bloquée pour compte démo: " . substr($sql, 0, 100));
            throw new Exception("Opération non autorisée en mode démo");
        }
    }

    return $sql;
}

/**
 * Vérifie que le compte démo ne peut pas modifier d'autres utilisateurs
 *
 * @param int $targetUserId ID de l'utilisateur cible
 * @return bool True si l'opération est autorisée
 */
function checkDemoUserModification($targetUserId)
{
    if (isDemoUser()) {
        $demoUserId = $_SESSION['user_id'] ?? 0;

        // Le compte démo ne peut modifier QUE ses propres données (et encore, en lecture seule)
        if ($targetUserId != $demoUserId) {
            error_log("SECURITY: Tentative de modification d'un autre utilisateur bloquée pour compte démo");
            return false;
        }
    }
    return true;
}
