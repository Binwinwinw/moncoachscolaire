<?php
/**
 * Script de mise à jour de la base de données - Exercices
 * 
 * Ce script permet d'importer des exercices depuis un fichier structuré
 * et de les ranger correctement dans la BDD selon :
 * - Niveau scolaire (6ème, 5ème, 4ème, 3ème, Seconde, Première, Terminale)
 * - Matière (Mathématiques, Français, etc.)
 * - Contenu structuré : Content (consigne) | Answer (réponse) | Tips (astuces)
 * 
 * Usage:
 *   php tools/update_exercises_db.php --source fichier.json
 *   php tools/update_exercises_db.php --source fichier.csv
 *   php tools/update_exercises_db.php --source fichier.html
 */

// Charger les dépendances
// Le script utilise automatiquement .env.production si présent en production
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../db/connection.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur: Connexion à la base de données échouée\n");
}

// Niveaux scolaires autorisés
$NIVEAUX_AUTORISES = [
    '6ème', '6eme', '6EME',
    '5ème', '5eme', '5EME',
    '4ème', '4eme', '4EME',
    '3ème', '3eme', '3EME',
    'Seconde', 'seconde', '2nde',
    'Première', 'Premiere', 'première', '1ère',
    'Terminale', 'terminale', 'BAC', 'Bac'
];

// Matières autorisées
$MATIERES_AUTORISEES = [
    'Mathématiques',
    'Français',
    'Anglais',
    'Espagnol',
    'Histoire-Géographie',
    'SVT',
    'Physique-Chimie',
    'Technologie',
    'Arts',
    'EPS',
    'Philosophie'
];

/**
 * Normalise le niveau scolaire
 */
function normalizeLevel($level) {
    $levelMapping = [
        '6ème' => '6ème', '6eme' => '6ème', '6EME' => '6ème',
        '5ème' => '5ème', '5eme' => '5ème', '5EME' => '5ème',
        '4ème' => '4ème', '4eme' => '4ème', '4EME' => '4ème',
        '3ème' => '3ème', '3eme' => '3ème', '3EME' => '3ème',
        'Seconde' => 'Seconde', 'seconde' => 'Seconde', '2nde' => 'Seconde',
        'Première' => 'Première', 'Premiere' => 'Première', 'première' => 'Première', '1ère' => 'Première',
        'Terminale' => 'Terminale', 'terminale' => 'Terminale', 'BAC' => 'Terminale', 'Bac' => 'Terminale'
    ];
    
    return $levelMapping[trim($level)] ?? $level;
}

/**
 * Parse un fichier JSON structuré
 * Format attendu:
 * [
 *   {
 *     "level": "6ème",
 *     "subject": "Mathématiques",
 *     "title": "Titre de l'exercice",
 *     "content": "📝 Consigne de l'exercice...",
 *     "answer": "✅ Réponse détaillée...",
 *     "tips": "💡 Astuces pédagogiques..."
 *   }
 * ]
 */
function parseJSON($filepath) {
    if (!file_exists($filepath)) {
        die("❌ Fichier non trouvé: $filepath\n");
    }
    
    $json = file_get_contents($filepath);
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("❌ Erreur JSON: " . json_last_error_msg() . "\n");
    }
    
    return $data;
}

/**
 * Parse un fichier CSV structuré
 * Format attendu: Level,Subject,Title,Content,Answer,Tips
 */
function parseCSV($filepath) {
    if (!file_exists($filepath)) {
        die("❌ Fichier non trouvé: $filepath\n");
    }
    
    $exercises = [];
    $handle = fopen($filepath, 'r');
    
    // Lire la première ligne (header)
    $headers = fgetcsv($handle);
    
    // Vérifier que les colonnes nécessaires sont présentes
    $requiredCols = ['Level', 'Subject', 'Title', 'Content', 'Answer', 'Tips'];
    foreach ($requiredCols as $col) {
        if (!in_array($col, $headers)) {
            die("❌ Colonne manquante dans le CSV: $col\n");
        }
    }
    
    // Lire les lignes
    while (($row = fgetcsv($handle)) !== false) {
        $exercise = array_combine($headers, $row);
        $exercises[] = [
            'level' => $exercise['Level'],
            'subject' => $exercise['Subject'],
            'title' => $exercise['Title'],
            'content' => $exercise['Content'],
            'answer' => $exercise['Answer'],
            'tips' => $exercise['Tips'] ?? ''
        ];
    }
    
    fclose($handle);
    return $exercises;
}

/**
 * Parse un fichier HTML structuré
 * (Simple parsing - peut être amélioré avec DOMDocument si nécessaire)
 */
function parseHTML($filepath) {
    if (!file_exists($filepath)) {
        die("❌ Fichier non trouvé: $filepath\n");
    }
    
    $html = file_get_contents($filepath);
    $exercises = [];
    
    // Pattern pour extraire les exercices
    // Recherche les blocs div.exercise-card
    preg_match_all('/<div class="exercise-card">(.*?)<\/div>\s*<\/div>\s*<\/div>/s', $html, $matches);
    
    foreach ($matches[1] as $cardHtml) {
        $exercise = [];
        
        // Extraire le titre
        if (preg_match('/<div class="exercise-title">(.*?)<\/div>/s', $cardHtml, $titleMatch)) {
            $exercise['title'] = html_entity_decode(strip_tags($titleMatch[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // Extraire le niveau et la matière depuis les badges
        if (preg_match_all('/<span class="badge"[^>]*>(.*?)<\/span>/s', $cardHtml, $badgeMatches)) {
            foreach ($badgeMatches[1] as $badge) {
                $badgeText = html_entity_decode(strip_tags($badge), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                // Matière (commence par emoji 📚)
                if (strpos($badgeText, '📚') !== false) {
                    $exercise['subject'] = trim(str_replace('📚', '', $badgeText));
                }
            }
        }
        
        // Extraire Content (consigne)
        if (preg_match('/<div class="section consigne">.*?<div class="section-content">(.*?)<\/div>/s', $cardHtml, $contentMatch)) {
            $exercise['content'] = html_entity_decode(strip_tags(str_replace('<br>', "\n", $contentMatch[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // Extraire Answer (réponse)
        if (preg_match('/<div class="section reponse">.*?<div class="section-content">(.*?)<\/div>/s', $cardHtml, $answerMatch)) {
            $exercise['answer'] = html_entity_decode(strip_tags(str_replace('<br>', "\n", $answerMatch[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // Extraire Tips (astuces)
        if (preg_match('/<div class="section astuces">.*?<div class="section-content">(.*?)<\/div>/s', $cardHtml, $tipsMatch)) {
            $tipsContent = html_entity_decode(strip_tags(str_replace('<br>', "\n", $tipsMatch[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Ne pas ajouter si c'est le message par défaut
            if (strpos($tipsContent, "Pas d'astuces disponibles") === false && 
                strpos($tipsContent, "Aucune astuce") === false) {
                $exercise['tips'] = $tipsContent;
            } else {
                $exercise['tips'] = '';
            }
        }
        
        // Niveau: on doit le déduire du contexte (section)
        // Pour simplifier, on cherche dans l'en-tête de section
        $exercise['level'] = ''; // À compléter manuellement ou via contexte
        
        if (!empty($exercise['title'])) {
            $exercises[] = $exercise;
        }
    }
    
    return $exercises;
}

/**
 * Insère ou met à jour un exercice dans la base de données
 */
function insertOrUpdateExercise($pdo, $exercise) {
    global $NIVEAUX_AUTORISES, $MATIERES_AUTORISEES;
    
    // Valider les données
    if (empty($exercise['level']) || empty($exercise['subject']) || empty($exercise['title'])) {
        echo "⚠️  Exercice ignoré (données incomplètes): " . ($exercise['title'] ?? 'Sans titre') . "\n";
        return false;
    }
    
    // Normaliser le niveau
    $level = normalizeLevel($exercise['level']);
    $subject = trim($exercise['subject']);
    $title = trim($exercise['title']);
    $content = trim($exercise['content'] ?? '');
    $answer = trim($exercise['answer'] ?? '');
    $tips = trim($exercise['tips'] ?? '');
    
    // Nouveaux champs enrichis (optionnels)
    $domain = trim($exercise['domain'] ?? '');
    $competence = trim($exercise['competence'] ?? '');
    $difficulty = trim($exercise['difficulty'] ?? '');
    $identifier = trim($exercise['identifier'] ?? '');
    
    // Vérifier si l'exercice existe déjà (par identifier OU par titre + niveau + matière)
    if (!empty($identifier)) {
        $checkStmt = $pdo->prepare("
            SELECT Id FROM Exercises 
            WHERE Identifier = :identifier
            LIMIT 1
        ");
        $checkStmt->execute([':identifier' => $identifier]);
    } else {
        $checkStmt = $pdo->prepare("
            SELECT Id FROM Exercises 
            WHERE Title = :title AND Level = :level AND Subject = :subject
            LIMIT 1
        ");
        $checkStmt->execute([
            ':title' => $title,
            ':level' => $level,
            ':subject' => $subject
        ]);
    }
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Mise à jour
        $updateStmt = $pdo->prepare("
            UPDATE Exercises 
            SET Content = :content,
                Answer = :answer,
                Tips = :tips,
                Domain = :domain,
                Competence = :competence,
                Difficulty = :difficulty,
                Identifier = :identifier
            WHERE Id = :id
        ");
        $updateStmt->execute([
            ':content' => $content,
            ':answer' => $answer,
            ':tips' => $tips,
            ':domain' => $domain,
            ':competence' => $competence,
            ':difficulty' => $difficulty,
            ':identifier' => $identifier,
            ':id' => $existing['Id']
        ]);
        echo "🔄 Mis à jour: [$level] $subject - $title ($identifier) (ID: {$existing['Id']})\n";
        return 'updated';
    } else {
        // Insertion
        $insertStmt = $pdo->prepare("
            INSERT INTO Exercises (Level, Subject, Title, Content, Answer, Tips, Domain, Competence, Difficulty, Identifier)
            VALUES (:level, :subject, :title, :content, :answer, :tips, :domain, :competence, :difficulty, :identifier)
        ");
        $insertStmt->execute([
            ':level' => $level,
            ':subject' => $subject,
            ':title' => $title,
            ':content' => $content,
            ':answer' => $answer,
            ':tips' => $tips,
            ':domain' => $domain,
            ':competence' => $competence,
            ':difficulty' => $difficulty,
            ':identifier' => $identifier
        ]);
        $newId = $pdo->lastInsertId();
        echo "➕ Inséré: [$level] $subject - $title ($identifier) (ID: $newId)\n";
        return 'inserted';
    }
}

/**
 * Fonction principale
 */
function main($argc, $argv) {
    global $pdo;
    
    echo "╔════════════════════════════════════════════════════════════════╗\n";
    echo "║   📚 Mise à jour de la Base de Données - Exercices           ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    
    // Vérifier les arguments
    if ($argc < 3 || $argv[1] !== '--source') {
        echo "Usage: php tools/update_exercises_db.php --source fichier.[json|csv|html]\n";
        echo "\nFormats supportés:\n";
        echo "  • JSON : Tableau d'objets avec level, subject, title, content, answer, tips\n";
        echo "  • CSV  : Colonnes Level,Subject,Title,Content,Answer,Tips\n";
        echo "  • HTML : Fichier généré par generate_all_exercises_html.php\n";
        exit(1);
    }
    
    $filepath = $argv[2];
    
    if (!file_exists($filepath)) {
        die("❌ Fichier non trouvé: $filepath\n");
    }
    
    // Détecter le format
    $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    
    echo "📄 Fichier source: $filepath\n";
    echo "📝 Format détecté: " . strtoupper($ext) . "\n\n";
    
    // Parser selon le format
    $exercises = [];
    switch ($ext) {
        case 'json':
            $exercises = parseJSON($filepath);
            break;
        case 'csv':
            $exercises = parseCSV($filepath);
            break;
        case 'html':
        case 'htm':
            $exercises = parseHTML($filepath);
            break;
        default:
            die("❌ Format non supporté: $ext\n");
    }
    
    echo "📊 Total d'exercices à traiter: " . count($exercises) . "\n\n";
    
    // Statistiques
    $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];
    
    // Traiter chaque exercice
    foreach ($exercises as $index => $exercise) {
        $result = insertOrUpdateExercise($pdo, $exercise);
        if ($result === 'inserted') {
            $stats['inserted']++;
        } elseif ($result === 'updated') {
            $stats['updated']++;
        } else {
            $stats['skipped']++;
        }
    }
    
    echo "\n╔════════════════════════════════════════════════════════════════╗\n";
    echo "║   ✅ Traitement terminé                                        ║\n";
    echo "╚════════════════════════════════════════════════════════════════╝\n\n";
    echo "📊 Statistiques:\n";
    echo "   • Exercices insérés : {$stats['inserted']}\n";
    echo "   • Exercices mis à jour : {$stats['updated']}\n";
    echo "   • Exercices ignorés : {$stats['skipped']}\n";
    echo "   • Total traité : " . ($stats['inserted'] + $stats['updated'] + $stats['skipped']) . "\n";
}

// Exécuter
main($argc, $argv);
?>
