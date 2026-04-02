<?php
/**
 * Génère un document HTML avec TOUS les exercices de tous les niveaux
 * Format : Consigne (gris) | Réponse (vert) | Astuces (orange)
 */

// Charger les dépendances
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../db/connection.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur: Connexion à la base de données échouée\n");
}

// Récupérer TOUS les exercices
try {
    $stmt = $pdo->prepare("
        SELECT 
            Id, 
            Subject, 
            Level, 
            Title, 
            Content, 
            Answer, 
            Tips
        FROM Exercises
        ORDER BY Level ASC, Subject ASC, Id ASC
    ");
    $stmt->execute();
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("❌ Erreur SQL: " . $e->getMessage());
}

echo "📊 Total d'exercices trouvés: " . count($exercises) . "\n";

// Grouper par niveau
$exercisesByLevel = [];
foreach ($exercises as $ex) {
    $level = $ex['Level'] ?? 'Inconnu';
    if (!isset($exercisesByLevel[$level])) {
        $exercisesByLevel[$level] = [];
    }
    $exercisesByLevel[$level][] = $ex;
}

// Générer le HTML
$html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Exercices - MonCoachScolaire</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .level-section {
            margin-bottom: 50px;
            page-break-inside: avoid;
        }
        
        .level-title {
            background: #e8e8e8;
            padding: 15px 20px;
            border-left: 5px solid #667eea;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .exercise-card {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 30px;
            padding: 20px;
            page-break-inside: avoid;
        }
        
        .exercise-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .exercise-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            flex: 1;
        }
        
        .exercise-meta {
            display: flex;
            gap: 10px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .badge {
            background: #e0e7ff;
            color: #667eea;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .section {
            margin: 15px 0;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid;
        }
        
        .section.consigne {
            background: #f0f0f0;
            border-left-color: #999;
        }
        
        .section.consigne-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section.reponse {
            background: #e8f5e9;
            border-left-color: #4caf50;
        }
        
        .section.reponse-title {
            font-weight: 600;
            color: #2e7d32;
            margin-bottom: 10px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section.astuces {
            background: #fff3e0;
            border-left-color: #ff9800;
        }
        
        .section.astuces-title {
            font-weight: 600;
            color: #e65100;
            margin-bottom: 10px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .section-content {
            color: #333;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .section-content p {
            margin-bottom: 10px;
        }
        
        .section-content ul, .section-content ol {
            margin-left: 20px;
            margin-bottom: 10px;
        }
        
        .section-content li {
            margin-bottom: 8px;
        }
        
        .no-content {
            font-style: italic;
            color: #999;
            font-size: 0.9rem;
        }
        
        .footer {
            background: #f0f0f0;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            border-top: 1px solid #e0e0e0;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }
        
        .stat-card {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border-radius: 0;
            }
            .exercise-card {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Tous les Exercices MonCoachScolaire</h1>
            <p>Document complet - Tous niveaux et matières</p>
        </div>
        
        <div class="content">';

// Compter les statistiques
$totalBySubject = [];
foreach ($exercises as $ex) {
    $subject = $ex['Subject'] ?? 'Inconnu';
    if (!isset($totalBySubject[$subject])) {
        $totalBySubject[$subject] = 0;
    }
    $totalBySubject[$subject]++;
}

// Ajouter les exercices groupés par niveau
foreach ($exercisesByLevel as $level => $levelExercises) {
    $html .= "\n\t\t<!-- Niveau: $level -->\n";
    $html .= "\t\t<div class=\"level-section\">\n";
    $html .= "\t\t\t<div class=\"level-title\">📖 Niveau: " . htmlspecialchars($level) . "</div>\n";
    
    foreach ($levelExercises as $exercise) {
        $id = $exercise['Id'] ?? 'N/A';
        $subject = $exercise['Subject'] ?? 'Inconnu';
        $title = $exercise['Title'] ?? 'Sans titre';
        $content = $exercise['Content'] ?? '';
        $answer = $exercise['Answer'] ?? '';
        $tips = $exercise['Tips'] ?? '';
        
        $html .= "\t\t\t<div class=\"exercise-card\">\n";
        
        // En-tête de l'exercice
        $html .= "\t\t\t\t<div class=\"exercise-header\">\n";
        $html .= "\t\t\t\t\t<div class=\"exercise-title\">" . htmlspecialchars($title) . "</div>\n";
        $html .= "\t\t\t\t\t<div class=\"exercise-meta\">\n";
        $html .= "\t\t\t\t\t\t<span class=\"badge\" title=\"Matière\">📚 " . htmlspecialchars($subject) . "</span>\n";
        $html .= "\t\t\t\t\t\t<span class=\"badge\" title=\"ID\">#" . htmlspecialchars($id) . "</span>\n";
        $html .= "\t\t\t\t\t</div>\n";
        $html .= "\t\t\t\t</div>\n";
        
        // Section Consigne (gris)
        $html .= "\t\t\t\t<div class=\"section consigne\">\n";
        $html .= "\t\t\t\t\t<div class=\"section consigne-title\">📝 Consigne</div>\n";
        if (!empty($content)) {
            $html .= "\t\t\t\t\t<div class=\"section-content\">" . nl2br(htmlspecialchars($content)) . "</div>\n";
        } else {
            $html .= "\t\t\t\t\t<div class=\"section-content no-content\">Aucune consigne fournie</div>\n";
        }
        $html .= "\t\t\t\t</div>\n";
        
        // Section Réponse (vert)
        $html .= "\t\t\t\t<div class=\"section reponse\">\n";
        $html .= "\t\t\t\t\t<div class=\"section reponse-title\">✅ Réponse Détaillée</div>\n";
        if (!empty($answer)) {
            $html .= "\t\t\t\t\t<div class=\"section-content\">" . nl2br(htmlspecialchars($answer)) . "</div>\n";
        } else {
            $html .= "\t\t\t\t\t<div class=\"section-content no-content\">Aucune réponse fournie</div>\n";
        }
        $html .= "\t\t\t\t</div>\n";
        
        // Section Astuces (orange)
        $html .= "\t\t\t\t<div class=\"section astuces\">\n";
        $html .= "\t\t\t\t\t<div class=\"section astuces-title\">💡 Astuces Pédagogiques</div>\n";
        if (!empty($tips)) {
            $html .= "\t\t\t\t\t<div class=\"section-content\">" . nl2br(htmlspecialchars($tips)) . "</div>\n";
        } else {
            $html .= "\t\t\t\t\t<div class=\"section-content no-content\">Pas d'astuces disponibles</div>\n";
        }
        $html .= "\t\t\t\t</div>\n";
        
        $html .= "\t\t\t</div>\n";
    }
    
    $html .= "\t\t</div>\n";
}

// Ajouter les statistiques
$html .= "\t\t<div class=\"stats\">\n";
$html .= "\t\t\t<div class=\"stat-card\">\n";
$html .= "\t\t\t\t<div class=\"stat-number\">" . count($exercises) . "</div>\n";
$html .= "\t\t\t\t<div class=\"stat-label\">Total Exercices</div>\n";
$html .= "\t\t\t</div>\n";

$html .= "\t\t\t<div class=\"stat-card\">\n";
$html .= "\t\t\t\t<div class=\"stat-number\">" . count($exercisesByLevel) . "</div>\n";
$html .= "\t\t\t\t<div class=\"stat-label\">Niveaux</div>\n";
$html .= "\t\t\t</div>\n";

$html .= "\t\t\t<div class=\"stat-card\">\n";
$html .= "\t\t\t\t<div class=\"stat-number\">" . count($totalBySubject) . "</div>\n";
$html .= "\t\t\t\t<div class=\"stat-label\">Matières</div>\n";
$html .= "\t\t\t</div>\n";

// Compter les exercices avec astuces
$withTips = 0;
foreach ($exercises as $ex) {
    if (!empty($ex['Tips'])) {
        $withTips++;
    }
}
$html .= "\t\t\t<div class=\"stat-card\">\n";
$html .= "\t\t\t\t<div class=\"stat-number\">" . $withTips . "</div>\n";
$html .= "\t\t\t\t<div class=\"stat-label\">Avec Astuces</div>\n";
$html .= "\t\t\t</div>\n";

$html .= "\t\t</div>\n";

$html .= "
        </div>
        
        <div class=\"footer\">
            <p>📄 Document généré automatiquement par MonCoachScolaire</p>
            <p>Date: " . date('d/m/Y H:i:s') . "</p>
            <p>Tous les exercices - Tous les niveaux (Collège et Lycée)</p>
        </div>
    </div>
</body>
</html>";

// Sauvegarder le fichier HTML
$filename = 'tous_les_exercices_' . date('Y-m-d_H-i-s') . '.html';
$filepath = __DIR__ . '/../docs/exercices/' . $filename;

// Créer le répertoire s'il n'existe pas
if (!is_dir(dirname($filepath))) {
    mkdir(dirname($filepath), 0755, true);
}

file_put_contents($filepath, $html);

echo "✅ Document HTML généré avec succès!\n";
echo "📁 Fichier: " . $filepath . "\n";
echo "📊 Statistiques:\n";
echo "   • Total exercices: " . count($exercises) . "\n";
echo "   • Niveaux: " . count($exercisesByLevel) . "\n";
echo "   • Matières: " . count($totalBySubject) . "\n";
echo "   • Avec astuces: " . $withTips . "\n";
echo "   • Lien: docs/exercices/" . $filename . "\n";
?>
