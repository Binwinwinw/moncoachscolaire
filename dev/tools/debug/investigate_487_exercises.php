<?php
/**
 * Investigation: Où sont les 487 exercices?
 */

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/db/connection.php';

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     🔍 INVESTIGATION - 487 exercices en BDD                   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// 1. Compte total actuel
echo "1️⃣  EXERCICES ACTUELS\n";
echo "───────────────────\n";
$stmt = $pdo->query('SELECT COUNT(*) as total FROM Exercises');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$totalNow = $row['total'];
echo "Total en base: {$totalNow} exercices\n";
echo "Attendus: 487 exercices\n";
echo "Manquants: " . (487 - $totalNow) . " exercices\n\n";

// 2. Par niveau
echo "2️⃣  RÉPARTITION PAR NIVEAU\n";
echo "────────────────────────\n";
$stmt = $pdo->query('SELECT Level, COUNT(*) as count FROM Exercises GROUP BY Level ORDER BY Level');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalByLevel = 0;
foreach ($results as $r) {
    echo "  " . str_pad($r['Level'], 12) . ": " . str_pad($r['count'], 3, ' ', STR_PAD_LEFT) . " exercices\n";
    $totalByLevel += $r['count'];
}
echo "  " . str_repeat("─", 26) . "\n";
echo "  TOTAL" . str_pad("", 7) . ": " . str_pad($totalByLevel, 3, ' ', STR_PAD_LEFT) . " exercices\n\n";

// 3. Par matière
echo "3️⃣  RÉPARTITION PAR MATIÈRE\n";
echo "────────────────────────\n";
$stmt = $pdo->query('SELECT Subject, COUNT(*) as count FROM Exercises GROUP BY Subject ORDER BY count DESC');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $r) {
    echo "  " . str_pad($r['Subject'], 30) . ": " . str_pad($r['count'], 3, ' ', STR_PAD_LEFT) . " exercices\n";
}

// 4. Chercher des indices sur les 487 exercices manquants
echo "\n4️⃣  RECHERCHE D'INDICES\n";
echo "──────────────────────\n";

// Chercher les fichiers de migration/import
echo "   Fichiers de migration trouvés:\n";
$files = glob(__DIR__ . '/db/*.sql');
$importFiles = array_filter($files, function($f) {
    return strpos(basename($f), 'import') !== false || 
           strpos(basename($f), 'seed') !== false ||
           strpos(basename($f), 'exercise') !== false;
});

if (count($importFiles) > 0) {
    foreach ($importFiles as $f) {
        echo "     • " . basename($f) . "\n";
    }
} else {
    echo "     (aucun fichier import trouvé)\n";
}

// Chercher les tables de sauvegarde
echo "\n   Tables potentiellement sauvegardées:\n";
$stmt = $pdo->query("SHOW TABLES");
$allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
$relatedTables = array_filter($allTables, function($t) {
    return stripos($t, 'exercise') !== false || stripos($t, 'backup') !== false;
});
if (count($relatedTables) > 0) {
    foreach ($relatedTables as $table) {
        echo "     • " . $table . "\n";
    }
} else {
    echo "     (aucune table de sauvegarde)\n";
}

// 5. Chercher les backups/archives
echo "\n5️⃣  FICHIERS SAUVEGARDE/ARCHIVAGE\n";
echo "───────────────────────────────────\n";

$backupPath = __DIR__ . '/backups';
if (is_dir($backupPath)) {
    $files = scandir($backupPath);
    $exerciseRelated = array_filter($files, function($f) {
        return strpos($f, 'exercise') !== false || 
               strpos($f, 'Exercise') !== false ||
               strpos($f, 'sql') !== false;
    });
    
    if (count($exerciseRelated) > 0) {
        foreach ($exerciseRelated as $f) {
            echo "   • backups/{$f}\n";
        }
    } else {
        echo "   (pas de fichiers exercise en backups)\n";
    }
} else {
    echo "   (dossier backups inexistant)\n";
}

// 6. Vérifier les fichiers tools
echo "\n6️⃣  SCRIPTS TOOLS (potentiels indices)\n";
echo "─────────────────────────────────────\n";
$toolsPath = __DIR__ . '/tools';
if (is_dir($toolsPath)) {
    $files = scandir($toolsPath);
    $exerciseRelated = array_filter($files, function($f) {
        return (strpos($f, 'exercise') !== false || 
                strpos($f, 'exercice') !== false) &&
               $f !== '.' && $f !== '..';
    });
    
    if (count($exerciseRelated) > 0) {
        echo "   Scripts concernant les exercices:\n";
        foreach ($exerciseRelated as $f) {
            echo "     • {$f}\n";
        }
    }
}

// 7. Analyser les logs si disponibles
echo "\n7️⃣  RECHERCHE DANS LES LOGS\n";
echo "───────────────────────────\n";

// Chercher des mentions dans les fichiers PHP
$patterns = glob(__DIR__ . '/tools/*.php');
$relevant = [];

foreach ($patterns as $file) {
    $content = file_get_contents($file);
    if (stripos($content, '487') !== false) {
        $relevant[] = basename($file);
    }
}

if (count($relevant) > 0) {
    echo "   Fichiers mentionnant '487':\n";
    foreach ($relevant as $f) {
        echo "     • {$f}\n";
    }
} else {
    echo "   (aucun fichier ne mentionne '487')\n";
}

// 8. Conclusion
echo "\n\n8️⃣  ANALYSE\n";
echo "──────────\n";
echo "   Situation: 89 exercices en DB vs 487 attendus\n";
echo "   Différence: " . (487 - $totalNow) . " exercices manquants\n\n";

if (487 - $totalNow > 0) {
    echo "   Hypothèses:\n";
    echo "   1. Les 487 exercices n'ont jamais été importés\n";
    echo "   2. Ils ont été supprimés ou archivés\n";
    echo "   3. Ils sont dans une table/backup séparé\n";
    echo "   4. C'est une donnée de test jamais implémentée\n";
}

echo "\n";
?>
