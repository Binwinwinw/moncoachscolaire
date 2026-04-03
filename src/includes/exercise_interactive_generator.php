<?php

/**
 * Générateur d'exercices interactifs amélioré
 * Crée des exercices vraiment interactifs pour toutes les matières
 */

/**
 * Génère des exercices interactifs de qualité à partir du contenu
 */
function generateQualityInteractiveExercise($exercise, $subject)
{
    $content = $exercise['Content'] ?? '';
    $title = $exercise['Title'] ?? '';
    $answer = $exercise['Answer'] ?? '';
    $id = $exercise['Id'] ?? 0;

    if (empty($content)) {
        return null;
    }

    $cleanContent = strip_tags($content);
    $cleanAnswer = strip_tags($answer);

    // Détecter le type d'exercice
    $exerciseType = detectQualityExerciseType($subject, $cleanContent, $title);

    // Générer les questions selon le type
    switch ($exerciseType) {
        case 'qcm':
            return generateQualityQCM($exercise, $cleanContent, $cleanAnswer, $title, $id);
        case 'math':
            return generateQualityMath($exercise, $cleanContent, $cleanAnswer, $id);
        case 'fill_blank':
            return generateQualityFillBlank($exercise, $cleanContent, $cleanAnswer, $id);
        case 'ordering':
            return generateQualityOrdering($exercise, $cleanContent, $cleanAnswer, $id);
        case 'matching':
            return generateQualityMatching($exercise, $cleanContent, $cleanAnswer, $id);
        default:
            return generateQualityQCM($exercise, $cleanContent, $cleanAnswer, $title, $id);
    }
}

/**
 * Détecte le type d'exercice avec plus de précision
 */
function detectQualityExerciseType($subject, $content, $title)
{
    $contentLower = mb_strtolower($content);
    $titleLower = mb_strtolower($title);

    // Mathématiques - exercices de calcul
    if ($subject === 'Mathématiques' || $subject === 'Maths') {
        if (preg_match('/\d+\s*[+\-×*÷\/]\s*\d+|calcul|fraction|équation|problème/i', $content)) {
            return 'math';
        }
        return 'qcm';
    }

    // Français
    if ($subject === 'Français') {
        // Conjugaison ou réécriture (champs à remplir)
        // Détecter les formats: ____, ..., (_), _+, "Accorde", "complète", etc.
        if (preg_match('/complét|complète|____|\(_\)|_+|récris|écris|transforme|conjug|accorde/i', $content)) {
            return 'fill_blank';
        }
        // Mise en ordre (chronologie, phrases)
        if (preg_match('/classe|ordre|chronolog|remets dans/i', $content)) {
            return 'ordering';
        }
        // Par défaut : QCM
        return 'qcm';
    }

    // Histoire-Géo - souvent QCM ou mise en ordre
    if (in_array($subject, ['Histoire', 'Géographie', 'Histoire-Géo'])) {
        if (preg_match('/classe|ordre|chronolog|remets|dates/i', $content)) {
            return 'ordering';
        }
        return 'qcm';
    }

    // Sciences - souvent QCM ou correspondance
    if (in_array($subject, ['SVT', 'Physique-Chimie', 'Sciences'])) {
        if (preg_match('/associe|relie|correspond/i', $content)) {
            return 'matching';
        }
        return 'qcm';
    }

    // Par défaut : QCM
    return 'qcm';
}

/**
 * Génère un QCM de qualité avec de vrais choix
 */
function generateQualityQCM($exercise, $content, $answer, $title, $id)
{
    // Extraire les questions du contenu
    $questionsData = extractQCMQuestions($content, $answer, $title);

    if (empty($questionsData)) {
        return null;
    }

    // Générer les choix pour chaque question
    $formattedQuestions = [];
    foreach ($questionsData as $index => $qData) {
        $questionText = $qData['question'] ?? '';
        $choiceInfo = generateQualityChoices($answer, $questionText, $index);

        if (!empty($choiceInfo['choices'])) {
            $formattedQuestions[] = [
                'question' => $questionText,
                'choices' => $choiceInfo['choices'],
                'correct' => $choiceInfo['correct'],
            ];
        }
    }

    if (empty($formattedQuestions)) {
        return null;
    }

    $questionsJson = json_encode($formattedQuestions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

    return <<<HTML
    <div class="qcm-exercise" data-questions='{$questionsJson}'>
        <div class="qcm-container"></div>
        <button class="btn-check-qcm">
            ✅ Vérifier mes réponses
        </button>
        <div class="qcm-feedback"></div>
    </div>
HTML;
}

/**
 * Extrait des questions QCM pertinentes depuis le contenu
 */
function extractQCMQuestions($content, $answer, $title)
{
    $questions = [];

    // Méthode 1 : Chercher des questions avec format Q1:, Q2:, Question 1 :, Question 1:, etc.
    if (preg_match_all('/(?:Q\d+|Question\s+\d+)[:\s\.]\s*(.+?)(?:\?|$|\n)/iu', $content, $matches)) {
        foreach ($matches[1] as $index => $questionText) {
            $questionText = trim($questionText);
            if (!empty($questionText) && mb_strlen($questionText) > 3) {
                // Ajouter un ? si ce n'est pas déjà là
                if (mb_substr($questionText, -1) !== '?') {
                    $questionText .= ' ?';
                }
                $questions[] = [
                    'question' => $questionText,
                ];
            }
        }
    }

    // Méthode 1b : Chercher des questions explicites (avec ?) - format général
    if (empty($questions) && preg_match_all('/(.+?\?)/u', $content, $matches)) {
        foreach ($matches[1] as $index => $questionText) {
            $questionText = trim($questionText);
            // Ignorer les lignes qui commencent par A), B), C), D) (choix)
            if (!preg_match('/^[A-D]\)\s/', $questionText)
                && !preg_match('/^Q\d+/i', $questionText)
                && mb_strlen($questionText) > 10) {
                $questions[] = [
                    'question' => $questionText,
                ];
            }
        }
    }

    // Méthode 2 : Si pas de questions trouvées, créer depuis le titre
    if (empty($questions) && !empty($title)) {
        $questionText = $title;
        if (substr($questionText, -1) !== '?') {
            $questionText .= ' ?';
        }

        $questions[] = [
            'question' => $questionText,
        ];
    }

    // Méthode 3 : Créer depuis la première phrase du contenu
    if (empty($questions) && !empty($content)) {
        $sentences = preg_split('/[.!?]+/', $content);
        $firstSentence = trim($sentences[0] ?? '');

        if (!empty($firstSentence) && mb_strlen($firstSentence) > 10) {
            $questionText = mb_substr($firstSentence, 0, 100);
            if (mb_strlen($firstSentence) > 100) {
                $questionText .= '...';
            }
            $questionText .= ' ?';

            $questions[] = [
                'question' => $questionText,
            ];
        }
    }

    return $questions;
}

/**
 * Génère des choix de qualité pour un QCM
 */
function generateQualityChoices($answer, $question, $index)
{
    $choices = [];

    // Extraire la bonne réponse depuis la correction
    $correctAnswer = extractCorrectAnswer($answer, $question, $index);

    // Générer des distracteurs pertinents
    $distractors = generateDistractors($correctAnswer, $question);

    // Mélanger les choix (la bonne réponse en premier sera mélangée par le JS)
    $allChoices = array_merge([$correctAnswer], $distractors);
    shuffle($allChoices);

    // Formater au format attendu
    foreach ($allChoices as $idx => $choice) {
        $value = chr(97 + $idx); // a, b, c, d
        $choices[] = [
            'value' => $value,
            'label' => $value . ') ' . $choice,
        ];

        // Garder en mémoire quelle est la bonne réponse
        if ($choice === $correctAnswer) {
            $choices[count($choices) - 1]['isCorrect'] = true;
        }
    }

    // Trouver la lettre de la bonne réponse et nettoyer
    $correctValue = 'a';
    $cleanedChoices = [];

    foreach ($choices as $choice) {
        $cleanChoice = $choice;
        if (isset($choice['isCorrect']) && $choice['isCorrect']) {
            $correctValue = $choice['value'];
            unset($cleanChoice['isCorrect']);
        }
        $cleanedChoices[] = $cleanChoice;
    }

    return ['choices' => $cleanedChoices, 'correct' => $correctValue];
}

/**
 * Extrait la bonne réponse depuis la correction
 */
function extractCorrectAnswer($answer, $question, $index)
{
    // Essayer d'extraire depuis la question elle-même d'abord
    if (preg_match('/est\s+(.+?)[\?\.]/i', $question, $matches)) {
        $extracted = trim($matches[1]);
        if (!empty($extracted) && mb_strlen($extracted) > 2) {
            return $extracted;
        }
    }

    // Chercher une année dans la question (pour les questions d'histoire)
    if (preg_match('/\b(1[89]\d{2}|20\d{2})\b/', $question, $yearMatch)) {
        return $yearMatch[1];
    }

    if (empty($answer)) {
        // Essayer d'extraire depuis le contexte de la question
        if (preg_match('/\b(1[89]\d{2}|20\d{2})\b/', $question, $yearMatch)) {
            return $yearMatch[1];
        }
        return 'Réponse correcte';
    }

    // Essayer d'extraire la réponse depuis la correction
    $lines = explode("\n", $answer);

    // Chercher une année dans la réponse
    foreach ($lines as $line) {
        if (preg_match('/\b(1[89]\d{2}|20\d{2})\b/', $line, $yearMatch)) {
            return $yearMatch[1];
        }
    }

    // Chercher Q1:, Q2:, etc. dans la réponse
    foreach ($lines as $line) {
        if (preg_match('/Q\d+[:\.]\s*(.+?)(?:\n|$)/i', $line, $qMatch)) {
            $answerText = trim($qMatch[1]);
            // Enlever les balises comme "B) " si présentes
            $answerText = preg_replace('/^[A-D]\)\s*/', '', $answerText);
            if (!empty($answerText) && mb_strlen($answerText) > 1) {
                return $answerText;
            }
        }
    }

    // Chercher des mots-clés dans la question pour trouver la réponse
    if (preg_match('/qui|quel|quelle|quels|quelles|combien|où|quand|comment|pourquoi/i', $question)) {
        // Question ouverte, extraire la première phrase de la réponse
        $firstLine = trim($lines[$index] ?? $lines[0] ?? '');
        if (!empty($firstLine)) {
            $firstLine = preg_replace('/^[•\-\d+\.)]+\s*/', '', $firstLine); // Enlever les puces
            $firstLine = preg_replace('/^Q\d+[:\.]\s*/i', '', $firstLine); // Enlever Q1:, Q2:, etc.
            $firstLine = preg_replace('/^[A-D]\)\s*/', '', $firstLine); // Enlever A), B), etc.
            $firstLine = mb_substr($firstLine, 0, 60);
            if (!empty($firstLine) && mb_strlen($firstLine) > 1) {
                return $firstLine;
            }
        }
    }

    // Sinon, prendre la première ligne non vide
    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line) && mb_strlen($line) > 2) {
            $line = preg_replace('/^[•\-\d+\.)]+\s*/', '', $line);
            $line = preg_replace('/^Q\d+[:\.]\s*/i', '', $line); // Enlever Q1:, Q2:, etc.
            $line = preg_replace('/^[A-D]\)\s*/', '', $line); // Enlever A), B), etc.
            $line = mb_substr($line, 0, 60);
            if (!empty($line)) {
                return $line;
            }
        }
    }

    // Dernier recours : essayer d'extraire depuis la question
    if (preg_match('/est\s+(.+?)[\?\.]/i', $question, $matches)) {
        return trim($matches[1]);
    }

    return 'Réponse correcte';
}

/**
 * Génère des distracteurs pertinents basés sur la matière, le niveau et la question
 */
function generateDistractors($correctAnswer, $question)
{
    $distractors = [];

    // Déterminer le type de question et le contexte
    $questionLower = mb_strtolower($question);

    // === QUESTIONS DE DATES/ANNÉES (Histoire, etc.) ===
    if (preg_match('/quand|date|année|an\b/i', $question)) {
        if (preg_match('/\b(1[0-9]{3}|20[0-9]{2})\b/', $correctAnswer, $yearMatch)) {
            $year = (int) $yearMatch[0];
            $distractors = [
                (string) ($year - 10),
                (string) ($year + 5),
                (string) ($year - 25),
            ];
        } elseif (preg_match('/siècle/i', $correctAnswer)) {
            $distractors = ['Au siècle précédent', 'Au siècle suivant', 'Deux siècles plus tôt'];
        } else {
            $distractors = ['À une autre époque', 'Plus tard', 'Bien avant'];
        }
    }

    // === QUESTIONS DE PERSONNES/QUI (Histoire, Littérature) ===
    elseif (preg_match('/\b(qui|auteur|écrivain|roi|empereur|président)\b/i', $question)) {
        // Identifier le contexte
        if (preg_match('/Charlemagne|Louis XIV|Napoléon|Charles de Gaulle/i', $correctAnswer)) {
            $distractors = ['François Ier', 'Louis XVI', 'Henri IV'];
        } elseif (preg_match('/Hugo|Molière|Voltaire|Zola|Balzac/i', $correctAnswer)) {
            $distractors = ['Gustave Flaubert', 'Guy de Maupassant', 'Alexandre Dumas'];
        } else {
            $distractors = ['Une autre personnalité', 'Un contemporain', 'Son prédécesseur'];
        }
    }

    // === QUESTIONS DE LIEU/OÙ (Géographie, Histoire) ===
    elseif (preg_match('/\b(où|lieu|ville|capitale|pays|région)\b/i', $question)) {
        if (preg_match('/Paris|France/i', $correctAnswer)) {
            $distractors = ['Londres', 'Berlin', 'Rome'];
        } elseif (preg_match('/Londres|Royaume-Uni|Angleterre/i', $correctAnswer)) {
            $distractors = ['Paris', 'Berlin', 'Madrid'];
        } elseif (preg_match('/Europe/i', $correctAnswer)) {
            $distractors = ['Asie', 'Afrique', 'Amérique'];
        } else {
            $distractors = ['Dans une autre région', 'Dans un autre pays', 'À un autre endroit'];
        }
    }

    // === QUESTIONS MATHÉMATIQUES (Nombres, Calculs) ===
    elseif (preg_match('/\b(calcul|résultat|somme|produit|quotient|différence)\b/i', $question)
            || is_numeric(trim($correctAnswer))) {
        if (is_numeric(trim($correctAnswer))) {
            $num = (float) trim($correctAnswer);
            // Générer des nombres proches mais faux
            $distractors = [
                (string) ($num + 1),
                (string) ($num - 1),
                (string) ($num * 2),
            ];
        } else {
            $distractors = ['Un autre résultat', 'Le double', 'La moitié'];
        }
    }

    // === QUESTIONS DE GRAMMAIRE (Français) ===
    elseif (preg_match('/\b(verbe|nom|adjectif|déterminant|pronom|classe de mots)\b/i', $question)) {
        if (preg_match('/verbe/i', $question)) {
            $distractors = ['Nom', 'Adjectif', 'Déterminant'];
        } elseif (preg_match('/nom/i', $question)) {
            $distractors = ['Verbe', 'Adjectif', 'Pronom'];
        } elseif (preg_match('/adjectif/i', $question)) {
            $distractors = ['Nom', 'Verbe', 'Adverbe'];
        } else {
            $distractors = ['Une autre classe', 'Un autre type', 'Une autre catégorie'];
        }
    }

    // === QUESTIONS DE CONJUGAISON (Français) ===
    elseif (preg_match('/\b(temps|présent|imparfait|futur|passé|conjugaison)\b/i', $question)) {
        if (preg_match('/présent/i', $correctAnswer)) {
            $distractors = ['Imparfait', 'Futur simple', 'Passé composé'];
        } elseif (preg_match('/imparfait/i', $correctAnswer)) {
            $distractors = ['Présent', 'Passé simple', 'Plus-que-parfait'];
        } elseif (preg_match('/futur/i', $correctAnswer)) {
            $distractors = ['Conditionnel', 'Présent', 'Imparfait'];
        } else {
            $distractors = ['Un autre temps', 'Un autre mode', 'Une autre forme'];
        }
    }

    // === QUESTIONS SCIENTIFIQUES (SVT, Physique-Chimie) ===
    elseif (preg_match('/\b(cellule|organe|système|atome|molécule|énergie)\b/i', $question)) {
        if (preg_match('/cellule/i', $correctAnswer)) {
            $distractors = ['Tissu', 'Organe', 'Appareil'];
        } elseif (preg_match('/photosynthèse/i', $correctAnswer)) {
            $distractors = ['Respiration', 'Transpiration', 'Fermentation'];
        } elseif (preg_match('/oxygène|O2/i', $correctAnswer)) {
            $distractors = ['Azote (N₂)', 'Dioxyde de carbone (CO₂)', 'Hydrogène (H₂)'];
        } else {
            $distractors = ['Une autre réponse scientifique', 'Un processus différent', 'Un autre élément'];
        }
    }

    // === QUESTIONS DE DÉFINITION (toutes matières) ===
    elseif (preg_match('/\b(définition|qu.?est-ce|signifie|veut dire)\b/i', $question)) {
        $distractors = [
            'Une définition différente',
            'Un sens proche mais incorrect',
            'Une autre interprétation',
        ];
    }

    // === QUESTIONS SUR LES RAISONS/CAUSES ===
    elseif (preg_match('/\b(pourquoi|cause|raison|expliqu)\b/i', $question)) {
        $distractors = [
            'Pour une raison économique',
            'Pour des motifs politiques',
            'En raison d\'autres facteurs',
        ];
    }

    // === QUESTIONS SUR LA MANIÈRE/COMMENT ===
    elseif (preg_match('/\b(comment|manière|façon|méthode)\b/i', $question)) {
        $distractors = [
            'Par une autre méthode',
            'De façon différente',
            'Selon un autre procédé',
        ];
    }

    // === PAR DÉFAUT : Distracteurs génériques mais variés ===
    else {
        $distractors = [
            'Une autre réponse possible',
            'Réponse alternative',
            'Aucune des propositions ci-dessus',
        ];
    }

    // S'assurer qu'on a exactement 3 distracteurs uniques
    $distractors = array_unique($distractors);
    while (count($distractors) < 3) {
        $distractors[] = 'Autre proposition';
    }

    return array_slice(array_values($distractors), 0, 3);
}

/**
 * Génère un exercice mathématique interactif
 */
function generateQualityMath($exercise, $content, $answer, $id)
{
    // Extraire les calculs
    $calculations = extractMathCalculations($content);

    if (empty($calculations)) {
        return null;
    }

    $questionsJson = json_encode($calculations, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

    return <<<HTML
    <div class="math-exercise" data-questions='{$questionsJson}'>
        <div class="math-container"></div>
        <button class="btn-check-math">
            ✅ Vérifier mes réponses
        </button>
        <div class="math-feedback"></div>
    </div>
HTML;
}

/**
 * Extrait les calculs mathématiques du contenu
 */
function extractMathCalculations($content, $answer = '')
{
    $calculations = [];
    $seenCalculations = []; // Pour éviter les doublons

    // Chercher des calculs du type: X + Y = ?, X - Y = ?, Q1: Combien font X + Y ?, etc.
    // Pattern amélioré pour détecter aussi "Combien font X + Y ?"
    $patterns = [
        // Format: Q1: Combien font 15 + 27 ? ou Q1: Combien font 12 + 34 - 8 ?
        '/(?:Q\d+[:\.]|Question\s+\d+[:\.])?\s*(?:Combien\s+font|Calcule|Calculez)?\s*(\d+)\s*([+\-×*÷\/])\s*(\d+)(?:\s*([+\-])\s*(\d+))?\s*[=:]?\s*\?/iu',
        // Format: 15 + 27 = ? (mais seulement si pas déjà capturé par le premier pattern)
        '/(\d+)\s*([+\-×*÷\/])\s*(\d+)(?:\s*([+\-])\s*(\d+))?\s*=\s*\?/i',
    ];

    $allMatches = [];
    foreach ($patterns as $patternIndex => $pattern) {
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // Créer une clé unique pour ce calcul pour éviter les doublons
                $num1 = (int) $match[1];
                $op = $match[2];
                $num2 = (int) $match[3];
                // Pour les calculs à 3 nombres: $match[4] = op2, $match[5] = num3
                $num3 = isset($match[5]) ? (int) $match[5] : null;
                $op2 = isset($match[5]) && isset($match[4]) ? $match[4] : null;

                // Clé unique pour identifier les doublons
                if ($num3 !== null && $op2 !== null) {
                    $uniqueKey = "$num1$op$num2$op2$num3";
                } else {
                    $uniqueKey = "$num1$op$num2";
                }

                // Ignorer si déjà vu (évite les doublons entre les deux patterns)
                if (!isset($seenCalculations[$uniqueKey])) {
                    $seenCalculations[$uniqueKey] = true;
                    $allMatches[] = $match;
                }
            }
        }
    }

    if (!empty($allMatches)) {
        foreach ($allMatches as $index => $match) {
            $num1 = (int) $match[1];
            $op = $match[2];
            $num2 = (int) $match[3];

            // Gérer les calculs à trois nombres (ex: 12 + 34 - 8)
            // Pour les calculs à 3 nombres: $match[4] = op2, $match[5] = num3
            $num3 = isset($match[5]) ? (int) $match[5] : null;
            $op2 = isset($match[5]) && isset($match[4]) ? $match[4] : null;

            if ($num3 !== null && $op2) {
                // Calcul en deux étapes
                $result = calculateMathResult($num1, $op, $num2);
                $result = calculateMathResult($result, $op2, $num3);
                $questionText = "$num1 $op $num2 $op2 $num3 = ?";
            } else {
                $result = calculateMathResult($num1, $op, $num2);
                $questionText = "$num1 $op $num2 = ?";
            }

            // Générer des distracteurs variés
            $wrong1 = $result + rand(1, 10);
            $wrong2 = max(0, $result - rand(1, 10));
            $wrong3 = $result + rand(11, 20);

            // Mélanger l'ordre
            $allAnswers = [(string) $result, (string) $wrong1, (string) $wrong2, (string) $wrong3];
            shuffle($allAnswers);

            $calculations[] = [
                'question' => $questionText,
                'answer' => (string) $result,
                'choices' => $allAnswers,
            ];
        }
    }

    return $calculations;
}

/**
 * Calcule le résultat d'une opération
 */
function calculateMathResult($num1, $op, $num2)
{
    switch ($op) {
        case '+': return $num1 + $num2;
        case '-': return $num1 - $num2;
        case '*':
        case '×': return $num1 * $num2;
        case '/':
        case '÷': return $num2 != 0 ? round($num1 / $num2, 2) : 0;
        default: return 0;
    }
}

/**
 * Génère un exercice à compléter (fill in the blank)
 */
function generateQualityFillBlank($exercise, $content, $answer, $id)
{
    $questions = [];

    // Méthode 1 : Détecter le format avec adjectifs entre parenthèses
    // Exemple: "a) Les fleurs (blanc) et (parfumé) embaument..." -> plusieurs champs à remplir
    // Pattern: détecter les lignes commençant par a), b), etc. avec des mots entre parenthèses
    if (preg_match_all('/(?:^|\n)([a-h])\)\s*([^\n]+)/u', $content, $lineMatches, PREG_SET_ORDER)) {
        $answers = extractFillBlankAnswers($answer);

        foreach ($lineMatches as $lineIndex => $lineMatch) {
            $letter = $lineMatch[1];
            $fullSentence = trim($lineMatch[2]);

            // Vérifier si la phrase contient des mots entre parenthèses (adjectifs à accorder)
            if (preg_match_all('/\(([^)]+)\)/u', $fullSentence, $adjectiveMatches)) {
                $adjectives = $adjectiveMatches[1];

                // Extraire la réponse depuis la correction
                // Format: "a) Les fleurs blanches et parfumées"
                $answerLine = '';

                // Méthode 1 : Chercher par lettre (a), b), c), etc.)
                foreach ($answers as $ans) {
                    // Chercher "a) " ou "a)" au début
                    if (preg_match('/^' . preg_quote($letter, '/') . '\)\s*/i', $ans)) {
                        $answerLine = $ans;
                        break;
                    }
                    // Chercher la lettre suivie d'un point ou d'un espace
                    if (preg_match('/^' . preg_quote($letter, '/') . '[\.\)]\s*/i', $ans)) {
                        $answerLine = $ans;
                        break;
                    }
                }

                // Méthode 2 : Si pas trouvé avec la lettre, prendre par index
                if (empty($answerLine) && isset($answers[$lineIndex])) {
                    $answerLine = $answers[$lineIndex];
                }

                // Méthode 3 : Chercher dans toutes les réponses une phrase qui contient des mots similaires
                if (empty($answerLine)) {
                    // Extraire quelques mots clés de la phrase originale
                    $sentenceWords = preg_split('/\s+/', $fullSentence);
                    $keyWords = array_filter($sentenceWords, function ($word) {
                        $clean = preg_replace('/[.,;:!?()]/', '', $word);
                        return mb_strlen($clean) > 3 && !in_array(strtolower($clean), ['les', 'une', 'des', 'dans', 'pour', 'avec', 'sont', 'est', 'ont', 'a']);
                    });
                    $keyWords = array_slice($keyWords, 0, 3); // Prendre les 3 premiers mots significatifs

                    foreach ($answers as $ans) {
                        $matchCount = 0;
                        foreach ($keyWords as $keyWord) {
                            $cleanKey = preg_replace('/[.,;:!?()]/', '', $keyWord);
                            if (stripos($ans, $cleanKey) !== false) {
                                $matchCount++;
                            }
                        }
                        // Si au moins 2 mots correspondent, c'est probablement la bonne réponse
                        if ($matchCount >= 2) {
                            $answerLine = $ans;
                            break;
                        }
                    }
                }

                // Extraire les adjectifs accordés depuis la réponse
                $correctAnswers = [];

                // Nettoyer la ligne de réponse (enlever a), b), etc.)
                $cleanAnswerLine = preg_replace('/^[a-h]\)\s*/i', '', $answerLine);
                $cleanAnswerLine = trim($cleanAnswerLine);

                // Extraire tous les mots de la réponse
                $answerWords = preg_split('/\s+/', $cleanAnswerLine);
                $answerWords = array_map(function ($word) {
                    return preg_replace('/[.,;:!?]/', '', $word);
                }, $answerWords);

                foreach ($adjectives as $adjIndex => $baseAdj) {
                    $found = false;

                    // Chercher le mot accordé dans la réponse (blanc -> blanches, parfumé -> parfumées, passionnant -> passionnante)
                    // Pattern flexible pour gérer les accords (e, es, s, euse, euses, etc.)
                    $pattern = '/' . preg_quote($baseAdj, '/') . '[a-zéèêës]*/iu';

                    // Chercher dans tous les mots de la réponse
                    foreach ($answerWords as $word) {
                        if (preg_match($pattern, $word, $matched)) {
                            $correctAnswers[] = $matched[0];
                            $found = true;
                            break;
                        }
                    }

                    // Si pas trouvé avec le pattern, chercher manuellement
                    if (!$found) {
                        foreach ($answerWords as $word) {
                            $cleanWord = strtolower($word);
                            $baseAdjLower = strtolower($baseAdj);

                            // Vérifier si le mot commence par la base de l'adjectif
                            if (stripos($cleanWord, $baseAdjLower) === 0 && mb_strlen($cleanWord) >= mb_strlen($baseAdjLower)) {
                                $correctAnswers[] = $word; // Garder la casse originale
                                $found = true;
                                break;
                            }
                        }
                    }

                    // Si toujours pas trouvé, utiliser la base (sera corrigé par l'utilisateur)
                    if (!$found) {
                        $correctAnswers[] = $baseAdj;
                    }
                }

                // Construire la phrase avec placeholders pour chaque adjectif
                $displaySentence = $fullSentence;
                $placeholderIndex = 0;
                foreach ($adjectives as $adj) {
                    $displaySentence = preg_replace('/\(' . preg_quote($adj, '/') . '\)/', '(_' . $placeholderIndex . '_)', $displaySentence, 1);
                    $placeholderIndex++;
                }

                // Créer une question avec tous les champs
                // S'assurer qu'on a au moins une réponse
                if (empty($correctAnswers) && !empty($adjectives)) {
                    // Si pas de réponses trouvées, utiliser les adjectifs de base
                    $correctAnswers = $adjectives;
                }

                $questions[] = [
                    'sentence' => $displaySentence,
                    'question' => $fullSentence,
                    'answer' => !empty($correctAnswers) ? $correctAnswers[0] : '', // Pour compatibilité
                    'answers' => $correctAnswers, // Tableau de réponses pour plusieurs champs
                    'adjectives' => $adjectives,
                    'letter' => $letter,
                ];
            }
        }
    }

    // Méthode 1b : Détecter le format avec (_) et verbe entre parenthèses
    // Exemple: "Les élèves (_) (arriver)" -> sentence: "Les élèves", verb: "arriver"
    if (empty($questions) && preg_match_all('/([^(_)]+)\(_\)\s*\(([^)]+)\)/u', $content, $matches, PREG_SET_ORDER)) {
        $answers = extractFillBlankAnswers($answer);

        foreach ($matches as $index => $match) {
            $sentence = trim($match[1]);
            $verb = trim($match[2]);

            // Construire la phrase complète pour affichage
            $fullSentence = $sentence . ' (_) (' . $verb . ')';

            // Extraire la réponse correcte
            $correctAnswer = $answers[$index] ?? '';

            // Si pas de réponse dans la correction, utiliser le verbe comme indice
            if (empty($correctAnswer)) {
                $correctAnswer = $verb; // Le JavaScript devra gérer la conjugaison
            }

            $questions[] = [
                'sentence' => $fullSentence,
                'question' => $sentence . ' ?',
                'answer' => $correctAnswer,
                'verb' => $verb,
            ];
        }
    }

    // Méthode 2 : Détecter les formats classiques (____, ..., _+)
    if (empty($questions) && preg_match_all('/____|\.\.\.|_+/', $content, $matches)) {
        $sentences = preg_split('/____|\.\.\.|_+/', $content);
        $answers = extractFillBlankAnswers($answer);

        foreach ($sentences as $index => $sentence) {
            $sentence = trim($sentence);
            if (!empty($sentence) && $index < count($answers)) {
                $questions[] = [
                    'sentence' => $sentence,
                    'question' => $sentence . ' ?',
                    'answer' => $answers[$index] ?? '',
                ];
            }
        }
    }

    // Méthode 3 : Détecter les phrases avec lettres (a), b), c), etc.) et (_)
    if (empty($questions) && preg_match_all('/(?:^|\n)[a-h]\)\s*([^(_)]+)\(_\)\s*\(([^)]+)\)/u', $content, $matches, PREG_SET_ORDER)) {
        $answers = extractFillBlankAnswers($answer);

        foreach ($matches as $index => $match) {
            $sentence = trim($match[1]);
            $verb = trim($match[2]);
            $fullSentence = $sentence . ' (_) (' . $verb . ')';
            $correctAnswer = $answers[$index] ?? $verb;

            $questions[] = [
                'sentence' => $fullSentence,
                'question' => $sentence . ' ?',
                'answer' => $correctAnswer,
                'verb' => $verb,
            ];
        }
    }

    if (!empty($questions)) {
        $questionsJson = json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS);

        return <<<HTML
        <div class="conjugation-exercise" data-questions='{$questionsJson}' data-exercise-id="{$id}">
            <div class="conjugation-container"></div>
            <button class="btn-check-conjugation">
                ✅ Vérifier mes réponses
            </button>
            <div class="conjugation-feedback"></div>
        </div>
HTML;
    }

    return null;
}

/**
 * Extrait les réponses pour un exercice à compléter
 */
function extractFillBlankAnswers($answer)
{
    if (empty($answer)) {
        return [];
    }

    $lines = explode("\n", $answer);
    $answers = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (!empty($line)) {
            // Enlever les numéros: 1., 2., Q1:, a), b), etc.
            $line = preg_replace('/^[•\-\d+\.)]+\s*/', '', $line);
            $line = preg_replace('/^Q\d+[:\.]\s*/i', '', $line);
            $line = preg_replace('/^[a-h]\)\s*/i', '', $line); // Enlever a), b), c), etc.

            // Si la ligne contient "arrivent", "partons", etc., extraire juste le verbe conjugué
            // Format possible: "a) arrivent" ou "arrivent" ou "Les élèves arrivent"
            if (preg_match('/\b(arrivent|partons|est|sont|ont|veulent|visite|visiter|mangent|manger|etc\.?)\b/i', $line, $verbMatch)) {
                $line = $verbMatch[1];
            } else {
                // Extraire le verbe conjugué (dernier mot significatif)
                $words = preg_split('/\s+/', $line);
                if (count($words) > 0) {
                    $lastWord = end($words);
                    // Si c'est un verbe conjugué (pas un article, préposition, etc.)
                    if (mb_strlen($lastWord) > 2 && !in_array(strtolower($lastWord), ['le', 'la', 'les', 'un', 'une', 'de', 'du', 'des', 'à', 'au', 'aux'])) {
                        $line = $lastWord;
                    }
                }
            }

            $line = trim($line);
            if (!empty($line) && mb_strlen($line) > 1) {
                $answers[] = $line;
            }
        }
    }

    return $answers;
}

/**
 * Génère un exercice de mise en ordre
 */
function generateQualityOrdering($exercise, $content, $answer, $id)
{
    // Extraire les éléments à ordonner
    $items = extractOrderingItems($content);

    if (count($items) < 2) {
        return null;
    }

    $itemsJson = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT);

    return <<<HTML
    <div class="chronology-exercise" data-events='{$itemsJson}'>
        <div class="chronology-container"></div>
        <button class="btn-check-chronology">
            ✅ Vérifier l'ordre
        </button>
        <div class="chronology-feedback"></div>
    </div>
HTML;
}

/**
 * Extrait les éléments à ordonner
 */
function extractOrderingItems($content)
{
    $items = [];

    // Chercher des listes numérotées ou à puces
    if (preg_match_all('/(?:^|\n)[•\-\d+\.)]+\s*([^\n]+)/u', $content, $matches)) {
        foreach ($matches[1] as $item) {
            $item = trim($item);
            if (!empty($item)) {
                $items[] = ['text' => $item, 'order' => count($items)];
            }
        }
    }

    return $items;
}

/**
 * Génère un exercice de correspondance
 */
function generateQualityMatching($exercise, $content, $answer, $id)
{
    // Pour l'instant, retourner null (à implémenter plus tard)
    return null;
}

