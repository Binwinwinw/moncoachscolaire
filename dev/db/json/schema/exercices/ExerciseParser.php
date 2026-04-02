<?php
/**
 * =============================================================
 *  MonCoachScolaire — ExerciseParser (mise à jour 2026)
 * =============================================================
 *
 * Ce fichier utilise et référence le schéma officiel des exercices JSON/BDD :
 *   - Schéma officiel : dev/db/json/schema/exercices/schema_officiel_bdd_exercises_20260215.json
 *
 * Toute évolution de la structure doit être conforme à ce schéma unique, qui fait foi (voir DOCUMENTATION.md).
 *
 * Compatibilité ascendante : aucun champ existant n’est supprimé, les nouveaux champs sont ajoutés et documentés.
 *
 * Pour toute modification, vérifier la dernière version du schéma officiel.
 *
 * Champs principaux attendus (voir schéma officiel pour détails) :
 *   Subject, Level, Title, Content, Instruction, Answer, AnswerType, Choices,
 *   Tips, Domain, Competence, Difficulty, Identifier, is_active, XP_Points, Coherence
 *
 * Exemple de structure JSON :
 *   Voir schema_officiel_bdd_exercises_20260215.json (et DOCUMENTATION.md, section annexe technique)
 *
 * Historique :
 *   - 2026-02-14 : Ajout documentation schéma officiel, compat ascendante
 *   - 2026-02-18 : Harmonisation des références, schéma unique officiel
 *
 * PROMPT COPILOT (historique) :
 *   Crée une classe ExerciseParser qui analyse le champ Content de la table exercises
 *   et détecte automatiquement la structure de l'exercice selon le schéma officiel unique.
 *
 * STRUCTURE JSON ATTENDUE POUR LE PARSING DU CONTENT :
 * Voir schema_officiel_bdd_exercises_20260215.json pour la structure complète et à jour.
 *
 * Méthodes principales :
 *   - parseExercise($content): analyse et retourne la structure alignée sur le schéma officiel
 *   - detectPattern($content): identifie le pattern utilisé
 *   - extractSubQuestions($content, $pattern): extrait les parties
 *   - updateDatabase($exerciseId, $parsedData): met à jour la table
 *
 * =============================================================
 */

class ExerciseParser {
    private $db;

    // Patterns de détection
    private $patterns = [
        'lettres_min_parenthese' => '/^([a-z])\)\s*/m',
        'lettres_min_point' => '/^([a-z])\.\s*/m',
        'lettres_maj_parenthese' => '/^([A-Z])\)\s*/m',
        'chiffres_parenthese' => '/^(\d+)\)\s*/m',
        'chiffres_point' => '/^(\d+)\.\s*/m',
    ];

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Parse un exercice complet (préserve tous les champs existants, complète uniquement les champs vides)
     * @param array $exercise Exercice complet (tableau associatif)
     * @param array|null $schema Liste des champs du schéma officiel (optionnel, sinon hardcodé)
     * @return array Exercice aligné sur le schéma officiel, non destructif
     */
    public function parseExercise($exercise, $schema = null) {
        // Liste des champs du schéma officiel (à extraire dynamiquement si $schema non fourni)
        if ($schema === null) {
            $schema = [
                'Id','Subject','Level','Title','Content','structure_type','pattern_detected','sub_questions','Type','Answer','InteractiveConfig','Tips','Domain','Competence','Difficulty','exam_prep','Identifier','AnswerType','Choices','Instruction','is_active','XP_Points','Coherence','processed','course_id','LinkedCourses','created_at','updated_at'
            ];
        }

        $result = [];
        // Extraction avancée des blocs Markdown (Texte Support, Questions, Resources...)
        $content = $exercise['Content'] ?? '';
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
        // Consigne : priorité bloc explicite, puis fallback
        $parts = $this->separateConsigneContent($content);
        // Pattern (pour sub_questions)
        $pattern = $this->detectPattern($parts['contenu']);

        foreach ($schema as $field) {
            // Si le champ existe et est non vide/non nul, on le conserve
            if (isset($exercise[$field]) && $exercise[$field] !== null && $exercise[$field] !== '') {
                $result[$field] = $exercise[$field];
                continue;
            }
            // Sinon, on tente de le compléter intelligemment
            switch ($field) {
                case 'Content':
                    $result['Content'] = $content;
                    break;
                case 'Instruction':
                    $result['Instruction'] = $parts['consigne'] ?? null;
                    break;
                case 'structure_type':
                    $result['structure_type'] = !empty($extracted) ? 'markdown-bloc' : ($pattern ? 'multi-parties' : 'simple');
                    break;
                case 'pattern_detected':
                    $result['pattern_detected'] = !empty($extracted) ? 'markdown' : ($pattern['type'] ?? null);
                    break;
                case 'sub_questions':
                    $result['sub_questions'] = $pattern ? json_encode($this->extractSubQuestions($parts['contenu'], $pattern), JSON_UNESCAPED_UNICODE) : null;
                    break;
                case 'texte_support':
                    $result['texte_support'] = $extracted['texte_support'] ?? null;
                    break;
                case 'questions_bloc':
                    $result['questions_bloc'] = $extracted['questions'] ?? null;
                    break;
                case 'resources_bloc':
                    $result['resources_bloc'] = $extracted['resources'] ?? null;
                    break;
                default:
                    $result[$field] = null;
            }
        }
        return $result;
    }

    /**
     * Sépare consigne du reste
     */
    private function separateConsigneContent($content) {
        // Chercher "Consigne" ou "Énoncé"
        if (preg_match('/^(Consigne|Énoncé)\s*:?\s*(.+?)(?=\n\n|\n[a-zA-Z0-9]\.?\))/s', $content, $matches)) {
            return [
                'consigne' => trim($matches[2]),
                'contenu' => trim(str_replace($matches[0], '', $content)),
                'introduction' => ''
            ];
        }

        // Sinon, première phrase = consigne
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, 2);
        return [
            'consigne' => $sentences[0] ?? '',
            'contenu' => $sentences[1] ?? '',
            'introduction' => ''
        ];
    }

    /**
     * Détecte le pattern utilisé
     */
    private function detectPattern($content) {
        foreach ($this->patterns as $type => $regex) {
            if (preg_match_all($regex, $content, $matches, PREG_OFFSET_CAPTURE)) {
                return [
                    'type' => $type,
                    'regex' => $regex,
                    'matches' => $matches
                ];
            }
        }
        return null;
    }

    /**
     * Extrait les sous-questions
     */
    private function extractSubQuestions($content, $pattern) {
        $matches = $pattern['matches'];
        $subQuestions = [];

        // Extraire l'introduction (avant première sous-question)
        $firstPos = $matches[0][0][1];
        $introduction = trim(substr($content, 0, $firstPos));

        // Extraire chaque sous-question
        for ($i = 0; $i < count($matches[0]); $i++) {
            $id = $matches[1][$i][0];
            $start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $end = isset($matches[0][$i + 1]) ? $matches[0][$i + 1][1] : strlen($content);

            $enonce = trim(substr($content, $start, $end - $start));

            if (!empty($enonce)) {
                $subQuestions[] = [
                    'id' => $id,
                    'ordre' => $i + 1,
                    'enonce' => $enonce
                ];
            }
        }

        return [
            'introduction' => $introduction,
            'questions' => $subQuestions
        ];
    }

    /**
     * Met à jour la BDD
     */
    public function updateDatabase($exerciseId, $parsedData) {
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

        return $stmt->execute([
            ':structure_type' => $parsedData['structure_type'],
            ':pattern_detected' => $parsedData['pattern_detected'],
            ':sub_questions' => $parsedData['sub_questions'],
            ':consigne' => $parsedData['consigne'],
            ':id' => $exerciseId
        ]);
    }

    /**
     * Parser TOUS les exercices existants
     */
    public function parseAllExercises() {
        $stmt = $this->db->query("SELECT Id, Content FROM exercises WHERE processed = 0");
        $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = ['success' => 0, 'errors' => 0];

        foreach ($exercises as $exercise) {
            try {
                $parsed = $this->parseExercise($exercise['Content']);
                $this->updateDatabase($exercise['Id'], $parsed);
                $stats['success']++;
            } catch (Exception $e) {
                $stats['errors']++;
                error_log("Erreur parsing exercice {$exercise['Id']}: " . $e->getMessage());
            }
        }

        return $stats;
    }
}
