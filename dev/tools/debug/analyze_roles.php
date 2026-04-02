<?php
/**
 * Analyse et test du système de rôles
 * Affiche la stratégie actuelle et les utilisateurs
 */

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/db/connection.php';

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          🔐 ANALYSE SYSTÈME DE RÔLES - MonCoachScolaire       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// 1. Analyse de la colonne Role
echo "1️⃣  STRUCTURE DE LA COLONNE ROLE\n";
echo "────────────────────────────────\n";

$stmt = $pdo->query("DESCRIBE Users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    if ($col['Field'] === 'Role') {
        echo "  Nom: {$col['Field']}\n";
        echo "  Type: {$col['Type']}\n";
        echo "  Null: {$col['Null']}\n";
        echo "  Défaut: {$col['Default']}\n";
        echo "  Clé: {$col['Key']}\n";
    }
}

echo "\n  ⚠️  OBSERVATION: Type VARCHAR(50) (pas d'ENUM)\n";
echo "  → Permet n'importe quel texte comme rôle\n";
echo "  → Recommandation: Utiliser ENUM pour la sécurité\n\n";

// 2. Rôles utilisés dans le code
echo "2️⃣  RÔLES ACTUELLEMENT UTILISÉS\n";
echo "─────────────────────────────\n";

$roles = [
    'admin' => [
        'description' => 'Administrateur du système',
        'permissions' => [
            '📊 Voir statistiques',
            '👥 Gérer utilisateurs',
            '📋 Voir logs',
            '📚 Gérer qualité exercices',
            '🔧 Accès dashboard admin'
        ]
    ],
    'student' => [
        'description' => 'Élève',
        'permissions' => [
            '📚 Faire des exercices',
            '🎯 Voir progression',
            '💡 Accéder aux conseils',
            '📊 Voir son dashboard personnel'
        ]
    ],
    'parent' => [
        'description' => 'Parent d\'élève',
        'permissions' => [
            '👀 Voir progression enfant',
            '📊 Voir dashboard parent',
            '📋 Voir rapports (⏳ À implémenter)'
        ]
    ]
];

foreach ($roles as $role => $info) {
    echo "  ➤ {$role}\n";
    echo "    Description: {$info['description']}\n";
    echo "    Permissions:\n";
    foreach ($info['permissions'] as $perm) {
        echo "      - {$perm}\n";
    }
    echo "\n";
}

// 3. Utilisateurs par rôle
echo "3️⃣  UTILISATEURS PAR RÔLE\n";
echo "────────────────────────\n";

foreach (array_keys($roles) as $role) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM Users WHERE Role = ?");
    $stmt->execute([$role]);
    $result = $stmt->fetch();
    $count = $result['count'];
    
    echo "  {$role}: {$count} utilisateur(s)\n";
    
    if ($count > 0) {
        $stmt = $pdo->prepare("SELECT Id, Username, Email FROM Users WHERE Role = ? ORDER BY Id");
        $stmt->execute([$role]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($users as $user) {
            echo "    • ID {$user['Id']}: {$user['Username']} ({$user['Email']})\n";
        }
    }
    echo "\n";
}

// 4. Fonctions d'authentification
echo "4️⃣  SYSTÈME D'AUTHENTIFICATION\n";
echo "───────────────────────────────\n";

if (file_exists(__DIR__ . '/src/includes/admin_auth.php')) {
    echo "  ✅ Fichier admin_auth.php trouvé\n";
    echo "  Fonctions disponibles:\n";
    echo "    • isAdmin() - Vérifie si l'utilisateur est admin\n";
    echo "    • requireAdmin() - Bloque l'accès non-admin\n";
    echo "    • getAdminInfo() - Récupère les infos admin\n";
} else {
    echo "  ❌ Fichier admin_auth.php manquant\n";
}

// 5. APIs protégées
echo "\n5️⃣  APIS PROTÉGÉES (ADMIN ONLY)\n";
echo "───────────────────────────────\n";

$apiFiles = [
    'stats.php' => 'Statistiques globales',
    'exercises_quality_live.php' => 'Qualité exercices',
    'users.php' => 'Gestion utilisateurs',
    'logs.php' => 'Logs système',
    'parents.php' => 'Gestion parents',
    'maintenance.php' => 'Mode maintenance'
];

$apiPath = __DIR__ . '/src/api/admin/';
foreach ($apiFiles as $file => $description) {
    $path = $apiPath . $file;
    $status = file_exists($path) ? '✅' : '❌';
    echo "  {$status} {$file} - {$description}\n";
}

// 6. Recommandations
echo "\n6️⃣  RECOMMANDATIONS D'AMÉLIORATION\n";
echo "──────────────────────────────────\n";

$recommendations = [
    [
        'titre' => 'Améliorer la colonne Role',
        'current' => 'Role VARCHAR(50)',
        'recommended' => "Role ENUM('admin', 'student', 'parent')",
        'impact' => 'Sécurité, validation base de données',
        'priorité' => '🔴 HAUTE'
    ],
    [
        'titre' => 'Implémenter rôle Parent',
        'current' => 'Code partiel dans login.php',
        'recommended' => 'Compléter dashboard_parent.php',
        'impact' => 'Fonctionnalité utilisateur',
        'priorité' => '🟡 MOYENNE'
    ],
    [
        'titre' => 'Ajouter système de permissions',
        'current' => 'Seulement vérification rôle',
        'recommended' => 'RBAC complet (Roles → Permissions)',
        'impact' => 'Granularité fine',
        'priorité' => '🟢 BASSE (futur)'
    ]
];

foreach ($recommendations as $i => $rec) {
    echo "\n  " . ($i + 1) . ". {$rec['titre']} {$rec['priorité']}\n";
    echo "     Actuel: {$rec['current']}\n";
    echo "     Recommandé: {$rec['recommended']}\n";
    echo "     Impact: {$rec['impact']}\n";
}

echo "\n\n✨ Analyse terminée!\n\n";
?>
