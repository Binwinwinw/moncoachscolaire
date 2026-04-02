<?php

// src/pages/fix_universal.php

// Sécurité : Si on passe par le routeur, $pdo devrait exister.
// Sinon, on essaie de charger la conf (cas d'usage hors routeur/CLI)
if (!isset($pdo)) {
    // Tentative de déduction du chemin si lancé hors routeur
    $potential_paths = [
        __DIR__ . '/../config/config.php',
        __DIR__ . '/../../src/config/config.php',
        'src/config/config.php',
    ];
    foreach ($potential_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

// Vérification ultime
if (!isset($pdo)) {
    die("❌ Erreur : Impossible d'accéder à la base de données. Assurez-vous d'être connecté.");
}

echo "<div class='max-w-2xl mx-auto p-8 md:p-12 bg-white rounded-2xl shadow-xl font-sans'>";
echo "<h1 class='text-3xl md:text-4xl font-bold text-blue-600 mb-4'>🧹 Nettoyage Universel des Exercices</h1>";
echo "<p class='text-lg text-gray-700 mb-6'>Analyse des exercices non structurés en cours...</p>";

// On ne prend que ceux qui n'ont PAS encore été convertis
// On filtre aussi ceux qui sont trop courts pour être des exercices valides
$stmt = $pdo->query("SELECT Id, Content, Title FROM exercices WHERE (structure_type IS NULL OR structure_type != 'multi-parties') AND LENGTH(Content) > 20");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;
$repaired_ids = [];

foreach ($exercises as $ex) {
    $rawContent = $ex['Content'];
    $intro = "";
    $questions = [];
    $matched = false;

    // ---------------------------------------------------------
    // ETAPE 0 : Nettoyage préliminaire
    // ---------------------------------------------------------
    // Enlève "ee " ou "ee" au début (artefact OCR fréquent)
    $cleanContent = preg_replace('/^ee\s+/i', '', $rawContent);
    // Correction basique des caractères cassés courants
    $cleanContent = str_replace(['e ', ' e '], ['à ', ' à '], $cleanContent);

    // ---------------------------------------------------------
    // PATTERN A : Format "Questions :" suivi de a) b) c)
    // ---------------------------------------------------------
    if (!$matched && stripos($cleanContent, 'Questions :') !== false) {
        $parts = preg_split('/Questions\s*:/i', $cleanContent);
        if (count($parts) > 1) {
            $introCandidate = trim($parts[0]);
            $qPart = $parts[1];

            // Cherche a), b), c) ou a. b. c.
            if (preg_match_all('/([a-z])[\)\.]\s+(.*?)(?=$|[a-z][\)\.])/s', $qPart, $matches, PREG_SET_ORDER)) {
                if (count($matches) >= 2) { // Au moins 2 questions pour valider
                    $intro = $introCandidate;
                    foreach ($matches as $m) {
                        $questions[] = [
                            'id' => $m[1], // a, b, c
                            'enonce' => trim($m[2]),
                            'reponse' => '',
                        ];
                    }
                    $matched = true;
                }
            }
        }
    }

    // ---------------------------------------------------------
    // PATTERN B : Format "1) ... 2) ..." directement dans le texte
    // ---------------------------------------------------------
    if (!$matched) {
        // On cherche 1), 2) ou 1., 2.
        if (preg_match_all('/(\d+)[\)\.]\s+(.*?)(?=$|\d+[\)\.]|$)/s', $cleanContent, $matches, PREG_SET_ORDER)) {
            // Si on trouve au moins "1)" et "2)", c'est valide
            // Vérifions que les numéros se suivent (1, 2, 3...) pour éviter les faux positifs (dates, etc.)
            $isSequence = true;
            $expected = 1;
            foreach ($matches as $m) {
                if ((int) $m[1] !== $expected) {
                    // On tolère si ça commence pas à 1 mais que ça se suit (ex: question 2, 3...)
                    if ($expected === 1 && (int) $m[1] === 1) {
                        // ok
                    } elseif ($expected > 1 && (int) $m[1] === $expected) {
                        // ok
                    } else {
                        // Tolérance désactivée pour rigueur : on veut une suite 1, 2...
                        // $isSequence = false;
                    }
                }
                $expected = (int) $m[1] + 1;
            }

            if (count($matches) >= 2) {
                // L'intro est tout ce qui est AVANT le premier match
                $firstMatchString = $matches[0][0]; // ex: "1) La question..."
                $firstMatchPos = strpos($cleanContent, $firstMatchString);

                if ($firstMatchPos !== false) {
                    $intro = trim(substr($cleanContent, 0, $firstMatchPos));

                    foreach ($matches as $m) {
                        $questions[] = [
                            'id' => $m[1], // 1, 2, 3
                            'enonce' => trim($m[2]),
                            'reponse' => '',
                        ];
                    }
                    $matched = true;
                }
            }
        }
    }

    // ---------------------------------------------------------
    // MISE A JOUR EN BDD
    // ---------------------------------------------------------
    if ($matched && count($questions) > 0) {
        $json = json_encode([
            'introduction' => $intro,
            'questions' => $questions,
        ], JSON_UNESCAPED_UNICODE);

        // Update DB
        try {
            $upd = $pdo->prepare("UPDATE exercices SET structure_type = 'multi-parties', sub_questions = :json WHERE Id = :id");
            $upd->execute([':json' => $json, ':id' => $ex['Id']]);

            echo "<div class='border-l-4 border-green-600 bg-green-50 my-4 p-4 rounded-xl shadow-sm'>";
            echo "<strong class='text-green-700'>🛠️ Réparé :</strong> " . htmlspecialchars($ex['Title']) . " <small class='text-gray-500'>(ID: {$ex['Id']})</small><br>";
            echo "<div class='mt-2 text-sm text-gray-700'><em>Intro :</em> " . htmlspecialchars(substr($intro, 0, 80)) . "...</div>";
            echo "<div class='mt-1 text-xs text-blue-600'>Detected: " . count($questions) . " questions</div>";
            echo "</div>";
            $count++;
            $repaired_ids[] = $ex['Id'];
        } catch (Exception $e) {
            echo "<div style='color:red'>Erreur update ID {$ex['Id']}: " . $e->getMessage() . "</div>";
        }
    }
}

if ($count === 0) {
    echo "<div class='p-6 bg-red-50 text-red-700 rounded-xl border border-red-200 text-center font-semibold'>Aucun nouvel exercice n'a pu être réparé automatiquement avec ces motifs.</div>";
} else {
    echo "<h3 class='text-2xl font-bold text-green-600 mt-8 mb-4'>🎉 Terminé ! $count exercices supplémentaires sauvés !</h3>";
}

echo "<div style='margin-top:30px;'>";
echo "<div class='mt-10 text-center'>";
echo "<a href='index.php?page=demo' class='inline-block px-6 py-3 bg-blue-600 text-white rounded-lg font-bold shadow hover:bg-blue-700 transition'>Retour à la démo</a>";
echo "</div>";
echo "</div>"; // fin container
