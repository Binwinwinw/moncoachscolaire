<?php
/**
 * =============================================================
 *  MonCoachScolaire — Schema Mapper
 * =============================================================
 *  Fait la correspondance entre les colonnes réelles de la BDD
 *  (avec majuscules/minuscules variées) et le schéma officiel
 *  normalisé utilisé par les scripts.
 * =============================================================
 */

declare(strict_types=1);

class SchemaMapper
{
    /**
     * Mapping : Nom BDD réel → Nom normalisé
     */
    private const DB_TO_NORMALIZED = [
        // Colonnes de base
        'Id' => 'id',
        'Subject' => 'subject',
        'Level' => 'level',
        'Title' => 'title',
        'Content' => 'content',
        'Answer' => 'answer',
        'Tips' => 'tips',
        'Domain' => 'domain',
        'Competence' => 'competence',
        'Difficulty' => 'difficulty',
        'Identifier' => 'identifier',
        'AnswerType' => 'answer_type',
        'XP_Points' => 'xp_points',
        'is_active' => 'is_active',
        
        // Colonnes spécifiques BDD (à mapper vers normalized ou null)
        'structure_type' => 'question_type',      // → question_type
        'pattern_detected' => 'metadata',         // → metadata (JSON)
        'sub_questions' => 'metadata',            // → metadata (JSON)
        'Type' => 'question_type',                // → question_type
        'InteractiveConfig' => 'metadata',        // → metadata (JSON)
        'Choices' => 'choices',
        'Instruction' => 'content',               // Fusionner avec content
        'exam_prep' => 'tags',                    // → tags (JSON)
        'Coherence' => 'metadata',                // → metadata (JSON)
        'processed' => 'metadata',                // → metadata (JSON)
        'course_id' => 'metadata',                // → metadata (JSON)
        'LinkedCourses' => 'resources',           // → resources (JSON)
        
        // Colonnes du schéma officiel manquantes dans la BDD
        // → Seront ajoutées avec valeurs par défaut
    ];
    
    /**
     * Colonnes du schéma officiel (normalized) qui n'existent pas dans la BDD
     * → Seront créées avec valeurs par défaut
     */
    private const MISSING_IN_DB = [
        'chapter' => null,
        'sub_chapter' => null,
        'duration_minutes' => null,
        'correct_choices' => null,
        'explanation' => null,
        'created_at' => null,
        'updated_at' => null
    ];
    
    /**
     * Convertit un exercice BDD vers le format normalisé
     * 
     * @param array $dbExercise Exercice brut de la BDD
     * @return array Exercice normalisé
     */
    public static function fromDatabase(array $dbExercise): array
    {
        $normalized = [];
        
        // Mapping des colonnes existantes
        foreach ($dbExercise as $dbCol => $value) {
            $normalizedCol = self::DB_TO_NORMALIZED[$dbCol] ?? null;
            
            if ($normalizedCol) {
                // Cas spéciaux : fusion dans metadata/resources/tags (JSON)
                if (in_array($normalizedCol, ['metadata', 'resources', 'tags'])) {
                    if (!isset($normalized[$normalizedCol])) {
                        $normalized[$normalizedCol] = [];
                    }
                    
                    // Si la valeur est déjà un JSON, la décoder
                    if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $normalized[$normalizedCol] = array_merge($normalized[$normalizedCol], $decoded);
                        } else {
                            $normalized[$normalizedCol][$dbCol] = $value;
                        }
                    } else {
                        $normalized[$normalizedCol][$dbCol] = $value;
                    }
                }
                // Cas spécial : Instruction → fusionner avec content
                elseif ($dbCol === 'Instruction' && !empty($value)) {
                    $normalized['content'] = ($normalized['content'] ?? '') . "\n\n" . $value;
                }
                // Cas standard : mapping 1:1
                else {
                    $normalized[$normalizedCol] = $value;
                }
            }
        }
        
        // Ajouter les colonnes manquantes du schéma officiel
        foreach (self::MISSING_IN_DB as $col => $defaultValue) {
            if (!isset($normalized[$col])) {
                $normalized[$col] = $defaultValue;
            }
        }
        
        // Convertir les tableaux JSON en string si nécessaire
        if (isset($normalized['metadata']) && is_array($normalized['metadata'])) {
            $normalized['metadata'] = json_encode($normalized['metadata'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($normalized['resources']) && is_array($normalized['resources'])) {
            $normalized['resources'] = json_encode($normalized['resources'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($normalized['tags']) && is_array($normalized['tags'])) {
            $normalized['tags'] = json_encode($normalized['tags'], JSON_UNESCAPED_UNICODE);
        }
        
        return $normalized;
    }
    
    /**
     * Convertit un exercice normalisé vers le format BDD
     * 
     * @param array $normalized Exercice normalisé
     * @return array Exercice au format BDD
     */
    public static function toDatabase(array $normalized): array
    {
        $dbExercise = [];
        
        // Mapping inverse
        $normalizedToDb = array_flip(self::DB_TO_NORMALIZED);
        
        foreach ($normalized as $normCol => $value) {
            $dbCol = $normalizedToDb[$normCol] ?? null;
            
            if ($dbCol) {
                // Décoder les JSON si nécessaire
                if (in_array($normCol, ['metadata', 'resources', 'tags', 'choices', 'correct_choices'])) {
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $value = $decoded;
                        }
                    }
                    // Si c'est un tableau, re-encoder pour la BDD
                    if (is_array($value)) {
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                }
                
                $dbExercise[$dbCol] = $value;
            }
        }
        
        return $dbExercise;
    }
    
    /**
     * Retourne la liste des colonnes BDD à extraire dans SELECT
     * 
     * @return string Colonnes séparées par des virgules
     */
    public static function getSelectColumns(): string
    {
        return implode(', ', array_keys(self::DB_TO_NORMALIZED));
    }
    
    /**
     * Retourne le mapping complet
     * 
     * @return array Mapping DB → Normalized
     */
    public static function getMapping(): array
    {
        return self::DB_TO_NORMALIZED;
    }
}
