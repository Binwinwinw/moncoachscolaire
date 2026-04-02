<?php
/**
 * dev/tools/exercises/test_parser.php
 * Script de test pour le parser complet
 */

require_once __DIR__ . '/../../../src/database/connection.php';
// Dans test_parser.php, ça doit être :
require_once __DIR__ . '/../../../src/utils/ExerciseParser.php';

echo "🧪 TEST DU PARSER UNIFIÉ D'EXERCICES\n";
echo "=====================================\n\n";

try {
    $parser = new ExerciseParser($pdo);

    // IDs de test (ajuste selon tes exercices)
    $testIds = [1109, 1024, 1082, 1061];

    foreach ($testIds as $id) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📝 Exercice ID: $id\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

        $stmt = $pdo->prepare("SELECT * FROM exercises WHERE Id = ?");
        $stmt->execute([$id]);
        $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exercise) {
            echo "❌ Introuvable\n\n";
            continue;
        }

        echo "📚 {$exercise['Subject']} - {$exercise['Level']}\n";
        echo "📌 {$exercise['Title']}\n";

        // PARSING COMPLET
        $parsed = $parser->parseExerciseComplete($exercise);

        // 1. IDENTIFIER
        echo "\n🏷️  IDENTIFIER : " . ($exercise['Identifier'] ?: '(vide)') . "\n";
        if ($parsed['metadata']) {
            echo "   ✅ Valide\n";
            echo "   → Matière : {$parsed['metadata']['subject']}\n";
            echo "   → Niveau : {$parsed['metadata']['level']}\n";
            echo "   → Compétence : {$parsed['metadata']['competence']}\n";
            echo "   → Numéro : #{$parsed['metadata']['number']}\n";
            if ($parsed['metadata']['exam_prep']) {
                echo "   → Prépa : {$parsed['metadata']['exam_prep']}\n";
            }
        } else {
            echo "   ⚠️  Invalide ou manquant\n";
        }

        // 2. STRUCTURE
        echo "\n📄 STRUCTURE DE CONTENU\n";
        $struct = $parsed['structure'];
        echo "   Type : {$struct['structure_type']}\n";

        if ($struct['pattern_detected']) {
            echo "   Pattern : {$struct['pattern_detected']}\n";
        }

        $consigne = $struct['consigne'] ?? '';
        echo "   Consigne : " . substr($consigne, 0, 60) . (strlen($consigne) > 60 ? '...' : '') . "\n";

        if ($struct['structure_type'] === 'multi-parties' && !empty($struct['sub_questions'])) {
            echo "   🔢 Sous-questions : {$struct['nombre_parties']}\n";
            foreach (array_slice($struct['sub_questions'], 0, 3) as $sq) {
                echo "      {$sq['id']}) " . substr($sq['enonce'], 0, 50) . "...\n";
            }
            if ($struct['nombre_parties'] > 3) {
                echo "      ... et " . ($struct['nombre_parties'] - 3) . " autre(s)\n";
            }
        }

        // 3. DOUBLONS
        if (!empty($parsed['duplicates'])) {
            echo "\n⚠️  DOUBLONS DÉTECTÉS\n";

            if (!empty($parsed['duplicates']['exact_identifier'])) {
                echo "   🔴 Identifier identique :\n";
                foreach ($parsed['duplicates']['exact_identifier'] as $dup) {
                    echo "      → ID {$dup['Id']} : {$dup['Title']}\n";
                }
            }

            if (!empty($parsed['duplicates']['similar_content'])) {
                echo "   🟡 Contenu similaire :\n";
                foreach (array_slice($parsed['duplicates']['similar_content'], 0, 3) as $dup) {
                    echo "      → ID {$dup['Id']} ({$dup['similarity']}% similaire)\n";
                }
            }
        } else {
            echo "\n✅ Aucun doublon détecté\n";
        }

        // 4. VALIDATION
        echo "\n🔍 VALIDATION\n";
        if ($parsed['validation']['valid']) {
            echo "   ✅ Valide\n";
        } else {
            echo "   ❌ Erreurs :\n";
            foreach ($parsed['validation']['errors'] as $error) {
                echo "      - $error\n";
            }
        }

        if (!empty($parsed['validation']['warnings'])) {
            echo "   ⚠️  Avertissements :\n";
            foreach ($parsed['validation']['warnings'] as $warning) {
                echo "      - $warning\n";
            }
        }

        echo "\n\n";
    }

    echo "✅ Tests terminés !\n";
    echo "💡 Pour appliquer les modifications en BDD, utilise le script de migration.\n";

} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
