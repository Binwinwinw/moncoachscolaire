<?php
/**
 * Importeur d'exercices depuis des fichiers JSON
 * Usage (Windows/PowerShell):
 *   php tools/import_exercises_from_json.php                # importe tous les *.json dans /exercices
 *   php tools/import_exercises_from_json.php exercices\college-6eme-mathematiques.json exercices\college-6eme-francais.json
 *
 * Schéma attendu par fichier JSON:
 * {
 *   "level": "6ème",
 *   "subject": "Mathématiques",
 *   "replace": true,
 *   "source_pdf": "exercices/Exercices 6ème Collège.pdf",
 *   "corrections_pdf": "exercices/Corrigés Complets 6ème.pdf",
 *   "exercises": [
 *     {"title": "...", "content": "...", "answer": "...", "tags": ["..."], "difficulty": 1}
 *   ]
 * }
 */

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

if (!$pdo) {
    fwrite(STDERR, "[ERREUR] Base de données indisponible. Vérifiez db/connection.php.\n");
    exit(2);
}

if (function_exists('db_is_read_only') && db_is_read_only()) {
    fwrite(STDERR, "[ABORT] Mode lecture seule activé (DB_READ_ONLY). Import annulé.\n");
    exit(3);
}

function normalizeLevelInput($level) {
    return function_exists('normalizeLevelForDB') ? normalizeLevelForDB($level) : trim($level);
}

function normalizeSubjectInput($subject) {
    return function_exists('normalizeSubject') ? normalizeSubject($subject) : trim($subject);
}

function importFile($path) {
    global $pdo;

    if (!file_exists($path)) {
        fwrite(STDERR, "[WARN] Fichier introuvable: {$path}\n");
        return false;
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (!$data || !isset($data['level']) || !isset($data['subject']) || !isset($data['exercises'])) {
        fwrite(STDERR, "[WARN] Schéma JSON invalide pour {$path}.\n");
        return false;
    }

    $level = normalizeLevelInput($data['level']);
    $subject = normalizeSubjectInput($data['subject']);
    $replace = !empty($data['replace']);
    $count = is_array($data['exercises']) ? count($data['exercises']) : 0;

    echo "[INFO] Import: {$path} → Niveau={$level}, Matière={$subject}, Exercices={$count}, Replace=" . ($replace ? 'oui' : 'non') . "\n";

    try {
        $pdo->beginTransaction();

        if ($replace) {
            $del = $pdo->prepare("DELETE FROM Exercises WHERE Level = ? AND Subject = ?");
            $del->execute([$level, $subject]);
            echo "[INFO] Suppression des anciens exercices pour {$level}/{$subject}: " . $del->rowCount() . " supprimés\n";
        }

        $ins = $pdo->prepare("INSERT INTO Exercises (Subject, Level, Title, Content, Answer) VALUES (?, ?, ?, ?, ?)");
        $inserted = 0;

        foreach ($data['exercises'] as $idx => $ex) {
            $title = trim($ex['title'] ?? 'Exercice');
            $content = trim($ex['content'] ?? '');
            $answer = trim($ex['answer'] ?? '');

            if ($content === '') {
                fwrite(STDERR, "[WARN] Exercice #{$idx} ignoré: contenu vide.\n");
                continue;
            }

            $ins->execute([$subject, $level, $title, $content, $answer]);
            $inserted++;
        }

        $pdo->commit();
        echo "[OK] Import réussi: {$inserted} exercices insérés pour {$level}/{$subject}.\n";
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        fwrite(STDERR, "[ERREUR] PDO: " . $e->getMessage() . "\n");
        return false;
    } catch (Throwable $t) {
        $pdo->rollBack();
        fwrite(STDERR, "[ERREUR] " . $t->getMessage() . "\n");
        return false;
    }
}

// Résolution des fichiers à importer
$argvFiles = array_slice($argv ?? [], 1);
$files = [];

if (!empty($argvFiles)) {
    $files = $argvFiles;
} else {
    // Par défaut, importer tous les JSON dans /exercices
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'exercices';
    foreach (glob($dir . DIRECTORY_SEPARATOR . '*.json') as $f) {
        $files[] = $f;
    }
}

if (empty($files)) {
    echo "[INFO] Aucun fichier JSON détecté. Placez vos datasets dans /exercices/*.json.\n";
    exit(0);
}

$ok = true;
foreach ($files as $f) {
    $path = $f;
    // Normaliser chemins Windows/relative
    if (!is_file($path)) {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $f;
    }
    $res = importFile($path);
    $ok = $ok && $res;
}

exit($ok ? 0 : 1);
