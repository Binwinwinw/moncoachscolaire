<?php
/**
 * API pour récupérer les données de progression pour les graphiques
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

// Démarrer le buffer de sortie pour capturer toute sortie accidentelle
ob_start();

// Désactiver l'affichage des erreurs pour éviter de polluer le JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

api_require([
    'method' => 'GET',
    'auth' => true,
]);

try {
    // Nettoyer le buffer avant de charger les fichiers (au cas où ils génèrent de la sortie)
    ob_clean();

    // Charger les fichiers nécessaires
    // Connexion DB (préférence src/database, fallback legacy db/connection.php)
    if (file_exists(__DIR__ . '/../database/connection.php')) {
        require_once __DIR__ . '/../database/connection.php';
    } elseif (file_exists(__DIR__ . '/../../db/connection.php')) {
        require_once __DIR__ . '/../../db/connection.php';
    }
    require_once dirname(__DIR__, 2) . '/includes/gamification.php';
    require_once dirname(__DIR__, 2) . '/includes/progress_display.php';
    require_once dirname(__DIR__, 2) . '/includes/dashboard_extensions.php';

    // Nettoyer à nouveau après le chargement des fichiers
    ob_clean();

    // Définir le header JSON maintenant que tout est chargé
    header('Content-Type: application/json; charset=utf-8');

    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['error' => 'Non authentifié', 'labels' => [], 'datasets' => []]);
        exit;
    }

    $days = isset($_GET['days']) ? (int) $_GET['days'] : 30;

    // Vérifier que la fonction existe
    if (!function_exists('getProgressHistory')) {
        error_log("get_progress_chart.php: Fonction getProgressHistory non trouvée");
        echo json_encode([
            'error' => 'Fonction getProgressHistory non disponible',
            'labels' => [],
            'datasets' => [],
        ]);
        exit;
    }

    $history = getProgressHistory($user_id, $days);

    // Si l'historique est vide, retourner des données vides mais valides
    if (empty($history)) {
        // Créer des données par défaut pour les 30 derniers jours
        $labels = [];
        $xpData = [];
        $exercisesData = [];
        $cristauxData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('d/m', strtotime($date));
            $xpData[] = 0;
            $exercisesData[] = 0;
            $cristauxData[] = 0;
        }
    } else {
        // Format pour Chart.js
        $labels = [];
        $xpData = [];
        $exercisesData = [];
        $cristauxData = [];

        foreach ($history as $record) {
            // Vérifier que les clés existent
            $date = $record['Date'] ?? $record['date'] ?? date('Y-m-d');
            $xp = isset($record['XP']) ? (int) $record['XP'] : (isset($record['xp']) ? (int) $record['xp'] : 0);
            $exercises = isset($record['ExercisesCompleted']) ? (int) $record['ExercisesCompleted'] : (isset($record['exercisesCompleted']) ? (int) $record['exercisesCompleted'] : 0);
            $cristaux = isset($record['Cristaux']) ? (int) $record['Cristaux'] : (isset($record['cristaux']) ? (int) $record['cristaux'] : 0);

            $labels[] = date('d/m', strtotime($date));
            $xpData[] = $xp;
            $exercisesData[] = $exercises;
            $cristauxData[] = $cristaux;
        }
    }

    // Nettoyer le buffer une dernière fois avant d'envoyer le JSON
    ob_clean();

    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'XP',
                'data' => $xpData,
                'borderColor' => '#3b82f6',
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'tension' => 0.4,
            ],
            [
                'label' => 'Exercices complétés',
                'data' => $exercisesData,
                'borderColor' => '#10b981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'tension' => 0.4,
            ],
            [
                'label' => 'Cristaux',
                'data' => $cristauxData,
                'borderColor' => '#f59e0b',
                'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                'tension' => 0.4,
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Terminer le buffer et envoyer la sortie
    ob_end_flush();

} catch (Exception $e) {
    // Nettoyer le buffer en cas d'erreur
    ob_clean();
    error_log("Erreur dans get_progress_chart.php: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'Erreur serveur lors de la récupération des données',
        'message' => $e->getMessage(),
        'labels' => [],
        'datasets' => [],
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
} catch (Error $e) {
    // Nettoyer le buffer en cas d'erreur fatale
    ob_clean();
    error_log("Erreur fatale dans get_progress_chart.php: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'Erreur fatale lors de la récupération des données',
        'message' => $e->getMessage(),
        'labels' => [],
        'datasets' => [],
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
}

?>

