<?php

/**
 * src/utils/exercise_parser.php
 * Fonctions utilitaires pour parser les Identifiers d'exercices
 * Format attendu : SUJET-NIVEAU-COMPETENCE-NUMERO
 * Ex: MATHEMATIQUES-6EME-ADDITION-001
 */

/**
 * Parse l'identifier d'un exercice selon le format officiel
 * Format: SUJET-NIVEAU-COMPETENCE-NUMERO
 * Ex: MATHEMATIQUES-6EME-ADDITION-001
 *
 * @param string $identifier L'identifiant de l'exercice
 * @return array|null Tableau associatif avec subject, level, competence, number, raw
 */
function parseExerciseIdentifier($identifier)
{
    if (empty($identifier)) {
        return null;
    }

    // Nettoyage de base
    $identifier = trim($identifier);

    // Split par tiret
    $parts = explode('-', strtoupper($identifier));

    // Gestion des matières composées (Histoire-Géo, Physique-Chimie)
    if (count($parts) > 1) {
        if ($parts[0] === 'HISTOIRE' && isset($parts[1]) && $parts[1] === 'GEOGRAPHIE') {
            array_shift($parts); // Enlève HISTOIRE
            $parts[0] = 'HISTOIRE-GEOGRAPHIE'; // Remplace GEOGRAPHIE par le nom complet
        } elseif ($parts[0] === 'PHYSIQUE' && isset($parts[1]) && $parts[1] === 'CHIMIE') {
            array_shift($parts); // Enlève PHYSIQUE
            $parts[0] = 'PHYSIQUE-CHIMIE'; // Remplace CHIMIE par le nom complet
        }
    }

    // Validation stricte : minimum 4 parties
    if (count($parts) < 4) {
        return null;
    }

    // Format strict : SUJET-NIVEAU-COMPETENCE-NUMERO
    // Note: Competence peut contenir des tirets, donc on prend tout ce qui est entre Niveau et Numéro
    $subject = array_shift($parts);
    $level = array_shift($parts);
    $number = array_pop($parts);

    // Tout ce qui reste au milieu est la compétence (supporte les tirets dans la compétence)
    $competence = implode('-', $parts);

    return [
        'subject' => normalizeSubject($subject),
        'level' => normalizeLevel($level),
        'competence' => normalizeCompetence($competence),
        'number' => $number,
        'raw' => $identifier,
    ];
}

/**
 * Normalise le nom de la matière
 *
 * @param string $subject Matière brute (ex: MATHEMATIQUES, MATHS)
 * @return string Matière normalisée (ex: Mathématiques)
 */
function normalizeSubject($subject)
{
    $mapping = [
        'MATHEMATIQUES' => 'Mathématiques',
        'MATHS' => 'Mathématiques',
        'FRANCAIS' => 'Français',
        'PHYSIQUECHIMIE' => 'Physique-Chimie',
        'PHYSIQUE-CHIMIE' => 'Physique-Chimie',
        'PHYSIQUE' => 'Physique-Chimie',
        'CHIMIE' => 'Physique-Chimie',
        'SVT' => 'SVT',
        'HISTOIREGEO' => 'Histoire-Géo',
        'HISTOIREGEOGRAPHIE' => 'Histoire-Géo',
        'HISTOIRE-GEOGRAPHIE' => 'Histoire-Géo',
        'HISTOIRE' => 'Histoire-Géo',
        'GEOGRAPHIE' => 'Histoire-Géo',
        'ANGLAIS' => 'Anglais',
        'ESPAGNOL' => 'Espagnol',
        'ALLEMAND' => 'Allemand',
        'ITALIEN' => 'Italien',
        'SCIENCES' => 'Sciences',
        'PHILOSOPHIE' => 'Philosophie',
        'PHILO' => 'Philosophie',
        'EPS' => 'EPS',
        'ARTS' => 'Arts Plastiques',
        'MUSIQUE' => 'Musique',
        'TECHNOLOGIE' => 'Technologie',
        'TECH' => 'Technologie',
    ];

    // Nettoyer les tirets/underscores/espaces avant lookup
    $clean = str_replace(['-', '_', ' '], '', strtoupper($subject));

    return $mapping[$clean] ?? ucfirst(strtolower($subject));
}

/**
 * Normalise un niveau scolaire
 *
 * @param string $level Niveau brut (ex: 6EME, SIXIEME)
 * @return string Niveau normalisé (ex: 6eme)
 */
function normalizeLevel($level)
{
    $clean = strtoupper(trim($level));

    $mapping = [
        'SIXIEME' => '6eme',
        '6EME' => '6eme',
        '6E' => '6eme',
        'CINQUIEME' => '5eme',
        '5EME' => '5eme',
        '5E' => '5eme',
        'QUATRIEME' => '4eme',
        '4EME' => '4eme',
        '4E' => '4eme',
        'TROISIEME' => '3eme',
        '3EME' => '3eme',
        '3E' => '3eme',
        'SECONDE' => '2nde',
        '2NDE' => '2nde',
        '2DE' => '2nde',
        'PREMIERE' => '1ere',
        '1ERE' => '1ere',
        '1RE' => '1ere',
        'TERMINALE' => 'terminale',
        'TERM' => 'terminale',
        'BAC' => 'terminale',
    ];

    return $mapping[$clean] ?? strtolower($level);
}

/**
 * Normalise une compétence (chapitre du cours)
 *
 * @param string $competence Compétence brute (ex: ADDITION, GRAMMAIRE)
 * @return string Compétence normalisée (ex: Addition, Grammaire)
 */
function normalizeCompetence($competence)
{
    // Remplacer underscores et tirets par espaces
    $competence = str_replace(['_', '-'], ' ', $competence);

    // Nettoyer les espaces multiples
    $competence = preg_replace('/\s+/', ' ', $competence);

    // Capitaliser proprement (Première lettre de chaque mot)
    return ucwords(strtolower(trim($competence)));
}

/**
 * Valide si un identifier est bien formé
 *
 * @param string $identifier L'identifiant à valider
 * @return bool True si valide, False sinon
 */
function isValidExerciseIdentifier($identifier)
{
    return parseExerciseIdentifier($identifier) !== null;
}

/**
 * Extrait uniquement la compétence depuis un identifier
 *
 * @param string $identifier L'identifiant de l'exercice
 * @return string|null La compétence normalisée ou null si invalide
 */
function extractCompetenceFromIdentifier($identifier)
{
    $parsed = parseExerciseIdentifier($identifier);
    return $parsed ? $parsed['competence'] : null;
}
