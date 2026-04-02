<?php
/**
 * Script d'importation des 487 exercices depuis l'export JSON
 * Source: docs/exercices/export_20251227090627.json
 * 
 * Usage: php tools/import_487_exercises.php [--dry-run] [--update] [--force]
 * 
 * Flags:
 *   --dry-run : Affiche les stats sans importer
 *   --update  : Met à jour les exercices existants (par ID)
 *   --force   : Force l'importation même s'il y a des erreurs
 */

require_once __DIR__ . '/../db/connection.php';

// Debugging
if (!isset($pdo) || $pdo === null) {
    require_once __DIR__ . '/../src/database/connection.php';
}

// Configuration
$jsonFile = __DIR__ . '/../docs/exercices/export_20251227090627.json';
$dryRun = in_array('--dry-run', $argv);
$updateExisting = in_array('--update', $argv);
$force = in_array('--force', $argv);

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  📥 IMPORT DE 487 EXERCICES DEPUIS L'EXPORT JSON          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// 1. Vérifier le fichier
if (!file_exists($jsonFile)) {
    echo "❌ Erreur: Fichier JSON non trouvé: $jsonFile\n";
    exit(1);
}

echo "📂 Fichier source: $jsonFile\n";

// 2. Charger et parser le JSON
$jsonContent = file_get_contents($jsonFile);
$data = json_decode($jsonContent, true);

if (!$data || !isset($data['exercises'])) {
    echo "❌ Erreur: Impossible de parser le JSON\n";
    exit(1);
}

$exercises = $data['exercises'];
echo "✅ JSON parsé: " . count($exercises) . " exercices trouvés\n\n";

// 3. Analyser les données
$stats = [
    'total' => count($exercises),
    'byLevel' => [],
    'bySubject' => [],
    'issues' => []
];

foreach ($exercises as $ex) {
    $level = $ex['Level'] ?? 'Unknown';
    $subject = $ex['Subject'] ?? 'Unknown';
    
    if (!isset($stats['byLevel'][$level])) {
        $stats['byLevel'][$level] = 0;
    }
    $stats['byLevel'][$level]++;
    
    if (!isset($stats['bySubject'][$subject])) {
        $stats['bySubject'][$subject] = 0;
    }
    $stats['bySubject'][$subject]++;
    
    // Vérifier les champs obligatoires
    if (empty($ex['Title']) || empty($ex['Subject'])) {
        $stats['issues'][] = "Exercice ID {$ex['Id']}: champ manquant";
    }
}

echo "📊 STATISTIQUES DE L'EXPORT\n";
echo "───────────────────────────\n";
echo "Niveau:\n";
foreach ($stats['byLevel'] as $level => $count) {
    echo "  • $level: $count\n";
}
echo "\nMatière:\n";
foreach ($stats['bySubject'] as $subject => $count) {
    echo "  • $subject: $count\n";
}

if (!empty($stats['issues'])) {
    echo "\n⚠️  Problèmes détectés: " . count($stats['issues']) . "\n";
    foreach (array_slice($stats['issues'], 0, 5) as $issue) {
        echo "  • $issue\n";
    }
    if (count($stats['issues']) > 5) {
        echo "  ... et " . (count($stats['issues']) - 5) . " autres\n";
    }
}

// 4. Vérifier la BDD actuelle
echo "\n\n📊 ÉTAT ACTUEL DE LA BDD\n";
echo "───────────────────────\n";

try {
    $result = $pdo->query("SELECT COUNT(*) as count FROM Exercises");
    $currentCount = $result->fetch()['count'];
    echo "Exercices actuels: $currentCount\n";
    
    $result = $pdo->query("SELECT Level, COUNT(*) as count FROM Exercises GROUP BY Level");
    $currentByLevel = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "Par niveau actuellement:\n";
    foreach ($currentByLevel as $row) {
        echo "  • {$row['Level']}: {$row['count']}\n";
    }
} catch (Exception $e) {
    echo "⚠️  Erreur de connexion BDD: {$e->getMessage()}\n";
}

// 5. Mode DRY-RUN
if ($dryRun) {
    echo "\n\n🏁 MODE DRY-RUN - AUCUNE MODIFICATION\n";
    echo "───────────────────────────────────\n";
    echo "Avec --update, cela va:\n";
    echo "  • Mettre à jour " . count(array_filter($exercises, fn($e) => $currentCount > 0)) . " exercices existants\n";
    echo "  • Ajouter " . (count($exercises) - count(array_filter($exercises, fn($e) => $currentCount > 0))) . " nouveaux exercices\n";
    echo "  • Total en BDD après: " . ($currentCount + count($exercises)) . "\n";
    exit(0);
}

// 6. Préparation de l'import
echo "\n\n⚙️  PRÉPARATION DE L'IMPORT\n";
echo "──────────────────────────\n";

// Créer la table si elle n'existe pas (ou ajouter colonnes manquantes)
try {
    // Vérifier les colonnes nécessaires
    $columns = [];
    $result = $pdo->query("DESCRIBE Exercises");
    foreach ($result->fetchAll() as $col) {
        $columns[$col['Field']] = true;
    }
    
    $neededColumns = ['Id', 'Subject', 'Level', 'Title', 'Content', 'Answer', 'is_active'];
    $missingColumns = array_diff($neededColumns, array_keys($columns));
    
    if (!empty($missingColumns)) {
        echo "Ajout des colonnes manquantes:\n";
        foreach ($missingColumns as $col) {
            if ($col === 'is_active') {
                $pdo->exec("ALTER TABLE Exercises ADD COLUMN is_active TINYINT(1) DEFAULT 1");
            } else {
                // Colonnes texte par défaut
                $pdo->exec("ALTER TABLE Exercises ADD COLUMN $col LONGTEXT");
            }
            echo "  ✅ Colonne $col ajoutée\n";
        }
    }
} catch (Exception $e) {
    echo "⚠️  Erreur structure table: {$e->getMessage()}\n";
}

// 7. Import
echo "\n\n⏳ IMPORT EN COURS...\n";
echo "───────────────────\n";

$imported = 0;
$updated = 0;
$skipped = 0;
$errors = [];

foreach ($exercises as $i => $ex) {
    try {
        // Générer l'ID ou l'utiliser
        $id = $ex['Id'] ?? null;
        
        if ($updateExisting && $id) {
            // Vérifier si existe
            $check = $pdo->prepare("SELECT Id FROM Exercises WHERE Id = ?");
            $check->execute([$id]);
            
            if ($check->fetch()) {
                // Mettre à jour
                $stmt = $pdo->prepare("
                    UPDATE Exercises 
                    SET Subject = ?, Level = ?, Title = ?, Content = ?, Answer = ?, is_active = ?
                    WHERE Id = ?
                ");
                $stmt->execute([
                    $ex['Subject'] ?? '',
                    $ex['Level'] ?? '',
                    $ex['Title'] ?? '',
                    $ex['Content'] ?? '',
                    $ex['Answer'] ?? '',
                    $ex['is_active'] ?? 1,
                    $id
                ]);
                $updated++;
            } else {
                // Insérer avec ID
                $stmt = $pdo->prepare("
                    INSERT INTO Exercises (Id, Subject, Level, Title, Content, Answer, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $id,
                    $ex['Subject'] ?? '',
                    $ex['Level'] ?? '',
                    $ex['Title'] ?? '',
                    $ex['Content'] ?? '',
                    $ex['Answer'] ?? '',
                    $ex['is_active'] ?? 1
                ]);
                $imported++;
            }
        } else {
            // Insérer sans spécifier l'ID (auto-increment)
            $stmt = $pdo->prepare("
                INSERT INTO Exercises (Subject, Level, Title, Content, Answer, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $ex['Subject'] ?? '',
                $ex['Level'] ?? '',
                $ex['Title'] ?? '',
                $ex['Content'] ?? '',
                $ex['Answer'] ?? '',
                $ex['is_active'] ?? 1
            ]);
            $imported++;
        }
        
        // Afficher la progression tous les 100 exercices
        if (($i + 1) % 100 === 0) {
            echo "  ... " . ($i + 1) . "/" . count($exercises) . "\n";
        }
    } catch (Exception $e) {
        $errors[] = "ID {$ex['Id']}: {$e->getMessage()}";
        if (!$force) {
            throw $e;
        }
        $skipped++;
    }
}

// 8. Résultats
echo "\n\n✅ IMPORT TERMINÉ\n";
echo "──────────────────────\n";
echo "Exercices importés: $imported\n";
if ($updated > 0) {
    echo "Exercices mis à jour: $updated\n";
}
echo "Exercices ignorés: $skipped\n";

if (!empty($errors)) {
    echo "\n⚠️  Erreurs rencontrées:\n";
    foreach (array_slice($errors, 0, 10) as $error) {
        echo "  • $error\n";
    }
    if (count($errors) > 10) {
        echo "  ... et " . (count($errors) - 10) . " autres\n";
    }
}

// Vérifier le résultat
try {
    $result = $pdo->query("SELECT COUNT(*) as count FROM Exercises");
    $finalCount = $result->fetch()['count'];
    echo "\nTotal en BDD après import: $finalCount\n";
    
    $result = $pdo->query("SELECT Level, COUNT(*) as count FROM Exercises GROUP BY Level ORDER BY FIELD(Level, '6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale')");
    echo "\nRépartition par niveau:\n";
    foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo "  • {$row['Level']}: {$row['count']}\n";
    }
} catch (Exception $e) {
    echo "⚠️  Erreur vérification finale: {$e->getMessage()}\n";
}

echo "\n";
?>
