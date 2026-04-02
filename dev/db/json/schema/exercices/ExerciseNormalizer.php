<?php
/**
 * =============================================================
 *  MonCoachScolaire — Exercise Normalizer (Version BDD)
 * =============================================================
 *  Module de normalisation des exercices pour garantir
 *  la conformité au schéma réel de la BDD.
 *
 *  Utilise les VRAIS noms de colonnes :
 *    - Subject (pas subject)
 *    - AnswerType (pas answer_type)
 *    - XP_Points (pas xp_points)
 *    - etc.
 * =============================================================
 */

declare(strict_types=1);

class ExerciseNormalizer
{
    /**
     * Schéma réel de la table exercises (colonnes BDD)
     */
    private const DB_SCHEMA = [
        // Colonnes obligatoires
        'Id' => null,
        'Identifier' => '',
        'Subject' => '',
        'Level' => '',
        'Title' => '',
        'Content' => '',
        'Answer' => '',
        'Difficulty' => 'moyen',
        'AnswerType' => 'texte',
        'XP_Points' => 0,
        'is_active' => 1,

        // Colonnes optionnelles/spécifiques
        'Tips' => null,
        'Domain' => null,
        'Competence' => null,
        'structure_type' => null,
        'pattern_detected' => null,
        'sub_questions' => null,
        'Type' => null,
        'InteractiveConfig' => null,
        'Choices' => null,
        'Instruction' => null,
        'exam_prep' => null,
        'Coherence' => null,
        'processed' => null,
        'course_id' => null,
        'LinkedCourses' => null
    ];

    /**
     * Mapping : Noms alternatifs → Noms BDD officiels
     */
    private const FIELD_MAPPING = [
        // Noms snake_case → BDD
        'id' => 'Id',
        'identifier' => 'Identifier',
        'subject' => 'Subject',
        'level' => 'Level',
        'title' => 'Title',
        'content' => 'Content',
        'answer' => 'Answer',
        'tips' => 'Tips',
        'domain' => 'Domain',
        'competence' => 'Competence',
        'difficulty' => 'Difficulty',
        'answer_type' => 'AnswerType',
        'xp_points' => 'XP_Points',
        'choices' => 'Choices',
        'instruction' => 'Instruction',

        // Variantes possibles
        'sujet' => 'Subject',
        'matiere' => 'Subject',
        'niveau' => 'Level',
        'titre' => 'Title',
        'contenu' => 'Content',
        'reponse' => 'Answer',
        'conseils' => 'Tips',
        'domaine' => 'Domain',
        'difficulte' => 'Difficulty',
        'type_reponse' => 'AnswerType',
        'points' => 'XP_Points',
        'points_xp' => 'XP_Points',
        'actif' => 'is_active',
        'active' => 'is_active'
    ];

    /**
     * Champs obligatoires pour validation
     */
    private const REQUIRED_FIELDS = [
        'Identifier',
        'Subject',
        'Level',
        'Title',
        'Content',
        'Answer'
    ];

    /**
     * Normalise un exercice vers le schéma BDD
     *
     * @param array $exercise Exercice brut (n'importe quel format)
     * @return array Exercice normalisé avec noms de colonnes BDD
     */
    public static function normalizeToDbFields(array $exercise): array
    {
        $normalized = self::DB_SCHEMA;

        // Mapping des champs
        foreach ($exercise as $key => $value) {
            // Si le champ existe directement dans le schéma BDD
            if (array_key_exists($key, $normalized)) {
                $normalized[$key] = $value;
            }
            // Si c'est un nom alternatif (snake_case, français, etc.)
            else if (isset(self::FIELD_MAPPING[$key])) {
                $dbKey = self::FIELD_MAPPING[$key];
                $normalized[$dbKey] = $value;
            }
        }

        // Normalisation des types et valeurs
        $normalized = self::normalizeTypes($normalized);
        $normalized = self::normalizeEnums($normalized);

        return $normalized;
    }

    /**
     * Normalise un tableau d'exercices
     *
     * @param array $exercises Liste d'exercices
     * @return array Liste normalisée
     */
    public static function normalizeAllToDbFields(array $exercises): array
    {
        return array_map([self::class, 'normalizeToDbFields'], $exercises);
    }

    /**
     * Normalise les types de données
     */
    private static function normalizeTypes(array $exercise): array
    {
        // INT
        if ($exercise['Id'] !== null) {
            $exercise['Id'] = (int) $exercise['Id'];
        }
        $exercise['XP_Points'] = (int) ($exercise['XP_Points'] ?? 0);
        $exercise['is_active'] = (int) ($exercise['is_active'] ?? 1);

        // STRING
        $stringFields = [
            'Identifier', 'Subject', 'Level', 'Title', 'Content', 'Answer',
            'Tips', 'Domain', 'Competence', 'Difficulty', 'AnswerType',
            'structure_type', 'Type', 'Instruction'
        ];

        foreach ($stringFields as $field) {
            if ($exercise[$field] !== null && !is_string($exercise[$field])) {
                $exercise[$field] = (string) $exercise[$field];
            }
        }

        // JSON : si c'est déjà un string JSON valide, le garder
        $jsonFields = ['Choices', 'InteractiveConfig', 'LinkedCourses', 'pattern_detected', 'sub_questions'];

        foreach ($jsonFields as $field) {
            if ($exercise[$field] !== null && is_array($exercise[$field])) {
                $exercise[$field] = json_encode($exercise[$field], JSON_UNESCAPED_UNICODE);
            }
        }

        return $exercise;
    }

    /**
     * Normalise les valeurs ENUM et les valeurs spéciales
     */
    private static function normalizeEnums(array $exercise): array
    {
        // Difficulty : facile, moyen, difficile (et variantes à nettoyer)
        $difficulty = strtolower(trim($exercise['Difficulty'] ?? 'moyen'));

        // Mapping des variantes
        $difficultyMap = [
            'facile' => 'facile',
            'easy' => 'facile',
            'moyen' => 'moyen',
            'medium' => 'moyen',
            'difficile' => 'difficile',
            'hard' => 'difficile',
            'a_verifier' => 'moyen',  // Par défaut
            '★' => 'facile',
            '★★' => 'moyen',
            '★★★' => 'difficile',
            '' => 'moyen'
        ];

        $exercise['Difficulty'] = $difficultyMap[$difficulty] ?? 'moyen';

        // AnswerType : texte, qcm, calcul, etc.
        $answerType = strtolower(trim($exercise['AnswerType'] ?? 'texte'));

        $answerTypeMap = [
            'texte' => 'texte',
            'text' => 'texte',
            'qcm' => 'qcm',
            'qcm_multiple' => 'qcm',
            'choice' => 'qcm',
            'calcul' => 'calcul',
            'calcule' => 'calcul',
            'number' => 'calcul',
            'case_a_cocher' => 'qcm',
            'mixte' => 'texte',
            'association' => 'texte',
            '' => 'texte',
            'unknown' => 'texte'
        ];

        $exercise['AnswerType'] = $answerTypeMap[$answerType] ?? 'texte';

        // Subject : harmoniser la casse
        if (!empty($exercise['Subject'])) {
            $exercise['Subject'] = self::normalizeSubject($exercise['Subject']);
        }

        // Level : harmoniser les accents
        if (!empty($exercise['Level'])) {
            $exercise['Level'] = self::normalizeLevel($exercise['Level']);
        }

        return $exercise;
    }

    /**
     * Normalise le nom de la matière (harmonisation de la casse)
     */
    public static function normalizeSubject(string $subject): string
    {
        $subjectMap = [
            'mathématiques' => 'Mathématiques',
            'mathematiques' => 'Mathématiques',
            'maths' => 'Mathématiques',
            'français' => 'Français',
            'francais' => 'Français',
            'anglais' => 'Anglais',
            'sciences' => 'Sciences',
            'svt' => 'SVT',
            'physique-chimie' => 'Physique-Chimie',
            'histoire-geographie' => 'Histoire-Géographie',
            'histoire-géographie' => 'Histoire-Géographie',
            'histoire' => 'Histoire',
            'philosophie' => 'Philosophie',
            'grand oral' => 'Grand Oral',
            'culture générale' => 'Culture Générale',
            'culture generale' => 'Culture Générale'
        ];

        $lower = strtolower(trim($subject));
        return $subjectMap[$lower] ?? ucfirst($subject);
    }

    /**
     * Normalise le niveau (harmonisation des accents)
     */
    public static function normalizeLevel(string $level): string
    {
        $levelMap = [
            '6eme' => '6ème',
            '6ème' => '6ème',
            '5eme' => '5ème',
            '5ème' => '5ème',
            '4eme' => '4ème',
            '4ème' => '4ème',
            '3eme' => '3ème',
            '3ème' => '3ème',
            'seconde' => 'Seconde',
            '2nde' => 'Seconde',
            '2nd' => 'Seconde',
            'premiere' => 'Première',
            'première' => 'Première',
            '1ere' => 'Première',
            '1ère' => 'Première',
            'terminale' => 'Terminale'
        ];

        $lower = strtolower(trim($level));
        return $levelMap[$lower] ?? ucfirst($level);
    }

    /**
     * Normalise le type de réponse
     */
    public static function normalizeAnswerType(string $answerType): string
    {
        $map = [
            'texte' => 'texte',
            'text' => 'texte',
            'qcm' => 'qcm',
            'qcm_multiple' => 'qcm',
            'choice' => 'qcm',
            'calcul' => 'calcul',
            'calcule' => 'calcul',
            'number' => 'calcul',
            'case_a_cocher' => 'qcm',
            'mixte' => 'texte',
            'association' => 'texte'
        ];

        $lower = strtolower(trim($answerType));
        return $map[$lower] ?? 'texte';
    }

    /**
     * Vérifie si un exercice est valide (champs BDD obligatoires remplis)
     *
     * @param array $exercise Exercice normalisé
     * @return bool True si valide
     */
    public static function isValidDb(array $exercise): bool
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($exercise[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Retourne la liste des champs BDD obligatoires manquants
     *
     * @param array $exercise Exercice à vérifier
     * @return array Liste des champs manquants
     */
    public static function getMissingDbFields(array $exercise): array
    {
        $missing = [];
        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($exercise[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }

    /**
     * Génère un rapport de conformité (champs BDD)
     *
     * @param array $exercises Liste d'exercices
     * @return array Rapport de conformité
     */
    public static function getConformityReportDb(array $exercises): array
    {
        $report = [
            'total' => count($exercises),
            'valid' => 0,
            'invalid' => 0,
            'missing_fields' => [],
            'invalid_exercises' => []
        ];

        foreach ($exercises as $index => $exercise) {
            if (self::isValidDb($exercise)) {
                $report['valid']++;
            } else {
                $report['invalid']++;
                $missing = self::getMissingDbFields($exercise);
                $identifier = $exercise['Identifier'] ?? "UNKNOWN-$index";

                $report['invalid_exercises'][] = [
                    'index' => $index,
                    'identifier' => $identifier,
                    'missing' => $missing
                ];

                foreach ($missing as $field) {
                    $report['missing_fields'][$field] = ($report['missing_fields'][$field] ?? 0) + 1;
                }
            }
        }

        return $report;
    }

    /**
     * Prépare un exercice pour l'insertion en BDD
     * (Convertit les tableaux JSON en string)
     *
     * @param array $exercise Exercice normalisé
     * @return array Exercice prêt pour la BDD
     */
    public static function prepareForDatabase(array $exercise): array
    {
        // Convertir les champs JSON en string pour MySQL (si ce sont des tableaux)
        $jsonFields = ['Choices', 'InteractiveConfig', 'LinkedCourses', 'pattern_detected', 'sub_questions'];

        foreach ($jsonFields as $field) {
            if (isset($exercise[$field]) && is_array($exercise[$field])) {
                $exercise[$field] = json_encode($exercise[$field], JSON_UNESCAPED_UNICODE);
            }
        }

        // Retirer les champs qui ne sont pas dans la table (si ajoutés par les scripts)
        unset($exercise['_source_file']);
        unset($exercise['_source']);

        return $exercise;
    }

    /**
     * Retourne le schéma BDD
     *
     * @return array Schéma BDD
     */
    public static function getDbSchema(): array
    {
        return self::DB_SCHEMA;
    }

    /**
     * Retourne les champs obligatoires
     *
     * @return array Champs obligatoires
     */
    public static function getRequiredFields(): array
    {
        return self::REQUIRED_FIELDS;
    }

    /**
     * Nettoie les données d'un exercice (harmonisation globale)
     *
     * @param array $exercise Exercice à nettoyer
     * @return array Exercice nettoyé
     */
    public static function cleanExercise(array $exercise): array
    {
        // Normaliser d'abord vers BDD
        $exercise = self::normalizeToDbFields($exercise);

        // Nettoyages supplémentaires

        // Trim des strings
        $stringFields = ['Subject', 'Level', 'Title', 'Content', 'Answer', 'Tips', 'Domain', 'Competence', 'Difficulty', 'AnswerType'];
        foreach ($stringFields as $field) {
            if (isset($exercise[$field]) && is_string($exercise[$field])) {
                $exercise[$field] = trim($exercise[$field]);
            }
        }

        // Nettoyer les valeurs vides
        if (empty($exercise['Tips'])) {
            $exercise['Tips'] = null;
        }
        if (empty($exercise['Domain'])) {
            $exercise['Domain'] = null;
        }
        if (empty($exercise['Competence'])) {
            $exercise['Competence'] = null;
        }

        return $exercise;
    }

    /**
     * Nettoie un tableau d'exercices
     *
     * @param array $exercises Liste d'exercices
     * @return array Liste nettoyée
     */
    public static function cleanAllExercises(array $exercises): array
    {
        return array_map([self::class, 'cleanExercise'], $exercises);
    }
}
