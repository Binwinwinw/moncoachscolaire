<?php
/**
 * Validation et nettoyage des exercices
 */
require_once __DIR__ . '/../db/connection.php';

class ExerciseValidator {
    private $pdo;
    private $issues = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Vérifie l'intégrité des données
     */
    public function validate() {
        echo "=== VALIDATION DES EXERCICES ===\n\n";
        
        // 1. Champs vides
        $this->checkEmptyFields();
        
        // 2. Doublons
        $this->checkDuplicates();
        
        // 3. Format HTML (Content/Answer)
        $this->checkHtmlFormat();
        
        // 4. Longueurs excessives
        $this->checkLengths();
        
        // 5. Niveaux/Matières invalides
        $this->checkInvalidCategories();
        
        echo "\n✓ Validation terminée\n";
        echo "  Problèmes détectés: " . count($this->issues) . "\n";
    }
    
    private function checkEmptyFields() {
        echo "1. Champs vides:\n";
        
        $checks = [
            'Title' => 'exercices sans titre',
            'Content' => 'exercices sans contenu',
            'Answer' => 'exercices sans réponse'
        ];
        
        $count = 0;
        foreach ($checks as $field => $desc) {
            $stmt = $this->pdo->query("SELECT COUNT(*) as cnt FROM exercises WHERE $field IS NULL OR $field = ''");
            $cnt = $stmt->fetch()['cnt'];
            if ($cnt > 0) {
                echo "   ⚠ $desc: $cnt\n";
                $this->issues[] = "$desc: $cnt";
                $count += $cnt;
            }
        }
        
        if ($count === 0) echo "   ✓ Aucun\n";
    }
    
    private function checkDuplicates() {
        echo "2. Doublons (même Level+Subject+Title):\n";
        
        $stmt = $this->pdo->query(
            "SELECT Level, Subject, Title, COUNT(*) as cnt 
             FROM exercises 
             GROUP BY Level, Subject, Title 
             HAVING cnt > 1"
        );
        
        $dups = $stmt->fetchAll();
        if (empty($dups)) {
            echo "   ✓ Aucun\n";
        } else {
            echo "   ⚠ Doublons trouvés: " . count($dups) . "\n";
            foreach (array_slice($dups, 0, 5) as $dup) {
                echo "     [{$dup['Level']}][{$dup['Subject']}] {$dup['Title']}: {$dup['cnt']}x\n";
            }
            $this->issues[] = "Doublons: " . count($dups);
        }
    }
    
    private function checkHtmlFormat() {
        echo "3. Format HTML:\n";
        
        $stmt = $this->pdo->query("SELECT COUNT(*) as cnt FROM exercises WHERE Content NOT LIKE '%<p>%' AND Content NOT LIKE '%<li>%'");
        $non_html = $stmt->fetch()['cnt'];
        
        if ($non_html > 0) {
            echo "   ⚠ Contenu sans balises HTML: $non_html\n";
        } else {
            echo "   ✓ OK (tout en HTML)\n";
        }
    }
    
    private function checkLengths() {
        echo "4. Longueurs:\n";
        
        $stmt = $this->pdo->query(
            "SELECT Title, LENGTH(Content) as clen, LENGTH(Answer) as alen 
             FROM exercises 
             WHERE LENGTH(Content) > 5000 OR LENGTH(Answer) > 2000 
             ORDER BY clen DESC 
             LIMIT 5"
        );
        
        $long = $stmt->fetchAll();
        if (empty($long)) {
            echo "   ✓ Tous les contenus ont une longueur raisonnable\n";
        } else {
            echo "   ⚠ Contenus longs:\n";
            foreach ($long as $l) {
                echo "     {$l['Title']}: {$l['clen']} / {$l['alen']} caractères\n";
            }
        }
    }
    
    private function checkInvalidCategories() {
        echo "5. Catégories invalides:\n";
        
        $valid_levels = ['6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale', 'BAC', 'Bac'];
        $valid_subjects = ['Mathématiques', 'Français', 'Anglais', 'Sciences', 'SVT', 'Physique-Chimie', 'Histoire-Géographie', 'Histoire-Géo', 'Philosophie'];
        
        $placeholders_levels = implode(',', array_fill(0, count($valid_levels), '?'));
        $placeholders_subjects = implode(',', array_fill(0, count($valid_subjects), '?'));
        
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT Level FROM exercises WHERE Level NOT IN ($placeholders_levels)"
        );
        $stmt->execute($valid_levels);
        $bad_levels = $stmt->fetchAll();
        
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT Subject FROM exercises WHERE Subject NOT IN ($placeholders_subjects)"
        );
        $stmt->execute($valid_subjects);
        $bad_subjects = $stmt->fetchAll();
        
        if (!empty($bad_levels)) {
            echo "   ⚠ Niveaux invalides:\n";
            foreach ($bad_levels as $l) {
                echo "     - {$l['Level']}\n";
            }
        } else {
            echo "   ✓ Niveaux OK\n";
        }
        
        if (!empty($bad_subjects)) {
            echo "   ⚠ Matières invalides:\n";
            foreach ($bad_subjects as $s) {
                echo "     - {$s['Subject']}\n";
            }
        } else {
            echo "   ✓ Matières OK\n";
        }
    }
}

if (php_sapi_name() === 'cli') {
    $validator = new ExerciseValidator($pdo);
    $validator->validate();
}
