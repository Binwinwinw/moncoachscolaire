<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
// Connexion DB (préférence src/database, fallback legacy db/connection.php)
if (file_exists(__DIR__ . '/../../database/connection.php')) {
    require_once __DIR__ . '/../../database/connection.php';
} elseif (file_exists(__DIR__ . '/../../../db/connection.php')) {
    require_once __DIR__ . '/../../../db/connection.php';
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Fichier de connexion DB non trouvé']);
    exit;
}
require_once __DIR__ . '/../../includes/admin_auth.php';
require_once __DIR__ . '/../../includes/login_security.php';

// Assurer la session pour récupérer l'état admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin only
try {
    requireAdmin();
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès interdit']);
    exit;
}

$csrfToken = null;
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    $csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
}

if (!function_exists('verifyCSRFToken') || !verifyCSRFToken((string) $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF invalide']);
    exit;
}

function isPlaceholderLine($line)
{
    $trim = trim($line);
    $patterns = [
        '/^[a-dA-D][\)\.\-]\s*Réponse\s+correcte\s*$/u',
        '/^[a-dA-D][\)\.\-]\s*Une\s+autre\s+réponse\s+possible\s*$/u',
        '/^[a-dA-D][\)\.\-]\s*aucune\s+des\s+réponses\s*$/ui',
        '/^[a-dA-D][\)\.\-]\s*Réponse\s+alternative\s*$/u',
        '/^\-\s*Réponse\s+correcte\s*$/u',
        '/^\-\s*Une\s+autre\s+réponse\s+possible\s*$/u',
        '/^\-\s*aucune\s+des\s+réponses\s*$/ui',
        '/^\-\s*Réponse\s+alternative\s*$/u',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $trim)) {
            return true;
        }
    }
    return false;
}

function removePlaceholders($text)
{
    if (!$text) {
        return $text;
    }
    $lines = preg_split('/\r?\n/', $text);
    $filtered = array_filter($lines, function ($line) {
        return !isPlaceholderLine($line);
    });
    return implode("\n", $filtered);
}

if (!isset($pdo) || !$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Base de données non disponible']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT Id, Content, Answer FROM exercises");
    $updated = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = (int) $row['Id'];
        $content = $row['Content'];
        $answer = $row['Answer'];
        $newContent = removePlaceholders($content);
        $newAnswer = removePlaceholders($answer);
        if ($newContent !== $content || $newAnswer !== $answer) {
            $upd = $pdo->prepare("UPDATE exercises SET Content = ?, Answer = ? WHERE Id = ?");
            $upd->execute([$newContent, $newAnswer, $id]);
            $updated++;
        }
    }
    echo json_encode(['success' => true, 'data' => ['updated' => $updated]], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
