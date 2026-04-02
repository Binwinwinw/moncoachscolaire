<?php
// fix_questions.php
require_once 'src/config/config.php'; // Ajuste le chemin selon ta structure

echo "<h1>Correction des exercices format 'Question X :'</h1>";

// Récupérer les exercices contenant "**Question"
$stmt = $pdo->query("SELECT Id, Content, Title FROM exercises WHERE Content LIKE '%**Question%' AND (structure_type IS NULL OR structure_type != 'multi-parties')");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;

foreach ($exercises as $ex) {
    $content = $ex['Content'];
    $questions = [];
    $intro = "";

    // Regex pour capturer "**Question X :**" et le texte qui suit
    // On découpe le texte par "Question X"
    $parts = preg_split('/(\*\*Question\s+\d+\s*:\s*\*\*)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    // La partie 0 est l'introduction (avant la question 1)
    if (!empty(trim($parts[0]))) {
        $intro = trim($parts[0]);
    }

    // Parcourir les parties capturées
    for ($i = 1; $i < count($parts); $i += 2) {
        $label = str_replace(['**', ':'], '', $parts[$i]); // Nettoie "**Question 1 :**" -> "Question 1"
        $text = isset($parts[$i+1]) ? trim($parts[$i+1]) : '';

        if (!empty($text)) {
            $questions[] = [
                'id' => trim($label),
                'enonce' => $text,
                'reponse' => '' // On laisse vide pour l'instant
            ];
        }
    }

    // Si on a trouvé des questions, on met à jour la BDD
    if (count($questions) > 0) {
        $json = json_encode([
            'introduction' => $intro,
            'questions' => $questions
        ], JSON_UNESCAPED_UNICODE);

        // Update DB
        $upd = $pdo->prepare("UPDATE exercises SET structure_type = 'multi-parties', sub_questions = :json WHERE Id = :id");
        $upd->execute([':json' => $json, ':id' => $ex['Id']]);

        echo "<div style='border:1px solid green; margin:5px; padding:5px;'>";
        echo "✅ Exercice <strong>{$ex['Title']}</strong> (ID: {$ex['Id']}) converti avec " . count($questions) . " questions.<br>";
        echo "</div>";
        $count++;
    }
}

echo "<h3>Terminé ! $count exercices corrigés.</h3>";
echo "<a href='index.php?page=demo'>Retour à la démo</a>";
?>
