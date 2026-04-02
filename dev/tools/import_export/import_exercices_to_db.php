<?php
/**
 * Script d'import des exercices Markdown dans la base de données
 * 
 * Usage: php tools/import_exercices_to_db.php [--dry-run] [--limit=N]
 * 
 * Options:
 *   --dry-run  : Affiche ce qui serait importé sans insérer en base
 *   --limit=N  : Limite l'import aux N premiers exercices
 */

// Configuration
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../db/connection.php';

// Charger Parsedown (sera chargé via autoload)

// Options de ligne de commande
$dryRun = in_array('--dry-run', $argv);
$limit = null;
$sourceType = 'all'; // 'all', 'exercices', 'sources'
foreach ($argv as $arg) {
    if (preg_match('/--limit=(\d+)/', $arg, $matches)) {
        $limit = (int)$matches[1];
    }
    if (preg_match('/--source=(exercices|sources|all)/', $arg, $matches)) {
        $sourceType = $matches[1];
    }
}

// Vérifier la connexion à la base de données (sauf en mode dry-run)
if (!$dryRun) {
    if (!isset($pdo) || !$pdo) {
        // Essayer de se connecter avec des valeurs par défaut
        try {
            $pdo = new PDO("mysql:host=127.0.0.1;dbname=moncoachscolaire;charset=utf8mb4", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            echo "✅ Connexion à la base de données établie (valeurs par défaut).\n\n";
        } catch (PDOException $e) {
            die("❌ Erreur : Impossible de se connecter à la base de données.\n" . 
                "   Message : " . $e->getMessage() . "\n" .
                "   Assurez-vous que MySQL est démarré et que la base 'moncoachscolaire' existe.\n");
        }
    } else {
        echo "✅ Connexion à la base de données établie.\n\n";
    }
}

echo "🚀 Démarrage de l'import des exercices...\n\n";

// Charger Parsedown si disponible
$parsedown = null;
if (class_exists('Parsedown')) {
    $parsedown = new Parsedown();
    $parsedown->setSafeMode(false);
    echo "✅ Parsedown chargé.\n";
} else {
    echo "⚠️  Parsedown non disponible. Le Markdown sera converti en HTML basique.\n";
    echo "   Pour installer Parsedown : composer require erusev/parsedown\n\n";
}

// Fonction de conversion Markdown basique (sans bibliothèque)
function convertMarkdownBasic($markdown) {
    $html = $markdown;
    
    // Titres
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
    
    // Gras
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    
    // Italique
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
    
    // Listes
    $html = preg_replace('/^\- (.+)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/^(\d+)\. (.+)$/m', '<li>$2</li>', $html);
    
    // Paragraphes (lignes vides = nouveaux paragraphes)
    $paragraphs = preg_split('/\n\s*\n/', $html);
    $html = '';
    foreach ($paragraphs as $para) {
        $para = trim($para);
        if (!empty($para) && !preg_match('/^<[h|u|o]l/', $para)) {
            $html .= '<p>' . nl2br($para) . '</p>';
        } else {
            $html .= $para;
        }
    }
    
    // Code inline
    $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);
    
    return $html;
}

// Fonction de normalisation des niveaux
function normalizeLevel($level) {
    if (empty($level)) return null;
    
    $levelMapping = [
        '6ème' => '6ème', '6eme' => '6ème', '6eme' => '6ème', '6' => '6ème',
        '5ème' => '5ème', '5eme' => '5ème', '5eme' => '5ème', '5' => '5ème',
        '4ème' => '4ème', '4eme' => '4ème', '4eme' => '4ème', '4' => '4ème',
        '3ème' => '3ème', '3eme' => '3ème', '3eme' => '3ème', '3' => '3ème',
        'Seconde' => 'Seconde', 'seconde' => 'Seconde', '2nde' => 'Seconde', '2NDE' => 'Seconde',
        'Première' => 'Première', 'Premiere' => 'Première', 'première' => 'Première', 
        'premiere' => 'Première', '1ère' => 'Première', '1ere' => 'Première',
        'Terminale' => 'Terminale', 'terminale' => 'Terminale', 'TERMINALE' => 'Terminale',
        'BAC' => 'Terminale', 'bac' => 'Terminale'
    ];
    
    $levelClean = trim($level);
    if (isset($levelMapping[$levelClean])) {
        return $levelMapping[$levelClean];
    }
    
    // Fallback : chercher par motif
    if (stripos($level, '6') !== false) return '6ème';
    if (stripos($level, '5') !== false) return '5ème';
    if (stripos($level, '4') !== false) return '4ème';
    if (stripos($level, '3') !== false && stripos($level, '1') === false) return '3ème';
    if (stripos($level, 'seconde') !== false || stripos($level, '2nde') !== false) return 'Seconde';
    if (stripos($level, 'première') !== false || stripos($level, 'premiere') !== false || stripos($level, '1ère') !== false) return 'Première';
    if (stripos($level, 'terminale') !== false || stripos($level, 'bac') !== false) return 'Terminale';
    
    return $levelClean; // Retourner tel quel si pas de correspondance
}

// Fonction pour parser un fichier Markdown
function parseExerciseFile($filePath, $parsedown) {
    $content = file_get_contents($filePath);
    if (!$content) {
        return null;
    }
    
    $lines = explode("\n", $content);
    $metadata = [];
    $currentSection = '';
    $sections = [
        'énoncé' => '',
        'correction' => '',
        'gamification' => ''
    ];
    
    // Parser les métadonnées (lignes avec **)
    foreach ($lines as $line) {
        // Titre principal
        if (preg_match('/^#\s+Exercice\s+\d+\s*:\s*(.+)$/', $line, $matches)) {
            $metadata['title'] = trim($matches[1]);
        }
        
        // Métadonnées (format **Clé** : Valeur)
        if (preg_match('/^\*\*([^:]+)\*\*\s*:\s*(.+)$/', $line, $matches)) {
            $key = strtolower(trim($matches[1]));
            $value = trim($matches[2]);
            
            switch ($key) {
                case 'niveau':
                    $metadata['level'] = $value;
                    break;
                case 'domaine':
                    $metadata['domain'] = $value;
                    // Extraire la matière du domaine
                    if (stripos($value, 'math') !== false || stripos($value, 'nombre') !== false || stripos($value, 'géométrie') !== false) {
                        $metadata['subject'] = 'Mathématiques';
                    } elseif (stripos($value, 'français') !== false || stripos($value, 'grammaire') !== false || stripos($value, 'littérature') !== false) {
                        $metadata['subject'] = 'Français';
                    }
                    break;
                case 'compétence':
                    $metadata['competence'] = $value;
                    break;
                case 'difficulté':
                    $metadata['difficulty'] = $value;
                    break;
                case 'identifiant':
                    $metadata['identifier'] = $value;
                    // Extraire la matière de l'identifiant si pas déjà défini
                    if (!isset($metadata['subject'])) {
                        if (stripos($value, 'MATH') !== false) {
                            $metadata['subject'] = 'Mathématiques';
                        } elseif (stripos($value, 'FR') !== false) {
                            $metadata['subject'] = 'Français';
                        }
                    }
                    break;
            }
        }
        
        // Détecter les sections (## Énoncé, ## Correction, etc.)
        if (preg_match('/^##\s+(.+)$/', $line, $matches)) {
            $sectionTitle = trim($matches[1]);
            $sectionTitleLower = mb_strtolower($sectionTitle);
            
            // Normaliser les accents et détecter la section
            $sectionTitleLower = str_replace(['é', 'è', 'ê'], ['e', 'e', 'e'], $sectionTitleLower);
            
            if (stripos($sectionTitleLower, 'enonce') !== false || stripos($sectionTitleLower, 'énoncé') !== false) {
                $currentSection = 'énoncé';
            } elseif (stripos($sectionTitleLower, 'correction') !== false) {
                $currentSection = 'correction';
            } elseif (stripos($sectionTitleLower, 'gamification') !== false) {
                $currentSection = 'gamification';
            } elseif ($currentSection) {
                // Si on rencontre une autre section (Rappel, Astuce, etc.), arrêter la section précédente
                $currentSection = '';
            }
            continue; // Ne pas inclure la ligne du titre de section
        }
        
        // Ajouter le contenu à la section appropriée
        if ($currentSection && isset($sections[$currentSection])) {
            // Ne pas inclure les sections de gamification dans l'énoncé ou la correction
            if ($currentSection !== 'gamification') {
                $sections[$currentSection] .= $line . "\n";
            }
        }
        
        // Parser la section gamification
        if ($currentSection === 'gamification') {
            if (preg_match('/- \*\*Cristaux[^\*]+\*\*\s*:\s*(\d+)/i', $line, $matches)) {
                $metadata['cristaux'] = (int)$matches[1];
            }
            if (preg_match('/- \*\*Points d\'expérience[^\*]+\*\*\s*:\s*(\d+)/i', $line, $matches)) {
                $metadata['xp'] = (int)$matches[1];
            }
            if (preg_match('/- \*\*Badge[^\*]+\*\*\s*:\s*["\'](.+?)["\']/', $line, $matches)) {
                $metadata['badge'] = trim($matches[1]);
            }
        }
    }
    
    // Si la matière n'est pas détectée, essayer de la déduire du chemin du fichier
    if (!isset($metadata['subject'])) {
        if (stripos($filePath, 'mathematiques') !== false || stripos($filePath, 'math') !== false) {
            $metadata['subject'] = 'Mathématiques';
        } elseif (stripos($filePath, 'francais') !== false || stripos($filePath, 'français') !== false) {
            $metadata['subject'] = 'Français';
        }
    }
    
    // DÉTECTION DU NIVEAU DEPUIS LE CHEMIN DU FICHIER (source de vérité)
    $levelFromPath = null;
    if (preg_match('/college[\/\\\\](\d+[èmee]+)/i', $filePath, $pathMatches)) {
        $levelFromPath = normalizeLevel($pathMatches[1]);
    } elseif (preg_match('/lycee[\/\\\\](seconde|premi[èe]re|terminale)/i', $filePath, $pathMatches)) {
        $levelFromPath = normalizeLevel($pathMatches[1]);
    } elseif (preg_match('/bac[\/\\\\]/i', $filePath)) {
        $levelFromPath = 'Terminale'; // BAC = Terminale pour les exercices
    }
    
    // Normaliser le niveau déclaré dans le fichier
    $levelFromFile = null;
    if (isset($metadata['level'])) {
        $levelFromFile = normalizeLevel($metadata['level']);
    }
    
    // VÉRIFICATION DE COHÉRENCE : Comparer le niveau du dossier avec le niveau déclaré
    if ($levelFromPath && $levelFromFile && $levelFromPath !== $levelFromFile) {
        // INCOHÉRENCE DÉTECTÉE : Le niveau du dossier ne correspond pas au niveau déclaré
        $metadata['_warning'] = "INCOHÉRENCE : Fichier dans dossier '$levelFromPath' mais niveau déclaré '$levelFromFile'. Utilisation du niveau du dossier.";
        $metadata['level'] = $levelFromPath; // Utiliser le niveau du dossier (source de vérité)
    } elseif ($levelFromPath && !$levelFromFile) {
        // Pas de niveau déclaré, utiliser celui du dossier
        $metadata['level'] = $levelFromPath;
    } elseif (!$levelFromPath && $levelFromFile) {
        // Pas de niveau dans le chemin, utiliser celui déclaré
        $metadata['level'] = $levelFromFile;
    } elseif ($levelFromPath && $levelFromFile && $levelFromPath === $levelFromFile) {
        // Cohérence parfaite
        $metadata['level'] = $levelFromPath;
    } elseif (!$levelFromPath && !$levelFromFile) {
        // Aucun niveau trouvé - sera rejeté lors de la validation
        $metadata['level'] = null;
    }
    
    // Convertir Markdown en HTML
    if ($parsedown) {
        $metadata['content_html'] = $parsedown->text($sections['énoncé']);
        $metadata['answer_html'] = $parsedown->text($sections['correction']);
    } else {
        // Conversion basique Markdown → HTML (sans bibliothèque)
        $metadata['content_html'] = convertMarkdownBasic($sections['énoncé']);
        $metadata['answer_html'] = convertMarkdownBasic($sections['correction']);
    }
    
    // Garder aussi le markdown brut
    $metadata['content_md'] = trim($sections['énoncé']);
    $metadata['answer_md'] = trim($sections['correction']);
    
    return $metadata;
}

// Fonction pour trouver tous les fichiers d'exercices
function findExerciseFiles($directory) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $filename = $file->getFilename();
            // Ignorer les fichiers non-exercices (README, INDEX, etc.)
            if (strpos(strtolower($filename), 'exercice-') === 0 || 
                preg_match('/^exercice-\d+/i', $filename)) {
                $files[] = $file->getPathname();
            }
        }
    }
    
    return $files;
}

// Fonction pour parser le fichier sources (docs/exercices-sources-par-niveau.md)
function parseSourcesFile($filePath, $parsedown) {
    $content = file_get_contents($filePath);
    if (!$content) {
        return [];
    }
    
    $lines = explode("\n", $content);
    $exercises = [];
    $currentLevel = null;
    $currentSubject = null;
    $currentExercise = null;
    $currentSection = null;
    
    foreach ($lines as $lineNum => $line) {
        $lineTrimmed = trim($line);
        $lineOriginal = $line; // Garder la ligne originale pour préserver les sauts de ligne
        
        // Détecter les niveaux (## 🎓 6ème, ## 🎓 5ème, etc.)
        if (preg_match('/^##\s*[🎓📚]*\s*(\d+[èmee]+|Seconde|Premi[èe]re|Terminale|BAC)\s*$/i', $lineTrimmed, $matches)) {
            $levelRaw = trim($matches[1]);
            // Normaliser le niveau
            $levelMapping = [
                '6ème' => '6ème', '6eme' => '6ème', '6eme' => '6ème',
                '5ème' => '5ème', '5eme' => '5ème', '5eme' => '5ème',
                '4ème' => '4ème', '4eme' => '4ème', '4eme' => '4ème',
                '3ème' => '3ème', '3eme' => '3ème', '3eme' => '3ème',
                'Seconde' => 'Seconde', '2nde' => 'Seconde',
                'Première' => 'Première', 'Premiere' => 'Première', '1ère' => 'Première',
                'Terminale' => 'Terminale',
                'BAC' => 'Terminale'
            ];
            $currentLevel = $levelMapping[$levelRaw] ?? $levelRaw;
            $currentSubject = null;
            continue;
        }
        
        // Détecter un nouvel exercice (#### Exercice X : Titre) - AVANT les matières pour éviter les conflits
        if (preg_match('/^####\s*Exercice\s+\d+\s*:\s*(.+?)$/', $lineTrimmed, $matches)) {
            // Sauvegarder l'exercice précédent s'il existe
            if ($currentExercise && $currentLevel && $currentSubject) {
                if (!empty($currentExercise['title']) && 
                    !empty($currentExercise['content']) && 
                    !empty($currentExercise['answer'])) {
                    $currentExercise['level'] = $currentLevel;
                    $currentExercise['subject'] = $currentSubject;
                    $exercises[] = $currentExercise;
                }
            }
            
            // Nouvel exercice
            $currentExercise = [
                'title' => trim($matches[1]),
                'content' => '',
                'answer' => '',
                'level' => $currentLevel,
                'subject' => $currentSubject
            ];
            $currentSection = null;
            continue;
        }
        
        // Détecter les matières (### Mathématiques, ### Français, etc.) - APRÈS les exercices
        if (preg_match('/^###\s*(.+?)$/', $line, $matches)) {
            $subjectRaw = trim($matches[1]);
            // Ignorer si c'est un exercice (#### Exercice)
            if (preg_match('/^Exercice\s+\d+/i', $subjectRaw)) {
                continue; // Ignorer cette ligne, c'est un titre d'exercice mal détecté
            }
            // Normaliser la matière
            $subjectMapping = [
                'Mathématiques' => 'Mathématiques', 'Math' => 'Mathématiques',
                'Français' => 'Français', 'Francais' => 'Français',
                'Histoire-Géographie' => 'Histoire-Géographie', 'Histoire' => 'Histoire-Géographie',
                'SVT' => 'SVT', 'Sciences' => 'SVT',
                'Physique-Chimie' => 'Physique-Chimie', 'Physique' => 'Physique-Chimie',
                'Anglais' => 'Anglais', 'English' => 'Anglais',
                'Philosophie' => 'Philosophie', 'Philo' => 'Philosophie'
            ];
            $currentSubject = $subjectMapping[$subjectRaw] ?? $subjectRaw;
            continue;
        }
        
        // Métadonnées de l'exercice (**Matière**, **Niveau**, **Type**)
        if (preg_match('/^\*\*([^:]+)\*\*\s*:\s*(.+?)$/', $lineTrimmed, $matches)) {
            $key = strtolower(trim($matches[1]));
            $value = trim($matches[2]);
            
            if ($key === 'matière' && $currentExercise) {
                $subjectMapping = [
                    'Mathématiques' => 'Mathématiques', 'Math' => 'Mathématiques',
                    'Français' => 'Français', 'Francais' => 'Français',
                    'Histoire-Géographie' => 'Histoire-Géographie',
                    'SVT' => 'SVT', 'Sciences' => 'SVT',
                    'Physique-Chimie' => 'Physique-Chimie',
                    'Anglais' => 'Anglais',
                    'Philosophie' => 'Philosophie'
                ];
                $currentExercise['subject'] = $subjectMapping[$value] ?? $value;
            } elseif ($key === 'niveau' && $currentExercise) {
                $levelMapping = [
                    '6ème' => '6ème', '6eme' => '6ème',
                    '5ème' => '5ème', '5eme' => '5ème',
                    '4ème' => '4ème', '4eme' => '4ème',
                    '3ème' => '3ème', '3eme' => '3ème',
                    'Seconde' => 'Seconde',
                    'Première' => 'Première', 'Premiere' => 'Première',
                    'Terminale' => 'Terminale',
                    'BAC' => 'Terminale'
                ];
                $currentExercise['level'] = $levelMapping[$value] ?? $value;
            }
            continue;
        }
        
        // Détecter les sections **Contenu** et **Réponse attendue**
        // Format: **Contenu** : (avec deux-points, espaces optionnels)
        if (preg_match('/^\*\*Contenu\*\*\s*:\s*$/', $lineTrimmed)) {
            $currentSection = 'content';
            continue; // Ne pas inclure la ligne de titre
        }
        // Format: **Réponse attendue** : (avec deux-points, espaces optionnels)
        // Note: "Réponse" peut être écrit avec ou sans accent
        if (preg_match('/^\*\*R[ée]ponse attendue\*\*\s*:\s*$/', $lineTrimmed)) {
            $currentSection = 'answer';
            continue; // Ne pas inclure la ligne de titre
        }
        
        // Si on rencontre un nouvel exercice, sauvegarder l'exercice précédent
        if ($currentExercise && preg_match('/^####\s*Exercice/', $lineTrimmed)) {
            // Sauvegarder l'exercice précédent s'il est complet
            if (!empty($currentExercise['title']) && 
                !empty($currentExercise['content']) && 
                !empty($currentExercise['answer']) &&
                $currentLevel && $currentSubject) {
                $currentExercise['level'] = $currentLevel;
                $currentExercise['subject'] = $currentSubject;
                $exercises[] = $currentExercise;
            }
            $currentSection = null;
            // Ne pas continuer, laisser le traitement normal continuer pour créer le nouvel exercice
        }
        
        // Si on rencontre une nouvelle matière ou un nouveau niveau, sauvegarder l'exercice en cours
        if ($currentExercise && $currentSection && (
            (preg_match('/^###\s*(.+?)$/', $lineTrimmed) && !preg_match('/^###\s*Exercice/', $lineTrimmed)) || 
            preg_match('/^##\s*[🎓📚]/', $lineTrimmed)
        )) {
            // Sauvegarder l'exercice avant de passer à la section suivante
            if (!empty($currentExercise['title']) && 
                !empty($currentExercise['content']) && 
                !empty($currentExercise['answer']) &&
                $currentLevel && $currentSubject) {
                $currentExercise['level'] = $currentLevel;
                $currentExercise['subject'] = $currentSubject;
                $exercises[] = $currentExercise;
            }
            $currentSection = null;
            $currentExercise = null;
            // Ne pas continuer, laisser le traitement normal continuer
        }
        
        // Ajouter le contenu à la section appropriée
        if ($currentExercise && $currentSection) {
            // Ajouter toutes les lignes (même vides) pour préserver le formatage
            // Utiliser la ligne originale pour préserver les sauts de ligne
            if ($currentSection === 'content') {
                $currentExercise['content'] .= ($currentExercise['content'] ? "\n" : '') . $lineOriginal;
            } elseif ($currentSection === 'answer') {
                $currentExercise['answer'] .= ($currentExercise['answer'] ? "\n" : '') . $lineOriginal;
            }
        }
    }
    
    // Ajouter le dernier exercice
    if ($currentExercise && $currentLevel && $currentSubject) {
        if (!empty($currentExercise['title']) && 
            !empty($currentExercise['content']) && 
            !empty($currentExercise['answer'])) {
            $currentExercise['level'] = $currentLevel;
            $currentExercise['subject'] = $currentSubject;
            $exercises[] = $currentExercise;
        }
    }
    
    // Convertir en format compatible avec le reste du script
    $result = [];
    foreach ($exercises as $ex) {
        // Utiliser le Markdown brut pour Content et Answer
        $content_md = trim($ex['content']);
        $answer_md = trim($ex['answer']);
        
        // Convertir en HTML si Parsedown est disponible
        if ($parsedown) {
            $content_html = $parsedown->text($content_md);
            $answer_html = $parsedown->text($answer_md);
        } else {
            $content_html = convertMarkdownBasic($content_md);
            $answer_html = convertMarkdownBasic($answer_md);
        }
        
        $result[] = [
            'title' => $ex['title'],
            'level' => $ex['level'],
            'subject' => $ex['subject'],
            'content_html' => $content_html,
            'answer_html' => $answer_html,
            'content_md' => $content_md,
            'answer_md' => $answer_md
        ];
    }
    
    return $result;
}

// Trouver tous les fichiers d'exercices
$allExercises = [];
$totalFiles = 0;

if ($sourceType === 'all' || $sourceType === 'exercices') {
    $exercicesDir = __DIR__ . '/../exercices';
    if (is_dir($exercicesDir)) {
        $files = findExerciseFiles($exercicesDir);
        $totalFiles = count($files);
        echo "📁 $totalFiles fichiers d'exercices Markdown trouvés dans exercices/.\n\n";
        
        if ($limit) {
            $files = array_slice($files, 0, $limit);
            echo "⚠️  Mode limité : traitement des $limit premiers fichiers.\n\n";
        }
        
        foreach ($files as $filePath) {
            $allExercises[] = ['type' => 'file', 'path' => $filePath];
        }
    } else {
        echo "⚠️  Répertoire exercices/ non trouvé.\n\n";
    }
}

if ($sourceType === 'all' || $sourceType === 'sources') {
    $sourcesFile = __DIR__ . '/../docs/exercices-sources-par-niveau.md';
    if (file_exists($sourcesFile)) {
        echo "📄 Parsing du fichier sources : docs/exercices-sources-par-niveau.md\n\n";
        $sourceExercises = parseSourcesFile($sourcesFile, $parsedown);
        $sourcesCount = count($sourceExercises);
        echo "📋 $sourcesCount exercices extraits du fichier sources.\n\n";
        
        foreach ($sourceExercises as $ex) {
            $allExercises[] = ['type' => 'source', 'data' => $ex];
        }
        $totalFiles += $sourcesCount;
    } else {
        echo "⚠️  Fichier docs/exercices-sources-par-niveau.md non trouvé.\n\n";
    }
}

$totalExercises = count($allExercises);
if ($totalExercises === 0) {
    die("❌ Aucun exercice à traiter.\n");
}

echo "📁 $totalFiles fichiers d'exercices trouvés.\n\n";

if ($limit) {
    $files = array_slice($files, 0, $limit);
    echo "⚠️  Mode limité : traitement des $limit premiers fichiers.\n\n";
}

if ($dryRun) {
    echo "🔍 Mode DRY-RUN activé (aucune modification en base de données).\n\n";
}

// Statistiques
$stats = [
    'total' => 0,
    'success' => 0,
    'errors' => 0,
    'skipped' => 0
];

$errors = [];

// Fonction pour insérer/mettre à jour un exercice
function insertOrUpdateExercise($exercise, $pdo, $dryRun) {
    // Valider les champs requis
    if (empty($exercise['subject']) || empty($exercise['level']) || empty($exercise['title'])) {
        return ['status' => 'skipped', 'message' => 'Métadonnées manquantes (matière, niveau ou titre).'];
    }
    
    if (empty($exercise['content_html']) || empty($exercise['answer_html'])) {
        return ['status' => 'skipped', 'message' => 'Contenu ou réponse manquante.'];
    }
    
        // Afficher les informations
        echo "   ✓ Matière: {$exercise['subject']}\n";
        echo "   ✓ Niveau: {$exercise['level']}\n";
        echo "   ✓ Titre: {$exercise['title']}\n";
        
        // Afficher les avertissements s'il y en a
        if (isset($exercise['_warning'])) {
            echo "   ⚠️  ATTENTION : {$exercise['_warning']}\n";
        }
    
    if ($dryRun) {
        echo "   🔍 [DRY-RUN] Cet exercice serait inséré.\n\n";
        return ['status' => 'success'];
    }
    
    // Vérifier si l'exercice existe déjà (par titre et niveau)
    $checkStmt = $pdo->prepare("SELECT Id FROM Exercises WHERE Title = ? AND Level = ? AND Subject = ?");
    $checkStmt->execute([
        $exercise['title'],
        $exercise['level'],
        $exercise['subject']
    ]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // Mettre à jour l'exercice existant
        $updateStmt = $pdo->prepare("
            UPDATE Exercises 
            SET Content = ?, Answer = ?, Subject = ?, Level = ?
            WHERE Id = ?
        ");
        $updateStmt->execute([
            $exercise['content_html'],
            $exercise['answer_html'],
            $exercise['subject'],
            $exercise['level'],
            $existing['Id']
        ]);
        echo "   🔄 Exercice mis à jour (ID: {$existing['Id']})\n\n";
        return ['status' => 'success', 'id' => $existing['Id'], 'action' => 'updated'];
    } else {
        // Insérer un nouvel exercice
        $insertStmt = $pdo->prepare("
            INSERT INTO Exercises (Subject, Level, Title, Content, Answer)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insertStmt->execute([
            $exercise['subject'],
            $exercise['level'],
            $exercise['title'],
            $exercise['content_html'],
            $exercise['answer_html']
        ]);
        $newId = $pdo->lastInsertId();
        echo "   ✅ Exercice inséré (ID: $newId)\n\n";
        return ['status' => 'success', 'id' => $newId, 'action' => 'inserted'];
    }
}

// Traiter chaque exercice
foreach ($allExercises as $item) {
    $stats['total']++;
    
    try {
        $exercise = null;
        
        if ($item['type'] === 'file') {
            $filePath = $item['path'];
            $relativePath = str_replace(__DIR__ . '/../', '', $filePath);
            echo "📄 [$stats[total]/$totalExercises] Traitement de : $relativePath\n";
            
            $exercise = parseExerciseFile($filePath, $parsedown);
            
            if (!$exercise) {
                echo "   ⚠️  Fichier vide ou impossible à lire.\n\n";
                $stats['skipped']++;
                continue;
            }
        } elseif ($item['type'] === 'source') {
            $exerciseData = $item['data'];
            echo "📄 [$stats[total]/$totalExercises] Traitement exercice sources : {$exerciseData['title']}\n";
            $exercise = $exerciseData;
        }
        
        $result = insertOrUpdateExercise($exercise, $pdo, $dryRun);
        
        if ($result['status'] === 'skipped') {
            echo "   ⚠️  {$result['message']}\n\n";
            $stats['skipped']++;
        } elseif ($result['status'] === 'success') {
            $stats['success']++;
        }
        
    } catch (Exception $e) {
        $stats['errors']++;
        $errorMsg = "   ❌ Erreur : " . $e->getMessage();
        echo $errorMsg . "\n\n";
        $relativePath = ($item['type'] === 'file') ? str_replace(__DIR__ . '/../', '', $item['path']) : 'sources';
        $errors[] = [
            'file' => $relativePath,
            'error' => $e->getMessage()
        ];
    }
}

// Afficher le résumé
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RÉSUMÉ DE L'IMPORT\n";
echo str_repeat("=", 60) . "\n";
echo "Total d'exercices traités : {$stats['total']}\n";
echo "✅ Succès : {$stats['success']}\n";
echo "⚠️  Ignorés : {$stats['skipped']}\n";
echo "❌ Erreurs : {$stats['errors']}\n";

// Afficher les statistiques par niveau et matière si import réussi
if (!$dryRun && $stats['success'] > 0) {
    try {
        $statsStmt = $pdo->query("
            SELECT Level, Subject, COUNT(*) as count 
            FROM Exercises 
            GROUP BY Level, Subject 
            ORDER BY Level, Subject
        ");
        $statsData = $statsStmt->fetchAll();
        
        if (!empty($statsData)) {
            echo "\n📈 Répartition des exercices en base de données :\n";
            $currentLevel = null;
            foreach ($statsData as $row) {
                if ($currentLevel !== $row['Level']) {
                    echo "\n  🎓 {$row['Level']}:\n";
                    $currentLevel = $row['Level'];
                }
                echo "     • {$row['Subject']}: {$row['count']} exercice(s)\n";
            }
        }
    } catch (Exception $e) {
        // Ignorer les erreurs de statistiques
    }
}

if (!empty($errors)) {
    echo "\n❌ DÉTAILS DES ERREURS :\n";
    foreach ($errors as $error) {
        echo "   - {$error['file']} : {$error['error']}\n";
    }
}

if ($dryRun) {
    echo "\n⚠️  Mode DRY-RUN : Aucune modification n'a été effectuée en base de données.\n";
    echo "   Pour importer réellement, exécutez sans l'option --dry-run.\n";
} else {
    echo "\n✅ Import terminé !\n";
}

echo "\n";

