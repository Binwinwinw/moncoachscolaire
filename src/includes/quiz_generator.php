<?php

/**
 * Générateur de quiz basé sur les exercices et cours
 */

/**
 * Calcule le PGCD (Plus Grand Commun Diviseur) pour les fractions
 */
function gcd($a, $b)
{
    $a = abs($a);
    $b = abs($b);
    while ($b != 0) {
        $temp = $b;
        $b = $a % $b;
        $a = $temp;
    }
    return $a;
}

/**
 * Extrait une question de quiz à partir du contenu d'un exercice
 * Cette fonction analyse le contenu de l'exercice pour créer une question pertinente
 */
function extractQuizQuestionFromExercise($exercise)
{
    $title = $exercise['Title'] ?? '';
    $content = $exercise['Content'] ?? '';
    $answer = $exercise['Answer'] ?? '';
    $subject = $exercise['Subject'] ?? '';

    // Si l'exercice n'a pas de contenu, ne pas créer de question
    if (empty($content) && empty($title)) {
        return null;
    }

    // Extraire le texte principal (sans balises HTML si présentes)
    $cleanContent = strip_tags($content);
    $cleanAnswer = strip_tags($answer);

    // Générer une question basée sur le titre et le contenu
    $question = '';
    $choices = [];
    $correct = 'A';
    $explanation = '';

    // Analyser le type d'exercice selon la matière
    if ($subject === 'Mathématiques') {
        // PRIORITÉ 1 : Extraire des fractions du contenu
        if (preg_match('/(\d+)\/(\d+)\s*[+\-]\s*(\d+)\/(\d+)/', $cleanContent, $fracMatches)) {
            $num1 = (int) $fracMatches[1];
            $den1 = (int) $fracMatches[2];
            $num2 = (int) $fracMatches[3];
            $den2 = (int) $fracMatches[4];
            $operator = preg_match('/\+/', $cleanContent) ? '+' : '-';

            // Calculer la réponse
            if ($operator === '+') {
                // Addition de fractions : trouver PPCM
                $ppcm = ($den1 * $den2) / gcd($den1, $den2);
                $newNum1 = $num1 * ($ppcm / $den1);
                $newNum2 = $num2 * ($ppcm / $den2);
                $resultNum = $newNum1 + $newNum2;
                $resultDen = $ppcm;
                // Simplifier
                $gcd = gcd($resultNum, $resultDen);
                $resultNum = $resultNum / $gcd;
                $resultDen = $resultDen / $gcd;
                $correctAnswer = "$resultNum/$resultDen";
            } else {
                // Soustraction
                $ppcm = ($den1 * $den2) / gcd($den1, $den2);
                $newNum1 = $num1 * ($ppcm / $den1);
                $newNum2 = $num2 * ($ppcm / $den2);
                $resultNum = $newNum1 - $newNum2;
                $resultDen = $ppcm;
                $gcd = gcd(abs($resultNum), $resultDen);
                $resultNum = $resultNum / $gcd;
                $resultDen = $resultDen / $gcd;
                $correctAnswer = "$resultNum/$resultDen";
            }

            $question = "Quel est le résultat de $num1/$den1 $operator $num2/$den2 ?";
            $choices = [
                'A' => $correctAnswer,
                'B' => ($num1 + $num2) . '/' . ($den1 + $den2),
                'C' => ($num1 - $num2) . '/' . ($den1 - $den2),
                'D' => ($num1 * $num2) . '/' . ($den1 * $den2),
            ];
            $correct = 'A';
            $explanation = "Pour additionner/soustraire des fractions, il faut les mettre au même dénominateur. Ici, le résultat est $correctAnswer.";
        }
        // PRIORITÉ 2 : Extraire des calculs simples avec opérations
        elseif (preg_match('/(\d+)\s*[+\-×*÷]\s*(\d+)/', $cleanContent, $matches)) {
            $num1 = (int) $matches[1];
            $num2 = (int) $matches[2];
            $operator = preg_match('/\+/', $cleanContent) ? '+'
                       : (preg_match('/\-/', $cleanContent) ? '-'
                       : (preg_match('/[×*]/', $cleanContent) ? '×' : '÷'));

            $question = "Quel est le résultat de $num1 $operator $num2 ?";

            // Calculer la bonne réponse
            $correctAnswer = 0;
            if ($operator === '+') {
                $correctAnswer = $num1 + $num2;
            } elseif ($operator === '-') {
                $correctAnswer = $num1 - $num2;
            } elseif ($operator === '×') {
                $correctAnswer = $num1 * $num2;
            } else {
                $correctAnswer = $num2 != 0 ? round($num1 / $num2, 2) : 0;
            }

            // Générer des distracteurs réalistes selon le type d'opération
            if ($operator === '+') {
                $distractors = [
                    (string) ($correctAnswer + 1),
                    (string) ($correctAnswer - 1),
                    (string) ($num1 + $num2 + 10),
                ];
            } elseif ($operator === '-') {
                $distractors = [
                    (string) ($correctAnswer + 1),
                    (string) ($correctAnswer - 1),
                    (string) ($num1 + $num2),
                ];
            } elseif ($operator === '×') {
                $distractors = [
                    (string) ($correctAnswer + $num1),
                    (string) ($correctAnswer + $num2),
                    (string) ($num1 + $num2),
                ];
            } else {
                $distractors = [
                    (string) ($correctAnswer + 0.5),
                    (string) ($correctAnswer - 0.5),
                    (string) ($num1 / ($num2 + 1)),
                ];
            }
            shuffle($distractors);

            $choices = [
                'A' => (string) $correctAnswer,
                'B' => $distractors[0],
                'C' => $distractors[1],
                'D' => $distractors[2],
            ];
            $correct = 'A';
            $explanation = "Le résultat de $num1 $operator $num2 est $correctAnswer.";
        }
        // PRIORITÉ 3 : Extraire des concepts géométriques (périmètre, aire)
        elseif (preg_match('/(périmètre|perimetre|aire)/i', $title . ' ' . $cleanContent, $geoMatch)) {
            $concept = strtolower($geoMatch[1]);

            if (stripos($concept, 'périmètre') !== false || stripos($concept, 'perimetre') !== false) {
                $question = "Quelle est la formule pour calculer le périmètre d'un rectangle ?";
                $choices = [
                    'A' => 'P = 2 × (longueur + largeur)',
                    'B' => 'P = longueur × largeur',
                    'C' => 'P = longueur + largeur',
                    'D' => 'P = 2 × longueur × largeur',
                ];
                $correct = 'A';
                $explanation = "Le périmètre d'un rectangle se calcule en additionnant deux fois la longueur et deux fois la largeur : P = 2 × (L + l).";
            } elseif (stripos($concept, 'aire') !== false) {
                $question = "Quelle est la formule pour calculer l'aire d'un rectangle ?";
                $choices = [
                    'A' => 'A = longueur × largeur',
                    'B' => 'A = 2 × (longueur + largeur)',
                    'C' => 'A = longueur + largeur',
                    'D' => 'A = longueur²',
                ];
                $correct = 'A';
                $explanation = "L'aire d'un rectangle se calcule en multipliant la longueur par la largeur : A = L × l.";
            }

            // Si on trouve des valeurs numériques dans la réponse, créer une question avec calcul
            if (!empty($cleanAnswer) && preg_match('/(\d+)\s*(cm|m|cm²|m²|kg|€)/', $cleanAnswer, $valueMatch)) {
                $value = (int) $valueMatch[1];
                $unit = $valueMatch[2];

                // Créer une question basée sur le concept et la valeur trouvée
                if (stripos($concept, 'périmètre') !== false || stripos($concept, 'perimetre') !== false) {
                    $question = "Quel est le périmètre calculé dans cet exercice ?";
                } elseif (stripos($concept, 'aire') !== false) {
                    $question = "Quelle est l'aire calculée dans cet exercice ?";
                } else {
                    $question = "D'après l'exercice sur " . strtolower($concept) . ", quelle est la bonne réponse ?";
                }

                // Générer des distracteurs réalistes
                $distractors = [
                    ($value + 1) . ' ' . $unit,
                    ($value - 1) . ' ' . $unit,
                    ($value * 2) . ' ' . $unit,
                ];
                shuffle($distractors);

                $choices = [
                    'A' => $value . ' ' . $unit,
                    'B' => $distractors[0],
                    'C' => $distractors[1],
                    'D' => $distractors[2],
                ];
                $correct = 'A';

                // Améliorer l'explication avec la valeur trouvée
                if (empty($explanation)) {
                    $explanation = "D'après l'exercice, la réponse est $value $unit.";
                }
            }
        }
        // PRIORITÉ 4 : Extraire des pourcentages
        elseif (preg_match('/(pourcentage|%)/i', $title . ' ' . $cleanContent) && preg_match('/(\d+)\s*%/i', $cleanContent, $pctMatch)) {
            $percent = (int) $pctMatch[1];
            $question = "Comment calcule-t-on " . $percent . "% d'un nombre ?";
            $choices = [
                'A' => 'On multiplie le nombre par ' . $percent . ' puis on divise par 100',
                'B' => 'On divise le nombre par ' . $percent,
                'C' => 'On multiplie le nombre par ' . $percent,
                'D' => 'On additionne ' . $percent . ' au nombre',
            ];
            $correct = 'A';
            $explanation = "Pour calculer un pourcentage, on multiplie le nombre par le pourcentage puis on divise par 100. Par exemple, " . $percent . "% de 100 = (100 × " . $percent . ") ÷ 100 = " . $percent . ".";
        }
        // PRIORITÉ 5 : Extraire des concepts du titre et créer des questions conceptuelles
        elseif (preg_match('/(fraction|décimaux|proportionnalité|théorème|formule|équation)/i', $title, $conceptMatch)) {
            $concept = strtolower($conceptMatch[1]);

            if (stripos($concept, 'fraction') !== false) {
                $question = "Pour additionner deux fractions, que faut-il faire en premier ?";
                $choices = [
                    'A' => 'Les mettre au même dénominateur',
                    'B' => 'Additionner les numérateurs et les dénominateurs',
                    'C' => 'Multiplier les numérateurs entre eux',
                    'D' => 'Soustraire les dénominateurs',
                ];
                $correct = 'A';
                $explanation = "Pour additionner des fractions, il faut d'abord les mettre au même dénominateur (trouver le PPCM), puis additionner les numérateurs.";
            } elseif (stripos($concept, 'proportionnalité') !== false) {
                $question = "Deux grandeurs sont proportionnelles si...";
                $choices = [
                    'A' => 'Leur rapport est constant',
                    'B' => 'Leur somme est constante',
                    'C' => 'Leur différence est constante',
                    'D' => 'Leur produit est constant',
                ];
                $correct = 'A';
                $explanation = "Deux grandeurs sont proportionnelles si leur rapport (division) reste constant. Par exemple, si on double l'une, on double l'autre.";
            } elseif (stripos($concept, 'théorème') !== false || stripos($concept, 'theoreme') !== false) {
                if (stripos($title, 'pythagore') !== false || stripos($title, 'Pythagore') !== false) {
                    $question = "Le théorème de Pythagore s'applique dans quel type de triangle ?";
                    $choices = [
                        'A' => 'Triangle rectangle',
                        'B' => 'Triangle équilatéral',
                        'C' => 'Triangle isocèle',
                        'D' => 'Tous les triangles',
                    ];
                    $correct = 'A';
                    $explanation = "Le théorème de Pythagore s'applique uniquement dans un triangle rectangle : a² + b² = c² où c est l'hypoténuse.";
                } elseif (stripos($title, 'thales') !== false || stripos($title, 'Thalès') !== false) {
                    $question = "Le théorème de Thalès permet de calculer...";
                    $choices = [
                        'A' => 'Des longueurs dans des triangles avec des droites parallèles',
                        'B' => 'L\'aire d\'un triangle',
                        'C' => 'Le périmètre d\'un cercle',
                        'D' => 'L\'angle d\'un triangle',
                    ];
                    $correct = 'A';
                    $explanation = "Le théorème de Thalès permet de calculer des longueurs dans des configurations avec des droites parallèles.";
                } else {
                    $question = "Question sur : " . $title;
                    $choices = [
                        'A' => 'Réponse correcte',
                        'B' => 'Réponse incorrecte',
                        'C' => 'Réponse incorrecte',
                        'D' => 'Réponse incorrecte',
                    ];
                }
            } elseif (stripos($concept, 'équation') !== false || stripos($concept, 'equation') !== false) {
                $question = "Pour résoudre une équation du type ax + b = c, que fait-on en premier ?";
                $choices = [
                    'A' => 'On isole le terme avec x',
                    'B' => 'On multiplie tout par x',
                    'C' => 'On additionne x des deux côtés',
                    'D' => 'On divise tout par x',
                ];
                $correct = 'A';
                $explanation = "Pour résoudre une équation, on isole d'abord le terme contenant x en effectuant les opérations inverses.";
            } else {
                $question = "Question sur : " . $title;
                $choices = [
                    'A' => 'Réponse correcte',
                    'B' => 'Réponse incorrecte',
                    'C' => 'Réponse incorrecte',
                    'D' => 'Réponse incorrecte',
                ];
            }

            // Utiliser la réponse pour améliorer l'explication
            if (!empty($cleanAnswer)) {
                $explanation = substr($cleanAnswer, 0, 200);
            }
        }
        // PRIORITÉ 6 : Si la réponse contient un nombre, créer une question basée sur le titre et le contenu
        elseif (!empty($cleanAnswer) && preg_match('/(\d+[.,]?\d*)/', $cleanAnswer, $numMatch)) {
            $correctAnswer = $numMatch[1];

            // Créer une question plus spécifique basée sur le titre
            if (preg_match('/(fraction|décimaux|pourcentage|proportionnalité|périmètre|aire|équation)/i', $title, $titleConcept)) {
                $concept = strtolower($titleConcept[1]);
                $question = "Quelle est la réponse à la question sur " . $concept . " dans cet exercice ?";
            } else {
                $question = $title ?: "Question de mathématiques";
            }

            // Essayer de créer des distracteurs réalistes
            $numValue = (float) str_replace(',', '.', $correctAnswer);

            // Distracteurs adaptés selon la valeur
            if ($numValue < 10) {
                $distractors = [
                    (string) ($numValue + 1),
                    (string) ($numValue - 1),
                    (string) ($numValue + 2),
                ];
            } elseif ($numValue < 100) {
                $distractors = [
                    (string) ($numValue + 5),
                    (string) ($numValue - 5),
                    (string) ($numValue + 10),
                ];
            } else {
                $distractors = [
                    (string) ($numValue + 10),
                    (string) ($numValue - 10),
                    (string) ($numValue * 1.5),
                ];
            }
            shuffle($distractors);

            $choices = [
                'A' => (string) $correctAnswer,
                'B' => $distractors[0],
                'C' => $distractors[1],
                'D' => $distractors[2],
            ];
            $correct = 'A';

            // Extraire une explication pertinente de la réponse
            $explanationLines = explode("\n", $cleanAnswer);
            $explanation = trim($explanationLines[0]);
            if (strlen($explanation) > 200) {
                $explanation = substr($explanation, 0, 197) . '...';
            }
            if (empty($explanation)) {
                $explanation = "La bonne réponse est $correctAnswer.";
            }
        }
        // DERNIER RECOURS : Question générale basée sur le titre
        else {
            $question = $title ?: "Question de mathématiques";

            // Essayer d'extraire des concepts du titre
            if (preg_match('/(fraction|décimaux|périmètre|aire|pourcentage|proportionnalité|théorème|équation)/i', $title, $conceptMatch)) {
                $concept = strtolower($conceptMatch[1]);
                $question = "Quelle est la propriété principale concernant : " . $concept . " ?";
            }

            $choices = [
                'A' => 'Réponse correcte basée sur la leçon',
                'B' => 'Réponse incorrecte',
                'C' => 'Réponse incorrecte',
                'D' => 'Réponse incorrecte',
            ];
            $explanation = substr($cleanAnswer, 0, 200) ?: "Consulte ton cours sur " . ($title ?: "ce sujet") . " pour plus d'informations.";
        }
    } elseif ($subject === 'Français') {
        // Pour le français, créer des questions basées sur les règles grammaticales
        $question = '';

        // Analyser le titre pour identifier le concept
        if (preg_match('/(accord|conjugaison|classe|nature|fonction|genre|nombre)/i', $title, $conceptMatch)) {
            $concept = strtolower($conceptMatch[1]);

            if (stripos($title, 'accord') !== false) {
                $question = "Quelle est la règle principale pour les accords en français ?";
                $choices = [
                    'A' => 'L\'adjectif s\'accorde en genre et en nombre avec le nom',
                    'B' => 'L\'adjectif ne s\'accorde jamais',
                    'C' => 'L\'adjectif s\'accorde seulement au masculin',
                    'D' => 'L\'adjectif s\'accorde seulement au pluriel',
                ];
                $correct = 'A';
            } elseif (stripos($title, 'conjugaison') !== false) {
                $question = "Quel temps verbal est utilisé dans cette phrase ?";
                $choices = [
                    'A' => 'Présent',
                    'B' => 'Imparfait',
                    'C' => 'Passé composé',
                    'D' => 'Futur',
                ];
                $correct = 'A';
            } else {
                $question = "Question sur : " . $title;
                $choices = [
                    'A' => 'Réponse correcte',
                    'B' => 'Réponse incorrecte',
                    'C' => 'Réponse incorrecte',
                    'D' => 'Réponse incorrecte',
                ];
            }
        } else {
            $question = $title ?: "Question de français";
            $choices = [
                'A' => 'Réponse correcte',
                'B' => 'Réponse incorrecte',
                'C' => 'Réponse incorrecte',
                'D' => 'Réponse incorrecte',
            ];
        }

        // Utiliser la réponse pour créer une explication
        if (!empty($cleanAnswer)) {
            // Extraire la première phrase de l'explication
            $explanationLines = explode("\n", $cleanAnswer);
            $explanation = trim($explanationLines[0]);
            if (strlen($explanation) > 200) {
                $explanation = substr($explanation, 0, 197) . '...';
            }
        } else {
            $explanation = "Consulte ton cours de français sur " . ($title ?: "ce sujet") . " pour plus d'informations.";
        }

    } elseif ($subject === 'Sciences' || $subject === 'SVT') {
        // Pour les sciences, créer des questions basées sur les concepts
        $question = $title ?: "Question de sciences";

        // Essayer d'extraire un concept du titre
        if (preg_match('/(organe|cellule|photosynthèse|respiration|digestion|circulation)/i', $title, $conceptMatch)) {
            $concept = $conceptMatch[1];
            $question = "Quelle est la fonction principale de : " . $concept . " ?";
        }

        $choices = [
            'A' => 'Réponse correcte basée sur la leçon',
            'B' => 'Réponse incorrecte',
            'C' => 'Réponse incorrecte',
            'D' => 'Réponse incorrecte',
        ];
        $explanation = substr($cleanAnswer, 0, 200) ?: "Consulte ton cours de sciences pour plus d'informations.";

    } elseif ($subject === 'Histoire-Géo' || $subject === 'Histoire-Géographie') {
        // Pour l'histoire-géo, créer des questions factuelles
        $question = $title ?: "Question d'histoire-géographie";

        // Si le titre contient une date ou un lieu, créer une question spécifique
        if (preg_match('/(\d{4})/', $title, $dateMatch)) {
            $question = "En quelle année s'est déroulé : " . $title . " ?";
            $year = (int) $dateMatch[1];
            $choices = [
                'A' => (string) $year,
                'B' => (string) ($year + 1),
                'C' => (string) ($year - 1),
                'D' => (string) ($year + 10),
            ];
            $correct = 'A';
        } else {
            $choices = [
                'A' => 'Réponse correcte',
                'B' => 'Réponse incorrecte',
                'C' => 'Réponse incorrecte',
                'D' => 'Réponse incorrecte',
            ];
        }

        $explanation = substr($cleanAnswer, 0, 200) ?: "Consulte ton cours d'histoire-géographie pour plus d'informations.";

    } else {
        // Pour les autres matières, créer une question basée sur le titre
        $question = $title ?: "Question sur " . $subject;
        $choices = [
            'A' => 'Réponse correcte',
            'B' => 'Réponse incorrecte',
            'C' => 'Réponse incorrecte',
            'D' => 'Réponse incorrecte',
        ];
        $explanation = substr($cleanAnswer, 0, 200) ?: "Consulte ton cours pour plus d'informations.";
    }

    // S'assurer qu'on a une question valide
    if (empty($question)) {
        return null;
    }

    return [
        'id' => $exercise['Id'] ?? 0,
        'question' => $question,
        'choices' => $choices,
        'correct' => $correct,
        'explanation' => $explanation,
        'source' => 'exercise',
        'exercise_id' => $exercise['Id'] ?? 0,
        'exercise_title' => $title,
    ];
}

/**
 * Génère des questions de quiz par défaut pour une matière et un niveau
 */
function generateDefaultQuizQuestions($subject, $level)
{
    $questions = [];

    // Questions par matière et niveau (code existant conservé)
    switch ($subject) {
        case 'Mathématiques':
            if (in_array($level, ['6ème', '6eme'])) {
                $questions = [
                    [
                        'question' => 'Quel est le résultat de 15 + 27 ?',
                        'choices' => [
                            'A' => '40',
                            'B' => '42',
                            'C' => '41',
                            'D' => '43',
                        ],
                        'correct' => 'B',
                        'explanation' => '15 + 27 = 42. Additionner les unités : 5 + 7 = 12, on pose 2 et on retient 1. Additionner les dizaines : 1 + 2 + 1 = 4. Résultat : 42.',
                    ],
                    [
                        'question' => 'Combien fait 8 × 7 ?',
                        'choices' => [
                            'A' => '54',
                            'B' => '56',
                            'C' => '58',
                            'D' => '55',
                        ],
                        'correct' => 'B',
                        'explanation' => '8 × 7 = 56. C\'est la table de multiplication de 8.',
                    ],
                    [
                        'question' => 'Quel est le périmètre d\'un carré de côté 5 cm ?',
                        'choices' => [
                            'A' => '20 cm',
                            'B' => '25 cm',
                            'C' => '15 cm',
                            'D' => '10 cm',
                        ],
                        'correct' => 'A',
                        'explanation' => 'Le périmètre d\'un carré = 4 × côté. Donc 4 × 5 = 20 cm.',
                    ],
                ];
            } elseif (in_array($level, ['5ème', '5eme', '4ème', '4eme'])) {
                $questions = [
                    [
                        'question' => 'Quel est le résultat de (-15) + 8 ?',
                        'choices' => [
                            'A' => '-7',
                            'B' => '7',
                            'C' => '-23',
                            'D' => '23',
                        ],
                        'correct' => 'A',
                        'explanation' => '(-15) + 8 = -7. On soustrait 8 de 15 et on garde le signe du plus grand nombre en valeur absolue.',
                    ],
                    [
                        'question' => 'Quel est le résultat de 3/4 + 1/2 ?',
                        'choices' => [
                            'A' => '4/6',
                            'B' => '5/4',
                            'C' => '1',
                            'D' => '5/6',
                        ],
                        'correct' => 'B',
                        'explanation' => '3/4 + 1/2 = 3/4 + 2/4 = 5/4. Il faut mettre les fractions au même dénominateur.',
                    ],
                    [
                        'question' => 'Quelle est l\'aire d\'un rectangle de longueur 8 cm et largeur 5 cm ?',
                        'choices' => [
                            'A' => '13 cm²',
                            'B' => '26 cm²',
                            'C' => '40 cm²',
                            'D' => '35 cm²',
                        ],
                        'correct' => 'C',
                        'explanation' => 'L\'aire d\'un rectangle = longueur × largeur = 8 × 5 = 40 cm².',
                    ],
                ];
            } elseif (in_array($level, ['3ème', '3eme'])) {
                $questions = [
                    [
                        'question' => 'Quelle est la solution de l\'équation 2x + 5 = 13 ?',
                        'choices' => [
                            'A' => 'x = 3',
                            'B' => 'x = 4',
                            'C' => 'x = 5',
                            'D' => 'x = 6',
                        ],
                        'correct' => 'B',
                        'explanation' => '2x + 5 = 13, donc 2x = 13 - 5 = 8, donc x = 8/2 = 4.',
                    ],
                    [
                        'question' => 'Quel est le théorème de Pythagore dans un triangle rectangle ?',
                        'choices' => [
                            'A' => 'a² = b² + c²',
                            'B' => 'a² + b² = c²',
                            'C' => 'a = b + c',
                            'D' => 'a × b = c',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Dans un triangle rectangle, le carré de l\'hypoténuse est égal à la somme des carrés des deux autres côtés : a² + b² = c².',
                    ],
                    [
                        'question' => 'Quelle est la factorisation de x² - 9 ?',
                        'choices' => [
                            'A' => '(x - 3)(x + 3)',
                            'B' => '(x - 9)(x + 1)',
                            'C' => '(x - 3)²',
                            'D' => 'x(x - 9)',
                        ],
                        'correct' => 'A',
                        'explanation' => 'x² - 9 est une identité remarquable de la forme a² - b² = (a - b)(a + b). Donc x² - 9 = (x - 3)(x + 3).',
                    ],
                ];
            } elseif (in_array($level, ['Seconde'])) {
                $questions = [
                    [
                        'question' => 'Quelle est la dérivée de f(x) = x² ?',
                        'choices' => [
                            'A' => 'f\'(x) = 2x',
                            'B' => 'f\'(x) = x',
                            'C' => 'f\'(x) = 2',
                            'D' => 'f\'(x) = x²',
                        ],
                        'correct' => 'A',
                        'explanation' => 'La dérivée de x² est 2x. On utilise la formule : si f(x) = xⁿ, alors f\'(x) = n×xⁿ⁻¹.',
                    ],
                    [
                        'question' => 'Quelle est la solution de l\'inéquation 3x - 5 < 10 ?',
                        'choices' => [
                            'A' => 'x < 5',
                            'B' => 'x < 15',
                            'C' => 'x > 5',
                            'D' => 'x > 15',
                        ],
                        'correct' => 'A',
                        'explanation' => '3x - 5 < 10, donc 3x < 15, donc x < 5.',
                    ],
                ];
            }
            break;

        case 'Français':
            if (in_array($level, ['6ème', '6eme'])) {
                $questions = [
                    [
                        'question' => 'Quelle est la nature grammaticale du mot "rapidement" dans la phrase "Il court rapidement" ?',
                        'choices' => [
                            'A' => 'Adjectif',
                            'B' => 'Adverbe',
                            'C' => 'Nom',
                            'D' => 'Verbe',
                        ],
                        'correct' => 'B',
                        'explanation' => '"Rapidement" est un adverbe car il modifie le verbe "court" et se termine par le suffixe "-ment".',
                    ],
                    [
                        'question' => 'Quelle est la classe grammaticale de "le" dans "le chien" ?',
                        'choices' => [
                            'A' => 'Pronom',
                            'B' => 'Article défini',
                            'C' => 'Article indéfini',
                            'D' => 'Adjectif',
                        ],
                        'correct' => 'B',
                        'explanation' => '"Le" est un article défini qui détermine le nom "chien".',
                    ],
                    [
                        'question' => 'Quel temps est utilisé dans "Il mangeait" ?',
                        'choices' => [
                            'A' => 'Présent',
                            'B' => 'Imparfait',
                            'C' => 'Passé simple',
                            'D' => 'Futur',
                        ],
                        'correct' => 'B',
                        'explanation' => '"Mangeait" est à l\'imparfait. L\'imparfait exprime une action passée en cours ou répétée.',
                    ],
                ];
            } elseif (in_array($level, ['5ème', '5eme', '4ème', '4eme', '3ème', '3eme'])) {
                $questions = [
                    [
                        'question' => 'Quel est le temps du verbe dans "Il eut terminé" ?',
                        'choices' => [
                            'A' => 'Plus-que-parfait',
                            'B' => 'Passé antérieur',
                            'C' => 'Futur antérieur',
                            'D' => 'Passé simple',
                        ],
                        'correct' => 'B',
                        'explanation' => '"Il eut terminé" est au passé antérieur. C\'est un temps composé formé avec l\'auxiliaire "avoir" au passé simple.',
                    ],
                    [
                        'question' => 'Quelle figure de style est utilisée dans "Les étoiles dansent dans le ciel" ?',
                        'choices' => [
                            'A' => 'Métaphore',
                            'B' => 'Personnification',
                            'C' => 'Comparaison',
                            'D' => 'Métonymie',
                        ],
                        'correct' => 'B',
                        'explanation' => 'C\'est une personnification car on attribue une action humaine ("danser") aux étoiles qui sont inanimées.',
                    ],
                ];
            }
            break;

        case 'Sciences':
            if (in_array($level, ['6ème', '6eme'])) {
                $questions = [
                    [
                        'question' => 'Quelle planète est la plus proche du Soleil ?',
                        'choices' => [
                            'A' => 'Vénus',
                            'B' => 'Mercure',
                            'C' => 'Terre',
                            'D' => 'Mars',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Mercure est la planète la plus proche du Soleil dans notre système solaire.',
                    ],
                    [
                        'question' => 'L\'eau se transforme en glace à quelle température (en conditions normales) ?',
                        'choices' => [
                            'A' => '100°C',
                            'B' => '0°C',
                            'C' => '-10°C',
                            'D' => '50°C',
                        ],
                        'correct' => 'B',
                        'explanation' => 'À la pression atmosphérique normale, l\'eau gèle à 0°C. C\'est le point de fusion de l\'eau.',
                    ],
                    [
                        'question' => 'Quel est l\'organe qui pompe le sang dans le corps ?',
                        'choices' => [
                            'A' => 'Le poumon',
                            'B' => 'Le cœur',
                            'C' => 'Le foie',
                            'D' => 'Le rein',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Le cœur est l\'organe musculaire qui pompe le sang dans tout le corps.',
                    ],
                ];
            } elseif (in_array($level, ['5ème', '5eme', '4ème', '4eme'])) {
                $questions = [
                    [
                        'question' => 'Quelle est la formule chimique de l\'eau ?',
                        'choices' => [
                            'A' => 'H₂O',
                            'B' => 'CO₂',
                            'C' => 'O₂',
                            'D' => 'NaCl',
                        ],
                        'correct' => 'A',
                        'explanation' => 'L\'eau a pour formule H₂O : 2 atomes d\'hydrogène (H) et 1 atome d\'oxygène (O).',
                    ],
                    [
                        'question' => 'Quel est le processus par lequel les plantes produisent leur nourriture ?',
                        'choices' => [
                            'A' => 'Respiration',
                            'B' => 'Photosynthèse',
                            'C' => 'Digestion',
                            'D' => 'Circulation',
                        ],
                        'correct' => 'B',
                        'explanation' => 'La photosynthèse est le processus par lequel les plantes utilisent la lumière du soleil, l\'eau et le CO₂ pour produire du glucose.',
                    ],
                ];
            }
            break;

        case 'Histoire-Géo':
            if (in_array($level, ['6ème', '6eme'])) {
                $questions = [
                    [
                        'question' => 'Quelle est la capitale de la France ?',
                        'choices' => [
                            'A' => 'Lyon',
                            'B' => 'Paris',
                            'C' => 'Marseille',
                            'D' => 'Toulouse',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Paris est la capitale et la plus grande ville de France.',
                    ],
                    [
                        'question' => 'Quel océan borde l\'ouest de la France ?',
                        'choices' => [
                            'A' => 'Océan Atlantique',
                            'B' => 'Océan Pacifique',
                            'C' => 'Océan Indien',
                            'D' => 'Océan Arctique',
                        ],
                        'correct' => 'A',
                        'explanation' => 'L\'océan Atlantique borde l\'ouest de la France.',
                    ],
                    [
                        'question' => 'Qui était le premier empereur romain ?',
                        'choices' => [
                            'A' => 'Jules César',
                            'B' => 'Auguste',
                            'C' => 'Néron',
                            'D' => 'Marc Antoine',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Auguste (Octave) fut le premier empereur romain, de 27 av. J.-C. à 14 ap. J.-C.',
                    ],
                ];
            } elseif (in_array($level, ['5ème', '5eme', '4ème', '4eme', '3ème', '3eme'])) {
                $questions = [
                    [
                        'question' => 'En quelle année a commencé la Révolution française ?',
                        'choices' => [
                            'A' => '1787',
                            'B' => '1789',
                            'C' => '1792',
                            'D' => '1795',
                        ],
                        'correct' => 'B',
                        'explanation' => 'La Révolution française a commencé en 1789 avec la prise de la Bastille le 14 juillet.',
                    ],
                    [
                        'question' => 'Quelle est la plus haute montagne du monde ?',
                        'choices' => [
                            'A' => 'K2',
                            'B' => 'Mont Everest',
                            'C' => 'Kilimandjaro',
                            'D' => 'Mont Blanc',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Le Mont Everest, situé dans l\'Himalaya, est le point culminant de la Terre avec 8 849 mètres.',
                    ],
                ];
            }
            break;

        case 'Anglais':
            if (in_array($level, ['6ème', '6eme'])) {
                $questions = [
                    [
                        'question' => 'How do you say "bonjour" in English ?',
                        'choices' => [
                            'A' => 'Goodbye',
                            'B' => 'Hello',
                            'C' => 'Thank you',
                            'D' => 'Please',
                        ],
                        'correct' => 'B',
                        'explanation' => '"Bonjour" se traduit par "Hello" ou "Good morning" en anglais.',
                    ],
                    [
                        'question' => 'What is the plural of "cat" ?',
                        'choices' => [
                            'A' => 'cats',
                            'B' => 'cates',
                            'C' => 'caties',
                            'D' => 'cat',
                        ],
                        'correct' => 'A',
                        'explanation' => 'Le pluriel régulier en anglais s\'obtient en ajoutant "s" : cat → cats.',
                    ],
                ];
            } elseif (in_array($level, ['5ème', '5eme', '4ème', '4eme'])) {
                $questions = [
                    [
                        'question' => 'Choose the correct form: "I _____ to school yesterday."',
                        'choices' => [
                            'A' => 'go',
                            'B' => 'went',
                            'C' => 'going',
                            'D' => 'goes',
                        ],
                        'correct' => 'B',
                        'explanation' => 'Avec "yesterday" (hier), on utilise le prétérit. Le prétérit de "go" est "went".',
                    ],
                    [
                        'question' => 'What does "I am hungry" mean in French ?',
                        'choices' => [
                            'A' => 'Je suis heureux',
                            'B' => 'J\'ai faim',
                            'C' => 'Je suis fatigué',
                            'D' => 'J\'ai soif',
                        ],
                        'correct' => 'B',
                        'explanation' => '"I am hungry" signifie "J\'ai faim" en français.',
                    ],
                ];
            }
            break;
    }

    // Si pas de questions pour ce niveau, générer des questions générales
    if (empty($questions)) {
        $questions = [
            [
                'question' => 'Question générale sur ' . $subject,
                'choices' => [
                    'A' => 'Réponse A',
                    'B' => 'Réponse B',
                    'C' => 'Réponse C',
                    'D' => 'Réponse D',
                ],
                'correct' => 'B',
                'explanation' => 'Explication de la réponse.',
            ],
        ];
    }

    return $questions;
}

/**
 * Récupère les questions de quiz depuis la base de données ou génère des questions par défaut
 * PRIORITÉ : Utiliser les exercices réels de la base de données liés aux leçons
 */
function getQuizQuestions($subject, $level, $pdo = null, $limit = 5)
{
    // Recherche du fichier quiz JSON correspondant
    $quizDir = __DIR__ . '/../data/quiz/';
    $answersDir = __DIR__ . '/../data/quiz_answers/';
    $subjectNorm = strtolower(str_replace(
        ['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'û', 'ü', 'ç', ' '],
        ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'c', ''],
        $subject,
    ));
    $levelNorm = strtolower(str_replace(
        ['ème', 'è', 'é', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'û', 'ü', 'ç', ' '],
        ['eme', 'e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'c', ''],
        $level,
    ));

    // Chercher un fichier quiz qui matche le niveau et la matière
    $quizFile = null;
    foreach (glob($quizDir . '*.json') as $file) {
        $json = json_decode(file_get_contents($file), true);
        if (!empty($json['quiz']['subject']) && !empty($json['quiz']['level'])) {
            $fileSubject = strtolower(str_replace(
                ['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'û', 'ü', 'ç', ' '],
                ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'c', ''],
                $json['quiz']['subject'],
            ));
            $fileLevel = strtolower(str_replace(
                ['ème', 'è', 'é', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'û', 'ü', 'ç', ' '],
                ['eme', 'e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'c', ''],
                $json['quiz']['level'],
            ));
            if ($fileSubject === $subjectNorm && $fileLevel === $levelNorm) {
                $quizFile = $file;
                break;
            }
        }
    }

    if (!$quizFile) {
        // Aucun quiz trouvé pour ce niveau/matière
        return [];
    }

    $quizJson = json_decode(file_get_contents($quizFile), true);
    $quizId = basename($quizFile, '.json');
    $answersFile = $answersDir . $quizId . '.json';
    $answersJson = file_exists($answersFile) ? json_decode(file_get_contents($answersFile), true) : null;
    $answersMap = [];
    if ($answersJson && !empty($answersJson['quiz']['answers'])) {
        foreach ($answersJson['quiz']['answers'] as $ans) {
            $answersMap[$ans['question_id']] = $ans;
        }
    }

    $questions = [];
    if (!empty($quizJson['quiz']['questions'])) {
        foreach ($quizJson['quiz']['questions'] as $q) {
            $id = $q['id'] ?? null;
            $type = $q['type'] ?? 'qcm';
            $questionText = $q['question'] ?? '';
            $choices = [];
            $correct = null;
            $explanation = '';
            $answerData = $id && isset($answersMap[$id]) ? $answersMap[$id] : null;

            // On ne garde que QCM/QDM et vrai-faux
            if ($type === 'qcm' && isset($q['choices']) && is_array($q['choices'])) {
                $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
                $correctIndex = null;
                if (isset($answerData['answer'])) {
                    $correctValue = $answerData['answer'];
                    foreach ($q['choices'] as $i => $choice) {
                        $letter = $letters[$i] ?? chr(65 + $i);
                        $choices[$letter] = $choice;
                        if ($choice == $correctValue) {
                            $correct = $letter;
                        }
                    }
                } elseif (isset($answerData['correct'])) {
                    $correctIndex = $answerData['correct'];
                    foreach ($q['choices'] as $i => $choice) {
                        $letter = $letters[$i] ?? chr(65 + $i);
                        $choices[$letter] = $choice;
                        if ($i == $correctIndex) {
                            $correct = $letter;
                        }
                    }
                } else {
                    foreach ($q['choices'] as $i => $choice) {
                        $letter = $letters[$i] ?? chr(65 + $i);
                        $choices[$letter] = $choice;
                    }
                    $correct = 'A';
                }
                if ($correct === null && !empty($choices)) {
                    $correct = 'A';
                }
            } elseif ($type === 'vrai-faux' || $type === 'tf') {
                $choices = ['A' => 'Vrai', 'B' => 'Faux'];
                if (isset($answerData['answer'])) {
                    $correct = ($answerData['answer'] === true) ? 'A' : 'B';
                } elseif (isset($answerData['correct'])) {
                    $correct = ($answerData['correct'] == 1) ? 'A' : 'B';
                } else {
                    $correct = 'A';
                }
            } else {
                // Ignore les types texte/open
                continue;
            }
            if ($answerData && !empty($answerData['correction'])) {
                $explanation = $answerData['correction'];
            }

            $questions[] = [
                'id' => $id,
                'question' => $questionText,
                'choices' => $choices,
                'correct' => $correct,
                'explanation' => $explanation,
                'source' => 'json',
                'type' => $type,
            ];
            if (count($questions) >= $limit) {
                break;
            }
        }
    }
    return $questions;
}
