<?php
/**
 * Script d'import des cours et exercices depuis les fichiers markdown
 * 
 * Usage: 
 *   php tools/import_courses_exercises.php
 *   ou accès via navigateur: http://moncoachscolaire.local/tools/import_courses_exercises.php?action=import
 * 
 * Actions disponibles:
 *   - scan: Affiche les fichiers trouvés sans importer
 *   - import: Importe tous les cours et exercices
 *   - clear: Vide les tables (DANGER!)
 */

// Initialiser
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/course_markdown_loader.php';

if (!$pdo) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Erreur de connexion à la base de données'
    ]));
}

// Sécurité : vérifier accès admin ou CLI
$is_cli = php_sapi_name() === 'cli';
$is_admin = !empty($_SESSION['admin']) || isset($_GET['admin_token']);
$is_demo = !empty($_SESSION['demo']);

if (!$is_cli && !$is_admin) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Accès non autorisé. Veuillez être connecté comme administrateur.'
    ]));
}

// En CLI, parser les arguments depuis $argv
if ($is_cli && isset($argv)) {
    foreach ($argv as $arg) {
        if (strpos($arg, '=') !== false) {
            list($key, $value) = explode('=', $arg, 2);
            $_GET[$key] = $value;
        }
    }
}

$action = $_GET['action'] ?? 'scan';

class CourseImporter {
    private $pdo;
    private $baseDir;
    private $stats = [
        'courses_found' => 0,
        'exercises_found' => 0,
        'courses_imported' => 0,
        'exercises_imported' => 0,
        'errors' => [],
        'warnings' => []
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->baseDir = __DIR__ . '/../';
    }
    
    /**
     * Scanne les fichiers markdown sans importer
     */
    public function scan() {
        echo "=== SCAN DES FICHIERS MARKDOWN ===\n\n";
        
        $this->scanCourses();
        $this->scanExercises();
        
        return $this->stats;
    }
    
    /**
     * Importe tous les cours et exercices
     */
    public function import() {
        echo "=== IMPORT DES COURS ET EXERCICES ===\n\n";
        
        // Scanner d'abord
        $this->scanCourses();
        $this->scanExercises();
        
        echo "\nDémarrage de l'import...\n";
        echo str_repeat("-", 60) . "\n";
        
        // Importer les cours
        $this->importCourses();
        
        // Importer les exercices
        $this->importExercises();
        
        // Créer les liens automatiques
        $this->autoLinkCoursesAndExercises();
        
        return $this->stats;
    }
    
    /**
     * Scanne tous les fichiers de cours
     */
    private function scanCourses() {
        $coursesDir = $this->baseDir . 'cours/college';
        
        if (!is_dir($coursesDir)) {
            $this->stats['errors'][] = "Dossier cours non trouvé: $coursesDir";
            return;
        }
        
        $files = $this->findMarkdownFiles($coursesDir);
        echo "COURS TROUVÉS: " . count($files) . "\n";
        
        foreach ($files as $file) {
            $this->stats['courses_found']++;
            echo "  - " . str_replace($this->baseDir, '', $file) . "\n";
        }
    }
    
    /**
     * Scanne tous les fichiers d'exercices
     */
    private function scanExercises() {
        $exercisesDir = $this->baseDir . 'exercices/college';
        
        if (!is_dir($exercisesDir)) {
            $this->stats['errors'][] = "Dossier exercices non trouvé: $exercisesDir";
            return;
        }
        
        $files = $this->findMarkdownFiles($exercisesDir);
        echo "\nEXERCICES TROUVÉS: " . count($files) . "\n";
        
        foreach ($files as $file) {
            $this->stats['exercises_found']++;
            echo "  - " . str_replace($this->baseDir, '', $file) . "\n";
        }
    }
    
    /**
     * Import des cours depuis markdown
     */
    private function importCourses() {
        echo "\n📚 IMPORT DES COURS\n";
        echo str_repeat("-", 60) . "\n";
        
        $coursesDir = $this->baseDir . 'cours/college';
        $files = $this->findMarkdownFiles($coursesDir);
        
        foreach ($files as $file) {
            // Normaliser les slashes pour Windows
            $file = str_replace('\\', '/', $file);
            $baseDir = str_replace('\\', '/', $this->baseDir);
            $relativePath = str_replace($baseDir, '', $file);
            
            // Parser le chemin pour extraire niveau, matière, etc.
            // Exemple: cours/college/6eme/mathematiques/cours-001-fractions.md
            if (preg_match('/cours\/college\/(\w+)\/([^\/]+)\/cours-(\d+)-.+\.md/', $relativePath, $matches)) {
                $level = $this->normalizeLevel($matches[1]);
                $subject = $this->normalizeSubject($matches[2]);
                $courseNumber = intval($matches[3]);
                
                // Charger le markdown pour extraire le titre
                $content = file_get_contents($file);
                preg_match('/^#\s+(.+)$/m', $content, $titleMatch);
                $title = $titleMatch[1] ?? "Cours $courseNumber - $subject";
                
                // Vérifier si le cours existe déjà
                try {
                    $checkStmt = $this->pdo->prepare("
                        SELECT Id FROM Courses 
                        WHERE Subject = ? AND Level = ? AND CourseNumber = ?
                    ");
                    $checkStmt->execute([$subject, $level, $courseNumber]);
                    $exists = $checkStmt->fetch();
                    
                    if (!$exists) {
                        // Insérer le nouveau cours
                        $insertStmt = $this->pdo->prepare("
                            INSERT INTO Courses (Subject, Level, CourseNumber, Title, FilePath, Keywords)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        
                        // Extraire les mots-clés du markdown
                        $keywords = $this->extractKeywords($content);
                        
                        $success = $insertStmt->execute([
                            $subject,
                            $level,
                            $courseNumber,
                            $title,
                            $relativePath,
                            $keywords
                        ]);
                        
                        if ($success) {
                            $this->stats['courses_imported']++;
                            echo "✓ Importé: $level - $subject - $title\n";
                        } else {
                            $this->stats['errors'][] = "Erreur import cours: $relativePath";
                        }
                    } else {
                        echo "⊘ Déjà existant: $level - $subject - $title\n";
                    }
                } catch (PDOException $e) {
                    $this->stats['errors'][] = "BD Error pour $relativePath: " . $e->getMessage();
                }
            } else {
                $this->stats['warnings'][] = "Format de fichier non reconnu: $relativePath";
            }
        }
    }
    
    /**
     * Import des exercices depuis markdown
     */
    private function importExercises() {
        echo "\n✏️ IMPORT DES EXERCICES\n";
        echo str_repeat("-", 60) . "\n";
        
        $exercisesDir = $this->baseDir . 'exercices/college';
        $files = $this->findMarkdownFiles($exercisesDir);
        
        foreach ($files as $file) {
            // Normaliser les slashes pour Windows
            $file = str_replace('\\', '/', $file);
            $baseDir = str_replace('\\', '/', $this->baseDir);
            $relativePath = str_replace($baseDir, '', $file);
            
            // Parser le chemin
            // Exemple: exercices/college/6eme/mathematiques/exercice-001-fractions.md
            if (preg_match('/exercices\/college\/(\w+)\/([^\/]+)\/exercice-(\d+)-.+\.md/', $relativePath, $matches)) {
                $level = $this->normalizeLevel($matches[1]);
                $subject = $this->normalizeSubject($matches[2]);
                $exerciseNumber = intval($matches[3]);
                
                // Charger le contenu
                $content = file_get_contents($file);
                preg_match('/^#\s+(.+)$/m', $content, $titleMatch);
                $title = $titleMatch[1] ?? "Exercice $exerciseNumber";
                
                // Extraire l'énoncé et la réponse
                $parts = $this->parseExerciseMarkdown($content);
                
                // Vérifier l'existence
                try {
                    $checkStmt = $this->pdo->prepare("
                        SELECT Id FROM Exercises 
                        WHERE Subject = ? AND Level = ? AND Title LIKE ?
                    ");
                    $checkStmt->execute([$subject, $level, '%' . str_replace('Exercice ', '', $title) . '%']);
                    $exists = $checkStmt->fetch();
                    
                    if (!$exists) {
                        // Insérer
                        $insertStmt = $this->pdo->prepare("
                            INSERT INTO Exercises (Subject, Level, Title, Content, Answer)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        
                        $success = $insertStmt->execute([
                            $subject,
                            $level,
                            $title,
                            $parts['enonce'],
                            $parts['reponse']
                        ]);
                        
                        if ($success) {
                            $this->stats['exercises_imported']++;
                            echo "✓ Importé: $level - $subject - $title\n";
                        } else {
                            $this->stats['errors'][] = "Erreur import exercice: $relativePath";
                        }
                    } else {
                        echo "⊘ Déjà existant: $level - $subject - $title\n";
                    }
                } catch (PDOException $e) {
                    $this->stats['errors'][] = "BD Error pour $relativePath: " . $e->getMessage();
                }
            }
        }
    }
    
    /**
     * Crée automatiquement les liens cours-exercices basés sur la nomenclature
     * Exemple: cours-001-pythagore.md + exercice-001-pythagore.md → lien automatique
     */
    private function autoLinkCoursesAndExercises() {
        echo "\n🔗 CRÉATION DES LIENS COURS-EXERCICES\n";
        echo str_repeat("-", 60) . "\n";
        
        try {
            // Récupérer tous les cours et exercices
            $coursesStmt = $this->pdo->query("SELECT Id, Subject, Level, Title FROM Courses ORDER BY Subject, Level");
            $courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $exercisesStmt = $this->pdo->query("SELECT Id, Subject, Level, Title FROM Exercises ORDER BY Subject, Level");
            $exercises = $exercisesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $linkedCount = 0;
            
            foreach ($courses as $course) {
                // Chercher un exercice correspondant
                $courseTitle = strtolower(preg_replace('/[^a-z0-9]/', '', $course['Title']));
                
                foreach ($exercises as $exercise) {
                    if ($course['Subject'] === $exercise['Subject'] && 
                        $course['Level'] === $exercise['Level']) {
                        
                        $exerciseTitle = strtolower(preg_replace('/[^a-z0-9]/', '', $exercise['Title']));
                        
                        // Chercher des correspondances dans les titres
                        if (strlen($courseTitle) > 3 && strpos($exerciseTitle, substr($courseTitle, 0, 8)) !== false) {
                            // Vérifier si le lien existe déjà
                            $checkStmt = $this->pdo->prepare("
                                SELECT Id FROM ExerciseCourseLinks 
                                WHERE ExerciseId = ? AND CourseId = ?
                            ");
                            $checkStmt->execute([$exercise['Id'], $course['Id']]);
                            
                            if (!$checkStmt->fetch()) {
                                // Créer le lien
                                $linkStmt = $this->pdo->prepare("
                                    INSERT INTO ExerciseCourseLinks (ExerciseId, CourseId, LinkType)
                                    VALUES (?, ?, 'theory')
                                ");
                                $linkStmt->execute([$exercise['Id'], $course['Id']]);
                                $linkedCount++;
                                echo "🔗 Lié: " . $course['Level'] . " - " . $course['Subject'] . 
                                     "\n   Cours: " . $course['Title'] . 
                                     "\n   Ex: " . $exercise['Title'] . "\n";
                            }
                        }
                    }
                }
            }
            
            echo "\nTotal de liens créés: $linkedCount\n";
        } catch (PDOException $e) {
            $this->stats['errors'][] = "Erreur création liens: " . $e->getMessage();
        }
    }
    
    /**
     * Trouve tous les fichiers markdown dans un dossier
     */
    private function findMarkdownFiles($dir) {
        $files = [];
        
        if (!is_dir($dir)) {
            return $files;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $files[] = $file->getRealPath();
            }
        }
        
        sort($files);
        return $files;
    }
    
    /**
     * Normalise un niveau
     */
    private function normalizeLevel($level) {
        $mapping = [
            '6eme' => '6ème', '6EME' => '6ème',
            '5eme' => '5ème', '5EME' => '5ème',
            '4eme' => '4ème', '4EME' => '4ème',
            '3eme' => '3ème', '3EME' => '3ème',
        ];
        return $mapping[trim($level)] ?? trim($level);
    }
    
    /**
     * Normalise une matière
     */
    private function normalizeSubject($subject) {
        $subject = trim($subject);
        $mapping = [
            'mathematiques' => 'Mathématiques',
            'math' => 'Mathématiques',
            'francais' => 'Français',
            'anglais' => 'Anglais',
            'svt' => 'SVT',
            'physique-chimie' => 'Physique-Chimie',
            'histoire-geo' => 'Histoire-Géo',
            'histoire-geographie' => 'Histoire-Géo',
        ];
        return $mapping[strtolower($subject)] ?? ucfirst(str_replace('-', ' ', $subject));
    }
    
    /**
     * Extrait les mots-clés d'un contenu markdown
     */
    private function extractKeywords($content) {
        $keywords = [];
        
        // Chercher les mots-clés dans le titre principal
        if (preg_match('/^#\s+(.+)$/m', $content, $match)) {
            $title = strtolower($match[1]);
            $keywords[] = $title;
        }
        
        // Chercher dans les sections ## 
        if (preg_match_all('/^##\s+(.+)$/m', $content, $matches)) {
            foreach ($matches[1] as $section) {
                $section = strtolower(preg_replace('/[^a-z0-9]/', ' ', $section));
                if (strlen($section) > 3) {
                    $keywords[] = trim($section);
                }
            }
        }
        
        return implode(',', array_unique($keywords));
    }
    
    /**
     * Parse un fichier exercice pour extraire l'énoncé et la réponse
     */
    private function parseExerciseMarkdown($content) {
        $parts = [
            'enonce' => '',
            'reponse' => ''
        ];
        
        // Chercher la section "## Énoncé" ou "## Exercice"
        if (preg_match('/^##\s+(?:Énoncé|Exercice|Énoncé de l\'exercice)(.*?)(?:^##|$)/ms', $content, $match)) {
            $parts['enonce'] = trim($match[1]);
        } else {
            // Fallback: prendre tout le contenu avant la première section de réponse
            $parts['enonce'] = substr($content, 0, strpos($content, '## '));
        }
        
        // Chercher la section "## Réponse" ou "## Correction" ou "## Solution"
        if (preg_match('/^##\s+(?:Réponse|Correction|Solution)(.*?)(?:^##|$)/ms', $content, $match)) {
            $parts['reponse'] = trim($match[1]);
        }
        
        // Sanitize placeholders dans l'énoncé et la réponse
        $parts['enonce'] = $this->sanitizePlaceholderText($parts['enonce']);
        $parts['reponse'] = $this->sanitizePlaceholderText($parts['reponse']);
        
        return $parts;
    }

    /**
     * Supprime les lignes de réponses génériques placeholders (a) Réponse correcte, etc.)
     */
    private function sanitizePlaceholderText($text) {
        if (empty($text)) return $text;
        
        $lines = preg_split('/\r?\n/', $text);
        $clean = [];
        
        // Expressions des placeholders fréquents
        $placeholderPatterns = [
            '/^[a-dA-D][\)\.\-]\s*Réponse\s+correcte\s*$/u',
            '/^[a-dA-D][\)\.\-]\s*Une\s+autre\s+réponse\s+possible\s*$/u',
            '/^[a-dA-D][\)\.\-]\s*aucune\s+des\s+réponses\s*$/ui',
            '/^[a-dA-D][\)\.\-]\s*Réponse\s+alternative\s*$/u',
            '/^\-\s*Réponse\s+correcte\s*$/u',
            '/^\-\s*Une\s+autre\s+réponse\s+possible\s*$/u',
            '/^\-\s*aucune\s+des\s+réponses\s*$/ui',
            '/^\-\s*Réponse\s+alternative\s*$/u'
        ];
        
        foreach ($lines as $line) {
            $trim = trim($line);
            $isPlaceholder = false;
            foreach ($placeholderPatterns as $pat) {
                if (preg_match($pat, $trim)) {
                    $isPlaceholder = true;
                    break;
                }
            }
            if (!$isPlaceholder) {
                $clean[] = $line;
            }
        }
        
        // Nettoyer les lignes vides multiples
        $result = preg_replace("/\n{3,}/", "\n\n", implode("\n", $clean));
        return trim($result);
    }
    
    /**
     * Vide les tables (DANGER!)
     */
    public function clearTables() {
        echo "⚠️  SUPPRESSION DE TOUTES LES DONNÉES\n";
        echo str_repeat("-", 60) . "\n";
        
        try {
            // Demander confirmation
            if (!$_GET['confirm'] ?? false) {
                echo "ATTENTION: Cette action est IRRÉVERSIBLE!\n";
                echo "Pour confirmer, ajouter &confirm=yes à l'URL\n";
                return;
            }
            
            $this->pdo->exec("DELETE FROM ExerciseCourseLinks");
            echo "✓ Table ExerciseCourseLinks vidée\n";
            
            $this->pdo->exec("DELETE FROM Courses");
            echo "✓ Table Courses vidée\n";
            
            $this->pdo->exec("DELETE FROM UserCourseProgress");
            echo "✓ Table UserCourseProgress vidée\n";
            
            echo "\nTables vidées avec succès.\n";
        } catch (PDOException $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
        }
    }
}

// Exécuter l'action demandée
$importer = new CourseImporter($pdo);

switch ($action) {
    case 'scan':
        $stats = $importer->scan();
        break;
    
    case 'import':
        $stats = $importer->import();
        break;
    
    case 'clear':
        $importer->clearTables();
        exit;
    
    default:
        echo "Action inconnue: $action\n";
        $stats = [];
}

// Afficher le résumé
if (!empty($stats)) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "RÉSUMÉ\n";
    echo str_repeat("=", 60) . "\n";
    echo "Cours trouvés: " . $stats['courses_found'] . "\n";
    echo "Cours importés: " . $stats['courses_imported'] . "\n";
    echo "Exercices trouvés: " . $stats['exercises_found'] . "\n";
    echo "Exercices importés: " . $stats['exercises_imported'] . "\n";
    
    if (!empty($stats['errors'])) {
        echo "\n❌ Erreurs (" . count($stats['errors']) . "):\n";
        foreach ($stats['errors'] as $error) {
            echo "  - $error\n";
        }
    }
    
    if (!empty($stats['warnings'])) {
        echo "\n⚠️ Avertissements (" . count($stats['warnings']) . "):\n";
        foreach ($stats['warnings'] as $warning) {
            echo "  - $warning\n";
        }
    }
}

echo "\n✅ Script terminé.\n";
