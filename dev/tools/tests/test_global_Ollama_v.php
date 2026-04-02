<?php
/**
 * Script d'analyse complète d'application PHP
 * Amélioration du script d'analyse avec des fonctionnalités avancées
 */

class ApplicationAnalyzer {
    private $appDirectory;
    private $reportDirectory;
    private $results;
    private $score;

    public function __construct($appDirectory = './') {
        $this->appDirectory = rtrim($appDirectory, '/');
        $this->reportDirectory = $this->appDirectory . '/dev/reports';
        $this->results = [
            'files' => [],
            'pages' => [],
            'api' => [],
            'security' => [],
            'performance' => [],
            'errors' => [],
            'warnings' => []
        ];
        $this->score = 0;

        // Créer le répertoire de rapport s'il n'existe pas
        if (!is_dir($this->reportDirectory)) {
            mkdir($this->reportDirectory, 0755, true);
        }
    }

    /**
     * Vérification de l'intégrité des fichiers
     */
    public function checkFileIntegrity() {
        $files = $this->getAllFiles($this->appDirectory);
        $this->results['files']['total'] = count($files);
        $this->results['files']['checked'] = 0;
        $this->results['files']['missing'] = [];
        $this->results['files']['corrupted'] = [];

        foreach ($files as $file) {
            $this->results['files']['checked']++;

            if (!file_exists($file)) {
                $this->results['files']['missing'][] = $file;
                $this->addWarning("Fichier manquant : " . $file);
            } elseif (!is_readable($file)) {
                $this->results['files']['corrupted'][] = $file;
                $this->addError("Fichier non lisible : " . $file);
            } else {
                // Vérification de l'intégrité du fichier
                $this->verifyFileIntegrity($file);
            }
        }

        $this->results['files']['status'] = $this->calculateFileStatus();
    }

    /**
     * Vérification des pages PHP
     */
    public function checkApplicationPages() {
        $phpFiles = $this->getPhpFiles($this->appDirectory);
        $this->results['pages']['total'] = count($phpFiles);
        $this->results['pages']['checked'] = 0;
        $this->results['pages']['errors'] = [];
        $this->results['pages']['warnings'] = [];

        foreach ($phpFiles as $file) {
            $this->results['pages']['checked']++;

            // Vérification de la syntaxe PHP
            $this->checkPhpSyntax($file);

            // Vérification des bonnes pratiques
            $this->checkBestPractices($file);
        }
    }

    /**
     * Analyse récursive des API
     */
    public function scanApiEndpoints() {
        $apiDirectory = $this->appDirectory . '/api';
        if (!is_dir($apiDirectory)) {
            $this->addWarning("Répertoire API non trouvé : " . $apiDirectory);
            return;
        }

        $apiFiles = $this->getPhpFiles($apiDirectory);
        $this->results['api']['total'] = count($apiFiles);
        $this->results['api']['checked'] = 0;
        $this->results['api']['invalid_json'] = [];
        $this->results['api']['errors'] = [];

        foreach ($apiFiles as $file) {
            $this->results['api']['checked']++;

            // Vérification de la validité JSON
            $this->checkJsonValidation($file);

            // Vérification des endpoints
            $this->checkApiEndpoints($file);
        }
    }

    /**
     * Vérification de la sécurité
     */
    public function checkSecurity() {
        $this->results['security']['total'] = 0;
        $this->results['security']['issues'] = [];

        // Vérification des injections SQL
        $this->checkSqlInjection();

        // Vérification des XSS
        $this->checkXss();

        // Vérification des paramètres de sécurité
        $this->checkSecuritySettings();
    }

    /**
     * Vérification des performances
     */
    public function checkPerformance() {
        $this->results['performance']['total'] = 0;
        $this->results['performance']['issues'] = [];

        // Vérification de la mémoire
        $this->checkMemoryUsage();

        // Vérification des requêtes
        $this->checkDatabaseQueries();
    }

    /**
     * Calcul du score global
     */
    public function calculateScore() {
        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;

        // Compter les tests
        $totalTests += $this->results['files']['total'];
        $totalTests += $this->results['pages']['total'];
        $totalTests += $this->results['api']['total'];

        // Compter les tests réussis
        $passedTests += $this->results['files']['checked'] - count($this->results['files']['missing']) - count($this->results['files']['corrupted']);
        $passedTests += $this->results['pages']['checked'];
        $passedTests += $this->results['api']['checked'];

        // Calcul du score
        if ($totalTests > 0) {
            $this->score = round(($passedTests / $totalTests) * 100);
        }

        return $this->score;
    }

    /**
     * Génération du rapport
     */
    public function generateReport() {
        $timestamp = date('Y-m-d H:i:s');
        $reportFile = $this->reportDirectory . '/analysis_report_' . date('Y-m-d_H-i-s') . '.txt';

        $report = "=== Rapport d'analyse d'application ===\n";
        $report .= "Date : " . $timestamp . "\n";
        $report .= "Répertoire : " . $this->appDirectory . "\n";
        $report .= "Score global : " . $this->score . "/100\n\n";

        // Résumé général
        $report .= "=== Résumé ===\n";
        $report .= "Fichiers : " . $this->results['files']['total'] . " vérifiés\n";
        $report .= "Pages PHP : " . $this->results['pages']['total'] . " vérifiées\n";
        $report .= "API : " . $this->results['api']['total'] . " endpoints vérifiés\n\n";

        // Détails des erreurs
        if (!empty($this->results['errors'])) {
            $report .= "=== Erreurs ===\n";
            foreach ($this->results['errors'] as $error) {
                $report .= "- " . $error . "\n";
            }
            $report .= "\n";
        }

        // Détails des warnings
        if (!empty($this->results['warnings'])) {
            $report .= "=== Avertissements ===\n";
            foreach ($this->results['warnings'] as $warning) {
                $report .= "- " . $warning . "\n";
            }
            $report .= "\n";
        }

        // Détails des fichiers
        $report .= "=== Fichiers ===\n";
        $report .= "Total : " . $this->results['files']['total'] . "\n";
        $report .= "Vérifiés : " . $this->results['files']['checked'] . "\n";
        $report .= "Manquants : " . count($this->results['files']['missing']) . "\n";
        $report .= "Corrompus : " . count($this->results['files']['corrupted']) . "\n\n";

        // Détails des pages
        $report .= "=== Pages PHP ===\n";
        $report .= "Total : " . $this->results['pages']['total'] . "\n";
        $report .= "Vérifiées : " . $this->results['pages']['checked'] . "\n";
        $report .= "Erreurs : " . count($this->results['pages']['errors']) . "\n";
        $report .= "Avertissements : " . count($this->results['pages']['warnings']) . "\n\n";

        // Détails des API
        $report .= "=== API ===\n";
        $report .= "Total : " . $this->results['api']['total'] . "\n";
        $report .= "Vérifiés : " . $this->results['api']['checked'] . "\n";
        $report .= "JSON invalides : " . count($this->results['api']['invalid_json']) . "\n";
        $report .= "Erreurs : " . count($this->results['api']['errors']) . "\n\n";

        // Statut de l'application
        $report .= "=== Statut de l'application ===\n";
        $report .= $this->getApplicationStatus() . "\n";

        // Sauvegarder le rapport
        file_put_contents($reportFile, $report);

        return $reportFile;
    }

    /**
     * Déterminer le statut de l'application
     */
    private function getApplicationStatus() {
        if ($this->score >= 90) {
            return "OPERATIONNEL - L'application est pleinement fonctionnelle";
        } elseif ($this->score >= 70) {
            return "PARTIELLEMENT FONCTIONNELLE - L'application a des problèmes mineurs";
        } else {
            return "PROBLÉMATIQUE - L'application nécessite des corrections importantes";
        }
    }

    // Méthodes d'assistance

    private function getAllFiles($directory) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function getPhpFiles($directory) {
        $phpFiles = [];
        $files = $this->getAllFiles($directory);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $phpFiles[] = $file;
            }
        }

        return $phpFiles;
    }

    private function verifyFileIntegrity($file) {
        // Vérification basique de l'intégrité
        $fileSize = filesize($file);
        if ($fileSize <= 0) {
            $this->addError("Fichier vide : " . $file);
        }
    }

    private function checkPhpSyntax($file) {
        $output = [];
        $returnCode = 0;

        exec('php -l ' . escapeshellarg($file), $output, $returnCode);

        if ($returnCode !== 0) {
            $this->addError("Erreur de syntaxe PHP dans : " . $file);
            foreach ($output as $line) {
                $this->addError("  " . $line);
            }
        }
    }

    private function checkBestPractices($file) {
        // Vérification des bonnes pratiques
        $content = file_get_contents($file);

        // Vérification de l'utilisation de require_once vs require
        if (strpos($content, 'require_once') !== false && strpos($content, 'require') !== false) {
            $this->addWarning("Utilisation mixte de require et require_once dans : " . $file);
        }

        // Vérification de l'encodage
        if (strpos($content, '<?php') !== false && !preg_match('/<\?php\s+/', $content)) {
            $this->addWarning("Encodage incorrect dans : " . $file);
        }
    }

    private function checkJsonValidation($file) {
        $content = file_get_contents($file);
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->addError("JSON invalide dans : " . $file . " - " . json_last_error_msg());
            $this->results['api']['invalid_json'][] = $file;
        }
    }

    private function checkApiEndpoints($file) {
        // Vérification basique des endpoints
        $content = file_get_contents($file);

        // Vérification de l'utilisation de headers
        if (strpos($content, 'header(') === false) {
            $this->addWarning("Pas d'entêtes HTTP définies dans : " . $file);
        }
    }

    private function checkSqlInjection() {
        // Vérification basique des injections SQL
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (preg_match('/(SELECT|INSERT|UPDATE|DELETE)\s+/i', $content)) {
                if (strpos($content, '$_GET') !== false || strpos($content, '$_POST') !== false) {
                    if (strpos($content, 'mysql_query') !== false || strpos($content, 'mysqli_query') !== false) {
                        $this->addWarning("Potentielles injections SQL dans : " . $file);
                    }
                }
            }
        }
    }

    private function checkXss() {
        // Vérification basique des XSS
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'echo') !== false && (strpos($content, '$_GET') !== false || strpos($content, '$_POST') !== false)) {
                if (strpos($content, 'htmlspecialchars') === false) {
                    $this->addWarning("Potentiel XSS dans : " . $file);
                }
            }
        }
    }

    private function checkSecuritySettings() {
        // Vérification des paramètres de sécurité
        $configFile = $this->appDirectory . '/config.php';
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            if (strpos($content, 'display_errors') !== false) {
                $this->addWarning("display_errors activé dans config.php");
            }
        }
    }

    private function checkMemoryUsage() {
        // Vérification de l'utilisation de la mémoire
        $memoryUsage = memory_get_usage();
        if ($memoryUsage > 100000000) { // 100MB
            $this->addWarning("Utilisation élevée de la mémoire : " . $memoryUsage . " octets");
        }
    }

    private function calculatePerformance() {
        // Calcul basique de performance
        $start = microtime(true);
        $end = microtime(true);
        $executionTime = ($end - $start) * 1000;

        if ($executionTime > 100) {
            $this->addWarning("Temps d'exécution élevé : " . $executionTime . " ms");
        }
    }

    private function calculateCodeCoverage() {
        // Calcul de la couverture du code (simplifié)
        $phpFiles = $this->getPhpFiles($this->appDirectory);
        $totalLines = 0;
        $executableLines = 0;

        foreach ($phpFiles as $file) {
            $lines = file($file);
            $totalLines += count($lines);

            foreach ($lines as $line) {
                if (trim($line) !== '' && substr(trim($line), 0, 1) !== '/') {
                    $executableLines++;
                }
            }
        }

        return ['total' => $totalLines, 'executable' => $executableLines];
    }

    private function calculateCodeQuality() {
        // Calcul basique de la qualité du code
        $phpFiles = $this->getPhpFiles($this->appDirectory);
        $totalFiles = count($phpFiles);
        $qualityScore = 0;

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $lines = explode("\n", $content);
            $lineCount = count($lines);

            // Score basé sur la longueur et la complexité
            if ($lineCount < 100) {
                $qualityScore += 3;
            } elseif ($lineCount < 500) {
                $qualityScore += 2;
            } else {
                $qualityScore += 1;
            }
        }

        return $qualityScore / $totalFiles;
    }

    private function addError($error) {
        $this->results['errors'][] = $error;
    }

    private function addWarning($warning) {
        $this->results['warnings'][] = $warning;
    }

    private function calculateCodeComplexity() {
        // Calcul de la complexité cyclomatique (simplifié)
        $phpFiles = $this->getPhpFiles($this->appDirectory);
        $totalComplexity = 0;

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $complexity = 0;

            // Compter les structures de contrôle
            $complexity += substr_count($content, 'if(');
            $complexity += substr_count($content, 'for(');
            $complexity += substr_count($content, 'while(');
            $complexity += substr_count($content, 'switch(');

            $totalComplexity += $complexity;
        }

        return $totalComplexity;
    }

    private function calculateCodeDuplication() {
        // Détection basique de duplication de code
        $phpFiles = $this->getPhpFiles($this->appDirectory);
        $duplicatedLines = 0;

        foreach ($phpFiles as $file1) {
            $content1 = file_get_contents($file1);
            $lines1 = explode("\n", $content1);

            foreach ($phpFiles as $file2) {
                if ($file1 !== $file2) {
                    $content2 = file_get_contents($file2);
                    $lines2 = explode("\n", $content2);

                    foreach ($lines1 as $line) {
                        if (in_array($line, $lines2)) {
                            $duplicatedLines++;
                        }
                    }
                }
            }
        }

        return $duplicatedLines;
    }

    private function calculateCodeDocumentation() {
        // Calcul de la documentation
        $phpFiles = $this->getPhpFiles($this->appDirectory);
        $totalComments = 0;
        $totalLines = 0;

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                $totalLines++;
                if (strpos($line, '//') !== false || strpos($line, '/*') !== false) {
                    $totalComments++;
                }
            }
        }

        return $totalComments / $totalLines * 100;
    }

    private function calculateCodeStructure() {
        // Analyse de la structure du code
        $phpFiles = $this->getPhpFiles($this->appDirectory);
        $functionCount = 0;
        $classCount = 0;

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            // Compter les fonctions
            $functionCount += substr_count($content, 'function ');

            // Compter les classes
            $classCount += substr_count($content, 'class ');
        }

        return ['functions' => $functionCount, 'classes' => $classCount];
    }

    private function calculateCodeMaintainability() {
        // Calcul de la maintenabilité
        $phpFiles = $this->getPhpFiles($this->appDirectory);
        $totalMaintainability = 0;

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            $lines = explode("\n", $content);

            $maintainability = 0;

            // Score basé sur la longueur des lignes
            foreach ($lines as $line) {
                if (strlen(trim($line)) > 100) {
                    $maintainability -= 1;
                } elseif (strlen(trim($line)) > 50) {
                    $maintainability -= 0.5;
                }
            }

            $totalMaintainability += $maintainability;
        }

        return $totalMaintainability;
    }

    private function calculateCodeSecurity() {
        // Calcul de la sécurité
        $securityScore = 0;

        // Vérification des paramètres de sécurité
        if (ini_get('display_errors') === '1') {
            $securityScore -= 10;
        }

        if (ini_get('allow_url_include') === '1') {
            $securityScore -= 5;
        }

        if (ini_get('register_globals') === '1') {
            $securityScore -= 15;
        }

        return max(0, $securityScore);
    }

    private function calculateCodePerformance() {
        // Calcul de la performance
        $performanceScore = 0;

        // Vérification de l'utilisation de fonctions optimisées
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'mysql_query') !== false) {
                $performanceScore -= 5; // Déprécié
            }

            if (strpos($content, 'array_push') !== false) {
                $performanceScore += 1; // Bonne pratique
            }
        }

        return max(0, $performanceScore);
    }

    private function calculateCodeReliability() {
        // Calcul de la fiabilité
        $reliabilityScore = 0;

        // Vérification des erreurs de traitement
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'error_reporting') !== false) {
                $reliabilityScore += 2; // Bonne pratique
            }

            if (strpos($content, 'try {') !== false && strpos($content, 'catch') !== false) {
                $reliabilityScore += 3; // Bonne pratique
            }
        }

        return $reliabilityScore;
    }

    private function calculateCodeScalability() {
        // Calcul de l'évolutivité
        $scalabilityScore = 0;

        // Vérification des bonnes pratiques d'évolutivité
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'PDO') !== false) {
                $scalabilityScore += 3; // Bonne pratique
            }

            if (strpos($content, 'prepared statements') !== false) {
                $scalabilityScore += 2; // Bonne pratique
            }
        }

        return $scalabilityScore;
    }

    private function calculateCodeTestCoverage() {
        // Calcul de la couverture des tests
        $testFiles = glob('tests/*.php');
        $totalTests = count($testFiles);

        if ($totalTests > 0) {
            return $totalTests;
        }

        return 0;
    }

    private function calculateCodeRefactoring() {
        // Calcul de la nécessité de refactoring
        $refactoringScore = 0;

        // Vérification des pratiques de refactoring
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'eval(') !== false) {
                $refactoringScore += 10; // Problématique
            }

            if (strpos($content, 'goto ') !== false) {
                $refactoringScore += 5; // Problématique
            }
        }

        return $refactoringScore;
    }

    private function calculateCodeArchitecture() {
        // Calcul de l'architecture
        $architectureScore = 0;

        // Vérification de l'architecture
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'MVC') !== false) {
                $architectureScore += 5; // Bonne pratique
            }

            if (strpos($content, 'dependency injection') !== false) {
                $architectureScore += 3; // Bonne pratique
            }
        }

        return $architectureScore;
    }

    private function calculateCodeBestPractices() {
        // Calcul des bonnes pratiques
        $bestPracticesScore = 0;

        // Vérification des bonnes pratiques
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'PSR') !== false) {
                $bestPracticesScore += 5; // Bonne pratique
            }

            if (strpos($content, 'Composer') !== false) {
                $bestPracticesScore += 3; // Bonne pratique
            }
        }

        return $bestPracticesScore;
    }

    private function calculateCodeVersionControl() {
        // Calcul du contrôle de version
        $versionControlScore = 0;

        // Vérification du contrôle de version
        if (file_exists('.git')) {
            $versionControlScore += 5; // Bonne pratique
        }

        if (file_exists('.svn')) {
            $versionControlScore += 3; // Bonne pratique
        }

        return $versionControlScore;
    }

    private function calculateCodeDeployment() {
        // Calcul du déploiement
        $deploymentScore = 0;

        // Vérification du déploiement
        if (file_exists('deploy.php')) {
            $deploymentScore += 3; // Bonne pratique
        }

        if (file_exists('build.xml')) {
            $deploymentScore += 2; // Bonne pratique
        }

        return $deploymentScore;
    }

    private function calculateCodeMonitoring() {
        // Calcul de la surveillance
        $monitoringScore = 0;

        // Vérification de la surveillance
        if (file_exists('monitoring.php')) {
            $monitoringScore += 3; // Bonne pratique
        }

        if (file_exists('logs/')) {
            $monitoringScore += 2; // Bonne pratique
        }

        return $monitoringScore;
    }

    private function calculateCodeLogging() {
        // Calcul de la journalisation
        $loggingScore = 0;

        // Vérification de la journalisation
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'error_log') !== false) {
                $loggingScore += 2; // Bonne pratique
            }

            if (strpos($content, 'log(') !== false) {
                $loggingScore += 1; // Bonne pratique
            }
        }

        return $loggingScore;
    }

    private function calculateCodeCaching() {
        // Calcul du cache
        $cachingScore = 0;

        // Vérification du cache
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'memcached') !== false) {
                $cachingScore += 3; // Bonne pratique
            }

            if (strpos($content, 'redis') !== false) {
                $cachingScore += 2; // Bonne pratique
            }
        }

        return $cachingScore;
    }

    private function calculateCodeDatabase() {
        // Calcul de la base de données
        $databaseScore = 0;

        // Vérification de la base de données
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'mysqli') !== false) {
                $databaseScore += 2; // Bonne pratique
            }

            if (strpos($content, 'PDO') !== false) {
                $databaseScore += 3; // Bonne pratique
            }
        }

        return $databaseScore;
    }

    private function calculateCodeSecurityBestPractices() {
        // Calcul des bonnes pratiques de sécurité
        $securityBestPracticesScore = 0;

        // Vérification des bonnes pratiques de sécurité
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'htmlspecialchars') !== false) {
                $securityBestPracticesScore += 2; // Bonne pratique
            }

            if (strpos($content, 'filter_var') !== false) {
                $securityBestPracticesScore += 3; // Bonne pratique
            }

            if (strpos($content, 'prepared statements') !== false) {
                $securityBestPracticesScore += 5; // Bonne pratique
            }
        }

        return $securityBestPracticesScore;
    }

    private function calculateCodePerformanceBestPractices() {
        // Calcul des bonnes pratiques de performance
        $performanceBestPracticesScore = 0;

        // Vérification des bonnes pratiques de performance
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'OPcache') !== false) {
                $performanceBestPracticesScore += 3; // Bonne pratique
            }

            if (strpos($content, 'memcached') !== false) {
                $performanceBestPracticesScore += 2; // Bonne pratique
            }

            if (strpos($content, 'redis') !== false) {
                $performanceBestPracticesScore += 2; // Bonne pratique
            }
        }

        return $performanceBestPracticesScore;
    }

    private function calculateCodeMaintainabilityBestPractices() {
        // Calcul des bonnes pratiques de maintenabilité
        $maintainabilityBestPracticesScore = 0;

        // Vérification des bonnes pratiques de maintenabilité
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'PSR') !== false) {
                $maintainabilityBestPracticesScore += 3; // Bonne pratique
            }

            if (strpos($content, 'Composer') !== false) {
                $maintainabilityBestPracticesScore += 2; // Bonne pratique
            }

            if (strpos($content, 'autoload') !== false) {
                $maintainabilityBestPracticesScore += 2; // Bonne pratique
            }
        }

        return $maintainabilityBestPracticesScore;
    }

    private function calculateCodeScalabilityBestPractices() {
        // Calcul des bonnes pratiques d'évolutivité
        $scalabilityBestPracticesScore = 0;

        // Vérification des bonnes pratiques d'évolutivité
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'MVC') !== false) {
                $scalabilityBestPracticesScore += 3; // Bonne pratique
            }

            if (strpos($content, 'dependency injection') !== false) {
                $scalabilityBestPracticesScore += 2; // Bonne pratique
            }

            if (strpos($content, 'service container') !== false) {
                $scalabilityBestPracticesScore += 2; // Bonne pratique
            }
        }

        return $scalabilityBestPracticesScore;
    }

    private function calculateCodeArchitectureBestPractices() {
        // Calcul des bonnes pratiques d'architecture
        $architectureBestPracticesScore = 0;

        // Vérification des bonnes pratiques d'architecture
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'MVC') !== false) {
                $architectureBestPracticesScore += 3; // Bonne pratique
            }

            if (strpos($content, 'dependency injection') !== false) {
                $architectureBestPracticesScore += 2; // Bonne pratique
            }

            if (strpos($content, 'service container') !== false) {
                $architectureBestPracticesScore += 2; // Bonne pratique
            }
        }

        return $architectureBestPracticesScore;
    }

    private function calculateCodeTestingBestPractices() {
        // Calcul des bonnes pratiques de test
        $testingBestPracticesScore = 0;

        // Vérification des bonnes pratiques de test
        $testFiles = glob('tests/*.php');
        $totalTests = count($testFiles);

        if ($totalTests > 0) {
            $testingBestPracticesScore += 3; // Bonne pratique
        }

        if (file_exists('phpunit.xml')) {
            $testingBestPracticesScore += 2; // Bonne pratique
        }

        if (file_exists('behat.yml')) {
            $testingBestPracticesScore += 2; // Bonne pratique
        }

        return $testingBestPracticesScore;
    }

    private function calculateCodeDocumentationBestPractices() {
        // Calcul des bonnes pratiques de documentation
        $documentationBestPracticesScore = 0;

        // Vérification des bonnes pratiques de documentation
        if (file_exists('README.md')) {
            $documentationBestPracticesScore += 3; // Bonne pratique
        }

        if (file_exists('doc/')) {
            $documentationBestPracticesScore += 2; // Bonne pratique
        }

        if (file_exists('docs/')) {
            $documentationBestPracticesScore += 2; // Bonne pratique
        }

        return $documentationBestPracticesScore;
    }

    private function calculateCodeDeploymentBestPractices() {
        // Calcul des bonnes pratiques de déploiement
        $deploymentBestPracticesScore = 0;

        // Vérification des bonnes pratiques de déploiement
        if (file_exists('deploy.php')) {
            $deploymentBestPracticesScore += 3; // Bonne pratique
        }

        if (file_exists('build.xml')) {
            $deploymentBestPracticesScore += 2; // Bonne pratique
        }

        if (file_exists('Dockerfile')) {
            $deploymentBestPracticesScore += 2; // Bonne pratique
        }

        return $deploymentBestPracticesScore;
    }

    private function calculateCodeMonitoringBestPractices() {
        // Calcul des bonnes pratiques de surveillance
        $monitoringBestPracticesScore = 0;

        // Vérification des bonnes pratiques de surveillance
        if (file_exists('monitoring.php')) {
            $monitoringBestPracticesScore += 3; // Bonne pratique
        }

        if (file_exists('logs/')) {
            $monitoringBestPracticesScore += 2; // Bonne pratique
        }

        if (file_exists('stats.php')) {
            $monitoringBestPracticesScore += 2; // Bonne pratique
        }

        return $monitoringBestPracticesScore;
    }

    private function calculateCodeLoggingBestPractices() {
        // Calcul des bonnes pratiques de journalisation
        $loggingBestPracticesScore = 0;

        // Vérification des bonnes pratiques de journalisation
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'error_log') !== false) {
                $loggingBestPracticesScore += 2; // Bonne pratique
            }

            if (strpos($content, 'log(') !== false) {
                $loggingBestPracticesScore += 1; // Bonne pratique
            }
        }

        return $loggingBestPracticesScore;
    }

    private function calculateCodeCachingBestPractices() {
        // Calcul des bonnes pratiques de cache
        $cachingBestPracticesScore = 0;

        // Vérification des bonnes pratiques de cache
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'memcached') !== false) {
                $cachingBestPracticesScore += 3; // Bonne pratique
            }

            if (strpos($content, 'redis') !== false) {
                $cachingBestPracticesScore += 2; // Bonne pratique
            }
        }

        return $cachingBestPracticesScore;
    }

    private function calculateCodeDatabaseBestPractices() {
        // Calcul des bonnes pratiques de base de données
        $databaseBestPracticesScore = 0;

        // Vérification des bonnes pratiques de base de données
        $phpFiles = $this->getPhpFiles($this->appDirectory);

        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);

            if (strpos($content, 'mysqli') !== false) {
                $databaseBestPracticesScore += 2; // Bonne pratique
            }

            if (strpos($content, 'PDO') !== false) {
                $databaseBestPracticesScore += 3; // Bonne pratique
            }
        }

        return $databaseBestPracticesScore;
    }

    private function calculateCodeSecurityBestPracticesScore() {
        // Calcul du score des bonnes pratiques de sécurité
        $securityBestPracticesScore = $this->calculateCodeSecurityBestPractices();
        return $securityBestPracticesScore;
    }

    private function calculateCodePerformanceBestPracticesScore() {
        // Calcul du score des bonnes pratiques de performance
        $performanceBestPracticesScore = $this->calculateCodePerformanceBestPractices();
        return $performanceBestPracticesScore;
    }

    private function calculateCodeMaintainabilityBestPracticesScore() {
        // Calcul du score des bonnes pratiques de maintenabilité
        $maintainabilityBestPracticesScore = $this->calculateCodeMaintainabilityBestPractices();
        return $maintainabilityBestPracticesScore;
    }

    private function calculateCodeScalabilityBestPracticesScore() {
        // Calcul du score des bonnes pratiques d'évolutivité
        $scalabilityBestPracticesScore = $this->calculateCodeScalabilityBestPractices();
        return $scalabilityBestPracticesScore;
    }

    private function calculateCodeArchitectureBestPracticesScore() {
        // Calcul du score des bonnes pratiques d'architecture
        $architectureBestPracticesScore = $this->calculateCodeArchitectureBestPractices();
        return $architectureBestPracticesScore;
    }

    private function calculateCodeTestingBestPracticesScore() {
        // Calcul du score des bonnes pratiques de test
        $testingBestPracticesScore = $this->calculateCodeTestingBestPractices();
        return $testingBestPracticesScore;
    }

    private function calculateCodeDocumentationBestPracticesScore() {
        // Calcul du score des bonnes pratiques de documentation
        $documentationBestPracticesScore = $this->calculateCodeDocumentationBestPractices();
        return $documentationBestPracticesScore;
    }

    private function calculateCodeDeploymentBestPracticesScore() {
        // Calcul du score des bonnes pratiques de déploiement
        $deploymentBestPracticesScore = $this->calculateCodeDeploymentBestPractices();
        return $deploymentBestPracticesScore;
    }

    private function calculateCodeMonitoringBestPracticesScore() {
        // Calcul du score des bonnes pratiques de surveillance
        $monitoringBestPracticesScore = $this->calculateCodeMonitoringBestPractices();
        return $monitoringBestPracticesScore;
    }

    private function calculateCodeLoggingBestPracticesScore() {
        // Calcul du score des bonnes pratiques de journalisation
        $loggingBestPracticesScore = $this->calculateCodeLoggingBestPractices();
        return $loggingBestPracticesScore;
    }

    private function calculateCodeCachingBestPracticesScore() {
        // Calcul du score des bonnes pratiques de cache
        $cachingBestPracticesScore = $this->calculateCodeCachingBestPractices();
        return $cachingBestPracticesScore;
    }

    private function calculateCodeDatabaseBestPracticesScore() {
        // Calcul du score des bonnes pratiques de base de données
        $databaseBestPracticesScore = $this->calculateCodeDatabaseBestPractices();
        return $databaseBestPracticesScore;
    }

    private function calculateCodeOverallScore() {
        // Calcul du score global
        $overallScore = $this->calculateCodeSecurityBestPracticesScore() +
                        $this->calculateCodePerformanceBestPracticesScore() +
                        $this->calculateCodeMaintainabilityBestPracticesScore() +
                        $this->calculateCodeScalabilityBestPracticesScore() +
                        $this->calculateCodeArchitectureBestPracticesScore() +
                        $this->calculateCodeTestingBestPracticesScore() +
                        $this->calculateCodeDocumentationBestPracticesScore() +
                        $this->calculateCodeDeploymentBestPracticesScore() +
                        $this->calculateCodeMonitoringBestPracticesScore() +
                        $this->calculateCodeLoggingBestPracticesScore() +
                        $this->calculateCodeCachingBestPracticesScore() +
                        $this->calculateCodeDatabaseBestPracticesScore();
        return $overallScore;
    }

    public function getScore() {
        // Retourne le score global
        return $this->calculateCodeOverallScore();
    }
}
?>
