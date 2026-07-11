<?php

/**
 * course_display.php
 *
 * Fonctions d'affichage des cours intégrées à l'architecture existante
 * Compatible avec course_content.php et exercice_loader.php
 *
 * Usage: require_once __DIR__ . '/course_display.php';
 */

if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/../database/connection.php')) {
        require_once __DIR__ . '/../database/connection.php';
    } elseif (file_exists(__DIR__ . '/../../db/connection.php')) {
        require_once __DIR__ . '/../../db/connection.php';
    }
}

/**
 * Récupère les ressources externes d'un cours
 * @param int $courseId
 * @return array
 */
function getCourseExternalResources($courseId)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM CourseExternalResources
            WHERE CourseId = ? AND IsActive = 1
            ORDER BY ResourceType, Title
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log("Erreur getCourseExternalResources: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les ressources recommandées pour un exercice (basé sur le cours)
 * @param int $exerciseId
 * @return array
 */
function getExerciseExternalResources($exerciseId)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT c.Id FROM exercisecourselinks ecl
            INNER JOIN courses c ON ecl.CourseId = c.Id
            WHERE ecl.ExerciseId = ?
            LIMIT 1
        ");
        $stmt->execute([$exerciseId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return getCourseExternalResources($result['Id']);
        }
        return [];
    } catch (Exception $e) {
        error_log("Erreur getExerciseExternalResources: " . $e->getMessage());
        return [];
    }
}

/**
 * Affiche HTML d'une ressource externe
 * @param array $resource
 * @return string
 */
function renderResourceCard($resource)
{
    if (empty($resource)) {
        return '';
    }

    $icons = [
        'video' => '▶️',
        'article' => '📄',
        'website' => '🌐',
        'podcast' => '🎙️',
        'document' => '📋',
    ];

    $icon = $icons[$resource['ResourceType'] ?? 'website'] ?? '📌';
    $duration = $resource['Duration'] ?? 0;
    $durationText = $duration > 0 ? "⏱️ {$duration} min" : '';

    $html = '<div class="resource-card resource-' . htmlspecialchars($resource['ResourceType']) . '">';
    $html .= '<div class="resource-header">';
    $html .= '<span class="resource-icon">' . $icon . '</span>';
    $html .= '<div class="resource-info">';
    $html .= '<h4>' . htmlspecialchars($resource['Title']) . '</h4>';
    $html .= '<p class="resource-source">' . htmlspecialchars($resource['Source'] ?? 'Source') . '</p>';
    $html .= '</div>';
    $html .= '</div>';

    if (!empty($resource['Description'])) {
        $html .= '<p class="resource-description">' . htmlspecialchars($resource['Description']) . '</p>';
    }

    $html .= '<div class="resource-footer">';
    if ($durationText) {
        $html .= '<span class="resource-duration">' . $durationText . '</span>';
    }
    $html .= '<a href="' . htmlspecialchars($resource['URL']) . '" target="_blank" rel="noopener" class="btn btn-secondary btn-small">Accéder</a>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
}

/**
 * Affiche les ressources externes d'un cours (HTML)
 * @param int $courseId
 * @return string
 */
function displayCourseResourcesHtml($courseId)
{
    $resources = getCourseExternalResources($courseId);

    if (empty($resources)) {
        return '<p class="no-resources">Aucune ressource disponible.</p>';
    }

    $html = '<div class="resources-grid">';
    foreach ($resources as $resource) {
        $html .= renderResourceCard($resource);
    }
    $html .= '</div>';

    return $html;
}

/**
 * Récupère le cours associé à un exercice
 * @param int $exerciseId
 * @return array|null
 */
function getExerciseAssociatedCourse($exerciseId)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT c.* FROM courses c
            INNER JOIN exercisecourselinks ecl ON c.Id = ecl.CourseId
            WHERE ecl.ExerciseId = ?
            LIMIT 1
        ");
        $stmt->execute([$exerciseId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur getExerciseAssociatedCourse: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupère les exercices liés à un cours avec progression utilisateur
 * @param int $courseId
 * @param int|null $userId
 * @return array
 */
function getCourseLinkedExercises($courseId, $userId = null)
{
    global $pdo;
    try {
        $sql = "
            SELECT e.*";
        if ($userId) {
            $sql .= ", up.IsComplete, up.Score";
        }
        $sql .= " FROM exercises e
            INNER JOIN exercisecourselinks ecl ON e.Id = ecl.ExerciseId
            LEFT JOIN UserProgress up ON e.Id = up.ExerciseId" . ($userId ? " AND up.UserId = ?" : "") . "
            WHERE ecl.CourseId = ?
            ORDER BY e.Difficulty DESC, e.Id";

        $stmt = $pdo->prepare($sql);
        if ($userId) {
            $stmt->execute([$userId, $courseId]);
        } else {
            $stmt->execute([$courseId]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log("Erreur getCourseLinkedExercises: " . $e->getMessage());
        return [];
    }
}
