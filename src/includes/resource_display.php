<?php

/**
 * resource_display.php
 *
 * Fonctions d'affichage des ressources externes
 * Complément à course_display.php pour les ressources avancées
 */

if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/../database/connection.php')) {
        require_once __DIR__ . '/../database/connection.php';
    } elseif (file_exists(__DIR__ . '/../../db/connection.php')) {
        require_once __DIR__ . '/../../db/connection.php';
    }
}

/**
 * Récupère les ressources recommandées pour l'utilisateur basé sur sa progression
 * @param int $userId
 * @param int $limit
 * @return array
 */
function getRecommendedResourcesForUser($userId, $limit = 6)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT cer.* FROM CourseExternalResources cer
            INNER JOIN exercisecourselinks ecl ON cer.CourseId = ecl.CourseId
            INNER JOIN UserProgress up ON ecl.ExerciseId = up.ExerciseId
            WHERE up.UserId = ? AND cer.IsActive = 1
            ORDER BY up.DateCompleted DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log("Erreur getRecommendedResourcesForUser: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les ressources par type
 * @param string $type video|article|website|podcast
 * @param int $limit
 * @return array
 */
function getResourcesByType($type, $limit = 10)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM CourseExternalResources
            WHERE ResourceType = ? AND IsActive = 1
            ORDER BY Title
            LIMIT ?
        ");
        $stmt->execute([$type, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log("Erreur getResourcesByType: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les statistiques des ressources par type
 * @return array
 */
function getResourcesStatistics()
{
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT ResourceType, COUNT(*) as count
            FROM CourseExternalResources
            WHERE IsActive = 1
            GROUP BY ResourceType
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log("Erreur getResourcesStatistics: " . $e->getMessage());
        return [];
    }
}

/**
 * Affiche un grid de ressources formatées
 * @param array $resources
 * @param bool $compact
 * @return string
 */
function displayResourcesGrid($resources, $compact = false)
{
    if (empty($resources)) {
        return '<div class="empty-state"><p>Aucune ressource trouvée.</p></div>';
    }

    $html = '<div class="resources-grid' . ($compact ? ' compact' : '') . '">';
    foreach ($resources as $resource) {
        $html .= renderResourceCardAdvanced($resource);
    }
    $html .= '</div>';

    return $html;
}

/**
 * Affiche une ressource avec détails avancés
 * @param array $resource
 * @return string
 */
function renderResourceCardAdvanced($resource)
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
    $level = $resource['Level'] ?? 'Tous niveaux';

    $html = '<div class="resource-card resource-card-advanced">';
    $html .= '<div class="resource-header-advanced">';
    $html .= '<span class="resource-icon">' . $icon . '</span>';
    $html .= '<span class="resource-type">' . htmlspecialchars($resource['ResourceType']) . '</span>';
    $html .= '</div>';

    $html .= '<h4 class="resource-title">' . htmlspecialchars($resource['Title']) . '</h4>';
    $html .= '<p class="resource-source">' . htmlspecialchars($resource['Source'] ?? 'Source') . '</p>';

    if (!empty($resource['Description'])) {
        $desc = substr($resource['Description'], 0, 100);
        $html .= '<p class="resource-description">' . htmlspecialchars($desc) . (strlen($resource['Description']) > 100 ? '...' : '') . '</p>';
    }

    $html .= '<div class="resource-meta">';
    if ($duration > 0) {
        $html .= '<span class="duration">⏱️ ' . $duration . ' min</span>';
    }
    $html .= '<span class="level">📊 ' . htmlspecialchars($level) . '</span>';
    $html .= '</div>';

    $html .= '<a href="' . htmlspecialchars($resource['URL']) . '" target="_blank" rel="noopener" class="btn btn-primary btn-small">Accéder</a>';
    $html .= '</div>';

    return $html;
}

/**
 * Récupère les ressources les plus populaires (par cours)
 * @param int $limit
 * @return array
 */
function getPopularResources($limit = 8)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT cer.* FROM CourseExternalResources cer
            INNER JOIN (
                SELECT CourseId, COUNT(*) as exercise_count
                FROM exercisecourselinks
                GROUP BY CourseId
            ) stats ON cer.CourseId = stats.CourseId
            WHERE cer.IsActive = 1
            ORDER BY stats.exercise_count DESC, cer.Id DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log("Erreur getPopularResources: " . $e->getMessage());
        return [];
    }
}
