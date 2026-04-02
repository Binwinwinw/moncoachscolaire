<?php
/**
 * dev/tools/exercises/import_exercises.php
 *
 * Importe ou met à jour les exercices définis dans les fichiers JSON (db/json/exercices/).
 * Utilise 'Identifier' comme clé pour éviter les doublons.
 */

if (php_sapi_name() !== 'cli') {
    die('Ce script doit être exécuté en ligne de commande.');
}

require_once __DIR__ . '/../../../src/config/config.php';
require_once __DIR__ . '/../../../src/database/connection.php';

echo "🚀 Importation des exercices JSON\n";
echo "==============================\n\n";

$jsonDir = __DIR__ . '/../../../db/json/exercices';

if (!is_dir($jsonDir)) {
    die("Dossier introuvable : $jsonDir\n");
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($jsonDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$filesToProcess = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'json') {
        // Ignorer les fichiers de schéma
        if (strpos($file->getFilename(), 'schema') !== false) {
            continue;
        }
        $filesToProcess[] = $file->getPathname();
    }
}

echo "INFO : " . count($filesToProcess) . " fichiers JSON trouvés.\n\n";

$totalProcessed = 0;
$totalInserted = 0;
$totalUpdated = 0;
$totalErrors = 0;

foreach ($filesToProcess as $filePath) {
    echo "Processing: " . basename($filePath) . "\n";
    $json = file_get_contents($filePath);
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "  ERREUR JSON : " . json_last_error_msg() . "\n";
        $totalErrors++;
        continue;
    }

    if (!is_array($data)) {
        echo "  ERREUR : Format invalide (pas un tableau)\n";
        $totalErrors++;
        continue;
    }

    foreach ($data as $exo) {
        if (empty($exo['Identifier']) || empty($exo['Title'])) {
            // Ignorer entrées invalides
            echo "  ⚠️  Entrée ignorée (Identifier ou Title manquant)\n";
            continue;
        }

        $totalProcessed++;

        // Nettoyage / Typage
        $identifier = $exo['Identifier'];
        if (is_array($identifier)) $identifier = implode('-', $identifier);

        $subject = $exo['Subject'] ?? null;
        if (is_array($subject)) $subject = implode(', ', $subject);

        $level = $exo['Level'] ?? null;
        if (is_array($level)) $level = implode(', ', $level);

        $title = $exo['Title'] ?? null;
        if (is_array($title)) $title = implode(' - ', $title);

        // Content traité plus bas
        //$content = $exo['Content'] ?? null;
        //$instruction = $exo['Instruction'] ?? null;
        $answer = $exo['Answer'] ?? null;
        $answerType = $exo['AnswerType'] ?? null;
        if (is_array($answerType)) $answerType = $answerType[0] ?? 'texte';

        $tips = $exo['Tips'] ?? null;
        $domain = $exo['Domain'] ?? null;
        $competence = $exo['Competence'] ?? null;
        $difficulty = $exo['Difficulty'] ?? null;

        // Gestion des champs JSON/Booleens qui peuvent être des chaînes ou tableaux
        $choices = $exo['Choices'] ?? null;
        if (is_array($choices)) {
            $choices = json_encode($choices, JSON_UNESCAPED_UNICODE);
        } elseif ($choices === 'null') {
            $choices = null;
        }

        // Fix: Answer peut être un tableau dans certains JSON
        $answer = $exo['Answer'] ?? null;
        if (is_array($answer)) {
            $answer = json_encode($answer, JSON_UNESCAPED_UNICODE);
        }

        $instruction = $exo['Instruction'] ?? null;
        if (is_array($instruction)) {
            $instruction = implode("\n", $instruction);
        }

        $tips = $exo['Tips'] ?? null;
        if (is_array($tips)) {
            $tips = implode("\n", $tips);
        }

        $domain = $exo['Domain'] ?? null;
        if (is_array($domain)) {
            $domain = implode(", ", $domain);
        }

        $competence = $exo['Competence'] ?? null;
        if (is_array($competence)) {
            $competence = implode(", ", $competence);
        }

        $difficulty = $exo['Difficulty'] ?? null;
        if (is_array($difficulty)) { // Peu probable mais bon
            $difficulty = $difficulty[0] ?? 'moyen';
        }

        $content = $exo['Content'] ?? null;
        if (is_array($content)) {
            $content = implode("\n", $content);
        }

        $isActive = filter_var($exo['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $xpPoints = intval($exo['XP_Points'] ?? 10);
        $coherence = filter_var($exo['Coherence'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        try {
            // Vérifier existence
            $checkStmt = $pdo->prepare("SELECT Id FROM exercises WHERE Identifier = ?");
            $checkStmt->execute([$identifier]);
            $existingId = $checkStmt->fetchColumn();

            if ($existingId) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE exercises SET
                        Subject = :subject, Level = :level, Title = :title, Content = :content,
                        Instruction = :instruction, Answer = :answer, AnswerType = :answerType,
                        Choices = :choices, Tips = :tips, Domain = :domain, Competence = :competence,
                        Difficulty = :difficulty, is_active = :isActive, XP_Points = :xpPoints,
                        Coherence = :coherence
                    WHERE Id = :id
                ");
                $stmt->execute([
                    ':subject' => $subject, ':level' => $level, ':title' => $title, ':content' => $content,
                    ':instruction' => $instruction, ':answer' => $answer, ':answerType' => $answerType,
                    ':choices' => $choices, ':tips' => $tips, ':domain' => $domain, ':competence' => $competence,
                    ':difficulty' => $difficulty, ':isActive' => $isActive, ':xpPoints' => $xpPoints,
                    ':coherence' => $coherence, ':id' => $existingId
                ]);
                $totalUpdated++;
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO exercises (
                        Identifier, Subject, Level, Title, Content, Instruction, Answer,
                        AnswerType, Choices, Tips, Domain, Competence, Difficulty,
                        is_active, XP_Points, Coherence
                    ) VALUES (
                        :identifier, :subject, :level, :title, :content, :instruction, :answer,
                        :answerType, :choices, :tips, :domain, :competence, :difficulty,
                        :isActive, :xpPoints, :coherence
                    )
                ");
                $stmt->execute([
                    ':identifier' => $identifier, ':subject' => $subject, ':level' => $level, ':title' => $title, ':content' => $content,
                    ':instruction' => $instruction, ':answer' => $answer, ':answerType' => $answerType,
                    ':choices' => $choices, ':tips' => $tips, ':domain' => $domain, ':competence' => $competence,
                    ':difficulty' => $difficulty, ':isActive' => $isActive, ':xpPoints' => $xpPoints,
                    ':coherence' => $coherence
                ]);
                $totalInserted++;
            }
        } catch (PDOException $e) {
            echo "  ❌ Erreur BDD pour $identifier : " . $e->getMessage() . "\n";
            $totalErrors++;
        }
    }
}

echo "\n==============================\n";
echo "Rapport d'importation :\n";
echo "  Total traités : $totalProcessed\n";
echo "  Ajoutés       : $totalInserted\n";
echo "  Mis à jour    : $totalUpdated\n";
echo "  Erreurs       : $totalErrors\n";
echo "==============================\n";
