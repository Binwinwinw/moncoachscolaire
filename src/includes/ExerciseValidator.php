<?php

/**
 * CLASSE VALIDATEUR D'EXERCICES
 * À utiliser avant toute insertion/modification d'exercices
 * Prévient les réponses incohérentes et vides
 */

class ExerciseValidator
{
    /**
     * Patterns de réponses problématiques
     */
    private static $badPatterns = [
        'placeholder' => [
            'pattern' => '/^(a\)|b\)|c\)|d\))/i',
            'description' => 'Réponse commence par a), b), c), d)',
        ],
        'liste_simple' => [
            'pattern' => '/^[a-d]\).*\n[a-d]\).*\n[a-d]\).*\n[a-d]\).*/i',
            'description' => 'Liste a) b) c) d) sans explication',
        ],
        'vide' => [
            'pattern' => '/^[\s\r\n]*$/',
            'description' => 'Réponse vide ou espaces uniquement',
        ],
        'trop_court' => [
            'pattern' => '/^.{1,15}$/',
            'description' => 'Réponse trop courte (< 15 caractères)',
        ],
        'numero_seul' => [
            'pattern' => '/^[0-9]+$/',
            'description' => 'Réponse = numéro uniquement',
        ],
        'caracteres_etranges' => [
            'pattern' => '/[\x00-\x08\x0B\x0C\x0E-\x1F]/',
            'description' => 'Caractères de contrôle suspects',
        ],
    ];

    /**
     * Validation complète d'un exercice
     *
     * @param array $exercise Tableau avec keys: title, content, answer, level, subject
     * @return array ['valid' => bool, 'errors' => array, 'warnings' => array]
     */
    public static function validate($exercise)
    {
        $errors = [];
        $warnings = [];

        // Champs obligatoires
        $required = ['title', 'content', 'answer', 'level', 'subject'];
        foreach ($required as $field) {
            if (empty($exercise[$field])) {
                $errors[] = "Champ obligatoire manquant: $field";
            }
        }

        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Validation de la réponse
        $answerCheck = self::validateAnswer($exercise['answer']);
        if (!$answerCheck['valid']) {
            $errors = array_merge($errors, $answerCheck['errors']);
        }
        $warnings = array_merge($warnings, $answerCheck['warnings']);

        // Validation du contenu
        $contentCheck = self::validateContent($exercise['content']);
        $warnings = array_merge($warnings, $contentCheck['warnings']);

        // Cohérence contenu/réponse
        $coherenceCheck = self::checkCoherence($exercise['content'], $exercise['answer']);
        $warnings = array_merge($warnings, $coherenceCheck['warnings']);

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validation de la réponse
     */
    private static function validateAnswer($answer)
    {
        $errors = [];
        $warnings = [];

        foreach (self::$badPatterns as $key => $pattern) {
            if (preg_match($pattern['pattern'], $answer)) {
                $errors[] = "⚠️ Réponse problématique détectée: " . $pattern['description'];
            }
        }

        // Réponse doit contenir des explications (minimum 30 caractères)
        if (strlen($answer) < 30) {
            $warnings[] = "💡 Réponse courte (< 30 caractères). Pensez à ajouter des explications.";
        }

        return ['valid' => count($errors) === 0, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Validation du contenu
     */
    private static function validateContent($content)
    {
        $warnings = [];

        if (strlen($content) < 20) {
            $warnings[] = "💡 Contenu très court (< 20 caractères)";
        }

        return ['warnings' => $warnings];
    }

    /**
     * Vérifier cohérence contenu/réponse
     */
    private static function checkCoherence($content, $answer)
    {
        $warnings = [];

        // Si QCM, vérifier que la réponse mentionne a), b), c) ou d)
        if (preg_match('/a\).*b\).*c\).*d\)/is', $content)) {
            if (!preg_match('/[abcd]\)/i', $answer)) {
                $warnings[] = "⚠️ QCM détecté mais la réponse ne mentionne pas a), b), c) ou d)";
            }
        }

        return ['warnings' => $warnings];
    }

    /**
     * Validation rapide (retourne bool uniquement)
     */
    public static function isValid($exercise)
    {
        $result = self::validate($exercise);
        return $result['valid'];
    }
}
