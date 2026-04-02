<?php
/**
 * dev/tools/exercises/fix_duplicate_exercises.php
 * Détection et suppression des exercices en doublon
 */

require_once __DIR__ . '/../../../src/database/connection.php';

echo "🔍 RECHERCHE ET NETTOYAGE DES DOUBLONS D'EXERCICES\n";
echo str_repeat("=", 70) . "\n\n";

try {
    $pdo->beginTransaction();

    $stats = [
        'identifier_duplicates' => 0,
        'content_duplicates' => 0,
        'deleted' => 0,
        'kept' => 0
    ];

    // ========================================
    // 1. DOUBLONS PAR IDENTIFIER EXACT
    // ========================================
    echo "🔴 ÉTAPE 1 : Doublons d'Identifier\n";
    echo str_repeat("-", 70) . "\n";

    $stmt = $pdo->query("
        SELECT Identifier, COUNT(*) as count, GROUP_CONCAT(Id ORDER BY Id) as ids
        FROM exercises
        WHERE Identifier IS NOT NULL AND Identifier != ''
        GROUP BY Identifier
        HAVING count > 1
        ORDER BY count DESC
    ");

    $identifierDuplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($identifierDuplicates)) {
        echo "   ✅ Aucun doublon d'identifier trouvé\n\n";
    } else {
        echo "   ⚠️  " . count($identifierDuplicates) . " groupes de doublons trouvés\n\n";

        foreach ($identifierDuplicates as $dup) {
            $ids = explode(',', $dup['ids']);
            $keepId = $ids[0]; // Garder le premier (plus ancien)
            $deleteIds = array_slice($ids, 1);

            echo "   Identifier: {$dup['Identifier']} ({$dup['count']} occurrences)\n";
            echo "      ✅ Conservé : ID $keepId\n";
            echo "      🗑️  Supprimés : " . implode(', ', $deleteIds) . "\n\n";

            // Supprimer les doublons
            if (!empty($deleteIds)) {
                $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
                $stmt = $pdo->prepare("DELETE FROM exercises WHERE Id IN ($placeholders)");
                $stmt->execute($deleteIds);

                $stats['identifier_duplicates'] += count($deleteIds);
                $stats['deleted'] += count($deleteIds);
                $stats['kept']++;
            }
        }
    }

    // ========================================
    // 2. DOUBLONS PAR CONTENU IDENTIQUE
    // ========================================
    echo "\n🟡 ÉTAPE 2 : Doublons de contenu (même matière + niveau)\n";
    echo str_repeat("-", 70) . "\n";

    $stmt = $pdo->query("
        SELECT
            MD5(LOWER(TRIM(Content))) as content_hash,
            Subject,
            Level,
            COUNT(*) as count,
            GROUP_CONCAT(Id ORDER BY Id) as ids
        FROM exercises
        WHERE Content IS NOT NULL
        AND Content != ''
        AND LENGTH(Content) > 20
        GROUP BY content_hash, Subject, Level
        HAVING count > 1
        ORDER BY count DESC
        LIMIT 50
    ");

    $contentDuplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($contentDuplicates)) {
        echo "   ✅ Aucun doublon de contenu trouvé\n\n";
    } else {
        echo "   ⚠️  " . count($contentDuplicates) . " groupes de contenus identiques\n\n";

        foreach ($contentDuplicates as $dup) {
            $ids = explode(',', $dup['ids']);
            $keepId = $ids[0];
            $deleteIds = array_slice($ids, 1);

            echo "   {$dup['Subject']} - {$dup['Level']} ({$dup['count']} occurrences)\n";
            echo "      ✅ Conservé : ID $keepId\n";
            echo "      🗑️  Supprimés : " . implode(', ', $deleteIds) . "\n\n";

            // Supprimer les doublons
            if (!empty($deleteIds)) {
                $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
                $stmt = $pdo->prepare("DELETE FROM exercises WHERE Id IN ($placeholders)");
                $stmt->execute($deleteIds);

                $stats['content_duplicates'] += count($deleteIds);
                $stats['deleted'] += count($deleteIds);
                $stats['kept']++;
            }
        }
    }

    // ========================================
    // 3. DOUBLONS DE TITRE (même matière + niveau)
    // ========================================
    echo "\n🟢 ÉTAPE 3 : Doublons de titre\n";
    echo str_repeat("-", 70) . "\n";

    $stmt = $pdo->query("
        SELECT
            Title,
            Subject,
            Level,
            COUNT(*) as count,
            GROUP_CONCAT(Id ORDER BY Id) as ids
        FROM exercises
        WHERE Title IS NOT NULL
        AND Title != ''
        AND LENGTH(Title) > 5
        GROUP BY Title, Subject, Level
        HAVING count > 1
        ORDER BY count DESC
        LIMIT 30
    ");

    $titleDuplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($titleDuplicates)) {
        echo "   ✅ Aucun doublon de titre trouvé\n\n";
    } else {
        echo "   ⚠️  " . count($titleDuplicates) . " groupes de titres identiques\n\n";
        echo "   ⚠️  MODE SÉCURISÉ : Affichage seulement (pas de suppression)\n";
        echo "   💡 Vérifie manuellement si ce sont vraiment des doublons\n\n";

        foreach (array_slice($titleDuplicates, 0, 10) as $dup) {
            $ids = explode(',', $dup['ids']);

            echo "   \"{$dup['Title']}\" ({$dup['Subject']} - {$dup['Level']})\n";
            echo "      Occurrences : {$dup['count']} (IDs: {$dup['ids']})\n\n";
        }

        if (count($titleDuplicates) > 10) {
            echo "   ... et " . (count($titleDuplicates) - 10) . " autre(s)\n\n";
        }
    }

    // ========================================
    // CONFIRMATION ET COMMIT
    // ========================================
    echo "\n📊 RÉSUMÉ DES OPÉRATIONS\n";
    echo str_repeat("=", 70) . "\n";
    echo "🗑️  Doublons d'identifier supprimés : {$stats['identifier_duplicates']}\n";
    echo "🗑️  Doublons de contenu supprimés  : {$stats['content_duplicates']}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📉 Total supprimés : {$stats['deleted']}\n";
    echo "✅ Total conservés : {$stats['kept']}\n\n";

    if ($stats['deleted'] > 0) {
        echo "⚠️  ATTENTION : {$stats['deleted']} exercices vont être supprimés !\n\n";
        echo "Confirmer ? (oui/non) : ";

        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);

        if (strtolower($line) === 'oui') {
            $pdo->commit();
            echo "\n✅ Modifications appliquées avec succès !\n";
            echo "💡 Lance maintenant : php dev/tools/exercises/count_exercises.php\n";
        } else {
            $pdo->rollBack();
            echo "\n❌ Annulation : aucune modification appliquée\n";
        }
    } else {
        $pdo->commit();
        echo "✅ Aucune suppression nécessaire\n";
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n❌ ERREUR : " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
