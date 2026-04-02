<?php
/**
 * Convertisseur de requêtes SQL INSERT vers données importables
 * Permet d'extraire les exercices d'un fichier SQL brut
 * et de les importer avec validation
 */

class ExerciseImporter {
    private $pdo;
    private $errors = [];
    private $imported = 0;
    private $skipped = 0;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Importe depuis un fichier contenant des INSERT statements
     */
    public function importFromFile($filepath) {
        if (!file_exists($filepath)) {
            $this->errors[] = "Fichier non trouvé: $filepath";
            return false;
        }
        
        $content = file_get_contents($filepath);
        
        // Extraire tous les INSERT statements
        $pattern = '/INSERT\s+INTO\s+exercises\s*\([^)]+\)\s*VALUES\s*(\([^)]+\)(?:\s*,\s*\([^)]+\))*)/i';
        
        if (!preg_match_all($pattern, $content, $matches)) {
            $this->errors[] = "Aucun INSERT trouvé dans le fichier";
            return false;
        }
        
        // Extraire les tuples (values)
        $valuesStr = $matches[1][0];
        $tuples = $this->parseValues($valuesStr);
        
        echo "Exercices trouvés: " . count($tuples) . "\n";
        
        $stmt = $this->pdo->prepare(
            "INSERT INTO exercises (Subject, Level, Title, Content, Answer, is_active) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        foreach ($tuples as $tuple) {
            try {
                if ($this->validateTuple($tuple)) {
                    $stmt->execute($tuple);
                    $this->imported++;
                } else {
                    $this->skipped++;
                }
            } catch (Exception $e) {
                $this->errors[] = "Erreur insertion: " . $e->getMessage();
                $this->skipped++;
            }
        }
        
        return true;
    }
    
    /**
     * Parse les valeurs depuis une chaîne SQL
     */
    private function parseValues($valuesStr) {
        $tuples = [];
        
        // Diviser par ), (
        $lines = preg_split('/\),\s*\(/', $valuesStr);
        
        foreach ($lines as $line) {
            // Nettoyer les parenthèses
            $line = trim($line, "() \t\n");
            if (empty($line)) continue;
            
            // Extraire les valeurs quoted et unquoted
            $values = [];
            $matches = [];
            
            // Regex pour capturer les strings et les valeurs
            $pattern = "/'([^']*)'/|(\d+)(?=\s*[,)])|(\w+)(?=\s*[,)])/";
            
            if (preg_match_all($pattern, $line, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    if (!empty($m[1])) {
                        $values[] = $m[1]; // Valeur quoted
                    } elseif (!empty($m[2])) {
                        $values[] = intval($m[2]); // Nombre
                    } elseif (!empty($m[3])) {
                        $values[] = $m[3]; // Mot clé
                    }
                }
            }
            
            if (count($values) >= 5) {
                $tuples[] = array_slice($values, 0, 6);
            }
        }
        
        return $tuples;
    }
    
    /**
     * Valide un tuple d'exercice
     */
    private function validateTuple($tuple) {
        // Vérifications basiques
        if (count($tuple) < 5) return false;
        if (empty($tuple[0]) || empty($tuple[1]) || empty($tuple[2])) return false;
        
        // Subject, Level, Title, Content, Answer, is_active
        list($subject, $level, $title, $content, $answer, $active) = $tuple + [null, null, null, null, null, 1];
        
        // Vérifier que Subject/Level sont reconnus
        $valid_levels = ['6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale', 'BAC', 'Bac'];
        $valid_subjects = ['Mathématiques', 'Français', 'Anglais', 'Sciences', 'SVT', 'Physique-Chimie', 'Histoire-Géographie', 'Histoire-Géo', 'Philosophie'];
        
        if (!in_array($level, $valid_levels)) {
            $this->errors[] = "Niveau invalide: $level";
            return false;
        }
        
        if (!in_array($subject, $valid_subjects)) {
            $this->errors[] = "Matière invalide: $subject";
            return false;
        }
        
        // Vérifier que ce n'est pas un doublon
        $check = $this->pdo->prepare(
            "SELECT Id FROM exercises WHERE Subject = ? AND Level = ? AND Title = ? LIMIT 1"
        );
        $check->execute([$subject, $level, $title]);
        
        if ($check->rowCount() > 0) {
            // Doublon trouvé - on pourrait updater ou skip
            $this->errors[] = "Doublon: [$level] [$subject] $title";
            return false;
        }
        
        return true;
    }
    
    /**
     * Affiche le rapport d'import
     */
    public function report() {
        echo "\n=== RAPPORT D'IMPORT ===\n";
        echo "✓ Importés: {$this->imported}\n";
        echo "⊘ Skippés: {$this->skipped}\n";
        
        if (!empty($this->errors)) {
            echo "\n⚠ Erreurs (" . count($this->errors) . "):\n";
            foreach (array_slice($this->errors, 0, 10) as $err) {
                echo "  - $err\n";
            }
            if (count($this->errors) > 10) {
                echo "  ... et " . (count($this->errors) - 10) . " autres\n";
            }
        }
    }
}

// Usage
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        die("Usage: php import_exercises.php <filepath>\n");
    }
    
    require_once __DIR__ . '/../db/connection.php';
    
    $importer = new ExerciseImporter($pdo);
    $importer->importFromFile($argv[1]);
    $importer->report();
}
