<?php

/**
 * src/utils/ExerciseParser.php
 * Parser unifié : Identifier + Structure de contenu + Détection doublons
 */

class ExerciseParser
{
    private $db;

    // Patterns pour détecter les sous-questions
    private $contentPatterns = [
        'lettres_min_parenthese' => '/^([a-z])\)\s*/m',
        'lettres_min_point' => '/^([a-z])\.\s*/m',
        'lettres_maj_parenthese' => '/^([A-Z])\)\s*/m',
        'chiffres_parenthese' => '/^(\d+)\)\s*/m',
        'chiffres_point' => '/^(\d+)\.\s*/m',
        'question_num' => '/^Question\s+(\d+)\s*:?/mi',
    ];

    // Mapping matières (gestion flexible)
    private $subjectMapping = [
        'MATHS' => 'Mathématiques',
        'MATHEMATIQUES' => 'Mathématiques',
        'MATH' => 'Mathématiques',

        'FRANCAIS' => 'Français',
        'FRANÇAIS' => 'Français',
        'FR' => 'Français',

        'HISTOIRE-GEOGRAPHIE' => 'Histoire-Géographie',
        'HISTOIRE-GEO' => 'Histoire-Géographie',
        'HG' => 'Histoire-Géographie',

        'PHYSIQUE-CHIMIE' => 'Physique-Chimie',
        'PC' => 'Physique-Chimie',

        'SVT' => 'SVT',
        'SCIENCES-VIE-TERRE' => 'SVT',

        'ANGLAIS' => 'Anglais',
        'ANG' => 'Anglais',
    ];

    // Mapping niveaux
    private $levelMapping = [
        '6EME' => '6ème',
        '6E' => '6ème',
        '5EME' => '5ème',
        '5E' => '5ème',
        '4EME' => '4ème',
        '4E' => '4ème',
        '3EME' => '3ème',
        '3E' => '3ème',

        '2NDE' => 'Seconde',
        'SECONDE' => 'Seconde',

        'PREMIERE' => 'Première',
        '1ERE' => 'Première',

        'TERMINALE' => 'Terminale',
        'TERM' => 'Terminale',
        'BAC' => 'Terminale',
    ];

    public function __construct($dbConnection)
    {
        $this->db = $dbConnection;
    }

    /**
     * PARSING COMPLET d'un exercice
     * Retourne : metadata (identifier) + structure (content) + validation
     */
    public function parseExerciseComplete($exerciseData)
    {
        $result = [
            'metadata' => $this->parseIdentifier($exerciseData['Identifier'] ?? ''),
            'structure' => $this->parseContent($exerciseData['Content'] ?? '', $exerciseData['Instruction'] ?? ''),
            'validation' => [],
        ];

        // Validation croisée
        $result['validation'] = $this->validateExercise($result, $exerciseData);

        return $result;
    }

    /**
     * 1. PARSE IDENTIFIER
     * Formats acceptés :
     * - Standard : MATIERE-NIVEAU-COMPETENCE-NUMERO
     * - BAC : MATIERE-BAC-COMPETENCE-NUMERO
     */
    public function parseIdentifier($identifier)
    {
        if (empty($identifier)) {
            return null;
        }

        // CAS SPÉCIAL : Format BAC (3 parties + BAC/BREVET)
        // Exemple : ANG-BAC-GRAMMAIRE-004 ou MATH-BREVET-CALCUL-012
        $patternBAC = '/^([A-Z\-]+)-(BAC|BREVET)-(.+)-(\d+)$/i';
        if (preg_match($patternBAC, $identifier, $matches)) {
            return [
                'raw' => $identifier,
                'subject' => $this->normalizeSubject($matches[1]),
                'level' => $matches[2] === 'BREVET' ? '3ème' : 'Terminale',
                'competence' => $this->normalizeCompetence($matches[3]),
                'number' => (int) $matches[4],
                'exam_prep' => strtoupper($matches[2]),
                'valid' => true,
            ];
        }

        // FORMAT STANDARD (4 parties)
        // Exemple : FRANCAIS-3EME-EXERCICE-002
        $pattern = '/^([A-Z\-]+)-([A-Z0-9]+)-(.+)-(\d+)$/i';

        if (!preg_match($pattern, $identifier, $matches)) {
            return null;
        }

        // Détecter prépa dans compétence
        $examPrep = null;
        if (stripos($matches[3], 'BAC') !== false) {
            $examPrep = 'BAC';
        } elseif (stripos($matches[3], 'BREVET') !== false) {
            $examPrep = 'BREVET';
        }

        return [
            'raw' => $identifier,
            'subject' => $this->normalizeSubject($matches[1]),
            'level' => $this->normalizeLevel($matches[2]),
            'competence' => $this->normalizeCompetence($matches[3]),
            'number' => (int) $matches[4],
            'exam_prep' => $examPrep,
            'valid' => true,
        ];
    }

    /**
     * Normaliser matière
     */
    private function normalizeSubject($subject)
    {
        $subjectUpper = strtoupper(trim($subject));
        return $this->subjectMapping[$subjectUpper] ?? ucfirst(strtolower($subject));
    }

    /**
     * Normaliser niveau
     */
    private function normalizeLevel($level)
    {
        $levelUpper = strtoupper(trim($level));
        return $this->levelMapping[$levelUpper] ?? $level;
    }

    /**
     * Normaliser compétence
     */
    private function normalizeCompetence($competence)
    {
        return ucwords(strtolower(str_replace('-', ' ', $competence)));
    }

    /**
     * 2. PARSE CONTENT (structure interne)
     * Détecte : consigne + sous-questions + type de structure
     */
    public function parseContent($content, $instruction = '')
    {
        // Extraction avancée des blocs Markdown (Texte Support, Questions, Resources...)
        $markdownBlocks = [
            'texte_support' => '/##\s*Texte Support\s*([\s\S]+?)(?=##|$)/i',
            'questions'     => '/##\s*Questions?\s*([\s\S]+?)(?=##|$)/i',
            'resources'     => '/##\s*ee Resources?([\s\S]+)/i',
        ];
        $extracted = [];
        foreach ($markdownBlocks as $key => $regex) {
            if (preg_match($regex, $content, $m)) {
                $extracted[$key] = trim($m[1]);
            }
        }

        // Consigne : priorité colonne, puis bloc explicite, puis fallback
        $parts = $this->separateConsigneContent($content, $instruction);

        // Si on a extrait des blocs Markdown, on les utilise pour structurer l'exercice
        if (!empty($extracted)) {
            return [
                'structure_type' => 'markdown-bloc',
                'pattern_detected' => 'markdown',
                'consigne' => $parts['consigne'],
                'texte_support' => $extracted['texte_support'] ?? '',
                'questions_bloc' => $extracted['questions'] ?? '',
                'resources_bloc' => $extracted['resources'] ?? '',
                'contenu_introduction' => '',
                'sub_questions' => null,
                'nombre_parties' => 1,
            ];
        }

        // Sinon, fallback sur l'ancien parsing
        $pattern = $this->detectPattern($parts['contenu']);

        if ($pattern && count($pattern['matches'][0]) >= 2) {
            // Multi-parties (au moins 2 sous-questions)
            $subQuestions = $this->extractSubQuestions($parts['contenu'], $pattern);

            return [
                'structure_type' => 'multi-parties',
                'pattern_detected' => $pattern['type'],
                'consigne' => $parts['consigne'],
                'contenu_introduction' => $subQuestions['introduction'],
                'sub_questions' => $subQuestions['questions'],
                'nombre_parties' => count($subQuestions['questions']),
            ];
        } else {
            // Simple
            return [
                'structure_type' => 'simple',
                'pattern_detected' => null,
                'consigne' => $parts['consigne'],
                'contenu_introduction' => $parts['contenu'],
                'sub_questions' => null,
                'nombre_parties' => 1,
            ];
        }
    }

    /**
     * Séparer consigne du contenu
     */
    private function separateConsigneContent($content, $instruction)
    {
        // Priorité 1 : Colonne Instruction si remplie
        if (!empty($instruction) && strlen(trim($instruction)) > 5) {
            return [
                'consigne' => trim($instruction),
                'contenu' => trim($content),
            ];
        }

        // Priorité 2 : "Consigne :" explicite dans Content
        if (preg_match('/^Consigne\s*:?\s*(.+?)(?=\n\n|\n[a-zA-Z0-9]\.?\))/s', $content, $matches)) {
            $consigne = trim($matches[1]);
            $contenu = trim(str_replace($matches[0], '', $content));
            return [
                'consigne' => $consigne,
                'contenu' => $contenu,
            ];
        }

        // Priorité 3 : Première phrase comme consigne
        $sentences = preg_split('/(?<=[.!?:])\s+/', trim($content), 2);
        if (count($sentences) >= 2 && strlen($sentences[0]) < 200) {
            return [
                'consigne' => $sentences[0],
                'contenu' => $sentences[1],
            ];
        }

        // Fallback : Tout est contenu
        return [
            'consigne' => '',
            'contenu' => $content,
        ];
    }

    /**
     * Détecter pattern de numérotation
     * ET différencier sous-questions vs choix de réponse (QCM)
     */
    private function detectPattern($content)
    {
        foreach ($this->contentPatterns as $type => $regex) {
            if (preg_match_all($regex, $content, $matches, PREG_OFFSET_CAPTURE)) {
                // Au moins 2 occurrences
                if (count($matches[0]) >= 2) {

                    // VÉRIFICATION : Est-ce un QCM ou des sous-questions ?
                    $isQCM = $this->isQCMPattern($content, $matches);

                    if ($isQCM) {
                        // C'est un QCM, ignorer ce pattern
                        continue;
                    }

                    return [
                        'type' => $type,
                        'regex' => $regex,
                        'matches' => $matches,
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Déterminer si un pattern correspond à un QCM
     * plutôt qu'à des sous-questions
     */
    private function isQCMPattern($content, $matches)
    {
        $qcmIndicators = 0;
        $totalItems = count($matches[0]);

        // Vérifier les premiers items
        for ($i = 0; $i < min($totalItems, 5); $i++) {
            $start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $end = isset($matches[0][$i + 1]) ? $matches[0][$i + 1][1] : strlen($content);
            $text = trim(substr($content, $start, $end - $start));

            // Critère 1 : Texte très court (choix de réponse)
            if (strlen($text) < 50) {
                $qcmIndicators++;
            }

            // Critère 2 : Pas de verbe d'action
            $actionVerbs = [
                'calculer', 'calculez', 'résoudre', 'résous',
                'démontrer', 'démontrez', 'expliquer', 'expliquez',
                'justifier', 'justifiez', 'déterminer', 'détermine',
                'tracer', 'trace', 'écrire', 'écris',
                'compléter', 'complète', 'rédiger', 'rédige',
                'donner', 'donne', 'indiquer', 'indique',
                'traduire', 'traduis', 'conjuguer', 'conjugue',
            ];

            $hasActionVerb = false;
            foreach ($actionVerbs as $verb) {
                if (stripos($text, $verb) !== false) {
                    $hasActionVerb = true;
                    break;
                }
            }

            if (!$hasActionVerb) {
                $qcmIndicators++;
            }

            // Critère 3 : Pas de ponctuation de question
            if (strpos($text, '?') === false && strpos($text, ':') === false) {
                $qcmIndicators++;
            }
        }

        // Si > 60% des indicateurs pointent vers QCM
        $itemsChecked = min($totalItems, 5);
        $qcmRatio = $qcmIndicators / ($itemsChecked * 3);

        return $qcmRatio > 0.6;
    }

    /**
     * Extraire les sous-questions
     */
    private function extractSubQuestions($content, $pattern)
    {
        $matches = $pattern['matches'];
        $subQuestions = [];

        // Introduction (avant première sous-question)
        $firstPos = $matches[0][0][1];
        $introduction = trim(substr($content, 0, $firstPos));

        // Extraire chaque sous-question
        for ($i = 0; $i < count($matches[0]); $i++) {
            $id = $matches[1][$i][0];
            $start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $end = isset($matches[0][$i + 1]) ? $matches[0][$i + 1][1] : strlen($content);

            $enonce = trim(substr($content, $start, $end - $start));

            // Garder seulement si significatif
            if (!empty($enonce) && strlen($enonce) > 3) {
                $subQuestions[] = [
                    'id' => $id,
                    'ordre' => $i + 1,
                    'enonce' => $enonce,
                ];
            }
        }

        return [
            'introduction' => $introduction,
            'questions' => $subQuestions,
        ];
    }

    /**
     * 3. VALIDATION
     */
    private function validateExercise($parsed, $exerciseData)
    {
        $errors = [];
        $warnings = [];

        // Champs obligatoires
        if (empty($exerciseData['Subject'])) {
            $errors[] = "Subject manquant";
        }
        if (empty($exerciseData['Level'])) {
            $errors[] = "Level manquant";
        }
        if (empty($exerciseData['Content'])) {
            $errors[] = "Content vide";
        }

        // Identifier
        if (!$parsed['metadata']) {
            $warnings[] = "Identifier invalide ou manquant";
        } else {
            // Cohérence identifier <-> BDD
            $metaSubject = strtoupper($parsed['metadata']['subject']);
            $dbSubject = strtoupper($exerciseData['Subject']);

            if ($metaSubject !== $dbSubject
                && !$this->areSynonyms($metaSubject, $dbSubject)) {
                $warnings[] = "Incohérence matière : {$parsed['metadata']['subject']} (ID) vs {$exerciseData['Subject']} (BDD)";
            }
        }

        // Consigne vide
        if (empty($parsed['structure']['consigne'])) {
            $warnings[] = "Consigne non détectée";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Vérifier si deux matières sont synonymes
     */
    private function areSynonyms($subject1, $subject2)
    {
        $normalized1 = $this->normalizeSubject($subject1);
        $normalized2 = $this->normalizeSubject($subject2);
        return $normalized1 === $normalized2;
    }

    /**
     * 4. MISE À JOUR BDD
     */
    public function updateDatabase($exerciseId, $parsedData)
    {
        $struct = $parsedData['structure'];
        $meta = $parsedData['metadata'];

        $stmt = $this->db->prepare("
            UPDATE exercises
            SET
                structure_type = :structure_type,
                pattern_detected = :pattern_detected,
                sub_questions = :sub_questions,
                Instruction = :consigne,
                processed = 1
            WHERE Id = :id
        ");

        $subQuestionsJson = null;
        if (!empty($struct['sub_questions'])) {
            $subQuestionsJson = json_encode([
                'introduction' => $struct['contenu_introduction'] ?? '',
                'questions' => $struct['sub_questions'],
            ], JSON_UNESCAPED_UNICODE);
        }

        return $stmt->execute([
            ':structure_type' => $struct['structure_type'],
            ':pattern_detected' => $struct['pattern_detected'],
            ':sub_questions' => $subQuestionsJson,
            ':consigne' => $struct['consigne'] ?? '',
            ':id' => $exerciseId,
        ]);
    }

    /**
     * 5. PARSER TOUS LES EXERCICES
     */
    public function parseAllExercises($limit = null)
    {
        $sql = "SELECT Id, Subject, Level, Title, Content, Instruction, Identifier
                FROM exercises
                WHERE processed = 0";

        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }

        $stmt = $this->db->query($sql);
        $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = [
            'total' => count($exercises),
            'success' => 0,
            'errors' => 0,
            'warnings' => 0,
            'simple' => 0,
            'multi_parties' => 0,
            'details' => [],
        ];

        foreach ($exercises as $exercise) {
            try {
                $parsed = $this->parseExerciseComplete($exercise);

                // Compter warnings
                if (!empty($parsed['validation']['warnings'])) {
                    $stats['warnings'] += count($parsed['validation']['warnings']);
                }

                // Si valide, mettre à jour
                if ($parsed['validation']['valid']) {
                    $this->updateDatabase($exercise['Id'], $parsed);
                    $stats['success']++;

                    if ($parsed['structure']['structure_type'] === 'multi-parties') {
                        $stats['multi_parties']++;
                    } else {
                        $stats['simple']++;
                    }
                } else {
                    $stats['errors']++;
                    $stats['details'][] = [
                        'id' => $exercise['Id'],
                        'errors' => $parsed['validation']['errors'],
                    ];
                }

            } catch (Exception $e) {
                $stats['errors']++;
                $stats['details'][] = [
                    'id' => $exercise['Id'],
                    'exception' => $e->getMessage(),
                ];
            }
        }

        return $stats;
    }
}
