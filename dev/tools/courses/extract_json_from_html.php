<?php
/**
 * dev/tools/courses/extract_json_from_html.php
 * Extrait le JSON d'un fichier HTML généré par Genspark
 *
 * Usage: php dev/tools/courses/extract_json_from_html.php <fichier.html> [output.json]
 */

// Support running from CLI only
if (php_sapi_name() !== 'cli') {
    die('❌ Ce script doit être exécuté en ligne de commande.' . PHP_EOL);
}

$rootDir = dirname(dirname(dirname(__DIR__)));

echo "📄 Extraction JSON depuis fichier HTML Genspark\n";
echo "===============================================\n\n";

// Vérifier arguments
if ($argc < 2) {
    echo "❌ Usage: php " . basename(__FILE__) . " <fichier.html> [output.json]\n\n";
    echo "Exemples:\n";
    echo "  php dev/tools/courses/extract_json_from_html.php courses.html\n";
    echo "  php dev/tools/courses/extract_json_from_html.php courses.html dev/data/courses.json\n\n";
    exit(1);
}

$htmlFile = $argv[1];
$outputFile = $argc > 2 ? $argv[2] : null;

// Vérifier existence
if (!file_exists($htmlFile)) {
    echo "❌ Fichier introuvable : $htmlFile\n";
    exit(1);
}

// ============================================
// EXTRACTION
// ============================================

echo "📖 Lecture du fichier HTML : " . basename($htmlFile) . "\n";

$htmlContent = file_get_contents($htmlFile);

if ($htmlContent === false) {
    echo "❌ Impossible de lire le fichier\n";
    exit(1);
}

echo "✅ Fichier lu (" . number_format(strlen($htmlContent)) . " caractères)\n\n";
echo "🔍 Recherche du JSON...\n";

// Stratégies d'extraction (essayer plusieurs méthodes)

$jsonContent = null;

// Stratégie 1 : Chercher dans une balise <pre>
if (preg_match('/<pre[^>]*>(.*?)<\/pre>/is', $htmlContent, $matches)) {
    echo "✅ JSON trouvé dans une balise <pre>\n";
    $jsonContent = $matches[1];
}

// Stratégie 2 : Chercher dans un <script type="application/json">
if (!$jsonContent && preg_match('/<script[^>]*type=["\']application\/json["\'][^>]*>(.*?)<\/script>/is', $htmlContent, $matches)) {
    echo "✅ JSON trouvé dans <script type=\"application/json\">\n";
    $jsonContent = $matches[1];
}

// Stratégie 3 : Chercher un tableau JSON directement (commence par [ et finit par ])
if (!$jsonContent && preg_match('/(\[\s*\{[\s\S]*\}\s*\])/s', $htmlContent, $matches)) {
    echo "✅ JSON trouvé directement dans le HTML\n";
    $jsonContent = $matches[1];
}

// Stratégie 4 : Chercher dans un élément avec id="json-data" ou class="json-data"
if (!$jsonContent && preg_match('/<[^>]+(id|class)=["\']json-data["\'][^>]*>(.*?)<\/[^>]+>/is', $htmlContent, $matches)) {
    echo "✅ JSON trouvé dans un élément avec id/class 'json-data'\n";
    $jsonContent = $matches[2];
}

if (!$jsonContent) {
    echo "❌ Aucun JSON trouvé dans le fichier HTML\n";
    echo "💡 Le fichier doit contenir le JSON dans une balise <pre>, <script> ou directement\n";
    exit(1);
}

// ============================================
// NETTOYAGE
// ============================================

echo "🧹 Nettoyage du JSON...\n";

// Décoder les entités HTML
$jsonContent = html_entity_decode($jsonContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');

// Supprimer les espaces en début/fin
$jsonContent = trim($jsonContent);

// Vérifier que ça commence par [ ou {
if (!preg_match('/^[\[\{]/', $jsonContent)) {
    echo "⚠️  Le contenu ne commence pas par [ ou {\n";
    echo "💡 Tentative de nettoyage avancé...\n";

    // Chercher le premier [ ou {
    if (preg_match('/([\[\{].*[\]\}])/s', $jsonContent, $matches)) {
        $jsonContent = $matches[1];
        echo "✅ JSON extrait et nettoyé\n";
    } else {
        echo "❌ Impossible de nettoyer le contenu\n";
        exit(1);
    }
}

// ============================================
// VALIDATION JSON
// ============================================

echo "✅ JSON nettoyé (" . number_format(strlen($jsonContent)) . " caractères)\n\n";
echo "🔍 Validation du JSON...\n";

$data = json_decode($jsonContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ JSON invalide : " . json_last_error_msg() . "\n\n";

    // Afficher un extrait pour debug
    echo "📝 Extrait du contenu (100 premiers caractères) :\n";
    echo substr($jsonContent, 0, 100) . "...\n\n";

    echo "💡 Conseils :\n";
    echo "   - Vérifie que le fichier HTML contient bien du JSON valide\n";
    echo "   - Ouvre le fichier dans un éditeur et cherche le tableau JSON\n";
    echo "   - Copie/colle manuellement le JSON dans un fichier .json\n";
    exit(1);
}

// Gérer le format (tableau direct ou objet avec clé 'courses')
if (isset($data['courses']) && is_array($data['courses'])) {
    $courses = $data['courses'];
    echo "✅ JSON valide (format objet avec clé 'courses')\n";
} elseif (is_array($data)) {
    $courses = $data;
    echo "✅ JSON valide (format tableau direct)\n";
} else {
    echo "❌ Structure JSON invalide\n";
    exit(1);
}

echo "✅ " . count($courses) . " cours détectés\n\n";

// ============================================
// SAUVEGARDE
// ============================================

// Déterminer le nom du fichier de sortie
if (!$outputFile) {
    $outputFile = preg_replace('/\.html?$/i', '', $htmlFile) . '.json';
}

// Créer le dossier si nécessaire
$outputDir = dirname($outputFile);
if (!is_dir($outputDir) && !empty($outputDir) && $outputDir !== '.') {
    mkdir($outputDir, 0755, true);
}

// Reformater le JSON proprement
$jsonOutput = json_encode($courses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

if ($jsonOutput === false) {
    echo "❌ Erreur lors du formatage du JSON\n";
    exit(1);
}

// Sauvegarder
if (file_put_contents($outputFile, $jsonOutput) === false) {
    echo "❌ Impossible d'écrire le fichier : $outputFile\n";
    exit(1);
}

echo "💾 JSON extrait et sauvegardé !\n\n";
echo "📄 Fichier source : " . basename($htmlFile) . "\n";
echo "💾 Fichier généré : " . basename($outputFile) . "\n";
echo "📍 Emplacement : $outputFile\n";
echo "📊 Taille : " . number_format(strlen($jsonOutput)) . " caractères\n";
echo "📚 Cours : " . count($courses) . "\n\n";

// Afficher un aperçu
echo "📋 Aperçu des premiers cours :\n";
echo "===============================\n";

$preview = array_slice($courses, 0, 3);
foreach ($preview as $i => $course) {
    $num = $i + 1;
    $id = $course['id'] ?? '?';
    $level = $course['level'] ?? '?';
    $subject = $course['subject'] ?? '?';
    $competence = $course['competence'] ?? '?';

    echo "$num. Cours #$id : $level / $subject / $competence\n";
}

if (count($courses) > 3) {
    echo "... (+" . (count($courses) - 3) . " autres cours)\n";
}

echo "\n✅ EXTRACTION TERMINÉE !\n\n";

// ============================================
// PROCHAINES ÉTAPES
// ============================================

echo "🚀 PROCHAINES ÉTAPES :\n";
echo "======================\n\n";

echo "1️⃣  VALIDER le JSON :\n";
echo "   php dev/tools/courses/validate_genspark_output.php $outputFile\n\n";

echo "2️⃣  IMPORTER en base de données :\n";
echo "   php dev/tools/courses/import_genspark_courses.php $outputFile\n\n";

echo "3️⃣  LIER aux exercices :\n";
echo "   php dev/tools/exercises/link_exercises_to_courses.php\n\n";

exit(0);
