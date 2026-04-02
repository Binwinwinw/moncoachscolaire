<?php
/**
 * Démonstration : Les APIs fonctionnent quand tu es admin
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>📊 Démonstration Dashboard Admin</h1>\n";
echo "<pre>\n";

// Charger les dépendances
require_once __DIR__ . '/src/database/connection.php';
require_once __DIR__ . '/src/includes/admin_auth.php';

echo "=== 1. Vérifier si tu es admin ===\n";
if (isAdmin()) {
    echo "✅ Tu es connecté en ADMIN!\n";
    echo "Les APIs vont fonctionner.\n\n";
    $show_data = true;
} else {
    echo "❌ Tu n'es pas connecté en admin.\n";
    echo "Les APIs retourneront des erreurs 403.\n\n";
    $show_data = false;
    
    echo "Pour tester, connecte-toi d'abord:\n";
    echo "→ " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . "/moncoachscolaire/login_admin_quick.php\n\n";
}

// Si admin, afficher les données des APIs
if ($show_data) {
    echo "=== 2. Données de l'API Stats ===\n";
    try {
        $adminPath = __DIR__ . '/src/api/admin/stats.php';
        $_SERVER['REQUEST_URI'] = '/api/admin/stats.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        require $adminPath;
        $output = ob_get_clean();
        
        $data = json_decode($output, true);
        if ($data['success']) {
            echo "✅ Stats chargées avec succès!\n";
            echo "Données:\n";
            foreach ($data['data'] as $key => $value) {
                echo "  - $key: " . (is_array($value) ? count($value) . " éléments" : $value) . "\n";
            }
        } else {
            echo "❌ Erreur: " . ($data['error'] ?? 'Erreur inconnue') . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== 3. Données de l'API Users ===\n";
    try {
        $adminPath = __DIR__ . '/src/api/admin/users.php';
        $_SERVER['REQUEST_URI'] = '/api/admin/users.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        require $adminPath;
        $output = ob_get_clean();
        
        $data = json_decode($output, true);
        if ($data['success']) {
            echo "✅ Utilisateurs chargés avec succès!\n";
            $count = count($data['data']['users'] ?? []);
            echo "  - Total utilisateurs: $count\n";
            echo "  - Admins: " . ($data['data']['stats']['admins'] ?? 0) . "\n";
            echo "  - Étudiants: " . ($data['data']['stats']['students'] ?? 0) . "\n";
        } else {
            echo "❌ Erreur: " . ($data['error'] ?? 'Erreur inconnue') . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== 4. Données de l'API Logs ===\n";
    try {
        $adminPath = __DIR__ . '/src/api/admin/logs.php';
        $_SERVER['REQUEST_URI'] = '/api/admin/logs.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        require $adminPath;
        $output = ob_get_clean();
        
        $data = json_decode($output, true);
        if ($data['success']) {
            echo "✅ Logs chargés avec succès!\n";
            $count = count($data['data']['logs'] ?? []);
            echo "  - Total logs: $count\n";
        } else {
            echo "❌ Erreur: " . ($data['error'] ?? 'Erreur inconnue') . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
}

echo "\n=== 5. Prochaines étapes ===\n";
if ($show_data) {
    echo "✅ Les APIs fonctionnent!\n";
    echo "→ Visite: /moncoachscolaire/public/index.php?page=dashboard_admin\n";
    echo "→ Tu devrais voir tous les graphiques et données\n";
} else {
    echo "❌ Tu n'es pas connecté.\n";
    echo "→ Visite d'abord: /moncoachscolaire/login_admin_quick.php\n";
    echo "→ Puis reviens à cette page pour vérifier\n";
}

echo "</pre>";
