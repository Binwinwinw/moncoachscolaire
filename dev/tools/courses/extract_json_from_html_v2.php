<?php
/**
 * dev/tools/courses/extract_json_from_html_v2.php
 * Version améliorée pour extraire le JSON des fichiers Genspark
 */

if (php_sapi_name() !== 'cli') {
    die('❌ Ce script doit être exécuté en ligne de commande.' . PHP_EOL);
}

echo "📄 Extraction JSON depuis fichier HTML Genspark (v2)\n";
echo "====================================================\n\n";

if ($argc < 2) {
    echo "❌ Usage: php " . basename(__FILE__) . " <fichier.html> [output.json]\n";
    exit(1);
}

$htmlFile = $argv[1];
$outputFile = $argc > 2 ? $argv[2] : preg_replace('/\.html?$/i', '', $htmlFile) . '.json';

if (!file_exists($htmlFile)) {
    echo "❌ Fichier introuvable : $htmlFile\n";
    exit(1);
}

echo "📖 Lecture du fichier HTML : " . basename($htmlFile) . "\n";

$htmlContent = file_get_contents($htmlFile);
echo "✅ Fichier lu (" . number_format(strlen($htmlContent)) . " caractères)\n\n";

// ============================================
// MÉTHODE 1 : Extraire tout entre [ et ]
// ============================================

echo "🔍 Méthode 1 : Recherche du tableau JSON complet...\n";

// Chercher le premier [ et le dernier ] correspondant
$startPos = strpos($htmlContent, '[');

if ($startPos === false) {
    echo "❌ Aucun tableau JSON trouvé (pas de '[')\n";
    exit(1);
}

// Compter les [ et ] pour trouver la fin du tableau
$depth = 0;
$inString = false;
$escapeNext = false;
$endPos = null;

for ($i = $startPos; $i < strlen($htmlContent); $i++) {
    $char = $htmlContent[$i];

    if ($escapeNext) {
        $escapeNext = false;
        continue;
    }

    if ($char === '\\') {
        $escapeNext = true;
        continue;
    }

    if ($char === '"') {
        $inString = !$inString;
        continue;
    }

    if (!$inString) {
        if ($char === '[' || $char === '{') {
            $depth++;
        } elseif ($char === ']' || $char === '}') {
            $depth--;
            if ($depth === 0) {
                $endPos = $i;
                break;
            }
        }
    }
}

if ($endPos === null) {
    echo "❌ Impossible de trouver la fin du JSON\n";
    exit(1);
}

$jsonContent = substr($htmlContent, $startPos, $endPos - $startPos + 1);

echo "✅ JSON extrait (" . number_format(strlen($jsonContent)) . " caractères)\n\n";

// ============================================
// NETTOYAGE
// ============================================

echo "🧹 Nettoyage du JSON...\n";

// Décoder les entités HTML
$jsonContent = html_entity_decode($jsonContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');

// ============================================
// VALIDATION
// ============================================

echo "🔍 Validation du JSON...\n";

$data = json_decode($jsonContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ JSON invalide : " . json_last_error_msg() . "\n\n";

    // Sauvegarder quand même pour inspection
    $debugFile = 'debug_extracted.json';
    file_put_contents($debugFile, $jsonContent);
    echo "💾 JSON brut sauvegardé dans : $debugFile\n";
    echo "📝 Ouvre ce fichier pour voir ce qui a été extrait\n\n";

    echo "📝 Extrait (500 premiers caractères) :\n";
    echo substr($jsonContent, 0, 500) . "\n...\n\n";

    echo "📝 Extrait (500 derniers caractères) :\n";
    echo "..." . substr($jsonContent, -500) . "\n";

    exit(1);
}

$courses = is_array($data) ? $data : (isset($data['courses']) ? $data['courses'] : []);

echo "✅ JSON valide\n";
echo "✅ " . count($courses) . " cours détectés\n\n";

// ============================================
// SAUVEGARDE
// ============================================

$outputDir = dirname($outputFile);
if (!is_dir($outputDir) && !empty($outputDir) && $outputDir !== '.') {
    mkdir($outputDir, 0755, true);
}

$jsonOutput = json_encode($courses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($outputFile, $jsonOutput);

echo "💾 JSON extrait et sauvegardé !\n\n";
echo "📄 Fichier source : " . basename($htmlFile) . "\n";
echo "💾 Fichier généré : " . basename($outputFile) . "\n";
echo "📍 Emplacement : $outputFile\n";
echo "📊 Taille : " . number_format(strlen($jsonOutput)) . " caractères\n";
echo "📚 Cours : " . count($courses) . "\n\n";

// Aperçu
echo "📋 Aperçu des 5 premiers cours :\n";
echo "=================================\n";
foreach (array_slice($courses, 0, 5) as $i => $course) {
    echo ($i+1) . ". Cours #{$course['id']} : {$course['level']} / {$course['subject']} / {$course['competence']}\n";
}

if (count($courses) > 5) {
    echo "... (+" . (count($courses) - 5) . " autres cours)\n";
}

echo "\n✅ EXTRACTION TERMINÉE !\n\n";

echo "🚀 PROCHAINES ÉTAPES :\n";
echo "======================\n\n";
echo "1️⃣  VALIDER le JSON :\n";
echo "   php dev/tools/courses/validate_genspark_output.php $outputFile\n\n";
echo "2️⃣  IMPORTER en base :\n";
echo "   php dev/tools/courses/import_genspark_courses.php $outputFile\n\n";
