<?php
// Correction d'encodage des exercices en BDD (UTF-8)
// Usage : php dev/tools/fix_exercises_encoding.php

require_once __DIR__ . '/../../db/connection.php';

// Mode rapport si --report passé en argument
$reportMode = in_array('--report', $argv);

function fix_encoding($str) {
    // Corrige les caractères mal encodés (ex: Ã© → é)
    if ($str === null) return null;
    $str = mb_convert_encoding($str, 'UTF-8', 'auto');
    // Suppression des points d'interrogation isolés ou doubles
    $str = str_replace('??', '', $str);
    $str = str_replace('?', '', $str);
    // Remplacements spécifiques pour les séquences mal encodées
    $str = str_replace([
        'Franais', 'Francais', 'Franais',
        'Fran??ais', 'Fran?ais', 'fran??ais', 'fran?ais', 'FRANCAIS', 'FRANÇAIS', 'francais', 'français',
        '6me', '6eme', '6eme',
        '5me', '5eme',
        '4me', '4eme',
        '3me', '3eme',
        'lments', 'elements',
        'prsent', 'present',
        'tude', 'etude',
        'Units', 'Unites',
        'differentes unites',
        'gomtrie', 'geometrie', 'Gomtrie', 'GOMTRIE',
        'Gographie', 'Geographie',
        'Premire', 'Premiere',
        'Proportionnalit', 'Proportionnalite',
        'Reconnatre', 'Reconnaitre',
        'Probabilits', 'Probabilites',
        'Thorme', 'thorme', 'THORME',
        'Mathmatiques', 'Mathematiques', 'mathmatiques', 'mathematiques',
        'valuation', 'VALUATION', 'valuation',
        'prcise', 'prciser', 'prcis',
        'subordonne', 'subordonnee',
        'lves', 'leves',
        'coecient', 'coefficient',
        'dtermine', 'determine',
        'rsolution', 'resolution',
        'vnements', 'evenements',
        'rcrit', 'recrit',
        'rponse', 'reponse',
        'russite', 'reussite',
        'rgle', 'regle',
        'russir', 'reussir',
        'russies', 'reussies',
        'russis', 'reussis',
        'é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç'
    ], [
        'Français', 'Français', 'Français',
        'Français', 'Français', 'Français', 'Français', 'Français', 'Français', 'Français', 'Français',
        '6ème', '6ème', '6ème',
        '5ème', '5ème',
        '4ème', '4ème',
        '3ème', '3ème',
        'éléments', 'éléments',
        'présent', 'présent',
        'étude', 'étude',
        'Unités', 'Unités',
        'différentes unités',
        'géométrie', 'géométrie', 'Géométrie', 'GÉOMÉTRIE',
        'Géographie', 'Géographie',
        'Première', 'Première',
        'Proportionnalité', 'Proportionnalité',
        'Reconnaître', 'Reconnaître',
        'Probabilités', 'Probabilités',
        'Théorème', 'théorème', 'THÉORÈME',
        'Mathématiques', 'Mathématiques', 'Mathématiques', 'Mathématiques',
        'évaluation', 'ÉVALUATION', 'évaluation',
        'précise', 'préciser', 'précis',
        'subordonnée', 'subordonnée',
        'élèves', 'élèves',
        'coefficient', 'coefficient',
        'détermine', 'détermine',
        'résolution', 'résolution',
        'événements', 'événements',
        'récrit', 'récrit',
        'réponse', 'réponse',
        'réussite', 'réussite',
        'règle', 'règle',
        'réussir', 'réussir',
        'réussies', 'réussies',
        'réussis', 'réussis',
        'é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç'
    ], $str);
    return $str;
}


$sql = "SELECT Id, Title, Content, Instruction, Answer, Tips, Subject, Level FROM Exercises";
$result = $pdo->query($sql);

$error_patterns = ['??', '?', 'Fran', 'tude', 'Unit', 'lments', 'prsent', 'gom', 'Gograph', 'Premi', 'Proportionnalit', 'Reconn', 'Probabilit', 'me', 'eme'];
$error_count = 0;
$error_details = [];
$updated = 0;
foreach ($result as $row) {
    $fields = ['Title', 'Content', 'Instruction', 'Answer', 'Tips', 'Subject', 'Level'];
    $updates = [];
    foreach ($fields as $field) {
        if ($row[$field] === null) continue;
        $fixed = fix_encoding($row[$field]);
        if ($reportMode) {
            foreach ($error_patterns as $pat) {
                if (stripos($row[$field], $pat) !== false) {
                    $error_count++;
                    $error_details[] = "Id: {$row['Id']} Champ: $field => ".mb_substr($row[$field],0,80);
                    break;
                }
            }
        } else {
            if ($fixed !== $row[$field]) {
                $updates[$field] = $fixed;
            }
        }
    }
    if (!$reportMode && !empty($updates)) {
        $set = [];
        foreach ($updates as $k => $v) {
            $set[] = "$k = " . $pdo->quote($v);
        }
        $sqlUpdate = "UPDATE Exercises SET " . implode(', ', $set) . " WHERE Id = " . intval($row['Id']);
        $pdo->exec($sqlUpdate);
        $updated++;
    }
}

if ($reportMode) {
    echo "Erreurs détectées : $error_count\n";
    foreach ($error_details as $err) {
        echo $err."\n";
    }
} else {
    echo "Correction terminée. Exercices modifiés : $updated\n";
}
