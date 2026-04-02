<?php
/**
 * dev/tools/courses/fix_genspark_html.php
 * Corrige automatiquement les cours Genspark mal formatés
 *
 * Usage: php dev/tools/courses/fix_genspark_html.php <input.json> [output.json]
 */

// Support running from CLI only
if (php_sapi_name() !== 'cli') {
    die('❌ Ce script doit être exécuté en ligne de commande.' . PHP_EOL);
}

$rootDir = dirname(dirname(dirname(__DIR__)));

echo "🔧 Correction automatique du HTML Genspark\n";
echo "==========================================\n\n";

// Vérifier arguments
if ($argc < 2) {
    echo "❌ Usage: php " . basename(__FILE__) . " <input.json> [output.json]\n\n";
    echo "Exemple:\n";
    echo "  php dev/tools/courses/fix_genspark_html.php courses_raw.json courses_fixed.json\n\n";
    exit(1);
}

$inputFile = $argv[1];
$outputFile = $argc > 2 ? $argv[2] : str_replace('.json', '_fixed.json', $inputFile);

// Vérifier existence
if (!file_exists($inputFile)) {
    echo "❌ Fichier introuvable : $inputFile\n";
    exit(1);
}

// ============================================
// FONCTIONS DE CORRECTION
// ============================================

/**
 * Convertit du texte brut en liste HTML <ul><li>
 */
function textToHtmlList($text) {
    $text = trim($text);

    // Déjà du HTML valide ? Ne pas toucher
    if (preg_match('/<ul\b/i', $text)) {
        return $text;
    }

    // Séparer par retours à la ligne
    $lines = array_filter(array_map('trim', explode("\n", $text)));

    if (empty($lines)) {
        return '';
    }

    // Générer la liste HTML
    $html = "<ul>\n";
    foreach ($lines as $line) {
        // Enlever les puces textuelles si présentes (-, •, etc.)
        $line = preg_replace('/^[-•*]\s*/', '', $line);
        $html .= "  <li>" . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "</li>\n";
    }
    $html .= "</ul>";

    return $html;
}

/**
 * Convertit du texte brut en paragraphes HTML
 */
function textToHtmlParagraphs($text) {
    $text = trim($text);

    // Déjà du HTML valide ? Ne pas toucher
    if (preg_match('/<(h2|p)\b/i', $text)) {
        return $text;
    }

    // Séparer par double retour à la ligne (paragraphes)
    $paragraphs = array_filter(array_map('trim', preg_split('/\n\s*\n/', $text)));

    if (empty($paragraphs)) {
        return '';
    }

    // Premier paragraphe = titre <h2>
    $html = "<h2>" . htmlspecialchars(array_shift($paragraphs), ENT_QUOTES, 'UTF-8') . "</h2>\n";

    // Paragraphes suivants = <p>
    foreach ($paragraphs as $para) {
        // Mettre en gras les mots en majuscules ou après ":" si c'est une définition
        $para = preg_replace_callback('/(\w+)\s*:\s*/', function($m) {
            return "<strong>" . $m[1] . " :</strong> ";
        }, $para);

        $html .= "<p>" . nl2br(htmlspecialchars($para, ENT_QUOTES, 'UTF-8')) . "</p>\n";
    }

    return $html;
}

/**
 * Convertit un exemple en HTML structuré
 */
function textToHtmlExample($text) {
    $text = trim($text);

    // Déjà du HTML valide ? Ne pas toucher
    if (preg_match('/<div\b/i', $text)) {
        return $text;
    }

    $html = "<div class='example'>\n";

    // Chercher le titre (ligne commençant par "Exemple")
    if (preg_match('/^(Exemple[^:\n]*)/im', $text, $match)) {
        $html .= "  <h3>" . htmlspecialchars(trim($match[1]), ENT_QUOTES, 'UTF-8') . "</h3>\n";
        $text = preg_replace('/^' . preg_quote($match[0], '/') . '/im', '', $text, 1);
    } else {
        $html .= "  <h3>Exemple</h3>\n";
    }

    // Détecter les calculs alignés (lignes avec des espaces multiples ou des opérateurs)
    $lines = explode("\n", $text);
    $inCalc = false;
    $calcBuffer = '';

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Ligne de calcul ? (contient +, -, ×, =, ou commence par beaucoup d'espaces)
        if (preg_match('/^\s{3,}|\+|×|÷|---+|===+/', $line) || ($inCalc && preg_match('/^\s+/', $line))) {
            $inCalc = true;
            $calcBuffer .= $line . "\n";
        } else {
            // Fermer le bloc de calcul si on en sortait
            if ($inCalc && !empty($calcBuffer)) {
                $html .= "  <pre>" . htmlspecialchars(rtrim($calcBuffer), ENT_QUOTES, 'UTF-8') . "</pre>\n";
                $calcBuffer = '';
                $inCalc = false;
            }

            // Ligne normale
            if (!empty($trimmed)) {
                // Mettre en gras les "Étape X :"
                $trimmed = preg_replace('/^(Étape\s+\d+\s*:)/i', '<strong>$1</strong>', $trimmed);
                $trimmed = preg_replace('/^(Résultat\s*:)/i', '<strong>$1</strong>', $trimmed);

                $html .= "  <p>" . $trimmed . "</p>\n";
            }
        }
    }

    // Fermer le dernier calcul si nécessaire
    if ($inCalc && !empty($calcBuffer)) {
        $html .= "  <pre>" . htmlspecialchars(rtrim($calcBuffer), ENT_QUOTES, 'UTF-8') . "</pre>\n";
    }

    $html .= "</div>";

    return $html;
}

// ============================================
// TRAITEMENT
// ============================================

echo "📖 Lecture du fichier : " . basename($inputFile) . "\n";

$jsonContent = file_get_contents($inputFile);
$courses = json_decode($jsonContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Erreur JSON : " . json_last_error_msg() . "\n";
    exit(1);
}

echo "✅ " . count($courses) . " cours trouvés\n\n";
echo "🔧 Correction en cours...\n\n";

$fixed = 0;

foreach ($courses as &$course) {
    $courseId = $course['id'] ?? '?';
    $changes = [];

    // Corriger key_point
    if (!empty($course['key_point']) && !preg_match('/<ul\b/i', $course['key_point'])) {
        $course['key_point'] = textToHtmlList($course['key_point']);
        $changes[] = 'key_point';
    }

    // Corriger explanation
    if (!empty($course['explanation']) && !preg_match('/<(h2|p)\b/i', $course['explanation'])) {
        $course['explanation'] = textToHtmlParagraphs($course['explanation']);
        $changes[] = 'explanation';
    }

    // Corriger example
    if (!empty($course['example']) && !preg_match('/<div\b/i', $course['example'])) {
        $course['example'] = textToHtmlExample($course['example']);
        $changes[] = 'example';
    }

    // Nettoyer les espaces en début/fin
    foreach (['section', 'key_point', 'explanation', 'example', 'formula'] as $field) {
        if (isset($course[$field])) {
            $course[$field] = trim($course[$field]);
        }
    }

    if (!empty($changes)) {
        $fixed++;
        echo "✅ Cours #$courseId : Corrigé " . implode(', ', $changes) . "\n";
    } else {
        echo "⏭️  Cours #$courseId : Déjà OK\n";
    }
}
unset($course); // Libérer la référence

echo "\n📊 RÉSULTATS\n";
echo "============\n";
echo "✅ Cours corrigés : $fixed\n";
echo "⏭️  Déjà corrects : " . (count($courses) - $fixed) . "\n\n";

// Sauvegarder
$jsonOutput = json_encode($courses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($outputFile, $jsonOutput);

echo "💾 Fichier corrigé sauvegardé : " . basename($outputFile) . "\n";
echo "📍 Emplacement : $outputFile\n\n";

echo "✅ Correction terminée !\n\n";
echo "🔍 Valider le fichier avant import :\n";
echo "php dev/tools/courses/validate_genspark_output.php $outputFile\n\n";
echo "📥 Puis importer :\n";
echo "php dev/tools/courses/import_genspark_courses.php $outputFile\n";
