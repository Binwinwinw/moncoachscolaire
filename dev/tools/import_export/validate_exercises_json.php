<?php
// validate_exercises_json.php
// Valide les fichiers JSON d'exercices dans db/json/exercices/ contre le schéma db/json/exercices/schema_parsing_exercice.json

// Chercher le schéma (prioriser le fichier officiel '.schema.json' si présent)
$possibleSchemaPaths = [
    __DIR__ . '/../../db/json/exercices/schema_parsing_exercice.schema.json',
    __DIR__ . '/../../db/json/exercices/schema_parsing_exercice.json',
    __DIR__ . '/../../../db/json/exercices/schema_parsing_exercice.schema.json',
    __DIR__ . '/../../../db/json/exercices/schema_parsing_exercice.json',
    __DIR__ . '/../db/json/exercices/schema_parsing_exercice.schema.json',
    __DIR__ . '/../db/json/exercices/schema_parsing_exercice.json',
    __DIR__ . '/../../../../db/json/exercices/schema_parsing_exercice.schema.json',
    __DIR__ . '/../../../../db/json/exercices/schema_parsing_exercice.json',
];
$schemaFile = null;
foreach ($possibleSchemaPaths as $p) {
    if (is_file($p)) { $schemaFile = $p; break; }
}
$dir = dirname($schemaFile ?: (__DIR__ . '/../../db/json/exercices'));


if (!is_file($schemaFile)) {
    fwrite(STDERR, "[ERREUR] Schéma introuvable: $schemaFile\n");
    exit(2);
}
$schemaJson = json_decode(file_get_contents($schemaFile), true);
if (!is_array($schemaJson)) {
    fwrite(STDERR, "[ERREUR] Schéma invalide (JSON attendu)\n");
    exit(2);
}
// If schema file is an array example, use first element mapping; else if proper JSON Schema, use it directly
if (isset($schemaJson[0]) && is_array($schemaJson[0])) {
    // older example-style schema: convert to JSON Schema minimal
    $sample = $schemaJson[0];
    $required = array_keys($sample);
    // build a simple JSON Schema object
    $schema = (object)[
        '$schema' => 'http://json-schema.org/draft-07/schema#',
        'type' => 'object',
        'properties' => new stdClass(),
        'required' => $required
    ];
    foreach ($sample as $k => $v) {
        $schema->properties->{$k} = (object)['type' => 'string'];
    }
    $schemaObj = $schema;
} else {
    // assume it's already a JSON Schema
    $schemaObj = json_decode(file_get_contents($schemaFile));
}

// Try to load Opis validator if available (check multiple vendor/autoload.php locations)
$useOpis = false;
$possibleAutoload = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
];
$autoloadFound = false;
foreach ($possibleAutoload as $p) {
    if (file_exists($p)) {
        require_once $p;
        $autoloadFound = true;
        break;
    }
}
if ($autoloadFound && class_exists('\Opis\JsonSchema\Validator')) {
    $validator = new \Opis\JsonSchema\Validator();
    $useOpis = true;
}

// Prefer normalized files (strict)
$normalizedOnly = glob($dir . DIRECTORY_SEPARATOR . '*.normalized.json');
if ($normalizedOnly && count($normalizedOnly) > 0) {
    $files = $normalizedOnly;
} else {
    $files = glob($dir . DIRECTORY_SEPARATOR . '*.json');
}
if (!$files) {
    echo "[INFO] Aucun fichier JSON trouvé dans $dir\n";
    exit(0);
}
// DEBUG: list files we will validate
$baseFiles = array_map(function($p){ return basename($p); }, $files);
echo "[DEBUG] fichiers pris en compte: " . implode(', ', array_slice($baseFiles,0,20)) . (count($baseFiles)>20? ' ...':'') . "\n";

$errors = [];
$total = 0;
$valid = 0;

foreach ($files as $f) {
    $total++;
    $name = basename($f);

    // Ignorer les fichiers de schéma/exemples
    if (preg_match('/schema|example|sample/i', $name)) {
        continue;
    }

    $json = @json_decode(file_get_contents($f), true);
    if ($json === null) {
        $errors[] = "{$name}: JSON invalide ou malformé";
        continue;
    }
    // Si le fichier peut contenir container meta (level/subject/exercises) or array of exercises
    $candidates = [];
    if (isset($json['exercises']) && is_array($json['exercises'])) {
        $candidates = $json['exercises'];
    } elseif (isset($json[0]) && is_array($json[0])) {
        // array of exercises
        $candidates = $json;
    } else {
        $errors[] = "{$name}: format inattendu (attendu field 'exercises' ou tableau d'exercices)";
        continue;
    }

    if (count($candidates) === 0) {
        $errors[] = "{$name}: aucun exercice dans le fichier";
        continue;
    }

    foreach ($candidates as $i => $exo) {
        $idx = $i + 1;
        if ($useOpis) {
            // Opis expects objects
            $exoObj = json_decode(json_encode($exo));
            $result = $validator->validate($exoObj, $schemaObj);
            if (!$result->isValid()) {
                $err = $result->error();
                // recursive formatter for Opis errors
                $formatOpisError = function($e) use (&$formatOpisError) {
                    $out = [];
                    $out['message'] = method_exists($e, 'message') ? $e->message() : (string)$e;
                    $out['keyword'] = method_exists($e, 'keyword') ? $e->keyword() : null;
                    $out['dataPointer'] = method_exists($e, 'dataPointer') ? $e->dataPointer() : null;
                    $out['schemaPointer'] = method_exists($e, 'schemaPointer') ? $e->schemaPointer() : null;
                    if (method_exists($e, 'subErrors') && is_array($e->subErrors()) && count($e->subErrors()) > 0) {
                        $out['subErrors'] = [];
                        foreach ($e->subErrors() as $se) {
                            $out['subErrors'][] = $formatOpisError($se);
                        }
                    }
                    return $out;
                };

                if (is_object($err)) {
                    $details = $formatOpisError($err);
                } else {
                    $details = (string)$err;
                }
                // add a short snippet of the offending item to help debugging when dataPointer is null
                $exoJson = json_encode($exo, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                $exoSnippet = mb_substr($exoJson, 0, 500);
                $errors[] = "{$name}#{$idx}: échec validation schéma - " . json_encode($details, JSON_UNESCAPED_UNICODE) . " - item_snippet: " . $exoSnippet;
            }
        } else {
            // Fallback: Vérifier les champs obligatoires d'après schema (heuristique)
            foreach ($required as $field) {
                if (!array_key_exists($field, $exo)) {
                    $errors[] = "{$name}#{$idx}: champ manquant '$field'";
                } else {
                    // basic type checks: strings expected
                    if (in_array($field, ['Subject','Level','Title','Content','Instruction','AnswerType','Domain','Competence','Difficulty','Identifier'])) {
                        if (!is_string($exo[$field]) && !is_null($exo[$field])) {
                            $errors[] = "{$name}#{$idx}: champ '$field' doit être une string";
                        }
                    }
                    if ($field === 'Choices' && $exo[$field] !== null && !is_array($exo[$field]) && !is_string($exo[$field])) {
                        $errors[] = "{$name}#{$idx}: 'Choices' doit être un tableau, une chaîne ou null";
                    }
                }
            }
        }
    }
    $valid++;
}

// Résumé
if (!empty($errors)) {
    echo "[RESULT] Validation terminée: ERREURS détectées:\n";
    foreach ($errors as $e) echo " - $e\n";
    echo "\nTotal fichiers vérifiés: $total, fichiers potentiels: $valid, erreurs: " . count($errors) . "\n";
    exit(1);
} else {
    echo "[RESULT] Validation terminée: aucun problème trouvé. Fichiers vérifiés: $total\n";
    exit(0);
}
