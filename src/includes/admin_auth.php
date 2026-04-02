<?php

/**
 * Système d'authentification et autorisation pour les administrateurs
 * Vérifie que l'utilisateur a le rôle 'admin' dans la base de données
 */

/**
 * Vérifie si l'utilisateur actuel est un administrateur
 *
 * @return bool True si l'utilisateur est admin
 */
function isAdmin()
{
    global $pdo;

    // Vérifier la session
    if (empty($_SESSION['user_id']) || empty($_SESSION['logged_in'])) {
        return false;
    }

    // Vérifier le rôle dans la session (cache)
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        return true;
    }

    // Vérifier dans la base de données
    if ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT Role FROM users WHERE Id = ? LIMIT 1');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user && $user['Role'] === 'admin') {
                // Mettre à jour la session
                $_SESSION['user_role'] = 'admin';
                return true;
            }
        } catch (Exception $e) {
            error_log("Erreur vérification admin: " . $e->getMessage());
        }
    }

    return false;
}

/**
 * Bloque l'accès si l'utilisateur n'est pas admin
 * Pour les requêtes API (Accept: application/json ou chemin /api/), lance une exception
 * Sinon redirige vers le dashboard utilisateur ou la page de login
 */
function requireAdmin()
{
    if (!isAdmin()) {
        // Détecter si c'est une requête API
        $isApiRequest = false;

        // Méthode 1: vérifier le header Accept
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $isApiRequest = true;
        }

        // Méthode 2: vérifier le chemin de la requête
        if (isset($_SERVER['REQUEST_URI'])) {
            if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                $isApiRequest = true;
            }
            // Support pour le routeur ?page=api/...
            if (strpos($_SERVER['REQUEST_URI'], 'page=api/') !== false) {
                $isApiRequest = true;
            }
        }
        // Méthode 4 (Spécifique Routeur): vérifier explicitement $_GET['page']
        if (isset($_GET['page']) && strpos($_GET['page'], 'api/') === 0) {
            $isApiRequest = true;
        }

        // Méthode 5: Constante explicite (Le plus fiable)
        if (defined('IS_API_REQUEST') && IS_API_REQUEST === true) {
            $isApiRequest = true;
        }
        // Méthode 3: vérifier si le script est dans le dossier api
        if (isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'], '/api/') !== false) {
            $isApiRequest = true;
        }

        if ($isApiRequest) {
            // Pour les APIs: lancer une exception au lieu de rediriger
            throw new Exception('Accès refusé. Administrateur requis.');
        }

        // Pour les pages web: rediriger
        // Sauvegarder la page demandée
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'admin';

        // Rediriger vers login ou dashboard selon l'état de connexion
        if (empty($_SESSION['user_id']) || empty($_SESSION['logged_in'])) {
            if (!headers_sent()) {
                header('Location: ' . site_url('login'));
            } else {
                echo '<script>window.location.href="' . site_url('login') . '";</script>';
                exit;
            }
        } else {
            // Rediriger selon le rôle si connu
            $role = $_SESSION['user_role'] ?? null;
            $redirectUrl = site_url('eleve/dashboard'); // Default

            switch ($role) {
                case 'admin':
                    $redirectUrl = site_url('admin/dashboard_admin');
                    break;
                case 'parent':
                    $redirectUrl = site_url('parents/dashboard_parent');
                    break;
                case 'student':
                default:
                    $redirectUrl = site_url('eleve/dashboard');
                    break;
            }

            if (!headers_sent()) {
                header('Location: ' . $redirectUrl);
            } else {
                echo '<script>window.location.href="' . $redirectUrl . '";</script>';
            }
        }
        exit;
    }
}

/**
 * Retourne les informations de l'administrateur actuel
 *
 * @return array|null Informations de l'admin ou null
 */
function getAdminInfo()
{
    global $pdo;

    if (!isAdmin()) {
        return null;
    }

    if ($pdo) {
        try {
            $stmt = $pdo->prepare('SELECT Id, Username, Email, Role, UserLevel, CreatedAt FROM users WHERE Id = ? LIMIT 1');
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Erreur récupération info admin: " . $e->getMessage());
        }
    }

    return null;
}

/**
 * Vérifie si l'utilisateur peut accéder à une ressource sans restriction de niveau
 * Les admins ont accès à tout, les autres utilisateurs sont soumis aux restrictions
 *
 * @return bool True si l'utilisateur est admin (accès total)
 */
function hasFullAccess()
{
    return isAdmin();
}

/**
 * Log une action administrative
 *
 * @param string $action Action effectuée
 * @param string $details Détails de l'action
 * @param int|null $targetUserId ID de l'utilisateur ciblé (optionnel)
 */
function logAdminAction($action, $details = '', $targetUserId = null)
{
    global $pdo;

    if (!$pdo || !isAdmin()) {
        return;
    }

    try {
        // Créer la table AdminLogs si elle n'existe pas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS AdminLogs (
                Id INT AUTO_INCREMENT PRIMARY KEY,
                AdminId INT NOT NULL,
                Action VARCHAR(100) NOT NULL,
                Details TEXT,
                TargetUserId INT NULL,
                IpAddress VARCHAR(45),
                UserAgent TEXT,
                CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_admin (AdminId),
                INDEX idx_action (Action),
                INDEX idx_created (CreatedAt),
                FOREIGN KEY (AdminId) REFERENCES users(Id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Insérer le log (AdminLogs)
        $stmt = $pdo->prepare("
            INSERT INTO AdminLogs (AdminId, Action, Details, TargetUserId, IpAddress, UserAgent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $details,
            $targetUserId,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);

        // Aussi insérer dans admin_audit (si disponible)
        try {
            $meta = json_encode(['details' => $details]);
            $stmt2 = $pdo->prepare('INSERT INTO admin_audit (admin_id, username, action, resource, meta, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt2->execute([
                $_SESSION['user_id'],
                $_SESSION['username'] ?? null,
                $action,
                $targetUserId,
                $meta,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Exception $inner) {
            // Table admin_audit may not exist yet - ignore silently
        }

    } catch (Exception $e) {
        // Fallback : utiliser error_log si la table n'existe pas
        error_log("ADMIN ACTION: {$action} - {$details} - Admin: " . ($_SESSION['user_id'] ?? 'unknown'));
    }
}
