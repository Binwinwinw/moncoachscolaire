<?php

/**
 * Système de chargement et gestion des cours pédagogiques en markdown
 *
 * Usage:
 *   require_once __DIR__ . '/course_markdown_loader.php';
 *   $courseContent = loadCourseMarkdown('cours/college/6eme/mathematiques/cours-001-fractions.md');
 */

if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/../database/connection.php')) {
        require_once __DIR__ . '/../database/connection.php';
    } elseif (file_exists(__DIR__ . '/../../db/connection.php')) {
        require_once __DIR__ . '/../../db/connection.php';
    }
}

/**
 * Charge le contenu brut d'un fichier cours markdown
 *
 * @param string $filePath Chemin relatif au fichier (ex: cours/college/6eme/math/cours-001.md)
 * @return array|null Contenu structuré ou null si erreur
 */
function loadCourseMarkdown($filePath)
{
    $fullPath = __DIR__ . '/../' . $filePath;

    if (!file_exists($fullPath)) {
        error_log("Cours non trouvé: $fullPath");
        return null;
    }

    $content = file_get_contents($fullPath);
    if ($content === false) {
        error_log("Erreur de lecture du cours: $fullPath");
        return null;
    }

    // Parser le markdown pour extraire les sections
    return parseMarkdownCourse($content, $filePath);
}

/**
 * Parse le contenu markdown et le structure
 *
 * @param string $content Contenu markdown brut
 * @param string $filePath Chemin du fichier (pour la source)
 * @return array Contenu structuré
 */
function parseMarkdownCourse($content, $filePath = '')
{
    $lines = explode("\n", $content);
    $course = [
        'filePath' => $filePath,
        'title' => '',
        'level' => '',
        'subject' => '',
        'objectives' => [],
        'sections' => [],
        'rawContent' => $content,
        'htmlContent' => '',
    ];

    $currentSection = null;
    $sectionContent = [];
    $inCodeBlock = false;

    foreach ($lines as $line) {
        // Détecter les blocs de code
        if (strpos($line, '```') === 0) {
            $inCodeBlock = !$inCodeBlock;
            $sectionContent[] = $line;
            continue;
        }

        // Extraire le titre principal (# titre)
        if (preg_match('/^#\s+(.+)$/', $line, $matches) && empty($course['title'])) {
            $course['title'] = trim($matches[1]);
            continue;
        }

        // Extraire le niveau et la matière depuis les métadonnées
        if (preg_match('/^\*\*Niveau\*\*\s*:\s*(.+)$/', $line, $matches)) {
            $course['level'] = trim($matches[1]);
        }
        if (preg_match('/^\*\*Domaine\*\*\s*:\s*(.+)$/', $line, $matches)) {
            $course['subject'] = trim($matches[1]);
        }

        // Extraire les objectifs (## 🎯 Objectifs du cours)
        if (preg_match('/^##\s+🎯\s+Objectifs/', $line)) {
            $currentSection = 'objectives';
            continue;
        }

        // Les autres ## deviennent des sections
        if (preg_match('/^##\s+(.+)$/', $line, $matches)) {
            // Sauvegarder la section précédente
            if ($currentSection && !empty($sectionContent)) {
                if ($currentSection === 'objectives') {
                    $course['objectives'] = array_filter(array_map('trim', $sectionContent));
                } else {
                    $course['sections'][] = [
                        'title' => $currentSection,
                        'content' => implode("\n", $sectionContent),
                    ];
                }
            }

            $currentSection = trim($matches[1]);
            $sectionContent = [];
            continue;
        }

        // Accumuler le contenu de la section
        if ($currentSection) {
            $sectionContent[] = $line;
        }
    }

    // Sauvegarder la dernière section
    if ($currentSection && !empty($sectionContent)) {
        if ($currentSection === 'objectives') {
            $course['objectives'] = array_filter(array_map('trim', $sectionContent));
        } else {
            $course['sections'][] = [
                'title' => $currentSection,
                'content' => implode("\n", $sectionContent),
            ];
        }
    }

    // Convertir le markdown en HTML pour l'affichage (optionnel, utiliser Parsedown si dispo)
    $course['htmlContent'] = convertMarkdownToHtml($content);

    return $course;
}

/**
 * Convertit le markdown en HTML (version simple)
 *
 * @param string $markdown Contenu markdown
 * @return string Contenu HTML
 */
function convertMarkdownToHtml($markdown)
{
    // Si Parsedown n'est pas disponible, retourner le contenu brut avec échappement
    if (!class_exists('Parsedown')) {
        return '<pre>' . htmlspecialchars($markdown) . '</pre>';
    }

    $parsedown = new Parsedown();
    return $parsedown->text($markdown);
}

/**
 * Normalise une chaîne en slug ASCII simple
 */
function normalizeToAsciiSlug($text, $removeDashes = false)
{
    $map = [
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a',
        'î' => 'i', 'ï' => 'i', 'ì' => 'i',
        'ô' => 'o', 'ö' => 'o', 'ò' => 'o',
        'û' => 'u', 'ü' => 'u', 'ù' => 'u',
        'ç' => 'c', 'œ' => 'oe', 'æ' => 'ae',
        'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
        'À' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Á' => 'a',
        'Î' => 'i', 'Ï' => 'i', 'Ì' => 'i',
        'Ô' => 'o', 'Ö' => 'o', 'Ò' => 'o',
        'Û' => 'u', 'Ü' => 'u', 'Ù' => 'u',
        'Ç' => 'c', 'Œ' => 'oe', 'Æ' => 'ae',
    ];

    $text = strtr($text ?? '', $map);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    if ($removeDashes) {
        $text = str_replace('-', '', $text);
    }
    return $text;
}

/**
 * Devine un chemin de fichier markdown à partir des métadonnées du cours
 */
function guessCourseFilePath(array $course)
{
    $hasMeta = !empty($course['Subject']) && !empty($course['Level']) && !empty($course['CourseNumber']);
    if (!$hasMeta) {
        return null;
    }

    $subjectSlug = normalizeToAsciiSlug($course['Subject']);
    $levelSlug = normalizeToAsciiSlug($course['Level'], true); // ex: 6ème -> 6eme
    $courseNumber = str_pad((int) $course['CourseNumber'], 3, '0', STR_PAD_LEFT);

    $candidateDirs = [
        "docs/cours/college/{$levelSlug}/{$subjectSlug}/",
        "cours/{$levelSlug}/{$subjectSlug}/",
    ];

    $patterns = [
        "cours-{$courseNumber}-*.md",
        "{$courseNumber}_*.md",
        "{$courseNumber}-*.md",
    ];

    foreach ($candidateDirs as $dir) {
        foreach ($patterns as $pattern) {
            $fullPattern = __DIR__ . '/../' . $dir . $pattern;
            $matches = glob($fullPattern);
            if (!empty($matches)) {
                // Retourner le premier match en chemin relatif
                $relative = str_replace(__DIR__ . '/../', '', $matches[0]);
                return str_replace('\\', '/', $relative);
            }
        }
    }

    return null;
}

/**
 * Charge un cours depuis la BDD par ID
 *
 * @param int $courseId ID du cours
 * @param bool $loadMarkdown Charger aussi le contenu markdown
 * @return array|null Données du cours ou null
 */
function getCourseById($courseId, $loadMarkdown = true)
{
    global $pdo;

    if (!$pdo) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM Courses WHERE Id = ?
        ");
        $stmt->execute([$courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            return null;
        }

        // Charger le contenu markdown si demandé
        if ($loadMarkdown) {
            $resolvedPath = null;

            if (!empty($course['FilePath'])) {
                $cleanPath = ltrim($course['FilePath'], '/\\');
                $fullPath = __DIR__ . '/../' . $cleanPath;
                if (file_exists($fullPath)) {
                    $resolvedPath = $cleanPath;
                }
            }

            // Tentative de chemin automatique si aucun fichier existant
            if (!$resolvedPath) {
                $guessed = guessCourseFilePath($course);
                if ($guessed) {
                    $resolvedPath = $guessed;
                    if (empty($course['FilePath'])) {
                        $course['FilePath'] = $guessed;
                    }
                }
            }

            if ($resolvedPath) {
                $markdownContent = loadCourseMarkdown($resolvedPath);
                if ($markdownContent) {
                    $course['markdown'] = $markdownContent;
                }
            }
        }

        return $course;
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement du cours $courseId: " . $e->getMessage());
        return null;
    }
}

/**
 * Liste les cours par niveau et matière
 *
 * @param string $subject Matière (ex: 'Mathématiques', 'SVT')
 * @param string $level Niveau (ex: '6ème', '4ème')
 * @param int|null $limit Nombre max de résultats
 * @return array Liste des cours
 */
function getCoursesBySubjectAndLevel($subject, $level, $limit = null)
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        $query = "
            SELECT * FROM Courses
            WHERE Subject = ? AND Level = ?
            ORDER BY CourseNumber ASC
        ";

        if ($limit) {
            $query .= " OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$subject, $level, $limit]);
        } else {
            $stmt = $pdo->prepare($query);
            $stmt->execute([$subject, $level]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement des cours: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère tous les cours liés à un exercice
 *
 * @param int $exerciseId ID de l'exercice
 * @return array Liste des cours liés
 */
function getCoursesForExercise($exerciseId)
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT c.*, ecl.LinkType
            FROM Courses c
            INNER JOIN ExerciseCourseLinks ecl ON c.Id = ecl.CourseId
            WHERE ecl.ExerciseId = ?
            ORDER BY c.Level DESC, c.CourseNumber ASC
        ");
        $stmt->execute([$exerciseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement des cours de l'exercice: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère tous les exercices liés à un cours
 *
 * @param int $courseId ID du cours
 * @return array Liste des exercices liés
 */
function getExercisesForCourse($courseId)
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT e.*, ecl.LinkType
            FROM exercises e
            INNER JOIN ExerciseCourseLinks ecl ON e.Id = ecl.ExerciseId
            WHERE ecl.CourseId = ?
            ORDER BY e.Title ASC
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement des exercices du cours: " . $e->getMessage());
        return [];
    }
}

/**
 * Crée un lien entre un exercice et un cours
 *
 * @param int $exerciseId ID de l'exercice
 * @param int $courseId ID du cours
 * @param string $linkType Type de lien ('theory', 'practice', etc.)
 * @return bool Succès ou non
 */
function linkExerciseToCourse($exerciseId, $courseId, $linkType = 'theory')
{
    global $pdo;

    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO ExerciseCourseLinks (ExerciseId, CourseId, LinkType)
            VALUES (?, ?, ?)
            ON CONFLICT DO NOTHING
        ");
        return $stmt->execute([$exerciseId, $courseId, $linkType]);
    } catch (PDOException $e) {
        error_log("Erreur lors de la création du lien exercice-cours: " . $e->getMessage());
        return false;
    }
}

/**
 * Enregistre la progression d'un utilisateur dans un cours
 *
 * @param int $userId ID de l'utilisateur
 * @param int $courseId ID du cours
 * @param string $status Statut ('started', 'in_progress', 'completed', 'reviewed')
 * @param int $timeSpent Temps passé en secondes
 * @return bool Succès ou non
 */
function updateUserCourseProgress($userId, $courseId, $status = 'in_progress', $timeSpent = 0)
{
    global $pdo;

    if (!$pdo) {
        return false;
    }

    try {
        // Vérifier si la progression existe
        $checkStmt = $pdo->prepare("SELECT Id FROM UserCourseProgress WHERE UserId = ? AND CourseId = ?");
        $checkStmt->execute([$userId, $courseId]);
        $exists = $checkStmt->fetch();

        if ($exists) {
            // Mettre à jour
            $updateStmt = $pdo->prepare("
                UPDATE UserCourseProgress
                SET Status = ?, TimeSpent = TimeSpent + ?, UpdatedAt = SYSUTCDATETIME()
                WHERE UserId = ? AND CourseId = ?
            ");
            return $updateStmt->execute([$status, $timeSpent, $userId, $courseId]);
        } else {
            // Insérer
            $insertStmt = $pdo->prepare("
                INSERT INTO UserCourseProgress (UserId, CourseId, Status, TimeSpent, StartedAt)
                VALUES (?, ?, ?, ?, SYSUTCDATETIME())
            ");
            return $insertStmt->execute([$userId, $courseId, $status, $timeSpent]);
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la mise à jour de la progression: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les statistiques d'un utilisateur par rapport aux cours
 *
 * @param int $userId ID de l'utilisateur
 * @return array Statistiques
 */
function getUserCourseStats($userId)
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total_courses,
                SUM(CASE WHEN Status = 'completed' THEN 1 ELSE 0 END) as completed_courses,
                SUM(CASE WHEN Status IN ('in_progress', 'started') THEN 1 ELSE 0 END) as in_progress_courses,
                SUM(TimeSpent) as total_time_spent
            FROM UserCourseProgress
            WHERE UserId = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur lors du chargement des stats: " . $e->getMessage());
        return [];
    }
}
