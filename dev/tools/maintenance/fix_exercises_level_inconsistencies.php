<?php
/**
 * Script de correction automatique des incohérences de niveau
 * 
 * Ce script corrige automatiquement les exercices qui mentionnent un autre niveau
 * dans leur contenu en fonction de règles prédéfinies.
 * 
 * ATTENTION: Ce script modifie la base de données. Faites une sauvegarde avant !
 */

require_once __DIR__ . '/../config.php';

// Essayer de charger la connexion
require_once __DIR__ . '/../db/connection.php';

// Si la connexion a échoué, essayer avec la configuration locale
if (!$pdo) {
    try {
        $localHost = '127.0.0.1';
        $localDb = 'moncoachscolaire';
        $localUser = 'root';
        $localPass = '';
        
        $dsn = "mysql:host={$localHost};dbname={$localDb};charset=utf8mb4";
        $pdo = new PDO($dsn, $localUser, $localPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        echo "❌ ERREUR: Impossible de se connecter à la base de données.\n";
        exit(1);
    }
}

require_once __DIR__ . '/../includes/exercice_loader.php';

// Règles de correction (à adapter selon les cas)
$correctionRules = [
    // Si un exercice de 4ème mentionne "Seconde" dans le titre/contenu et parle de physique/mouvements
    // -> Probablement un exercice de Seconde mal classé
    [
        'condition' => function($exercise) {
            $level = $exercise['Level'] ?? '';
            $title = strtolower($exercise['Title'] ?? '');
            $content = strtolower(strip_tags($exercise['Content'] ?? ''));
            $subject = $exercise['Subject'] ?? '';
            
            return $level === '4ème' && 
                   (stripos($title, 'seconde') !== false || stripos($content, 'seconde') !== false) &&
                   (stripos($subject, 'Physique') !== false || stripos($title, 'mouvement') !== false);
        },
        'action' => 'move_to_seconde',
        'description' => 'Exercice de 4ème mentionnant Seconde (Physique) -> Déplacer vers Seconde'
    ],
    
    // Si un exercice de Première mentionne "3ème" et "Terminale" -> Probablement Terminale
    [
        'condition' => function($exercise) {
            $level = $exercise['Level'] ?? '';
            $title = strtolower($exercise['Title'] ?? '');
            $content = strtolower(strip_tags($exercise['Content'] ?? ''));
            
            return $level === 'Première' && 
                   (stripos($title, '3ème') !== false || stripos($content, '3ème') !== false) &&
                   (stripos($title, 'terminale') !== false || stripos($content, 'terminale') !== false || 
                    stripos($title, 'bac') !== false || stripos($content, 'bac') !== false);
        },
        'action' => 'move_to_terminale',
        'description' => 'Exercice de Première mentionnant 3ème et Terminale/BAC -> Déplacer vers Terminale'
    ],
    
    // Si un exercice de Seconde mentionne "3ème" -> Probablement un exercice de 3ème mal classé
    [
        'condition' => function($exercise) {
            $level = $exercise['Level'] ?? '';
            $title = strtolower($exercise['Title'] ?? '');
            $content = strtolower(strip_tags($exercise['Content'] ?? ''));
            
            return $level === 'Seconde' && 
                   (stripos($title, '3ème') !== false || stripos($content, '3ème') !== false);
        },
        'action' => 'move_to_3eme',
        'description' => 'Exercice de Seconde mentionnant 3ème -> Déplacer vers 3ème'
    ],
    
    // Si un exercice de Terminale mentionne "Seconde" -> Probablement un exercice de Seconde mal classé
    [
        'condition' => function($exercise) {
            $level = $exercise['Level'] ?? '';
            $title = strtolower($exercise['Title'] ?? '');
            $content = strtolower(strip_tags($exercise['Content'] ?? ''));
            
            return $level === 'Terminale' && 
                   (stripos($title, 'seconde') !== false || stripos($content, 'seconde') !== false) &&
                   stripos($title, 'composition') === false; // Exclure les compositions qui peuvent mentionner Seconde
        },
        'action' => 'move_to_seconde',
        'description' => 'Exercice de Terminale mentionnant Seconde -> Déplacer vers Seconde'
    ],
];

// Mapping des actions vers les nouveaux niveaux
$actionToLevel = [
    'move_to_seconde' => 'Seconde',
    'move_to_terminale' => 'Terminale',
    'move_to_3eme' => '3ème',
    'move_to_4eme' => '4ème',
];

echo "=== CORRECTION DES INCOHÉRENCES DE NIVEAU ===\n\n";
echo "⚠️  ATTENTION: Ce script va modifier la base de données.\n";
echo "   Assurez-vous d'avoir fait une sauvegarde avant de continuer.\n\n";

// Mode dry-run par défaut (ne modifie pas la BDD)
$dryRun = true;
if (isset($argv[1]) && $argv[1] === '--apply') {
    $dryRun = false;
    echo "✅ Mode APPLY activé - Les modifications seront effectuées.\n\n";
} else {
    echo "🔍 Mode DRY-RUN - Aucune modification ne sera effectuée.\n";
    echo "   Utilisez --apply pour appliquer les corrections.\n\n";
}

// Charger tous les exercices
$stmt = $pdo->query("SELECT * FROM Exercises ORDER BY Id");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$corrections = [];

foreach ($exercises as $exercise) {
    foreach ($correctionRules as $rule) {
        if ($rule['condition']($exercise)) {
            $newLevel = $actionToLevel[$rule['action']] ?? null;
            
            if ($newLevel && $newLevel !== $exercise['Level']) {
                $corrections[] = [
                    'id' => $exercise['Id'],
                    'current_level' => $exercise['Level'],
                    'new_level' => $newLevel,
                    'title' => $exercise['Title'],
                    'subject' => $exercise['Subject'],
                    'description' => $rule['description']
                ];
            }
        }
    }
}

if (empty($corrections)) {
    echo "✅ Aucune correction automatique possible.\n";
    echo "   Les incohérences nécessitent une vérification manuelle.\n";
    exit(0);
}

echo "📋 " . count($corrections) . " correction(s) proposée(s):\n\n";

foreach ($corrections as $correction) {
    echo sprintf("  ID %d | %s → %s | %s\n", 
        $correction['id'],
        $correction['current_level'],
        $correction['new_level'],
        $correction['subject']
    );
    echo sprintf("    Titre: %s\n", mb_substr($correction['title'], 0, 60));
    echo sprintf("    Raison: %s\n", $correction['description']);
    echo "\n";
}

if ($dryRun) {
    echo "🔍 Mode DRY-RUN: Aucune modification effectuée.\n";
    echo "   Pour appliquer ces corrections, exécutez:\n";
    echo "   php tools/fix_exercises_level_inconsistencies.php --apply\n";
} else {
    echo "🔧 Application des corrections...\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($corrections as $correction) {
        try {
            $stmt = $pdo->prepare("UPDATE Exercises SET Level = ? WHERE Id = ?");
            $stmt->execute([$correction['new_level'], $correction['id']]);
            $successCount++;
            echo sprintf("✅ ID %d: %s → %s\n", 
                $correction['id'],
                $correction['current_level'],
                $correction['new_level']
            );
        } catch (PDOException $e) {
            $errorCount++;
            echo sprintf("❌ ID %d: Erreur - %s\n", $correction['id'], $e->getMessage());
        }
    }
    
    echo "\n=== RÉSULTAT ===\n";
    echo sprintf("✅ %d correction(s) appliquée(s)\n", $successCount);
    if ($errorCount > 0) {
        echo sprintf("❌ %d erreur(s)\n", $errorCount);
    }
    
    echo "\n💡 Conseil: Ré-exécutez tools/analyze_exercises_issues.php pour vérifier les corrections.\n";
}
