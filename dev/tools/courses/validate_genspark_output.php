<?php
/**
 * dev/tools/courses/validate_genspark_output.php
 * Valide le JSON enrichi par Genspark avant import
 *
 * Usage: php dev/tools/courses/validate_genspark_output.php <fichier.json>
 */

// Support running from CLI only
if (php_sapi_name() !== 'cli') {
    die('❌ Ce script doit être exécuté en ligne de commande.' . PHP_EOL);
}

$rootDir = dirname(dirname(dirname(__DIR__)));

echo "🔍 Validation du JSON enrichi par Genspark\n";
echo "==========================================\n\n";

// Vérifier l'argument
if ($argc < 2) {
    echo "❌ Usage: php " . basename(__FILE__) . " <fichier.json>\n\n";
    echo "Exemple:\n";
    echo "  php dev/tools/courses/validate_genspark_output.php dev/data/courses_enriched.json\n\n";
    exit(1);
}

$jsonFile = $argv[1];

// Vérifier l'existence du fichier
if (!file_exists($jsonFile)) {
    echo "❌ Fichier introuvable : $jsonFile\n";
    exit(1);
}

// ============================================
// FONCTIONS DE VALIDATION
// ============================================

/**
 * Vérifie qu'une chaîne HTML ne contient pas de balises interdites
 */
function checkForbiddenTags($html, $fieldName) {
    $forbidden = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input'];
    $errors = [];

    foreach ($forbidden as $tag) {
        if (preg_match("/<$tag\b/i", $html)) {
            $errors[] = "Balise interdite <$tag> trouvée dans $fieldName";
        }
    }

    return $errors;
}

/**
 * Vérifie qu'une chaîne HTML ne contient pas d'event handlers
 */
function checkEventHandlers($html, $fieldName) {
    $events = ['onclick', 'onload', 'onerror', 'onmouseover', 'onfocus', 'onblur'];
    $errors = [];

    foreach ($events as $event) {
        if (preg_match("/\b$event\s*=/i", $html)) {
            $errors[] = "Event handler '$event' trouvé dans $fieldName (sécurité)";
        }
    }

    return $errors;
}

/**
 * Vérifie la qualité du contenu HTML
 */
function validateHtmlContent($html, $fieldName, $expectedTags = []) {
    $errors = [];

    if (empty(trim(strip_tags($html)))) {
        $errors[] = "$fieldName est vide (ou contient uniquement des balises vides)";
        return $errors;
    }

    // Vérifier les balises attendues
    foreach ($expectedTags as $tag) {
        if (!preg_match("/<$tag\b/i", $html)) {
            $errors[] = "$fieldName devrait contenir au moins une balise <$tag>";
        }
    }

    // Vérifier les balises mal fermées (basique)
    $openTags = preg_match_all('/<([a-z][a-z0-9]*)\b[^>]*>/i', $html, $opens);
    $closeTags = preg_match_all('/<\/([a-z][a-z0-9]*)\s*>/i', $html, $closes);

    return $errors;
}

/**
 * Compte les mots dans une chaîne HTML
 */
function countWords($html) {
    $text = strip_tags($html);
    $text = preg_replace('/\s+/', ' ', $text);
    return str_word_count($text);
}

/**
 * Valide un cours complet
 */
function validateCourse($course, $index) {
    $errors = [];
    $warnings = [];

    // === CHAMPS OBLIGATOIRES ===

    if (!isset($course['id']) || !is_numeric($course['id'])) {
        $errors[] = "Cours #$index : 'id' manquant ou invalide";
    }

    $courseId = $course['id'] ?? $index;

    // === SECTION ===

    if (empty($course['section'])) {
        $errors[] = "Cours #$courseId : 'section' vide";
    } else {
        $section = trim($course['section']);
        if (strlen($section) < 5) {
            $warnings[] = "Cours #$courseId : 'section' trop courte (< 5 caractères)";
        }
        if (strlen($section) > 100) {
            $warnings[] = "Cours #$courseId : 'section' très longue (> 100 caractères)";
        }
    }

    // === KEY_POINT ===

    if (empty($course['key_point'])) {
        $errors[] = "Cours #$courseId : 'key_point' vide";
    } else {
        $keyPoint = $course['key_point'];

        // Vérifier balises interdites
        $errors = array_merge($errors, checkForbiddenTags($keyPoint, "key_point"));
        $errors = array_merge($errors, checkEventHandlers($keyPoint, "key_point"));

        // Vérifier structure attendue (<ul><li>)
        if (!preg_match('/<ul\b/i', $keyPoint)) {
            $warnings[] = "Cours #$courseId : 'key_point' devrait contenir une liste <ul>";
        }

        if (!preg_match('/<li\b/i', $keyPoint)) {
            $warnings[] = "Cours #$courseId : 'key_point' devrait contenir des <li>";
        }

        // Compter les points
        $liCount = preg_match_all('/<li\b/i', $keyPoint);
        if ($liCount < 2) {
            $warnings[] = "Cours #$courseId : 'key_point' contient seulement $liCount point (recommandé: 3-5)";
        }
        if ($liCount > 7) {
            $warnings[] = "Cours #$courseId : 'key_point' contient $liCount points (recommandé: 3-5, peut être trop)";
        }
    }

    // === EXPLANATION ===

    if (empty($course['explanation'])) {
        $errors[] = "Cours #$courseId : 'explanation' vide";
    } else {
        $explanation = $course['explanation'];

        // Vérifier balises interdites
        $errors = array_merge($errors, checkForbiddenTags($explanation, "explanation"));
        $errors = array_merge($errors, checkEventHandlers($explanation, "explanation"));

        // Vérifier structure attendue (<h2>, <p>)
        if (!preg_match('/<h2\b/i', $explanation)) {
            $warnings[] = "Cours #$courseId : 'explanation' devrait commencer par un <h2>";
        }

        if (!preg_match('/<p\b/i', $explanation)) {
            $warnings[] = "Cours #$courseId : 'explanation' devrait contenir au moins un <p>";
        }

        // Vérifier longueur
        $wordCount = countWords($explanation);
        if ($wordCount < 50) {
            $warnings[] = "Cours #$courseId : 'explanation' trop courte ($wordCount mots, recommandé: 150-250)";
        }
        if ($wordCount > 400) {
            $warnings[] = "Cours #$courseId : 'explanation' très longue ($wordCount mots, recommandé: 150-250)";
        }
    }

    // === EXAMPLE ===

    if (empty($course['example'])) {
        $warnings[] = "Cours #$courseId : 'example' vide (recommandé d'en avoir un)";
    } else {
        $example = $course['example'];

        // Vérifier balises interdites
        $errors = array_merge($errors, checkForbiddenTags($example, "example"));
        $errors = array_merge($errors, checkEventHandlers($example, "example"));

        // Vérifier classe 'example'
        if (!preg_match('/class=["\']example["\']/i', $example)) {
            $warnings[] = "Cours #$courseId : 'example' devrait utiliser class='example'";
        }
    }

    // === FORMULA ===

    if (!empty($course['formula'])) {
        $formula = $course['formula'];

        // Vérifier qu'il n'y a pas de HTML (sauf LaTeX)
        if (preg_match('/<(?!\/?(em|strong)\b)[^>]+>/i', $formula)) {
            $warnings[] = "Cours #$courseId : 'formula' ne devrait pas contenir de HTML complexe";
        }

        // Vérifier format LaTeX si lycée
        $level = $course['level'] ?? '';
        if (in_array($level, ['2nde', '1ere', 'terminale'])) {
            // Vérifier qu'on utilise \( \) et pas $
            if (preg_match('/\$[^\$]+\$/', $formula)) {
                $warnings[] = "Cours #$courseId : 'formula' utilise \$ au lieu de \\( \\) pour LaTeX";
            }
        }
    }

    return ['errors' => $errors, 'warnings' => $warnings];
}

// ============================================
// LECTURE ET PARSING JSON
// ============================================

echo "📖 Lecture du fichier : " . basename($jsonFile) . "\n";

$jsonContent = file_get_contents($jsonFile);

if ($jsonContent === false) {
    echo "❌ Impossible de lire le fichier\n";
    exit(1);
}

$courses = json_decode($jsonContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Erreur de parsing JSON : " . json_last_error_msg() . "\n";
    exit(1);
}

// Gérer format avec clé 'courses'
if (isset($courses['courses']) && is_array($courses['courses'])) {
    $courses = $courses['courses'];
}

if (!is_array($courses)) {
    echo "❌ Format JSON invalide : attendu un tableau de cours\n";
    exit(1);
}

echo "✅ " . count($courses) . " cours trouvés\n\n";

// ============================================
// VALIDATION DE CHAQUE COURS
// ============================================

echo "🔍 Validation en cours...\n\n";

$allErrors = [];
$allWarnings = [];
$stats = [
    'total' => count($courses),
    'valid' => 0,
    'errors' => 0,
    'warnings_only' => 0
];

foreach ($courses as $index => $course) {
    $result = validateCourse($course, $index + 1);

    $hasErrors = !empty($result['errors']);
    $hasWarnings = !empty($result['warnings']);

    if ($hasErrors) {
        $stats['errors']++;
        $allErrors = array_merge($allErrors, $result['errors']);
    } elseif ($hasWarnings) {
        $stats['warnings_only']++;
    } else {
        $stats['valid']++;
    }

    if ($hasWarnings) {
        $allWarnings = array_merge($allWarnings, $result['warnings']);
    }

    // Affichage temps réel
    $courseId = $course['id'] ?? ($index + 1);
    if ($hasErrors) {
        echo "❌ Cours #$courseId : " . count($result['errors']) . " erreur(s)\n";
    } elseif ($hasWarnings) {
        echo "⚠️  Cours #$courseId : " . count($result['warnings']) . " avertissement(s)\n";
    } else {
        echo "✅ Cours #$courseId : OK\n";
    }
}

// ============================================
// RAPPORT FINAL
// ============================================

echo "\n📊 RAPPORT DE VALIDATION\n";
echo "========================\n\n";

echo "Total de cours : {$stats['total']}\n";
echo "✅ Valides : {$stats['valid']}\n";
echo "⚠️  Avertissements uniquement : {$stats['warnings_only']}\n";
echo "❌ Avec erreurs : {$stats['errors']}\n\n";

// Afficher les erreurs
if (!empty($allErrors)) {
    echo "🔴 ERREURS BLOQUANTES (" . count($allErrors) . ")\n";
    echo "===================================\n";
    foreach ($allErrors as $error) {
        echo "   ❌ $error\n";
    }
    echo "\n";
}

// Afficher les avertissements
if (!empty($allWarnings)) {
    echo "⚠️  AVERTISSEMENTS (" . count($allWarnings) . ")\n";
    echo "================================\n";

    // Grouper les avertissements similaires
    $warningGroups = [];
    foreach ($allWarnings as $warning) {
        // Extraire le type d'avertissement
        $type = preg_replace('/Cours #\d+ : /', '', $warning);
        $type = preg_replace('/\(.*?\)/', '', $type); // Enlever les détails entre parenthèses
        $type = trim($type);

        if (!isset($warningGroups[$type])) {
            $warningGroups[$type] = 0;
        }
        $warningGroups[$type]++;
    }

    arsort($warningGroups);

    foreach ($warningGroups as $type => $count) {
        echo "   ⚠️  $type : $count occurrence(s)\n";
    }
    echo "\n";
}

// Créer un rapport détaillé
$reportFile = $rootDir . '/dev/reports/validation_genspark_' . date('Ymd_His') . '.log';
$reportDir = dirname($reportFile);

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

$report = "Validation Genspark - " . date('Y-m-d H:i:s') . "\n";
$report .= "======================================\n\n";
$report .= "Fichier source : $jsonFile\n";
$report .= "Cours analysés : {$stats['total']}\n\n";
$report .= "RÉSULTATS :\n";
$report .= "✅ Valides : {$stats['valid']}\n";
$report .= "⚠️  Avertissements : {$stats['warnings_only']}\n";
$report .= "❌ Erreurs : {$stats['errors']}\n\n";

if (!empty($allErrors)) {
    $report .= "ERREURS :\n" . implode("\n", $allErrors) . "\n\n";
}

if (!empty($allWarnings)) {
    $report .= "AVERTISSEMENTS :\n" . implode("\n", $allWarnings) . "\n";
}

file_put_contents($reportFile, $report);

echo "📄 Rapport détaillé : " . basename($reportFile) . "\n";
echo "📍 Emplacement : $reportFile\n\n";

// ============================================
// CONCLUSION
// ============================================

if ($stats['errors'] > 0) {
    echo "❌ VALIDATION ÉCHOUÉE\n";
    echo "Le fichier contient des erreurs bloquantes.\n";
    echo "Corrige les erreurs avant d'importer dans la base de données.\n";
    exit(1);
} elseif ($stats['warnings_only'] > 0) {
    echo "⚠️  VALIDATION RÉUSSIE AVEC AVERTISSEMENTS\n";
    echo "Le fichier peut être importé, mais certains contenus pourraient être améliorés.\n";
    echo "Vérifie les avertissements ci-dessus.\n";
    exit(0);
} else {
    echo "✅ VALIDATION PARFAITE\n";
    echo "Tous les cours sont conformes aux consignes.\n";
    echo "Prêt pour l'import !\n\n";
    echo "Commande d'import :\n";
    echo "php dev/tools/courses/import_genspark_courses.php $jsonFile\n";
    exit(0);
}
