<?php
/**
 * Script d'import simplifié pour les exercices du fichier sources
 * Version simplifiée et robuste pour importer les exercices de 4ème, 5ème, Terminale
 * 
 * Usage: php tools/import_exercices_sources_simple.php [--dry-run]
 */

require_once __DIR__ . '/../db/connection.php';

// Fonction de normalisation du niveau (copiée pour éviter les dépendances)
if (!function_exists('normalizeLevel')) {
    function normalizeLevel($level) {
        if (empty($level)) return null;
        
        $levelMapping = [
            '6ème' => '6ème', '6eme' => '6ème', '6' => '6ème',
            '5ème' => '5ème', '5eme' => '5ème', '5' => '5ème',
            '4ème' => '4ème', '4eme' => '4ème', '4' => '4ème',
            '3ème' => '3ème', '3eme' => '3ème', '3' => '3ème',
            'Seconde' => 'Seconde', 'seconde' => 'Seconde', '2nde' => 'Seconde',
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
        if (stripos($level, '6') !== false && stripos($level, '5') === false && stripos($level, '4') === false && stripos($level, '3') === false) {
            return '6ème';
        }
        if (stripos($level, '5') !== false && stripos($level, '6') === false && stripos($level, '4') === false && stripos($level, '3') === false) {
            return '5ème';
        }
        if (stripos($level, '4') !== false && stripos($level, '6') === false && stripos($level, '5') === false && stripos($level, '3') === false) {
            return '4ème';
        }
        if (stripos($level, '3') !== false && stripos($level, '1') === false && stripos($level, '6') === false && stripos($level, '5') === false && stripos($level, '4') === false) {
            return '3ème';
        }
        if (stripos($level, 'seconde') !== false || stripos($level, '2nde') !== false) {
            return 'Seconde';
        }
        if (stripos($level, 'première') !== false || stripos($level, 'premiere') !== false || stripos($level, '1ère') !== false) {
            return 'Première';
        }
        if (stripos($level, 'terminale') !== false || stripos($level, 'bac') !== false) {
            return 'Terminale';
        }
        
        return $levelClean;
    }
}

// Fonction de conversion Markdown basique (copiée pour éviter les dépendances)
if (!function_exists('convertMarkdownBasic')) {
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
        
        // Retours à la ligne
        $html = nl2br($html);
        
        return $html;
    }
}

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

$dryRun = in_array('--dry-run', $argv);
$sourcesFile = __DIR__ . '/../docs/exercices-sources-par-niveau.md';

echo "🚀 Import simplifié des exercices depuis le fichier sources\n\n";

if (!file_exists($sourcesFile)) {
    die("❌ Fichier non trouvé : $sourcesFile\n");
}

$content = file_get_contents($sourcesFile);
$lines = explode("\n", $content);

$exercises = [];
$currentLevel = null;
$currentSubject = null;
$currentExercise = null;
$currentSection = null; // 'content' ou 'answer'
$targetLevels = ['5ème', '4ème', 'Terminale']; // Seulement les niveaux manquants

echo "📄 Parsing du fichier sources...\n\n";

foreach ($lines as $lineNum => $line) {
    $lineTrimmed = trim($line);
    
    // Détecter les niveaux ciblés
    if (preg_match('/^##\s*[🎓📚]*\s*(5ème|4ème|Terminale)\s*$/i', $lineTrimmed, $matches)) {
        $levelRaw = trim($matches[1]);
        $currentLevel = normalizeLevel($levelRaw);
        $currentSubject = null;
        $currentExercise = null;
        $currentSection = null;
        echo "✅ Niveau détecté : $currentLevel\n";
        continue;
    }
    
    // Si on n'est pas dans un niveau ciblé, continuer
    if (!$currentLevel || !in_array($currentLevel, $targetLevels)) {
        continue;
    }
    
    // Détecter les matières (### Mathématiques, ### Français, etc.)
    // MAIS PAS les exercices (#### Exercice)
    if (preg_match('/^###\s+(.+?)$/', $lineTrimmed, $matches)) {
        $subjectRaw = trim($matches[1]);
        // Ignorer si c'est un titre d'exercice
        if (!preg_match('/^Exercice\s+\d+/i', $subjectRaw)) {
            $subjectMapping = [
                'Mathématiques' => 'Mathématiques',
                'Math' => 'Mathématiques',
                'Français' => 'Français',
                'Francais' => 'Français',
                'Histoire-Géographie' => 'Histoire-Géographie',
                'Histoire' => 'Histoire-Géographie',
                'SVT' => 'SVT',
                'Sciences' => 'SVT',
                'Sciences (SVT)' => 'SVT',
                'Physique-Chimie' => 'Physique-Chimie',
                'Physique' => 'Physique-Chimie',
                'Anglais' => 'Anglais',
                'English' => 'Anglais',
                'Philosophie' => 'Philosophie',
                'Philo' => 'Philosophie'
            ];
            $currentSubject = $subjectMapping[$subjectRaw] ?? $subjectRaw;
            echo "  📖 Matière : $currentSubject\n";
        }
        continue;
    }
    
    // Détecter un nouvel exercice (#### Exercice X : Titre)
    if (preg_match('/^####\s+Exercice\s+\d+\s*:\s*(.+?)$/', $lineTrimmed, $matches)) {
        // Sauvegarder l'exercice précédent s'il est complet
        if ($currentExercise && $currentLevel && $currentSubject) {
            $contentTrimmed = trim($currentExercise['content']);
            $answerTrimmed = trim($currentExercise['answer']);
            
            // Debug: afficher les longueurs pour comprendre le problème
            $contentLen = strlen($contentTrimmed);
            $answerLen = strlen($answerTrimmed);
            
            if (!empty($contentTrimmed) && !empty($answerTrimmed)) {
                $exercises[] = [
                    'title' => $currentExercise['title'],
                    'level' => $currentLevel,
                    'subject' => $currentSubject,
                    'content' => $contentTrimmed,
                    'answer' => $answerTrimmed
                ];
                echo "    ✅ Exercice sauvegardé : {$currentExercise['title']} (contenu: {$contentLen} car, réponse: {$answerLen} car)\n";
            } else {
                echo "    ⚠️  Exercice ignoré (incomplet) : {$currentExercise['title']} (contenu: {$contentLen} car, réponse: {$answerLen} car)\n";
            }
        }
        
        // Créer un nouvel exercice
        $currentExercise = [
            'title' => trim($matches[1]),
            'content' => '',
            'answer' => '',
            'level' => $currentLevel,
            'subject' => $currentSubject
        ];
        $currentSection = null;
        echo "    📝 Nouvel exercice : {$currentExercise['title']}\n";
        continue;
    }
    
    // IMPORTANT: Détecter les sections AVANT d'ajouter le contenu
    // Détecter **Contenu** :
    if ($currentExercise && preg_match('/^\*\*Contenu\*\*\s*:\s*$/', $lineTrimmed)) {
        $currentSection = 'content';
        continue; // Ne pas inclure cette ligne dans le contenu
    }
    
    // Détecter **Réponse attendue** : (accepter les deux variantes : Réponse ou Reponse)
    if ($currentExercise && (
        preg_match('/^\*\*Réponse attendue\*\*\s*:\s*$/', $lineTrimmed) ||
        preg_match('/^\*\*Reponse attendue\*\*\s*:\s*$/', $lineTrimmed)
    )) {
        $currentSection = 'answer';
        continue; // Ne pas inclure cette ligne dans la réponse
    }
    
    // Si on rencontre une nouvelle section (## ou ###), arrêter la section courante
    if ($currentSection && (preg_match('/^##\s/', $lineTrimmed) || preg_match('/^###\s/', $lineTrimmed))) {
        $currentSection = null;
    }
    
    // Si on rencontre un nouvel exercice, arrêter la section courante
    if ($currentSection && preg_match('/^####\s+Exercice/', $lineTrimmed)) {
        $currentSection = null;
    }
    
    // Ajouter le contenu à la section appropriée (SEULEMENT si on est dans une section active)
    if ($currentExercise && $currentSection) {
        // Utiliser la ligne originale (non trimée) pour préserver les sauts de ligne
        if ($currentSection === 'content') {
            $currentExercise['content'] .= ($currentExercise['content'] ? "\n" : '') . $line;
        } elseif ($currentSection === 'answer') {
            $currentExercise['answer'] .= ($currentExercise['answer'] ? "\n" : '') . $line;
        }
    }
}

// Sauvegarder le dernier exercice
if ($currentExercise && $currentLevel && $currentSubject) {
    $contentTrimmed = trim($currentExercise['content']);
    $answerTrimmed = trim($currentExercise['answer']);
    
    if (!empty($contentTrimmed) && !empty($answerTrimmed)) {
        $exercises[] = [
            'title' => $currentExercise['title'],
            'level' => $currentLevel,
            'subject' => $currentSubject,
            'content' => $contentTrimmed,
            'answer' => $answerTrimmed
        ];
        echo "    ✅ Dernier exercice sauvegardé : {$currentExercise['title']}\n";
    }
}

echo "\n📊 Résumé du parsing :\n";
echo "   Total d'exercices extraits : " . count($exercises) . "\n\n";

// Compter par niveau
$byLevel = [];
foreach ($exercises as $ex) {
    $level = $ex['level'];
    if (!isset($byLevel[$level])) {
        $byLevel[$level] = [];
    }
    if (!isset($byLevel[$level][$ex['subject']])) {
        $byLevel[$level][$ex['subject']] = 0;
    }
    $byLevel[$level][$ex['subject']]++;
}

foreach ($byLevel as $level => $subjects) {
    echo "   🎓 $level :\n";
    foreach ($subjects as $subject => $count) {
        echo "      • $subject : $count exercice(s)\n";
    }
    echo "\n";
}

if (empty($exercises)) {
    echo "❌ Aucun exercice extrait. Vérifiez le format du fichier.\n";
    exit(1);
}

// Convertir Markdown en HTML
echo "🔄 Conversion Markdown → HTML...\n";

foreach ($exercises as &$ex) {
    $ex['content_html'] = convertMarkdownBasic($ex['content']);
    $ex['answer_html'] = convertMarkdownBasic($ex['answer']);
}

// Import en base de données
echo "\n💾 Import en base de données...\n\n";

$inserted = 0;
$updated = 0;
$errors = 0;

foreach ($exercises as $ex) {
    try {
        // Vérifier si l'exercice existe déjà
        $checkStmt = $pdo->prepare("SELECT Id FROM Exercises WHERE Title = ? AND Level = ? AND Subject = ?");
        $checkStmt->execute([$ex['title'], $ex['level'], $ex['subject']]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Mise à jour
            if (!$dryRun) {
                $updateStmt = $pdo->prepare("
                    UPDATE Exercises 
                    SET Content = ?, Answer = ?, Subject = ?, Level = ?
                    WHERE Id = ?
                ");
                $updateStmt->execute([
                    $ex['content_html'],
                    $ex['answer_html'],
                    $ex['subject'],
                    $ex['level'],
                    $existing['Id']
                ]);
            }
            $updated++;
            echo "   🔄 [DRY-RUN] " . ($dryRun ? '' : '') . "Exercice mis à jour : {$ex['title']} ({$ex['level']}, {$ex['subject']})\n";
        } else {
            // Insertion
            if (!$dryRun) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO Exercises (Title, Level, Subject, Content, Answer)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insertStmt->execute([
                    $ex['title'],
                    $ex['level'],
                    $ex['subject'],
                    $ex['content_html'],
                    $ex['answer_html']
                ]);
            }
            $inserted++;
            echo "   ✅ [DRY-RUN] " . ($dryRun ? '' : '') . "Exercice inséré : {$ex['title']} ({$ex['level']}, {$ex['subject']})\n";
        }
    } catch (PDOException $e) {
        $errors++;
        echo "   ❌ Erreur pour '{$ex['title']}' : " . $e->getMessage() . "\n";
    }
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ DE L'IMPORT\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "✅ Exercices insérés : $inserted\n";
echo "🔄 Exercices mis à jour : $updated\n";
if ($errors > 0) {
    echo "❌ Erreurs : $errors\n";
}
echo "\n";

if ($dryRun) {
    echo "⚠️  Mode DRY-RUN : aucune modification en base de données.\n";
    echo "   Exécutez sans --dry-run pour importer réellement.\n";
} else {
    echo "✅ Import terminé avec succès !\n";
}

echo "\n";

