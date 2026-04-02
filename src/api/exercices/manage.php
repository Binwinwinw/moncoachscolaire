<?php

/**
 * manage.php - API de gestion des exercices
 * MonCoachScolaire - Dashboard Admin (Moved from api/admin/exercises_api.php)
 *
 * Endpoints:
 * - GET  ?action=stats           : Statistiques des exercices
 * - GET  ?action=list            : Liste des exercices avec filtres
 * - GET  ?action=duplicates      : Détection des doublons
 * - GET  ?action=cleanup&mode=   : Nettoyage (dryrun/real)
 * - POST action=import           : Import JSON en masse
 * - POST action=delete           : Suppression d'un exercice
 */

// ========================================
// 1. CONFIGURATION & SÉCURITÉ
// ========================================

// Gestionnaire d'erreurs silencieux pour l'API (évite de corrompre le JSON avec des Warnings PHP)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Forcer la détection API pour requireAdmin
define('IS_API_REQUEST', true);

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/middleware.php';

// Démarrer buffer de sortie pour capturer tout texte indésirable
ob_start();

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') {
    api_require([
        'method' => 'POST',
        'csrf' => true,
    ]);
} else {
    api_require([
        'method' => 'GET',
    ]);
}

// Détermination de la source de données (BDD ou JSON Fallback)
$useJsonFallback = false;
if (!isset($pdo) || !$pdo) {
    $useJsonFallback = true;
    // On ne bloque pas ici, on permettra la lecture des JSON si l'auth passe (ex: session active)
}

// Vérifier que l'utilisateur est admin
require_once __DIR__ . '/../../includes/admin_auth.php';

try {
    // Si pas de BDD, requireAdmin ne peut vérifier que si la session est déjà active
    // Si la session est vide et pas de BDD, requireAdmin redirigera ou échouera
    requireAdmin();
} catch (Exception $e) {
    ob_clean(); // Nettoyer le buffer
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Accès refusé. Authentification admin requise. (' . $e->getMessage() . ')',
        'require_login' => true,
    ]);
    exit;
}

// Nettoyer le buffer avant d'envoyer la réponse légitime
while (ob_get_level() > 0) {
    ob_end_clean();
}
// Redémarrer le buffer pour la réponse finale
ob_start();
// ========================================
// 2. ROUTER PRINCIPAL
// ========================================


$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Endpoint pour récupérer le token CSRF
if ($action === 'get_csrf') {
    echo json_encode([
        'success' => true,
        'csrf_token' => csrf_token(),
    ]);
    exit;
}

switch ($action) {
    case 'stats':
        if ($useJsonFallback) {
            handleStatsJson();
        } else {
            handleStats($pdo);
        }
        break;

    case 'list':
        if ($useJsonFallback) {
            handleListJson();
        } else {
            handleList($pdo);
        }
        break;

    case 'duplicates':
        if ($useJsonFallback) {
            echo json_encode(['success' => true, 'message' => 'Non disponible en mode JSON']);
        } else {
            handleDuplicates($pdo);
        }
        break;

    case 'cleanup':
        if ($useJsonFallback) {
            echo json_encode(['success' => false, 'error' => 'Maintenance impossible sans BDD']);
        } else {
            $mode = $_GET['mode'] ?? 'dryrun';
            handleCleanup($pdo, $mode);
        }
        break;

    case 'import':
        if ($useJsonFallback) {
            echo json_encode(['success' => false, 'error' => 'Import impossible sans BDD']);
        } else {
            handleImport($pdo);
        }
        break;

    case 'delete':
        if ($useJsonFallback) {
            echo json_encode(['success' => false, 'error' => 'Suppression impossible sans BDD']);
        } else {
            handleDelete($pdo);
        }
        break;

    case 'toggle_active':
        if ($useJsonFallback) {
            echo json_encode(['success' => false, 'error' => 'Modification impossible sans BDD']);
        } else {
            handleToggleActive($pdo);
        }
        break;

    case 'duplicate':
        if ($useJsonFallback) {
            echo json_encode(['success' => false, 'error' => 'Duplication impossible sans BDD']);
        } else {
            handleDuplicate($pdo);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action invalide',
        ]);
        break;
}


// ========================================
// 3. FONCTIONS DE GESTION
// ========================================

/**
 * Activer/désactiver un exercice
 */
function handleToggleActive($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = isset($input['id']) ? (int) $input['id'] : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id requis']);
        return;
    }
    try {
        $stmt = $pdo->prepare('SELECT is_active FROM exercises WHERE Id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            throw new Exception('Exercice non trouvé');
        }
        $new = $row['is_active'] ? 0 : 1;
        $u = $pdo->prepare('UPDATE exercises SET is_active = ? WHERE Id = ?');
        $u->execute([$new, $id]);
        if (function_exists('logAdminAction')) {
            logAdminAction('exercise_toggle_active', 'Toggled active=' . $new, $id);
        }
        echo json_encode(['success' => true, 'id' => $id, 'is_active' => (int) $new]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Dupliquer un exercice
 */
function handleDuplicate($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = isset($input['id']) ? (int) $input['id'] : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'id requis']);
        return;
    }
    try {
        $stmt = $pdo->prepare('SELECT * FROM exercises WHERE Id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new Exception('Exercice non trouvé');
        }
        $baseIdentifier = $row['Identifier'] ?? 'EX-' . time();
        $newIdentifier = $baseIdentifier . '-COPY-' . rand(1000, 9999);
        $ins = $pdo->prepare('INSERT INTO exercises (Subject, Level, Title, Content, Instruction, Answer, AnswerType, Choices, Tips, Domain, Competence, Difficulty, Identifier, XP_Points, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $ins->execute([
            $row['Subject'],
            $row['Level'],
            $row['Title'] . ' (copie)',
            $row['Content'],
            $row['Instruction'],
            $row['Answer'],
            $row['AnswerType'],
            $row['Choices'],
            $row['Tips'],
            $row['Domain'],
            $row['Competence'],
            $row['Difficulty'],
            $newIdentifier,
            $row['XP_Points'] ?? 0,
            $row['is_active'] ?? 0,
        ]);
        $newId = $pdo->lastInsertId();
        if (function_exists('logAdminAction')) {
            logAdminAction('exercise_duplicate', 'Duplicated exercise ' . $id . ' → ' . $newId, $id);
        }
        echo json_encode(['success' => true, 'id' => $newId]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

/**
 * Statistiques globales des exercices
 */
function handleStats($pdo)
{
    try {
        // Total exercices
        $total = $pdo->query("SELECT COUNT(*) FROM exercises")->fetchColumn();

        // Exercices actifs
        $active = $pdo->query("
            SELECT COUNT(*) FROM exercises
            WHERE is_active = 'true' OR is_active = '1'
        ")->fetchColumn();

        // Identifiers uniques
        $unique = $pdo->query("
            SELECT COUNT(DISTINCT Identifier) FROM exercises
            WHERE Identifier IS NOT NULL AND Identifier != '' AND Identifier != 'NULL'
        ")->fetchColumn();

        // Doublons
        $duplicates = $pdo->query("
            SELECT COUNT(*) - COUNT(DISTINCT Identifier) FROM exercises
            WHERE Identifier IS NOT NULL AND Identifier != '' AND Identifier != 'NULL'
        ")->fetchColumn();

        // Exercices traités (si colonne processed existe)
        $processed = 0;
        try {
            $processed = $pdo->query("
                SELECT COUNT(*) FROM exercises WHERE processed = TRUE
            ")->fetchColumn();
        } catch (Exception $e) {
            // Colonne processed n'existe pas encore
        }

        // Problèmes détectés
        $issues = $pdo->query("
            SELECT COUNT(*) FROM exercises
            WHERE (Content IS NULL OR Content = '' OR LENGTH(Content) < 10)
               OR (Answer IS NULL OR Answer = '')
        ")->fetchColumn();

        // Contenus vides
        $emptyContent = $pdo->query("
            SELECT COUNT(*) FROM exercises
            WHERE Content IS NULL OR Content = '' OR LENGTH(Content) < 10
        ")->fetchColumn();

        echo json_encode([
            'success' => true,
            'stats' => [
                'total' => (int) $total,
                'active' => (int) $active,
                'unique_identifiers' => (int) $unique,
                'duplicates' => (int) $duplicates,
                'processed' => (int) $processed,
                'issues' => (int) $issues,
                'empty_content' => (int) $emptyContent,
            ],
        ]);

    } catch (Exception $e) {
        // En cas d'erreur SQL, on tente le fallback JSON
        error_log("Erreur SQL Stats: " . $e->getMessage() . " -> Fallback JSON");
        handleStatsJson();
    }
}

/**
 * Liste des exercices avec filtres et pagination
 */
function handleList($pdo)
{
    try {
        // Support 'p' as priority (pagination) over 'page' (router conflict)
        $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $subject = $_GET['subject'] ?? '';
        $level = $_GET['level'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        // Construction de la requête
        $where = [];
        $params = [];

        if ($subject) {
            $where[] = "Subject = :subject";
            $params[':subject'] = $subject;
        }

        if ($level) {
            $where[] = "Level = :level";
            $params[':level'] = $level;
        }

        if ($status === 'active') {
            $where[] = "(is_active = 'true' OR is_active = '1')";
        } elseif ($status === 'inactive') {
            $where[] = "(is_active = 'false' OR is_active = '0' OR is_active IS NULL)";
        }

        if ($search) {
            $where[] = "(Title LIKE :search OR Identifier LIKE :search OR Content LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

        // Compter le total
        $countSql = "SELECT COUNT(*) FROM exercises $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Récupérer les paramètres de tri
        $sort = $_GET['sort'] ?? 'id';
        $order = strtoupper($_GET['order'] ?? 'DESC');

        // Whitelist des colonnes autorisées pour le tri
        $allowedSorts = [
            'id', 'Identifier', 'Title', 'Subject', 'Level',
            'Domain', 'Competence', 'Difficulty', 'AnswerType',
            'is_active', 'XP_Points', 'Coherence',
        ];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Récupérer les exercices
        $sql = "
            SELECT
                id, Subject, Level, Title, Identifier, AnswerType,
                Difficulty, XP_Points, is_active, Content, Answer,
                Domain, Competence, Coherence
            FROM exercises
            $whereClause
            ORDER BY `$sort` $order
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // DEBUG: Vérification du nombre de résultats
        error_log("API List: " . count($exercises) . " exercises found.");

        $response = [
            'success' => true,
            'exercises' => $exercises,
            'total' => (int) $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];

        // Tentative d'encodage JSON sécurisée
        $json = json_encode($response, JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            $jsonError = json_last_error_msg();
            error_log("API List ERROR: json_encode failed ($jsonError). Trying partial cleanup.");

            // Tentative de rattrapage : conversion UTF-8 forcée
            array_walk_recursive($response, function (&$item, $key) {
                if (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-1');
                }
            });

            $json = json_encode($response, JSON_UNESCAPED_UNICODE);

            if ($json === false) {
                // Si ça échoue encore, on renvoie une erreur JSON propre
                throw new Exception("Impossible d'encoder les données (Problème Charset): " . json_last_error_msg());
            }
        }

        echo $json;

    } catch (Exception $e) {
        // En cas d'erreur SQL, on tente le fallback JSON
        error_log("Erreur SQL List: " . $e->getMessage() . " -> Fallback JSON");
        handleListJson();
    }
}

/**
 * Détection des doublons
 */
function handleDuplicates($pdo)
{
    try {
        $sql = "
            SELECT
                Identifier as identifier,
                COUNT(*) as count,
                GROUP_CONCAT(id ORDER BY id) as ids,
                MIN(Title) as title
            FROM exercises
            WHERE Identifier IS NOT NULL
              AND Identifier != ''
              AND Identifier != 'NULL'
            GROUP BY Identifier
            HAVING COUNT(*) > 1
            ORDER BY count DESC, Identifier
        ";

        $stmt = $pdo->query($sql);
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'duplicates' => $duplicates,
            'count' => count($duplicates),
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la détection des doublons',
            'error' => $e->getMessage(),
        ]);
    }
}

/**
 * Nettoyage des doublons (dry-run ou réel)
 */
function handleCleanup($pdo, $mode)
{
    try {
        // Validation du mode
        if (!in_array($mode, ['dryrun', 'real'], true)) {
            throw new Exception('Mode invalide. Utilisez "dryrun" ou "real"');
        }

        // 1. Trouver les doublons
        $sql = "
            SELECT
                Identifier,
                GROUP_CONCAT(id ORDER BY id) as ids,
                MIN(id) as keep_id
            FROM exercises
            WHERE Identifier IS NOT NULL
              AND Identifier != ''
              AND Identifier != 'NULL'
            GROUP BY Identifier
            HAVING COUNT(*) > 1
        ";

        $stmt = $pdo->query($sql);
        $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($duplicates)) {
            echo json_encode([
                'success' => true,
                'message' => 'Aucun doublon trouvé',
                'toDelete' => 0,
                'deleted' => 0,
            ]);
            return;
        }

        // 2. Collecter les IDs à supprimer
        $toDelete = [];
        foreach ($duplicates as $dup) {
            $ids = explode(',', $dup['ids']);
            $keepId = (int) $dup['keep_id'];
            foreach ($ids as $id) {
                $id = (int) $id;
                if ($id !== $keepId) {
                    $toDelete[] = $id;
                }
            }
        }

        // 3. Mode dry-run : juste compter
        if ($mode === 'dryrun') {
            echo json_encode([
                'success' => true,
                'message' => 'Simulation terminée',
                'toDelete' => count($toDelete),
                'ids' => $toDelete,
            ]);
            return;
        }

        // 4. Mode réel : transaction, backup puis suppression
        if ($mode === 'real') {
            $pdo->beginTransaction();
            try {
                $backupTable = 'exercises_backup_' . date('Ymd_His');
                $pdo->exec("CREATE TABLE `$backupTable` AS SELECT * FROM exercises");
                if (!empty($toDelete)) {
                    $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
                    $deleteSql = "DELETE FROM exercises WHERE id IN ($placeholders)";
                    $deleteStmt = $pdo->prepare($deleteSql);
                    $deleteStmt->execute($toDelete);
                    $deleted = $deleteStmt->rowCount();
                } else {
                    $deleted = 0;
                }
                $pdo->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Nettoyage terminé',
                    'deleted' => $deleted,
                    'backup' => $backupTable,
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors du nettoyage',
            'error' => $e->getMessage(),
        ]);
    }
}

/**
 * Import d'exercices depuis JSON
 */
function handleImport($pdo)
{
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['exercises']) || !is_array($input['exercises'])) {
            throw new Exception('Format JSON invalide. Attendu: {"exercises": [...]}');
        }

        $exercises = $input['exercises'];
        $validateOnly = $input['validateOnly'] ?? false;

        $errors = [];
        $imported = 0;

        // Champs requis
        $requiredFields = ['Subject', 'Level', 'Title', 'Content', 'Instruction', 'Answer', 'AnswerType'];

        foreach ($exercises as $index => $ex) {
            // Validation
            foreach ($requiredFields as $field) {
                if (!isset($ex[$field]) || trim($ex[$field]) === '') {
                    $errors[] = "Exercice #$index: Champ '$field' manquant ou vide";
                    continue 2;
                }
            }

            // Si mode validation uniquement, on continue
            if ($validateOnly) {
                $imported++;
                continue;
            }

            // Insertion
            try {
                $sql = "
                    INSERT INTO exercises (
                        Subject, Level, Title, Content, Instruction, Answer, AnswerType,
                        Choices, Tips, Domain, Competence, Difficulty, Identifier,
                        is_active, XP_Points, Coherence
                    ) VALUES (
                        :Subject, :Level, :Title, :Content, :Instruction, :Answer, :AnswerType,
                        :Choices, :Tips, :Domain, :Competence, :Difficulty, :Identifier,
                        :is_active, :XP_Points, :Coherence
                    )
                ";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':Subject' => $ex['Subject'],
                    ':Level' => $ex['Level'],
                    ':Title' => $ex['Title'],
                    ':Content' => $ex['Content'],
                    ':Instruction' => $ex['Instruction'],
                    ':Answer' => $ex['Answer'],
                    ':AnswerType' => $ex['AnswerType'],
                    ':Choices' => $ex['Choices'] ?? null,
                    ':Tips' => $ex['Tips'] ?? null,
                    ':Domain' => $ex['Domain'] ?? null,
                    ':Competence' => $ex['Competence'] ?? null,
                    ':Difficulty' => $ex['Difficulty'] ?? 'moyen',
                    ':Identifier' => $ex['Identifier'] ?? null,
                    ':is_active' => $ex['is_active'] ?? 'true',
                    ':XP_Points' => $ex['XP_Points'] ?? 10,
                    ':Coherence' => $ex['Coherence'] ?? 'true',
                ]);

                $imported++;

            } catch (PDOException $e) {
                $errors[] = "Exercice #$index: Erreur BDD - " . $e->getMessage();
            }
        }

        echo json_encode([
            'success' => true,
            'message' => $validateOnly ? 'Validation terminée' : 'Import terminé',
            'imported' => $imported,
            'validated' => $validateOnly ? $imported : null,
            'errors' => $errors,
            'total' => count($exercises),
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'import',
            'error' => $e->getMessage(),
        ]);
    }
}

/**
 * Suppression d'un exercice
 */
function handleDelete($pdo)
{
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        // Validation stricte
        if (!$id || !is_numeric($id) || $id <= 0) {
            throw new Exception('ID manquant ou invalide');
        }
        $id = (int) $id;

        // Vérifier que l'exercice existe
        $checkStmt = $pdo->prepare("SELECT id FROM exercises WHERE id = :id");
        $checkStmt->execute([':id' => $id]);
        if (!$checkStmt->fetch()) {
            throw new Exception('Exercice introuvable (ID: ' . $id . ')');
        }

        // Supprimer
        $stmt = $pdo->prepare("DELETE FROM exercises WHERE id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode([
            'success' => true,
            'message' => 'Exercice supprimé avec succès',
            'id' => $id,
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la suppression',
            'error' => $e->getMessage(),
        ]);
    }
}

// ========================================
// 4. FONCTIONS FALLBACK JSON
// ========================================

function getAllExercisesFromJson()
{
    $path = realpath(__DIR__ . '/../../../db/json/exercices/');
    $exercises = [];
    if (!$path || !is_dir($path)) {
        return [];
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'json') {
            // Ignorer les fichiers de schéma ou cachés
            if (strpos($file->getFilename(), 'schema') !== false) {
                continue;
            }
            if (strpos($file->getFilename(), '.') === 0) {
                continue;
            }

            $content = @file_get_contents($file->getPathname());
            if (!$content) {
                continue;
            }

            $data = json_decode($content, true);

            if (is_array($data)) {
                // Détecter si c'est une liste ou un objet unique
                // Si clés numériques (0, 1, ...), c'est une liste
                if (!empty($data) && array_keys($data) === range(0, count($data) - 1)) {
                    foreach ($data as $ex) {
                        if (is_array($ex)) {
                            $exercises[] = normalizeJsonExercise($ex);
                        }
                    }
                } elseif (!empty($data)) {
                    // Objet unique (tableau associatif)
                    $exercises[] = normalizeJsonExercise($data);
                }
            }
        }
    }
    return $exercises;
}

function normalizeJsonExercise($ex)
{
    // Générer un ID fictif (hash) si absent, car le front en a besoin pour les clés (API expects numeric ID usually, but string OK here)
    // On utilise crc32 pour avoir un entier compatible en pseudo-id
    $id = isset($ex['id']) ? $ex['id'] : crc32($ex['Identifier'] ?? serialize($ex));

    return [
        'id' => $id,
        'Identifier' => $ex['Identifier'] ?? '',
        'Title' => $ex['Title'] ?? 'Sans titre',
        'Subject' => $ex['Subject'] ?? '',
        'Level' => $ex['Level'] ?? '',
        'Domain' => $ex['Domain'] ?? '',
        'Competence' => $ex['Competence'] ?? '',
        'Difficulty' => $ex['Difficulty'] ?? '',
        'AnswerType' => $ex['AnswerType'] ?? '',
        'is_active' => isset($ex['is_active']) ? $ex['is_active'] : true,
        'XP_Points' => $ex['XP_Points'] ?? 0,
        'Coherence' => $ex['Coherence'] ?? null,
        'Content' => $ex['Content'] ?? '',
        'Answer' => is_array($ex['Answer'] ?? '') ? json_encode($ex['Answer'], JSON_UNESCAPED_UNICODE) : ($ex['Answer'] ?? ''),
        // Marquer comme venant du JSON
        '_source' => 'json',
    ];
}

function handleListJson()
{
    try {
        $all = getAllExercisesFromJson();

        // Filtres
        $subject = $_GET['subject'] ?? '';
        $level = $_GET['level'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = strtolower($_GET['search'] ?? '');

        $filtered = array_filter($all, function ($ex) use ($subject, $level, $status, $search) {
            if ($subject && $ex['Subject'] !== $subject) {
                return false;
            }
            if ($level && $ex['Level'] !== $level) {
                return false;
            }
            if ($status === 'active' && !$ex['is_active']) {
                return false;
            }
            if ($status === 'inactive' && $ex['is_active']) {
                return false;
            }
            if ($search) {
                if (strpos(strtolower($ex['Title']), $search) === false
                    && strpos(strtolower($ex['Identifier']), $search) === false
                    && strpos(strtolower($ex['Content']), $search) === false) {
                    return false;
                }
            }
            return true;
        });

        // Tri
        $sort = $_GET['sort'] ?? 'Identifier'; // Default sort for JSON
        $order = strtoupper($_GET['order'] ?? 'ASC'); // Default order

        usort($filtered, function ($a, $b) use ($sort, $order) {
            $valA = $a[$sort] ?? '';
            $valB = $b[$sort] ?? '';

            // Gestion numérique si besoin
            if ($sort === 'XP_Points') {
                $valA = (int) $valA;
                $valB = (int) $valB;
            } else {
                $valA = strtolower((string) $valA);
                $valB = strtolower((string) $valB);
            }

            if ($valA == $valB) {
                return 0;
            }
            $res = ($valA < $valB) ? -1 : 1;
            return ($order === 'DESC') ? -$res : $res;
        });

        // Pagination
        $total = count($filtered);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $paged = array_slice($filtered, $offset, $limit);

        // Comme les clés ont été préservées par array_filter, on réindexe
        $paged = array_values($paged);

        echo json_encode([
            'success' => true,
            'exercises' => $paged,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
            'mode' => 'fallback_json',
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors du chargement (Mode JSON)',
            'error' => $e->getMessage(),
        ]);
    }
}

function handleStatsJson()
{
    $all = getAllExercisesFromJson();

    $stats = [
        'total' => count($all),
        'active' => 0,
        'unique_identifiers' => 0,
        'duplicates' => 0,
        'processed' => 0,
        'issues' => 0,
        'empty_content' => 0,
    ];

    $identifiers = [];

    foreach ($all as $ex) {
        if (!empty($ex['is_active'])) {
            $stats['active']++;
        }

        if (!empty($ex['Identifier'])) {
            if (isset($identifiers[$ex['Identifier']])) {
                // C'est un doublon d'identifier
            } else {
                $identifiers[$ex['Identifier']] = true;
                $stats['unique_identifiers']++;
            }
        }

        if (empty($ex['Content']) || strlen($ex['Content']) < 10) {
            $stats['empty_content']++;
        }
    }

    $stats['duplicates'] = $stats['total'] - $stats['unique_identifiers'];

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'mode' => 'fallback_json',
    ]);
}
