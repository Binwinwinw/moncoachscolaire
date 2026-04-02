<?php
/**
 * dev/tools/exercises/detect_duplicates.php
 * Détecte les doublons potentiels
 */

require_once __DIR__ . '/../../../src/database/connection.php';

echo "🔍 DÉTECTION DE DOUBLONS\n";
echo "========================\n\n";

// 1. DOUBLONS PAR IDENTIFIER EXACT
echo "🔴 DOUBLONS D'IDENTIFIER (EXACT)\n";
echo "─────────────────────────────────────\n";

$stmt = $pdo->query("
    SELECT Identifier, COUNT(*) as count, GROUP_CONCAT(Id) as ids
    FROM exercises
    WHERE Identifier IS NOT NULL AND Identifier != ''
    GROUP BY Identifier
    HAVING count > 1
    ORDER BY count DESC
    LIMIT 20
");

$duplicateIdentifiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicateIdentifiers)) {
    echo "✅ Aucun doublon d'identifier trouvé\n";
} else {
    echo "⚠️  " . count($duplicateIdentifiers) . " identifiers en doublon trouvés :\n\n";
    foreach ($duplicateIdentifiers as $dup) {
        echo "   {$dup['Identifier']} : {$dup['count']} fois (IDs: {$dup['ids']})\n";
    }
}

// 2. DOUBLONS PAR TITRE EXACT
echo "\n\n🟡 DOUBLONS DE TITRE (EXACT)\n";
echo "─────────────────────────────────────\n";

$stmt = $pdo->query("
    SELECT Title, Subject, Level, COUNT(*) as count, GROUP_CONCAT(Id) as ids
    FROM exercises
    WHERE Title IS NOT NULL AND Title != ''
    GROUP BY Title, Subject, Level
    HAVING count > 1
    ORDER BY count DESC
    LIMIT 20
");

$duplicateTitles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicateTitles)) {
    echo "✅ Aucun doublon de titre trouvé\n";
} else {
    echo "⚠️  " . count($duplicateTitles) . " titres en doublon trouvés :\n\n";
    foreach ($duplicateTitles as $dup) {
        echo "   \"{$dup['Title']}\" ({$dup['Subject']} - {$dup['Level']}) : {$dup['count']} fois\n";
        echo "      IDs: {$dup['ids']}\n\n";
    }
}

// 3. CONTENU SIMILAIRE (via hash MD5)
echo "\n🟢 CONTENU POTENTIELLEMENT IDENTIQUE (par hash)\n";
echo "─────────────────────────────────────────────────\n";

$stmt = $pdo->query("
    SELECT MD5(LOWER(TRIM(Content))) as content_hash,
           COUNT(*) as count,
           GROUP_CONCAT(Id) as ids,
           Subject,
           Level
    FROM exercises
    WHERE Content IS NOT NULL AND Content != ''
    GROUP BY content_hash, Subject, Level
    HAVING count > 1
    ORDER BY count DESC
    LIMIT 20
");

$duplicateContent = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($duplicateContent)) {
    echo "✅ Aucun contenu identique trouvé\n";
} else {
    echo "⚠️  " . count($duplicateContent) . " contenus identiques trouvés :\n\n";
    foreach ($duplicateContent as $dup) {
        echo "   {$dup['Subject']} - {$dup['Level']} : {$dup['count']} exercices identiques\n";
        echo "      IDs: {$dup['ids']}\n\n";
    }
}

// 4. INCOHÉRENCES DE CASSE/ORTHOGRAPHE
echo "\n⚠️  INCOHÉRENCES À CORRIGER\n";
echo "─────────────────────────────\n";

// Matières avec casse différente
$stmt = $pdo->query("
    SELECT DISTINCT Subject
    FROM exercises
    ORDER BY Subject
");

$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "📚 Matières (vérifier casse/accents) :\n";
$subjectGroups = [];
foreach ($subjects as $subject) {
    $normalized = strtolower(str_replace(['-', ' ', 'é', 'è'], ['', '', 'e', 'e'], $subject));
    $subjectGroups[$normalized][] = $subject;
}

foreach ($subjectGroups as $variants) {
    if (count($variants) > 1) {
        echo "   ⚠️  Variations : " . implode(' / ', $variants) . "\n";
    }
}

// Niveaux avec casse différente
$stmt = $pdo->query("
    SELECT DISTINCT Level
    FROM exercises
    ORDER BY Level
");

$levels = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "\n🎓 Niveaux (vérifier casse) :\n";
$levelGroups = [];
foreach ($levels as $level) {
    $normalized = strtolower(str_replace(['è', 'é', 'ème', 'eme'], ['e', 'e', '', ''], $level));
    $levelGroups[$normalized][] = $level;
}

foreach ($levelGroups as $variants) {
    if (count($variants) > 1) {
        echo "   ⚠️  Variations : " . implode(' / ', $variants) . "\n";
    }
}

// 5. STATISTIQUES FINALES
echo "\n\n📊 RÉSUMÉ\n";
echo "─────────\n";

$totalDuplicates = count($duplicateIdentifiers) + count($duplicateTitles) + count($duplicateContent);
$percentDuplicates = ($totalDuplicates / 1635) * 100;

printf("Doublons potentiels détectés : %d catégories\n", $totalDuplicates);
printf("Taux estimé de doublons : %.1f%%\n", $percentDuplicates);

echo "\n✅ Analyse terminée !\n";
echo "💡 Utilise ces informations pour nettoyer avant le parsing complet.\n";
