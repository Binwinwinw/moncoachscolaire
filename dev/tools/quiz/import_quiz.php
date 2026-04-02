<?php
if ($argc < 2) {
    die("Usage: php import_quiz.php nom_du_fichier.json\n");
}

$file = $argv[1];
if (!file_exists($file)) {
    die("❌ Fichier $file introuvable\n");
}

$pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents($file), true);
$title = $data['contents']['title'] ?? 'Inconnu';
$level = $data['contents']['level'] ?? 'unknown';
$subject = $data['contents']['subject'] ?? 'unknown';

echo "📁 Import: $file\n";
echo "🎯 $title ($level $subject)\n";

// Vérif doublon
$stmt = $pdo->prepare("SELECT id FROM contents WHERE title = ? AND level = ? AND subject = ?");
$stmt->execute([$title, $level, $subject]);
if ($row = $stmt->fetch()) {
    echo "ℹ️ DOUBLON trouvé ID: {$row['id']} (ignore)\n";
    exit("🎉 TERMINÉ (déjà importé)\n");
}

// Nouveau
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO contents (title, type, level, subject, description, status) VALUES (?, 'quiz', ?, ?, ?, 'published')");
    $stmt->execute([$title, $level, $subject, $data['contents']['description'] ?? 'Diagnostic']);
    $id = $pdo->lastInsertId();
    $pdo->commit();
    echo "✅ ✅ NOUVEAU ID: $id\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ " . $e->getMessage() . "\n";
}
?>
