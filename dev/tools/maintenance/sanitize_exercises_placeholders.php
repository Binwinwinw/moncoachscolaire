<?php
/**
 * Nettoie les exercices en base qui contiennent des réponses placeholders
 * Exemple de placeholders :
 *  a) Réponse correcte
 *  b) Une autre réponse possible
 *  c) aucune des réponses
 *  d) Réponse alternative
 *
 * Usage (CLI): php tools/sanitize_exercises_placeholders.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

function isPlaceholderLine($line) {
    $trim = trim($line);
    $patterns = [
        '/^[a-dA-D][\)\.\-]\s*Réponse\s+correcte\s*$/u',
        '/^[a-dA-D][\)\.\-]\s*Une\s+autre\s+réponse\s+possible\s*$/u',
        '/^[a-dA-D][\)\.\-]\s*aucune\s+des\s+réponses\s*$/ui',
        '/^[a-dA-D][\)\.\-]\s*Réponse\s+alternative\s*$/u',
        '/^\-\s*Réponse\s+correcte\s*$/u',
        '/^\-\s*Une\s+autre\s+réponse\s+possible\s*$/u',
        '/^\-\s*aucune\s+des\s+réponses\s*$/ui',
        '/^\-\s*Réponse\s+alternative\s*$/u'
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $trim)) return true;
    }
    return false;
}

function sanitizeText($text) {
    if (!$text) return $text;
    $lines = preg_split('/\r?\n/', $text);
    $clean = [];
    foreach ($lines as $line) {
        if (!isPlaceholderLine($line)) $clean[] = $line;
    }
    $result = preg_replace("/\n{3,}/", "\n\n", implode("\n", $clean));
    return trim($result);
}

function hasOnlyPlaceholders($text) {
    if (!$text) return false;
    $lines = preg_split('/\r?\n/', $text);
    $count = 0; $place = 0;
    foreach ($lines as $line) {
        if (trim($line) !== '') $count++;
        if (isPlaceholderLine($line)) $place++;
    }
    return $count > 0 && $count === $place;
}

if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "❌ Base de données non disponible\n");
    exit(1);
}

echo "🧽 NETTOYAGE DES EXERCICES PLACEHOLDERS\n";

$updated = 0; $checked = 0; $answerCleared = 0; $levels = [];

$stmt = $pdo->query("SELECT Id, Level, Subject, Title, Content, Answer FROM Exercises ORDER BY Level ASC, Id ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$upd = $pdo->prepare("UPDATE Exercises SET Content = ?, Answer = ? WHERE Id = ?");

foreach ($rows as $r) {
    $checked++;
    $level = $r['Level'];
    $levels[$level] = ($levels[$level] ?? 0) + 1;

    $newContent = sanitizeText($r['Content']);
    $newAnswer = sanitizeText($r['Answer']);

    // Si la réponse ne contient que des placeholders, la vider
    if (hasOnlyPlaceholders($r['Answer'])) {
        $newAnswer = '';
        $answerCleared++;
    }

    if ($newContent !== $r['Content'] || $newAnswer !== $r['Answer']) {
        $upd->execute([$newContent, $newAnswer, $r['Id']]);
        $updated++;
        echo "✓ Exercice #{$r['Id']} nettoyé ({$r['Level']} - {$r['Subject']})\n";
    }
}

echo "\nRésumé:\n";
echo "  Exercices vérifiés: $checked\n";
echo "  Exercices nettoyés: $updated\n";
echo "  Réponses placeholders vidées: $answerCleared\n";
foreach ($levels as $lvl => $cnt) {
    echo "  - $lvl: $cnt exercices\n";
}

echo "\n✅ Nettoyage terminé.\n";
