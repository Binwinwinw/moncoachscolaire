<?php
/**
 * =============================================================
 *  MonCoachScolaire — Exercise Normalizer
 * =============================================================
 *  Module de normalisation des exercices pour garantir
 *  la conformité au schéma officiel de 27 champs.
 *  
 *  Utilisé par tous les scripts de la chaîne :
 *    - collect_exercises.php
 *    - deduplicate_exercises.php
 *    - import_unified_exercises.php
 *    - export_exercises_from_db.php
 * =============================================================
 */

declare(strict_types=1);

class ExerciseNormalizer
{
    /**
     * Schéma officiel de la table exercises (27 champs)
     * Basé sur exercises_schema.json
     */
    private const OFFICIAL_SCHEMA = [
        // Champs obligatoires
        'id' => null,                           // INT (auto-increment, null pour nouveaux exercices)
        'identifier' => '',                     // VARCHAR(100) UNIQUE
        'subject' => '',                        // VARCHAR(50)
        'level' => '',                          // VARCHAR(50)
        'title' => '',                          // VARCHAR(255)
        'content' => '',                        // TEXT
        'answer' => '',                         // TEXT
        'difficulty' => 'moyen',                // ENUM('facile','moyen','difficile')
        'answer_type' => 'text',                // ENUM('text','choice','multiple','number')
        'xp_points' => 0,                       // INT
        'is_active' => 1,                       // TINYINT(1)
        
        // Champs optionnels
        'tips' => null,                         // TEXT NULL
        'domain' => null,                       // VARCHAR(100) NULL
        'competence' => null,                   // VARCHAR(100) NULL
        'chapter' => null,                      // VARCHAR(255) NULL
        'sub_chapter' => null,                  // VARCHAR(255) NULL
        'duration_minutes' => null,             // INT NULL
        'question_type' => null,                // VARCHAR(50) NULL
        'choices' => null,                      // JSON NULL
        'correct_choices' => null,              // JSON NULL
        'explanation' => null,                  // TEXT NULL
        'resources' => null,                    // JSON NULL
        'tags' => null,                         // JSON NULL
        'metadata' => null,                     // JSON NULL
        
        // Champs de timestamp
        'created_at' => null,                   // DATETIME
        'updated_at' => null,                   // DATETIME
        
        // Champ spécial pour traçabilité (non en BDD)
        '_source_file' => null                  // STRING (ajouté par les scripts)
    ];

    /**
     * Normalise un exercice pour garantir la présence de tous les champs
     * 
     * @param array $exercise Exercice brut (peut avoir des champs manquants)
     * @return array Exercice normalisé avec tous les 27 champs
     */
    public static function normalize(array $exercise): array
    {
        $normalized = self::OFFICIAL_SCHEMA;
        
        // Fusion avec les données existantes
        foreach ($exercise as $key => $value) {
            if (array_key_exists($key, $normalized)) {
                $normalized[$key] = $value;
            }
        }
        
        // Normalisation des types
        $normalized = self::normalizeTypes($normalized);
        
        // Validation des valeurs ENUM
        $normalized = self::normalizeEnums($normalized);
        
        return $normalized;
    }
    
    /**
     * Normalise les types de données
     */
    private static function normalizeTypes(array $exercise): array
    {
        // INT : id, xp_points, duration_minutes, is_active
        if ($exercise['id'] !== null) {
            $exercise['id'] = (int) $exercise['id'];
        }
        $exercise['xp_points'] = (int) ($exercise['xp_points'] ?? 0);
        $exercise['is_active'] = (int) ($exercise['is_active'] ?? 1);
        
        if ($exercise['duration_minutes'] !== null) {
            $exercise['duration_minutes'] = (int) $exercise['duration_minutes'];
        }
        
        // STRING : identifier, subject, level, title, content, answer, tips, domain, competence, etc.
        $stringFields = [
            'identifier', 'subject', 'level', 'title', 'content', 'answer',
            'tips', 'domain', 'competence', 'chapter', 'sub_chapter',
            'difficulty', 'answer_type', 'question_type', 'explanation', '_source_file'
        ];
        
        foreach ($stringFields as $field) {
            if ($exercise[$field] !== null && !is_string($exercise[$field])) {
                $exercise[$field] = (string) $exercise[$field];
            }
        }
        
        // JSON : choices, correct_choices, resources, tags, metadata
        $jsonFields = ['choices', 'correct_choices', 'resources', 'tags', 'metadata'];
        
        foreach ($jsonFields as $field) {
            if ($exercise[$field] !== null && is_string($exercise[$field])) {
                // Si c'est une chaîne JSON, la décoder
                $decoded = json_decode($exercise[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $exercise[$field] = $decoded;
                }
            }
        }
        
        return $exercise;
    }
    
    /**
     * Normalise les valeurs ENUM
     */
    private static function normalizeEnums(array $exercise): array
    {
        // Difficulty : 'facile', 'moyen', 'difficile'
        $validDifficulties = ['facile', 'moyen', 'difficile'];
        if (!in_array($exercise['difficulty'], $validDifficulties, true)) {
            $exercise['difficulty'] = 'moyen'; // Valeur par défaut
        }
        
        // Answer type : 'text', 'choice', 'multiple', 'number'
        $validAnswerTypes = ['text', 'choice', 'multiple', 'number'];
        if (!in_array($exercise['answer_type'], $validAnswerTypes, true)) {
            $exercise['answer_type'] = 'text'; // Valeur par défaut
        }
        
        return $exercise;
    }
    
    /**
     * Normalise un tableau d'exercices
     * 
     * @param array $exercises Liste d'exercices bruts
     * @return array Liste d'exercices normalisés
     */
    public static function normalizeAll(array $exercises): array
    {
        return array_map([self::class, 'normalize'], $exercises);
    }
    
    /**
     * Vérifie si un exercice est valide (champs obligatoires remplis)
     * 
     * @param array $exercise Exercice à valider
     * @return bool True si valide
     */
    public static function isValid(array $exercise): bool
    {
        $requiredFields = ['identifier', 'subject', 'level', 'title', 'content', 'answer'];
        
        foreach ($requiredFields as $field) {
            if (empty($exercise[$field])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Retourne la liste des champs manquants
     * 
     * @param array $exercise Exercice à vérifier
     * @return array Liste des champs manquants
     */
    public static function getMissingFields(array $exercise): array
    {
        $missing = [];
        $requiredFields = ['identifier', 'subject', 'level', 'title', 'content', 'answer'];
        
        foreach ($requiredFields as $field) {
            if (empty($exercise[$field])) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
    
    /**
     * Prépare un exercice pour l'insertion en BDD (retire les champs non-BDD)
     * 
     * @param array $exercise Exercice normalisé
     * @return array Exercice prêt pour la BDD
     */
    public static function prepareForDatabase(array $exercise): array
    {
        // Retirer les champs non-BDD
        unset($exercise['_source_file']);
        
        // Convertir les champs JSON en string pour MySQL
        $jsonFields = ['choices', 'correct_choices', 'resources', 'tags', 'metadata'];
        
        foreach ($jsonFields as $field) {
            if ($exercise[$field] !== null && is_array($exercise[$field])) {
                $exercise[$field] = json_encode($exercise[$field], JSON_UNESCAPED_UNICODE);
            }
        }
        
        return $exercise;
    }
    
    /**
     * Retourne le schéma officiel
     * 
     * @return array Schéma officiel
     */
    public static function getOfficialSchema(): array
    {
        return self::OFFICIAL_SCHEMA;
    }
    
    /**
     * Génère un rapport de conformité
     * 
     * @param array $exercises Liste d'exercices
     * @return array Rapport de conformité
     */
    public static function getConformityReport(array $exercises): array
    {
        $report = [
            'total' => count($exercises),
            'valid' => 0,
            'invalid' => 0,
            'missing_fields' => [],
            'invalid_exercises' => []
        ];
        
        foreach ($exercises as $index => $exercise) {
            if (self::isValid($exercise)) {
                $report['valid']++;
            } else {
                $report['invalid']++;
                $missing = self::getMissingFields($exercise);
                $identifier = $exercise['identifier'] ?? "UNKNOWN-$index";
                
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
}
