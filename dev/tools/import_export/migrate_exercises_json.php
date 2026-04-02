<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * MIGRATE EXERCISES JSON - Adaptation au Nouveau Schéma
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * Script pour migrer tous les anciens fichiers JSON des exercices
 * vers le nouveau schéma officiel de la table `exercises`
 *
 * 📂 Dossier source : dev/db/json/exercices (scan récursif)
 * 📄 Schéma référence : dev/reports/exercises_schema.json
 * 💾 Sortie : Fichiers *.migrated.json (même dossier)
 * 📋 Rapport : dev/reports/migration_report.txt
 *
 * 🎯 Objectif :
 *    - Lire tous les fichiers JSON existants (dans tous les sous-dossiers)
 *    - Adapter chaque JSON au nouveau schéma de la table `exercises`
 *    - Gérer les champs manquants, obsolètes, renommés
 *    - Générer un rapport détaillé de migration
 *
 * 💻 Utilisation :
 *    php dev/tools/import_export/migrate_exercises_json.php
 *
 * ⚠️  Options :
 *    --dry-run : Simulation sans écriture de fichiers
 *    --force : Écraser les fichiers .migrated.json existants
 *
 * 📅 Créé : 14 février 2026
 * 👤 Auteur : MonCoachScolaire Team
 *
 * ═══════════════════════════════════════════════════════════════════════════
 */

// Chemins relatifs depuis la racine du projet
$projectRoot = dirname(__DIR__, 3); // Remonte de 3 niveaux

// Chemins
$jsonDir = $projectRoot . '/dev/db/json/exercices';
$schemaFile = $projectRoot . '/dev/reports/exercises_schema.json';
$reportFile = $projectRoot . '/dev/reports/migration_report.txt';

// Options
$dryRun = in_array('--dry-run', $argv);
$force = in_array('--force', $argv);

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  MIGRATE EXERCISES JSON - Adaptation Nouveau Schéma\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

if ($dryRun) {
    echo "⚠️  MODE DRY-RUN : Aucun fichier ne sera modifié\n\n";
}

// ═══════════════════════════════════════════════════════════════════════════
// 1. VÉRIFICATION DES PRÉREQUIS
// ═══════════════════════════════════════════════════════════════════════════

echo "📋 Vérification des prérequis...\n";
echo str_repeat("─", 65) . "\n";

// Vérifier que le dossier JSON existe
if (!is_dir($jsonDir)) {
    echo "❌ Dossier introuvable : $jsonDir\n";
    exit(1);
}
echo "✅ Dossier JSON : $jsonDir\n";

// Vérifier que le schéma existe
if (!file_exists($schemaFile)) {
    echo "❌ Fichier schéma introuvable : $schemaFile\n";
    echo "💡 Exécutez d'abord : php dev/tools/import_export/export_schema.php\n";
    exit(1);
}
echo "✅ Schéma trouvé : $schemaFile\n";

// Charger le schéma
$schemaContent = file_get_contents($schemaFile);
$schema = json_decode($schemaContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Erreur JSON dans le schéma : " . json_last_error_msg() . "\n";
    exit(1);
}
echo "✅ Schéma chargé : " . $schema['total_columns'] . " colonnes\n";

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// 2. SCANNER LES FICHIERS JSON (RÉCURSIF)
// ═══════════════════════════════════════════════════════════════════════════

echo "🔍 Scan récursif des fichiers JSON...\n";
echo str_repeat("─", 65) . "\n";

$jsonFiles = [];

// Scanner récursivement tous les sous-dossiers
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($jsonDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'json') {
        // Exclure les fichiers .migrated.json
        if (!str_ends_with($file->getFilename(), '.migrated.json')) {
            $jsonFiles[] = $file->getPathname();
        }
    }
}

if (empty($jsonFiles)) {
    echo "⚠️  Aucun fichier JSON trouvé dans : $jsonDir\n";
    echo "💡 Structure attendue :\n";
    echo "   dev/db/json/exercices/\n";
    echo "   ├── bac/\n";
    echo "   ├── college/6eme/\n";
    echo "   ├── lycee/2nd/\n";
    echo "   └── ...\n";
    exit(0);
}

echo "✅ Fichiers trouvés : " . count($jsonFiles) . "\n";

// Grouper par dossier pour affichage
$byFolder = [];
foreach ($jsonFiles as $file) {
    $relativePath = str_replace($jsonDir, '', dirname($file));
    $relativePath = trim($relativePath, '/\\');
    if (!isset($byFolder[$relativePath])) {
        $byFolder[$relativePath] = 0;
    }
    $byFolder[$relativePath]++;
}

echo "\n📂 Répartition par niveau :\n";
foreach ($byFolder as $folder => $count) {
    $folderDisplay = $folder ?: '(racine)';
    echo "   • $folderDisplay : $count fichier(s)\n";
}

echo "\n";

// ═══════════════════════════════════════════════════════════════════════════
// 3. MIGRATION DES FICHIERS
// ═══════════════════════════════════════════════════════════════════════════

$stats = [
    'total' => count($jsonFiles),
    'success' => 0,
    'errors' => 0,
    'skipped' => 0,
    'warnings' => [],
    'total_exercises' => 0,
    'migrated_exercises' => 0
];

$report = [];
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "  RAPPORT DE MIGRATION - Exercices JSON";
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";
$report[] = "Date : " . date('Y-m-d H:i:s');
$report[] = "Dossier source : $jsonDir (scan récursif)";
$report[] = "Schéma référence : $schemaFile";
$report[] = "Mode : " . ($dryRun ? "DRY-RUN (simulation)" : "PRODUCTION");
$report[] = "";
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";

echo "🔄 Migration en cours...\n";
echo str_repeat("─", 65) . "\n\n";

foreach ($jsonFiles as $index => $jsonFile) {
    $filename = basename($jsonFile);
    $relativePath = str_replace($jsonDir, '', $jsonFile);
    $relativePath = ltrim($relativePath, '/\\');
    $fileNum = $index + 1;

    echo "[$fileNum/$stats[total]] $relativePath... ";

    $report[] = "─────────────────────────────────────────────────────────────";
    $report[] = "Fichier $fileNum/$stats[total] : $relativePath";
    $report[] = "─────────────────────────────────────────────────────────────";

    try {
        // Lire le fichier JSON
        $jsonContent = file_get_contents($jsonFile);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON invalide : " . json_last_error_msg());
        }

        // Vérifier si le fichier migré existe déjà
        $migratedFile = str_replace('.json', '.migrated.json', $jsonFile);
        if (file_exists($migratedFile) && !$force) {
            echo "⏭️  SKIP (déjà migré)\n";
            $stats['skipped']++;
            $report[] = "Status : ⏭️  SKIPPED (fichier .migrated.json existe déjà)";
            $report[] = "Action : Utilisez --force pour réécrire";
            $report[] = "";
            continue;
        }

        // Détecter si c'est un tableau d'exercices ou un seul exercice
        $exercises = [];
        if (isset($data[0]) && is_array($data[0])) {
            // Tableau d'exercices
            $exercises = $data;
        } else {
            // Un seul exercice
            $exercises = [$data];
        }

        $stats['total_exercises'] += count($exercises);

        // Migrer chaque exercice
        $migratedExercises = [];
        $fileWarnings = [];

        foreach ($exercises as $exerciseIndex => $exercise) {
            try {
                $migratedExercise = migrateExercise($exercise, $schema['columns']);
                $migratedExercises[] = $migratedExercise;
                $stats['migrated_exercises']++;
            } catch (Exception $e) {
                $fileWarnings[] = "Exercice #$exerciseIndex : " . $e->getMessage();
            }
        }

        if (!empty($fileWarnings)) {
            $report[] = "⚠️  Avertissements :";
            foreach ($fileWarnings as $warning) {
                $report[] = "  • $warning";
            }
        }

        // Générer le rapport de différences (sur le premier exercice)
        if (!empty($exercises) && !empty($migratedExercises)) {
            $differences = compareExercises($exercises[0], $migratedExercises[0], $schema['columns']);

            if (!empty($differences['added'])) {
                $report[] = "Champs ajoutés : " . implode(', ', $differences['added']);
            }
            if (!empty($differences['removed'])) {
                $report[] = "Champs supprimés : " . implode(', ', $differences['removed']);
            }
            if (!empty($differences['modified'])) {
                $report[] = "Champs modifiés : " . implode(', ', array_keys($differences['modified']));
            }
        }

        $report[] = "Exercices traités : " . count($exercises) . " → " . count($migratedExercises);

        // Sauvegarder le fichier migré (sauf en dry-run)
        if (!$dryRun) {
            // Conserver la structure : tableau si c'était un tableau, objet sinon
            $outputData = (isset($data[0]) && is_array($data[0])) ? $migratedExercises : $migratedExercises[0];

            $migratedJson = json_encode($outputData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            file_put_contents($migratedFile, $migratedJson);
            $report[] = "Fichier généré : " . basename($migratedFile);
        }

        echo "✅ OK (" . count($exercises) . " ex.)\n";
        $stats['success']++;
        $report[] = "Status : ✅ SUCCESS";

    } catch (Exception $e) {
        echo "❌ ERREUR\n";
        $stats['errors']++;
        $report[] = "Status : ❌ ERROR";
        $report[] = "Message : " . $e->getMessage();
        $stats['warnings'][] = "$relativePath : " . $e->getMessage();
    }

    $report[] = "";
}

// ═══════════════════════════════════════════════════════════════════════════
// 4. GÉNÉRATION DU RAPPORT
// ═══════════════════════════════════════════════════════════════════════════

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  📊 RÉSUMÉ DE LA MIGRATION\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "Fichiers traités : " . $stats['total'] . "\n";
echo "✅ Succès : " . $stats['success'] . "\n";
echo "⏭️  Ignorés : " . $stats['skipped'] . "\n";
echo "❌ Erreurs : " . $stats['errors'] . "\n";
echo "\n";
echo "Exercices individuels :\n";
echo "   Total : " . $stats['total_exercises'] . "\n";
echo "   Migrés : " . $stats['migrated_exercises'] . "\n";
echo "\n";

$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "  STATISTIQUES FINALES";
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";
$report[] = "Fichiers :";
$report[] = "  Total : $stats[total]";
$report[] = "  Succès : $stats[success]";
$report[] = "  Ignorés : $stats[skipped]";
$report[] = "  Erreurs : $stats[errors]";
$report[] = "";
$report[] = "Exercices individuels :";
$report[] = "  Total : $stats[total_exercises]";
$report[] = "  Migrés : $stats[migrated_exercises]";
$report[] = "";

if (!empty($stats['warnings'])) {
    $report[] = "⚠️  AVERTISSEMENTS :";
    foreach ($stats['warnings'] as $warning) {
        $report[] = "  • $warning";
    }
    $report[] = "";
}

// Sauvegarder le rapport
if (!$dryRun) {
    file_put_contents($reportFile, implode("\n", $report));
    echo "📄 Rapport généré : $reportFile\n";
} else {
    echo "⚠️  Mode DRY-RUN : Aucun fichier généré\n";
}

echo "\n";

if ($stats['errors'] > 0) {
    exit(1);
}

// ═══════════════════════════════════════════════════════════════════════════
// FONCTIONS
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Migre un exercice vers le nouveau schéma
 */
function migrateExercise(array $exercise, array $schemaColumns): array
{
    $migrated = [];

    foreach ($schemaColumns as $columnName => $columnInfo) {
        // Ignorer les champs auto-générés
        if (in_array($columnName, ['Id', 'created_at', 'updated_at'])) {
            continue;
        }

        // Mapper les anciens noms de champs vers les nouveaux
        $value = mapFieldValue($exercise, $columnName, $columnInfo);

        // Appliquer la valeur par défaut si NULL et non nullable
        if ($value === null && !$columnInfo['nullable']) {
            $value = getDefaultValue($columnInfo);
        }

        $migrated[$columnName] = $value;
    }

    return $migrated;
}

/**
 * Mapper un champ (gestion des renommages)
 */
function mapFieldValue(array $exercise, string $fieldName, array $columnInfo)
{
    // Chercher d'abord avec le nom exact (respect de la casse)
    if (array_key_exists($fieldName, $exercise)) {
        return $exercise[$fieldName];
    }

    // Chercher en ignorant la casse
    foreach ($exercise as $key => $value) {
        if (strtolower($key) === strtolower($fieldName)) {
            return $value;
        }
    }

    return null;
}

/**
 * Obtenir une valeur par défaut selon le type
 */
function getDefaultValue(array $columnInfo)
{
    // Si une valeur par défaut est définie dans le schéma
    if ($columnInfo['default'] !== 'NULL') {
        return $columnInfo['default'];
    }

    // Sinon, générer selon le type PHP
    return match($columnInfo['php_type']) {
        'integer' => 0,
        'float' => 0.0,
        'boolean' => false,
        'array' => [],
        'string' => '',
        default => null
    };
}

/**
 * Comparer deux exercices et lister les différences
 */
function compareExercises(array $old, array $new, array $schemaColumns): array
{
    $differences = [
        'added' => [],
        'removed' => [],
        'modified' => []
    ];

    // Champs ajoutés
    foreach ($new as $key => $value) {
        if (!array_key_exists($key, $old)) {
            $differences['added'][] = $key;
        }
    }

    // Champs supprimés
    foreach ($old as $key => $value) {
        if (!array_key_exists($key, $new) && !in_array($key, ['Id', 'created_at', 'updated_at'])) {
            $differences['removed'][] = $key;
        }
    }

    // Champs modifiés
    foreach ($old as $key => $oldValue) {
        if (array_key_exists($key, $new) && $new[$key] !== $oldValue) {
            $differences['modified'][$key] = [
                'old' => $oldValue,
                'new' => $new[$key]
            ];
        }
    }

    return $differences;
}
