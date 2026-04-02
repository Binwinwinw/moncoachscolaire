<?php

/**
 * Système de chargement des exercices depuis la base de données
 *
 * Usage: require_once __DIR__ . '/exercice_loader.php';
 */


// (Bloc supprimé : code orphelin et syntaxe cassée)


// Detecter la présence de la colonne is_active (DB ancienne sans ce champ)
if (!function_exists('exercisesHasIsActive')) {
    function exercisesHasIsActive()
    {
        static $has = null;
        if ($has !== null) {
            return $has;
        }
        global $pdo;
        if (!$pdo) {
            return $has = false;
        }
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM exercises LIKE 'is_active'");
            $has = (bool) $stmt->fetch();
        } catch (Exception $e) {
            $has = false;
        }
        return $has;
    }
}

// Detecter la présence de la colonne sort_order (ordre métier)
if (!function_exists('exercisesHasSortOrder')) {
    function exercisesHasSortOrder()
    {
        static $has = null;
        if ($has !== null) {
            return $has;
        }
        global $pdo;
        if (!$pdo) {
            return $has = false;
        }
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM exercises LIKE 'sort_order'");
            $has = (bool) $stmt->fetch();
        } catch (Exception $e) {
            $has = false;
        }
        return $has;
    }
}

/**
 * Charge un exercice par son ID
 *
 * @param int $id ID de l'exercice
 * @return array|null Données de l'exercice ou null si non trouvé
 */
function getExerciseById($id)
{
    global $pdo;

    if (!$pdo) {
        return null;
    }

    try {
        $sql = "SELECT * FROM exercises WHERE Id = ?";
        if (exercisesHasIsActive()) {
            $sql .= " AND (is_active = 1 OR is_active IS NULL)";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $exercise = $stmt->fetch();
        return $exercise ?: null;
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement de l'exercice $id: " . $e->getMessage());
        return null;
    }
}

/**
 * Normalise un niveau pour la recherche en base de données
 * Gère les variantes : 6ème/6eme, 5ème/5eme, etc.
 */
if (!function_exists('normalizeLevelForDB')) {
    function normalizeLevelForDB($level)
    {
        if (empty($level)) {
            return null;
        }

        // Mapping des niveaux standardisés (correspondance stricte avec la BDD)
        $levelMapping = [
            // Collège
            '6eme' => '6eme', '6ème' => '6eme', '6EME' => '6eme', '6e' => '6eme',
            '5eme' => '5eme', '5ème' => '5eme', '5EME' => '5eme', '5e' => '5eme',
            '4eme' => '4eme', '4ème' => '4eme', '4EME' => '4eme', '4e' => '4eme',
            '3eme' => '3eme', '3ème' => '3eme', '3EME' => '3eme', '3e' => '3eme',
            // Lycée (attention : pas de 2nde/1ere en BDD, mais Seconde/Premiere sans accent)
            '2nde' => 'Seconde', 'Seconde' => 'Seconde', 'seconde' => 'Seconde', 'SECONDE' => 'Seconde', '2NDE' => 'Seconde',
            '1ere' => 'Premiere', '1ère' => 'Premiere', 'Première' => 'Premiere', 'Premiere' => 'Premiere', 'première' => 'Premiere',
            'premiere' => 'Premiere', 'PREMIERE' => 'Premiere',
            'Terminale' => 'Terminale', 'terminale' => 'Terminale', 'TERMINALE' => 'Terminale',
            'BAC' => 'Terminale', 'bac' => 'Terminale', // BAC = Terminale pour les exercices
        ];

        $levelClean = trim($level);
        if (isset($levelMapping[$levelClean])) {
            return $levelMapping[$levelClean];
        }

        // Fallback : chercher par motif
        if (stripos($level, '6') !== false && stripos($level, '5') === false && stripos($level, '4') === false && stripos($level, '3') === false) {
            return '6eme';
        }
        if (stripos($level, '5') !== false && stripos($level, '6') === false && stripos($level, '4') === false && stripos($level, '3') === false) {
            return '5eme';
        }
        if (stripos($level, '4') !== false && stripos($level, '6') === false && stripos($level, '5') === false && stripos($level, '3') === false) {
            return '4eme';
        }
        if (stripos($level, '3') !== false && stripos($level, '1') === false && stripos($level, '6') === false && stripos($level, '5') === false && stripos($level, '4') === false) {
            return '3eme';
        }
        if (stripos($level, 'seconde') !== false || stripos($level, '2nde') !== false) {
            return 'Seconde';
        }
        if (stripos($level, 'première') !== false || stripos($level, 'premiere') !== false || stripos($level, '1ère') !== false) {
            return 'Premiere';
        }
        if (stripos($level, 'terminale') !== false || stripos($level, 'bac') !== false) {
            return 'Terminale';
        }

        return $levelClean; // Retourner tel quel si pas de correspondance
    }
}

/**
 * Liste les exercices par niveau et matière
 *
 * @param string $level Niveau (6ème, 3ème, Seconde, Première)
 * @param string|null $subject Matière (Mathématiques, Français) ou null pour toutes
 * @param int|null $limit Nombre maximum d'exercices à retourner
 * @param int $offset Offset pour la pagination
 * @return array Liste des exercices
 */
function getExercisesByLevel($level, $subject = null, $limit = null, $offset = 0)
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        // Vérifier si l'utilisateur est admin - si oui, charger tous les exercices
        $is_admin = false;
        if (function_exists('isAdmin')) {
            $is_admin = isAdmin();
        }

        // Charger les helpers de niveau si disponibles
        if (is_file(__DIR__ . '/level_access.php')) {
            require_once __DIR__ . '/level_access.php';
        }

        // Si un utilisateur non-admin demande un niveau supérieur, renvoyer vide
        // Les comptes démo peuvent accéder à tous les niveaux (bypass)
        if (!$is_admin && !empty($level) && function_exists('get_user_level_order')) {
            if (function_exists('isDemoUser') && isDemoUser()) {
                // Demo has full read access — do not filter
            } elseif (!empty($_SESSION['is_demo'])) {
                // Session flag: demo
            } else {
                $userOrder = get_user_level_order();
                if ($userOrder !== null) {
                    // Build variants and remove those with order > userOrder
                    $normalizedLevel = normalizeLevelForDB($level);
                    $levelVariants = [$normalizedLevel];
                    if ($normalizedLevel !== $level) {
                        $levelVariants[] = $level;
                    }
                    if (mb_strpos($normalizedLevel, 'ème') !== false) {
                        $levelVariants[] = str_replace('ème', 'eme', $normalizedLevel);
                    }

                    $allowedVariants = [];
                    foreach ($levelVariants as $v) {
                        $reqOrder = get_level_order($v);
                        if ($reqOrder === null || $reqOrder <= $userOrder) {
                            $allowedVariants[] = $v;
                        }
                    }
                    if (empty($allowedVariants)) {
                        return [];
                    }
                    // Continue but use only allowed variants
                    $level = $allowedVariants[0];
                }
            }
        }

        // Si admin et niveau est null ou 'all', charger tous les exercices
        $hasActive = exercisesHasIsActive();

        if ($is_admin && ($level === null || $level === 'all' || $level === '')) {
            $sql = "SELECT * FROM exercises";
            if ($hasActive) {
                $sql .= " WHERE (is_active = 1 OR is_active IS NULL)";
            }
            $params = [];

            if ($subject) {
                $sql .= $hasActive ? " AND Subject = ?" : " WHERE Subject = ?";
                $params[] = $subject;
            }

            $orderBy = exercisesHasSortOrder() ? "sort_order ASC, Id ASC" : "Id ASC";
            $sql .= " ORDER BY Level ASC, " . $orderBy;

            if ($limit) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        // Sinon, filtrer par niveau (comportement normal)
        // Normaliser le niveau pour la recherche
        $normalizedLevel = normalizeLevelForDB($level);

        // Essayer d'abord avec le niveau normalisé
        $levelVariants = [$normalizedLevel];

        // Ajouter aussi le niveau original au cas où
        if ($normalizedLevel !== $level) {
            $levelVariants[] = $level;
        }

        // Pour les niveaux avec accents, ajouter aussi la variante sans accent
        if (mb_strpos($normalizedLevel, 'ème') !== false) {
            $levelVariants[] = str_replace('ème', 'eme', $normalizedLevel);
        }

        // Construire la requête avec IN pour gérer les variantes
        $placeholders = implode(',', array_fill(0, count($levelVariants), '?'));
        $sql = "SELECT * FROM exercises WHERE Level IN ($placeholders)";
        if ($hasActive) {
            $sql .= " AND (is_active = 1 OR is_active IS NULL)";
        }
        $params = $levelVariants;

        if ($subject) {
            $sql .= " AND Subject = ?";
            $params[] = $subject;
        }

        $orderBy = exercisesHasSortOrder() ? "sort_order ASC, Id ASC" : "Id ASC";
        $sql .= " ORDER BY " . $orderBy;

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement des exercices: " . $e->getMessage());
        return [];
    }
}

/**
 * Retourne la liste des matières disponibles pour un ou plusieurs niveaux
 * @param string|array $levels Niveau(s) (ex: 'Seconde' ou ['Seconde','1ere'])
 * @return array Liste de matières distinctes triées
 */
function getSubjectsByLevels($levels)
{
    global $pdo;
    if (!$pdo) {
        return [];
    }
    if (!is_array($levels)) {
        $levels = [$levels];
    }
    // Normaliser chaque niveau
    $keys = array_map(function ($l) {
        return normalizeLevelForDB($l);
    }, $levels);
    $keys = array_filter($keys);
    if (empty($keys)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($keys), '?'));
    try {
        $sql = "SELECT DISTINCT Subject FROM exercises WHERE Level IN ($placeholders)";
        if (exercisesHasIsActive()) {
            $sql .= " AND (is_active = 1 OR is_active IS NULL)";
        }
        $sql .= " ORDER BY Subject ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($keys);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        return $rows ?: [];
    } catch (PDOException $e) {
        error_log("Erreur getSubjectsByLevels: " . $e->getMessage());
        return [];
    }
}

/**
 * NOUVELLE FONCTION: Charge les exercices par niveau et matière avec tri intelligent
 * Priorise les exercices avec des réponses complètes et cohérentes
 *
 * @param string $level Niveau scolaire
 * @param string|null $subject Matière (optionnel)
 * @param int|null $limit Nombre maximum d'exercices
 * @param int $offset Offset pour pagination
 * @return array Liste d'exercices triés par qualité
 */
function getExercisesByLevelSmart($level, $subject = null, $limit = null, $offset = 0)
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        $normalizedLevel = normalizeLevelForDB($level);
        $levelVariants = [$normalizedLevel];

        if ($normalizedLevel !== $level) {
            $levelVariants[] = $level;
        }

        if (mb_strpos($normalizedLevel, 'ème') !== false) {
            $levelVariants[] = str_replace('ème', 'eme', $normalizedLevel);
        }

        $placeholders = implode(',', array_fill(0, count($levelVariants), '?'));
        $hasActive = exercisesHasIsActive();

        // ✅ MODIFIÉ : SELECT explicite avec LinkedCourses
        // ✅ NOUVEAU (avec colonnes parsées)
        $sql = "SELECT
        Id, Title, Content, Answer, Subject, Level, Domain,
        Competence, XP_Points, AnswerType, Choices, Tips, Instruction,
        LinkedCourses, is_active,
        structure_type, pattern_detected, sub_questions, exam_prep, processed,
        LENGTH(Answer) as answer_length,

                CASE
                    WHEN LENGTH(Answer) >= 100 THEN 3
                    WHEN LENGTH(Answer) >= 50 THEN 2
                    ELSE 1
                END as quality_score
                FROM exercises
                WHERE Level IN ($placeholders)";

        if ($hasActive) {
            $sql .= " AND (is_active = 1 OR is_active IS NULL)";
        }
        $sql .= " AND Answer IS NOT NULL
                AND TRIM(Answer) != ''
                AND LENGTH(Answer) >= 30";

        $params = $levelVariants;

        if ($subject) {
            $sql .= " AND Subject = ?";
            $params[] = $subject;
        }

        // Trier par qualité d'abord, puis par ID
        $orderBy = exercisesHasSortOrder() ? "sort_order ASC, Id ASC" : "Id ASC";
        $sql .= " ORDER BY quality_score DESC, " . $orderBy;

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit . " OFFSET " . (int) $offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Fallback: si aucun exercice avec réponse complète, retourner la liste standard
        if (empty($rows)) {
            return getExercisesByLevel($level, $subject, $limit, $offset);
        }
        return $rows;
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement intelligent des exercices: " . $e->getMessage());
        // Fallback vers la méthode standard
        return getExercisesByLevel($level, $subject, $limit, $offset);
    }
}

/**
 * Charge tous les exercices (pour les admins)
 * Accepte soit des paramètres individuels, soit un tableau de filtres
 *
 * @param string|array|null $subject_or_filters Matière (string) ou tableau de filtres (array) ou null
 * @param int|null $limit Nombre maximum d'exercices à retourner (si $subject_or_filters est string)
 * @param int $offset Offset pour la pagination (si $subject_or_filters est string)
 * @return array Liste de tous les exercices
 */
function getAllExercises($subject_or_filters = null, $limit = null, $offset = 0)
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        $hasActive = exercisesHasIsActive();
        $sql = "SELECT * FROM exercises";
        if ($hasActive) {
            $sql .= " WHERE (is_active = 1 OR is_active IS NULL)";
        }
        $params = [];

        // Si le premier paramètre est un tableau, utiliser le format avec filtres
        if (is_array($subject_or_filters)) {
            $filters = $subject_or_filters;

            if (!empty($filters['level'])) {
                $sql .= ($hasActive ? " AND" : " WHERE") . " Level = ?";
                $params[] = $filters['level'];
            }

            if (!empty($filters['subject'])) {
                $sql .= ($hasActive || !empty($filters['level']) ? " AND" : " WHERE") . " Subject = ?";
                $params[] = $filters['subject'];
            }

            $orderBy = exercisesHasSortOrder() ? "sort_order ASC, Id ASC" : "Id ASC";
            $sql .= " ORDER BY Level ASC, Subject ASC, " . $orderBy;
        } else {
            // Sinon, utiliser le format avec paramètres individuels
            $subject = $subject_or_filters;

            if ($subject) {
                $sql .= ($hasActive ? " AND" : " WHERE") . " Subject = ?";
                $params[] = $subject;
            }

            $orderBy = exercisesHasSortOrder() ? "sort_order ASC, Id ASC" : "Id ASC";
            $sql .= " ORDER BY Level ASC, " . $orderBy;

            if ($limit) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
            }
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement de tous les exercices: " . $e->getMessage());
        return [];
    }
}

/**
 * Compte le nombre d'exercices par niveau et matière
 *
 * @param string $level Niveau
 * @param string|null $subject Matière ou null pour toutes
 * @return int Nombre d'exercices
 */
function countExercisesByLevel($level, $subject = null)
{
    global $pdo;

    if (!$pdo) {
        return 0;
    }

    try {
        $sql = "SELECT COUNT(*) as count FROM exercises WHERE Level = ? AND (is_active = 1 OR is_active IS NULL)";
        $params = [$level];

        if ($subject) {
            $sql .= " AND Subject = ?";
            $params[] = $subject;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 0);
    } catch (PDOException $e) {
        error_log("Erreur lors du comptage des exercices: " . $e->getMessage());
        return 0;
    }
}

/**
 * Recherche des exercices par titre ou domaine
 *
 * @param string $search Terme de recherche
 * @param string|null $level Niveau optionnel
 * @param string|null $subject Matière optionnelle
 * @return array Liste des exercices correspondants
 */
function searchExercises($search, $level = null, $subject = null)
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        $sql = "SELECT * FROM exercises WHERE (Title LIKE ? OR Content LIKE ?) AND (is_active = 1 OR is_active IS NULL)";
        $params = ["%$search%", "%$search%"];

        if ($level) {
            $sql .= " AND Level = ?";
            $params[] = $level;
        }

        if ($subject) {
            $sql .= " AND Subject = ?";
            $params[] = $subject;
        }

        $sql .= " ORDER BY Level ASC, Subject ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la recherche d'exercices: " . $e->getMessage());
        return [];
    }
}

/**
 * Normalise le niveau pour correspondre à la base de données
 *
 * @param string $level Niveau en format varié
 * @return string Niveau normalisé
 */
function normalizeLevel($level)
{
    $level = trim($level);

    // Standardiser les formats
    if (preg_match('/6|six|sixième/i', $level)) {
        return '6eme';
    }
    if (preg_match('/3|trois|troisième/i', $level)) {
        return '3eme';
    }
    if (preg_match('/seconde|2nde|2nd/i', $level)) {
        return '2nde';
    }
    if (preg_match('/première|premiere|1ère|1ere/i', $level)) {
        return '1ere';
    }
    if (preg_match('/terminale|term/i', $level)) {
        return 'Terminale';
    }

    return $level; // Retourner tel quel si non reconnu
}

/**
 * Normalise la matière pour correspondre à la base de données
 *
 * @param string $subject Matière en format varié
 * @return string Matière normalisée
 */
function normalizeSubject($subject)
{
    $subject = trim($subject);

    if (preg_match('/math/i', $subject)) {
        return 'Mathematiques';
    }
    if (preg_match('/français|francais/i', $subject)) {
        return 'Francais';
    }

    return $subject;
}

/**
 * Obtient les statistiques d'exercices par niveau et matière
 *
 * @return array Statistiques
 */
function getExerciseStats()
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->query("
            SELECT
                Level,
                Subject,
                COUNT(*) as count
            FROM exercises
            GROUP BY Level, Subject
            ORDER BY Level, Subject
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des stats: " . $e->getMessage());
        return [];
    }
}

// Nouveau loader pour course_key_points
function getKeyPointsByLevel($level, $subject = null, $limit = null, $offset = 0)
{
    global $pdo;
    if (!$pdo) {
        return [];
    }
    $normalizedLevel = normalizeLevelForDB($level);
    $levelVariants = [$normalizedLevel];
    if ($normalizedLevel !== $level) {
        $levelVariants[] = $level;
    }
    if (mb_strpos($normalizedLevel, 'ème') !== false) {
        $levelVariants[] = str_replace('ème', 'eme', $normalizedLevel);
    }
    $placeholders = implode(',', array_fill(0, count($levelVariants), '?'));
    $sql = "SELECT * FROM course_key_points WHERE level IN ($placeholders)";
    $params = $levelVariants;
    if (isset($pdo) && $pdo->query("SHOW COLUMNS FROM course_key_points LIKE 'is_active'")->fetch()) {
        $sql .= " AND (is_active = 1 OR is_active IS NULL)";
    }
    if ($subject) {
        $sql .= " AND subject = ?";
        $params[] = $subject;
    }
    $sql .= " ORDER BY order_in_chapter ASC, id ASC";
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getKeyPointSubjectsByLevels($levels)
{
    global $pdo;
    if (!$pdo) {
        return [];
    }
    if (!is_array($levels)) {
        $levels = [$levels];
    }
    $keys = array_map(function ($l) {
        return normalizeLevelForDB($l);
    }, $levels);
    $keys = array_filter($keys);
    if (empty($keys)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($keys), '?'));
    try {
        $sql = "SELECT DISTINCT subject FROM course_key_points WHERE level IN ($placeholders)";
        if (isset($pdo) && $pdo->query("SHOW COLUMNS FROM course_key_points LIKE 'is_active'")->fetch()) {
            $sql .= " AND (is_active = 1 OR is_active IS NULL)";
        }
        $sql .= " ORDER BY subject ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($keys);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        return $rows ?: [];
    } catch (PDOException $e) {
        error_log("Erreur getKeyPointSubjectsByLevels: " . $e->getMessage());
        return [];
    }
}
