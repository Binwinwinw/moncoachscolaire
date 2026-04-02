
<?php
/**
 * Vérifie si un niveau correspond au BAC (Terminale ou BAC)
 * @param string $level
 * @return bool
 */
function is_bac_level($level)
{
    return normalize_school_level($level) === 'Terminale';
}
/**
 * Normalisation des niveaux scolaires
 * Gère les problèmes d'encodage et uniformise les niveaux
 */

/**
 * Normalise un niveau scolaire pour éviter les problèmes d'encodage
 * Convertit les accents en version sans accent pour comparaisons
 *
 * @param string $level Niveau brut (peut contenir des accents mal encodés)
 * @return string Niveau normalisé sans accent
 */
function normalize_school_level($level)
{
    // Mapping des niveaux avec et sans accents
    $levelMapping = [
        // Mapping des niveaux avec et sans accents

        // Collège - avec accents
        '6ème' => '6eme',
        '5ème' => '5eme',
        '4ème' => '4eme',
        '3ème' => '3eme',
        // Collège - déjà sans accents
        '6eme' => '6eme',
        '5eme' => '5eme',
        '4eme' => '4eme',
        '3eme' => '3eme',
        // Collège - avec encodage cassé
        '6??me' => '6eme',
        '5??me' => '5eme',
        '4??me' => '4eme',
        '3??me' => '3eme',
        // Collège - variantes génériques
        'college' => '6eme','5eme' => '5eme','4eme' => '4eme','3eme' => '3eme',
        'collège' => '6eme','5ème' => '5eme','4ème' => '4eme','3ème' => '3eme',
        'collége' => '6eme','5éme' => '5eme','4éme' => '4eme','3éme' => '3eme',
        'collèges' => '6eme','5èmes' => '5eme','4èmes' => '4eme','3èmes' => '3eme',
        // Brevet des collèges (fin de collège)
        'brevet' => '3eme',
        'Brevet' => '3eme',
        'brevet des colleges' => '3eme',
        'Brevet des colleges' => '3eme',
        'brevet des collèges' => '3eme',
        'Brevet des collèges' => '3eme',
        'dnb' => '3eme',
        'DNB' => '3eme',
        // Lycée - avec accents
        'Seconde' => 'Seconde','seconde' => 'Seconde',
        'Première' => 'Premiere','première' => 'Premiere',
        'Terminale' => 'Terminale','terminale' => 'Terminale',
        // Lycée - sans accents
        'lycee' => 'Seconde','Premiere' => 'Premiere','Terminale' => 'Terminale',
        'lycée' => 'Seconde',
        'lycees' => 'Seconde',
        'lycées' => 'Seconde',
        // Nouvelles variantes lycée (compatibilité UserLevel)
        '2nd' => 'Seconde',
        '2nde' => 'Seconde',
        '1ere' => 'Premiere',
        '1ère' => 'Premiere',
        // Variantes
        '2nde' => 'Seconde',
        '1ère' => 'Premiere',
        '1ere' => 'Premiere',
        'Tale' => 'Terminale',
        // BAC (toutes variantes renvoient à Terminale)
        'BAC' => 'Terminale',
        'Bac' => 'Terminale',
        'bac' => 'Terminale',
        'BACCALAUREAT' => 'Terminale',
        'baccalaureat' => 'Terminale',
        'baccalauréat' => 'Terminale',
        'baccalauréat' => 'Terminale',
        'terminale' => 'Terminale',
        'Terminale' => 'Terminale',
        'TERM' => 'Terminale',
        'term' => 'Terminale',
        'tale' => 'Terminale',
        'Tale' => 'Terminale',
    ];

    $levelClean = trim($level);

    // Si mapping direct trouvé
    if (isset($levelMapping[$levelClean])) {
        return $levelMapping[$levelClean];
    }

    // Nettoyage générique des caractères spéciaux mal encodés
    $normalized = str_replace(['è', 'é', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'û', 'ü', 'ç', '??'], ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'c', 'e'], $levelClean);

    // Si après nettoyage on trouve un mapping
    if (isset($levelMapping[$normalized])) {
        return $levelMapping[$normalized];
    }

    // Mapping générique lycée : toute valeur contenant 'terminale', 'premiere', 'seconde' (insensible à la casse)
    $normLower = mb_strtolower($normalized);
    if (strpos($normLower, 'terminale') !== false) {
        return 'Terminale';
    }
    if (strpos($normLower, 'premiere') !== false) {
        return 'Premiere';
    }
    if (strpos($normLower, 'seconde') !== false) {
        return 'Seconde';
    }

    return $normalized;
}

/**
 * Obtient le nom d'affichage du niveau (avec accents corrects)
 *
 * @param string $level Niveau normalisé ou brut
 * @return string Nom d'affichage avec accents UTF-8
 */
function get_level_display_name($level)
{
    $normalized = normalize_school_level($level);

    $displayNames = [
        '6eme' => '6ème',
        '5eme' => '5ème',
        '4eme' => '4ème',
        '3eme' => '3ème',
        'Seconde' => 'Seconde',
        'Premiere' => 'Première',
        'Terminale' => 'Terminale',
    ];

    return $displayNames[$normalized] ?? $normalized;
}

/**
 * Compare deux niveaux en normalisant d'abord
 *
 * @param string $level1
 * @param string $level2
 * @return bool True si les niveaux correspondent
 */
function levels_match($level1, $level2)
{
    return normalize_school_level($level1) === normalize_school_level($level2);
}

/**
 * Vérifie si un niveau appartient au collège
 *
 * @param string $level
 * @return bool
 */
function is_college_level($level)
{
    $normalized = normalize_school_level($level);
    return in_array($normalized, ['6eme', '5eme', '4eme', '3eme']);
}

/**
 * Vérifie si un niveau appartient au lycée
 *
 * @param string $level
 * @return bool
 */
function is_lycee_level($level)
{
    $normalized = normalize_school_level($level);
    return in_array($normalized, ['Seconde', 'Premiere', 'Terminale', '2nd', '2nde', '1ere', '1ère']);
}
?>
