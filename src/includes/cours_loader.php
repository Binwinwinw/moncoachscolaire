<?php

/**
 * Chargement des cours depuis la table Courses (miroir exercice_loader.php).
 */

if (!function_exists('normalizeLevelForDB') && is_file(__DIR__ . '/exercice_loader.php')) {
    require_once __DIR__ . '/exercice_loader.php';
}

if (!function_exists('coursesHasIsActive')) {
    function coursesHasIsActive(): bool
    {
        global $pdo;
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        if (!$pdo) {
            $cached = false;
            return false;
        }
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM Courses LIKE 'is_active'");
            $cached = (bool) $stmt->fetch();
        } catch (PDOException $e) {
            $cached = false;
        }
        return $cached;
    }
}

if (!function_exists('buildLevelVariantsForCourses')) {
    function buildLevelVariantsForCourses(string $level): array
    {
        $normalizedLevel = normalizeLevelForDB($level);
        $variants = array_values(array_unique(array_filter([
            $normalizedLevel,
            $normalizedLevel !== $level ? $level : null,
        ])));

        if (mb_strpos($normalizedLevel, 'ème') !== false) {
            $variants[] = str_replace('ème', 'eme', $normalizedLevel);
        }

        return array_values(array_unique(array_filter($variants)));
    }
}

if (!function_exists('getCoursesByLevel')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function getCoursesByLevel($level, $subject = null, $limit = null, $offset = 0): array
    {
        global $pdo;

        if (!$pdo || empty($level)) {
            return [];
        }

        if (is_file(__DIR__ . '/level_access.php')) {
            require_once __DIR__ . '/level_access.php';
        }

        $is_admin = function_exists('isAdmin') && isAdmin();
        if (!$is_admin && function_exists('can_current_user_access_level')) {
            if (!(function_exists('isDemoUser') && isDemoUser()) && empty($_SESSION['is_demo'])) {
                if (!can_current_user_access_level($level)) {
                    return [];
                }
            }
        }

        try {
            $levelVariants = buildLevelVariantsForCourses($level);
            if (empty($levelVariants)) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($levelVariants), '?'));
            $sql = "SELECT * FROM Courses WHERE Level IN ($placeholders)";
            if (coursesHasIsActive()) {
                $sql .= ' AND (is_active = 1 OR is_active IS NULL)';
            }
            $params = $levelVariants;

            if ($subject) {
                $sql .= ' AND Subject = ?';
                $params[] = $subject;
            }

            $sql .= ' ORDER BY CourseNumber ASC, Id ASC';

            if ($limit) {
                $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('getCoursesByLevel: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('getCourseSubjectsByLevel')) {
    /**
     * Matières disponibles pour un niveau (Courses puis fallback course_key_points).
     *
     * @return array<int, string>
     */
    function getCourseSubjectsByLevel(string $level): array
    {
        global $pdo;

        $subjects = [];
        $levelVariants = buildLevelVariantsForCourses($level);

        if ($pdo && !empty($levelVariants)) {
            try {
                $placeholders = implode(',', array_fill(0, count($levelVariants), '?'));
                $sql = "SELECT DISTINCT Subject FROM Courses WHERE Level IN ($placeholders)";
                if (coursesHasIsActive()) {
                    $sql .= ' AND (is_active = 1 OR is_active IS NULL)';
                }
                $sql .= ' ORDER BY Subject ASC';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($levelVariants);
                $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN, 0) ?: [];
            } catch (PDOException $e) {
                error_log('getCourseSubjectsByLevel Courses: ' . $e->getMessage());
            }
        }

        if (empty($subjects) && function_exists('getKeyPointSubjectsByLevels')) {
            $subjects = getKeyPointSubjectsByLevels($levelVariants);
        }

        return array_values(array_unique(array_filter($subjects)));
    }
}
