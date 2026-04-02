#!/usr/bin/env php
<?php
/**
 * Convertir les fichiers Markdown d'exercices en fichiers HTML structurés
 * Scanne les dossiers docs/exercices/college/* et docs/exercices/lycee/*
 * Génère des fichiers HTML exploitables par update_exercises_db.php
 */

// Configuration
$baseDir = __DIR__ . '/../docs/exercices';
$outputDir = __DIR__ . '/../docs/exercices/converted_html';
$htmlTemplates = [];
$exercisesData = [];
$stats = [
    'dirs_scanned' => 0,
    'md_files_found' => 0,
    'exercises_extracted' => 0,
    'html_files_created' => 0,
    'errors' => 0
];

// Vérifier que le répertoire de base existe
if (!is_dir($baseDir)) {
    echo "❌ Erreur: Le répertoire $baseDir n'existe pas\n";
    exit(1);
}

// Créer le dossier de sortie
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

echo "📚 Conversion des fichiers Markdown en HTML structurés\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Base dir: $baseDir\n";
echo "Output dir: $outputDir\n\n";

/**
 * Analyser les métadonnées d'un fichier markdown
 */
function parseMarkdownMetadata($content) {
    $metadata = [
        'niveau' => null,
        'domaine' => null,
        'competence' => null,
        'difficulte' => null,
        'identifiant' => null,
        'titre' => null,
        'matiere' => null
    ];

    // Chercher le titre dans # ou dans "# Exercice..."
    if (preg_match('/^#\s+(.+?)(?:\s*-\s*(.+?))?$/m', $content, $matches)) {
        $metadata['titre'] = trim($matches[1]);
        if (isset($matches[2]) && !empty($matches[2])) {
            $type = trim($matches[2]);
            // Ne pas compter CORRIGÉ ou RESSOURCES dans le titre
            if (!str_contains($type, 'CORRIGÉ') && !str_contains($type, 'RESSOURCES')) {
                $metadata['titre'] .= ' - ' . $type;
            }
        }
    }

    // Extraire les métadonnées structurées
    if (preg_match('/\*\*Niveau\*\*\s*:\s*([^\n]+)/i', $content, $m)) {
        $metadata['niveau'] = trim($m[1]);
    }
    if (preg_match('/\*\*Domaine\*\*\s*:\s*([^\n]+)/i', $content, $m)) {
        $metadata['domaine'] = trim($m[1]);
    }
    if (preg_match('/\*\*Compétence\*\*\s*:\s*([^\n]+)/i', $content, $m)) {
        $metadata['competence'] = trim($m[1]);
    }
    if (preg_match('/\*\*Difficulté\*\*\s*:\s*([^\n]+)/i', $content, $m)) {
        $metadata['difficulte'] = trim($m[1]);
    }
    if (preg_match('/\*\*Identifiant\*\*\s*:\s*([^\n]+)/i', $content, $m)) {
        $metadata['identifiant'] = trim($m[1]);
    }

    return $metadata;
}

/**
 * Extraire le contenu principal d'un fichier markdown
 */
function extractContent($content, $type = 'exercise') {
    // Supprimer les métadonnées
    $content = preg_replace('/^\*\*[^*]+\*\*\s*:\s*[^\n]+\n/m', '', $content);
    
    // Supprimer les sections d'en-tête
    $sections = [];
    
    if ($type === 'exercise') {
        // Chercher la section "Énoncé"
        if (preg_match('/##\s+Énoncé(.*?)(?=##|\Z)/is', $content, $m)) {
            return trim($m[1]);
        }
    } elseif ($type === 'correction') {
        // Chercher la section "Correction"
        if (preg_match('/##\s+(?:🔑\s*)?Correction(?:\s+Détaillée)?(.*?)(?=##|\Z)/is', $content, $m)) {
            return trim($m[1]);
        }
    } elseif ($type === 'course') {
        // Chercher le contenu du cours
        $content = preg_replace('/##\s+📌\s+Resources.*?\Z/is', '', $content);
        $content = preg_replace('/^#\s+[^\n]+\n/m', '', $content, 1);
        return trim($content);
    }
    
    return trim($content);
}

/**
 * Normaliser le niveau (6eme -> 6ème, etc.)
 */
function normalizeLevel($level) {
    $replacements = [
        '6eme' => '6ème',
        '5eme' => '5ème',
        '4eme' => '4ème',
        '3eme' => '3ème',
        'Seconde' => 'Seconde',
        'Premiere' => 'Première',
        'Terminale' => 'Terminale'
    ];
    
    foreach ($replacements as $search => $replace) {
        if (stripos($level, $search) !== false) {
            return str_ireplace($search, $replace, $level);
        }
    }
    
    return $level;
}

/**
 * Obtenir la matière à partir du chemin du dossier
 */
function extractSubject($subjectDir) {
    $subjectName = basename($subjectDir);
    
    $mapping = [
        'mathematiques' => 'Mathématiques',
        'francais' => 'Français',
        'anglais' => 'Anglais',
        'histoire-geo' => 'Histoire-Géographie',
        'histoire-géographie' => 'Histoire-Géographie',
        'svt' => 'SVT',
        'physique-chimie' => 'Physique-Chimie',
        'philosophie' => 'Philosophie',
        'sciences-economiques' => 'Sciences Économiques',
        'ses' => 'SES'
    ];
    
    $lower = strtolower(str_replace(' ', '-', $subjectName));
    
    foreach ($mapping as $key => $value) {
        if (stripos($lower, $key) !== false) {
            return $value;
        }
    }
    
    return ucfirst(str_replace('-', ' ', $subjectName));
}

/**
 * Obtenir le niveau du chemin du fichier
 */
function extractLevel($filePath) {
    // Chercher le pattern dans le chemin
    if (preg_match('/\/(6eme|5eme|4eme|3eme|seconde|premiere|premiere|terminale)/i', $filePath, $m)) {
        return normalizeLevel($m[1]);
    }
    
    return null;
}

/**
 * Traiter un groupe d'exercices (exercice.md + corrige.md + cours.md)
 */
function processExerciseGroup($basePath, $subjectDir) {
    global $stats, $exercisesData;
    
    $exercisePath = $basePath . '.md';
    $corrigePath = $basePath . '-corrige.md';
    $coursePath = $basePath . '-cours.md';
    
    if (!file_exists($exercisePath)) {
        return false;
    }
    
    $exerciseContent = file_get_contents($exercisePath);
    $correctionContent = file_exists($corrigePath) ? file_get_contents($corrigePath) : null;
    $courseContent = file_exists($coursePath) ? file_get_contents($coursePath) : null;
    
    // Extraire les métadonnées
    $metadata = parseMarkdownMetadata($exerciseContent);
    $level = extractLevel($exercisePath);
    $subject = extractSubject($subjectDir);
    
    if (!$level || !$metadata['titre']) {
        return false;
    }
    
    // Extraire les contenus
    $content = extractContent($exerciseContent, 'exercise');
    $correction = $correctionContent ? extractContent($correctionContent, 'correction') : '';
    $course = $courseContent ? extractContent($courseContent, 'course') : '';
    
    $exerciseData = [
        'title' => $metadata['titre'],
        'level' => normalizeLevel($level),
        'subject' => $subject,
        'content' => $content,
        'answer' => $correction,
        'tips' => $course,
        'domain' => $metadata['domaine'],
        'competence' => $metadata['competence'],
        'difficulty' => $metadata['difficulte'],
        'identifier' => $metadata['identifiant']
    ];
    
    $exercisesData[] = $exerciseData;
    $stats['exercises_extracted']++;
    
    return true;
}

/**
 * Scanner les fichiers markdown récursivement
 */
function scanDirectory($dir) {
    global $stats;
    
    // Vérifier les sous-dossiers
    $typeDir = $dir . '/college';
    if (is_dir($typeDir)) {
        scanCollegeDir($typeDir);
    }
    
    $typeDir = $dir . '/lycee';
    if (is_dir($typeDir)) {
        scanLyceeDir($typeDir);
    }
}

function scanCollegeDir($collegeDir) {
    global $stats;
    
    $levels = ['6eme', '5eme', '4eme', '3eme'];
    
    foreach ($levels as $level) {
        $levelDir = $collegeDir . '/' . $level;
        if (!is_dir($levelDir)) continue;
        
        $subjects = glob($levelDir . '/*', GLOB_ONLYDIR);
        foreach ($subjects as $subjectDir) {
            scanSubjectDir($subjectDir);
        }
    }
}

function scanLyceeDir($lyceeDir) {
    global $stats;
    
    $levels = ['seconde', 'premiere', 'terminale'];
    
    foreach ($levels as $level) {
        $levelDir = $lyceeDir . '/' . $level;
        if (!is_dir($levelDir)) continue;
        
        $subjects = glob($levelDir . '/*', GLOB_ONLYDIR);
        foreach ($subjects as $subjectDir) {
            scanSubjectDir($subjectDir);
        }
    }
}

function scanSubjectDir($subjectDir) {
    global $stats;
    
    $mdFiles = glob($subjectDir . '/exercice-*.md');
    if (empty($mdFiles)) return;
    
    $processedBases = [];
    
    foreach ($mdFiles as $file) {
        // Ignorer les fichiers de suffixe
        if (preg_match('/-corrige\.md$|-cours\.md$/', $file)) {
            continue;
        }
        
        // Extraire la base du nom
        $baseName = preg_replace('/-corrige\.md$|-cours\.md$|\.md$/', '', $file);
        
        if (in_array($baseName, $processedBases)) {
            continue;
        }
        
        if (processExerciseGroup($baseName, $subjectDir)) {
            $processedBases[] = $baseName;
            $stats['md_files_found']++;
        }
    }
}

/**
 * Générer un fichier HTML pour un groupe d'exercices
 */
function generateGroupHTML($level, $subject, $exercises, $outputDir) {
    global $stats;
    
    // Créer un dossier pour chaque niveau/matière
    $groupDir = $outputDir . '/' . str_replace('ème', 'eme', strtolower($level)) . '_' . strtolower(str_replace(' ', '_', str_replace('-', '_', $subject)));
    if (!is_dir($groupDir)) {
        mkdir($groupDir, 0755, true);
    }
    
    $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercices $level - $subject</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .exercise {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        .exercise-title {
            color: #2c3e50;
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
            padding-left: 10px;
        }
        .metadata {
            font-size: 0.9em;
            color: #7f8c8d;
            margin-bottom: 15px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .meta-item {
            display: flex;
            gap: 5px;
        }
        .section {
            margin-top: 20px;
        }
        .section-title {
            background: #ecf0f1;
            padding: 10px 15px;
            border-left: 4px solid #3498db;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            margin-top: 20px;
        }
        .section-title.content {
            border-left-color: #9b59b6;
        }
        .section-title.answer {
            border-left-color: #27ae60;
        }
        .section-title.tips {
            border-left-color: #f39c12;
        }
        .section-content {
            padding: 10px 15px;
            background: #fafafa;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .section-content.answer {
            background: #f0fdf4;
            border-left: 3px solid #27ae60;
        }
        .section-content.tips {
            background: #fffbf0;
            border-left: 3px solid #f39c12;
        }
        code {
            background: #e8e8e8;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0;
        }
        pre code {
            background: none;
            padding: 0;
            color: #ecf0f1;
        }
        ul, ol {
            margin-left: 25px;
            margin-bottom: 10px;
        }
        li {
            margin-bottom: 5px;
        }
        strong {
            color: #2c3e50;
        }
        em {
            color: #7f8c8d;
        }
        @media print {
            body {
                background: white;
            }
            .exercise {
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Exercices $level - $subject</h1>
        <p style="color: #7f8c8d; margin-bottom: 30px;">Généré automatiquement le " . date('d/m/Y H:i:s') . "</p>

HTML;

    foreach ($exercises as $exercise) {
        $html .= <<<HTML
        <div class="exercise">
            <div class="exercise-title">$exercise[title]</div>
            <div class="metadata">

HTML;
        
        if ($exercise['difficulty']) {
            $html .= "<div class=\"meta-item\"><strong>Difficulté:</strong> {$exercise['difficulty']}</div>";
        }
        if ($exercise['domain']) {
            $html .= "<div class=\"meta-item\"><strong>Domaine:</strong> {$exercise['domain']}</div>";
        }
        if ($exercise['identifier']) {
            $html .= "<div class=\"meta-item\"><strong>ID:</strong> {$exercise['identifier']}</div>";
        }
        
        $html .= <<<HTML
            </div>

            <div class="section">
                <div class="section-title content">📝 Consigne</div>
                <div class="section-content">

HTML;
        
        // Convertir le markdown simple en HTML
        $contentHtml = markdownToHtml($exercise['content']);
        $html .= $contentHtml;
        
        $html .= <<<HTML
                </div>
            </div>

HTML;
        
        if (!empty($exercise['answer'])) {
            $html .= <<<HTML
            <div class="section">
                <div class="section-title answer">✅ Réponse</div>
                <div class="section-content answer">

HTML;
            $answerHtml = markdownToHtml($exercise['answer']);
            $html .= $answerHtml;
            $html .= <<<HTML
                </div>
            </div>

HTML;
        }
        
        if (!empty($exercise['tips'])) {
            $html .= <<<HTML
            <div class="section">
                <div class="section-title tips">💡 Ressources & Astuces</div>
                <div class="section-content tips">

HTML;
            $tipsHtml = markdownToHtml($exercise['tips']);
            $html .= $tipsHtml;
            $html .= <<<HTML
                </div>
            </div>

HTML;
        }
        
        $html .= <<<HTML
        </div>

HTML;
    }
    
    $html .= <<<HTML
    </div>
</body>
</html>

HTML;

    $fileName = $groupDir . '/index.html';
    file_put_contents($fileName, $html);
    $stats['html_files_created']++;
    
    return $fileName;
}

/**
 * Convertir le markdown simple en HTML
 */
function markdownToHtml($markdown) {
    // Convertir les liens [texte](url)
    $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $markdown);
    
    // Convertir les titres ## en <h3>
    $html = preg_replace('/^## (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^### (.+)$/m', '<h4>$1</h4>', $html);
    
    // Convertir les listes
    $html = preg_replace('/^\- (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*?<\/li>)/s', '<ul>$1</ul>', $html);
    $html = str_replace('</li><ul>', '</li>', $html);
    $html = str_replace('</ul><li>', '<li>', $html);
    
    // Convertir les gras et italiques
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
    $html = preg_replace('/_(.+?)_/', '<em>$1</em>', $html);
    
    // Convertir les sauts de ligne
    $html = preg_replace('/\n\n+/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';
    $html = preg_replace('/<p><\/p>/', '', $html);
    $html = preg_replace('/<h[34]>/', '</p><h3>', $html);
    $html = preg_replace('/<\/h[34]>/', '</h3><p>', $html);
    
    return $html;
}

/**
 * Générer le fichier JSON global
 */
function generateGlobalJSON($outputDir) {
    global $exercisesData;
    
    $json = json_encode($exercisesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($outputDir . '/all_exercises.json', $json);
    
    return count($exercisesData);
}

/**
 * Générer le fichier CSV global
 */
function generateGlobalCSV($outputDir) {
    global $exercisesData;
    
    $fp = fopen($outputDir . '/all_exercises.csv', 'w');
    
    // En-têtes
    fputcsv($fp, ['Title', 'Level', 'Subject', 'Content', 'Answer', 'Tips', 'Domain', 'Competence', 'Difficulty', 'Identifier'], ',', '"', '\\');
    
    foreach ($exercisesData as $exercise) {
        fputcsv($fp, [
            $exercise['title'],
            $exercise['level'],
            $exercise['subject'],
            $exercise['content'],
            $exercise['answer'],
            $exercise['tips'],
            $exercise['domain'] ?? '',
            $exercise['competence'] ?? '',
            $exercise['difficulty'] ?? '',
            $exercise['identifier'] ?? ''
        ], ',', '"', '\\');
    }
    
    fclose($fp);
    
    return count($exercisesData);
}

// Exécuter le scanning
echo "🔍 Scanning des fichiers markdown...\n";
scanDirectory($baseDir);

echo "✅ Fichiers markdown trouvés: {$stats['md_files_found']}\n";
echo "✅ Exercices extraits: {$stats['exercises_extracted']}\n\n";

// Générer les fichiers HTML par groupes
echo "📝 Génération des fichiers HTML...\n";

$groupedByLevelSubject = [];
foreach ($exercisesData as $exercise) {
    $key = $exercise['level'] . '|' . $exercise['subject'];
    if (!isset($groupedByLevelSubject[$key])) {
        $groupedByLevelSubject[$key] = [];
    }
    $groupedByLevelSubject[$key][] = $exercise;
}

foreach ($groupedByLevelSubject as $key => $exercises) {
    list($level, $subject) = explode('|', $key);
    generateGroupHTML($level, $subject, $exercises, $outputDir);
    echo "  ✓ $level - $subject (" . count($exercises) . " exercices)\n";
}

// Générer les fichiers JSON et CSV globaux
echo "\n📊 Génération des fichiers d'import...\n";
generateGlobalJSON($outputDir);
echo "  ✓ all_exercises.json (" . count($exercisesData) . " exercices)\n";

generateGlobalCSV($outputDir);
echo "  ✓ all_exercises.csv (" . count($exercisesData) . " exercices)\n";

// Résumé
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Conversion terminée !\n\n";
echo "📁 Fichiers générés dans: $outputDir\n";
echo "  • all_exercises.json (importable avec update_exercises_db.php)\n";
echo "  • all_exercises.csv (importable avec update_exercises_db.php)\n";
echo "  • Dossiers par niveau/matière avec index.html\n\n";
echo "Statistiques:\n";
echo "  • Fichiers markdown traités: {$stats['md_files_found']}\n";
echo "  • Exercices extraits: {$stats['exercises_extracted']}\n";
echo "  • Fichiers HTML créés: {$stats['html_files_created']}\n";
