<?php
/**
 * Import DIRECT 6ème Maths - Sans config (28/02/2026)
 */

// PDO DIRECT (adapte DB/mot de passe)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ BDD connectée\n";
} catch (PDOException $e) {
    die("❌ BDD: " . $e->getMessage() . "\n");
}

$file = 'dev\tools\quiz\college\diagnostic_6eme_svt.json';
$data = json_decode(file_get_contents($file), true);

if (!$data) {
    die("❌ JSON KO\n");
}

echo "📄 {$data['contents']['title']}\n";

try {
    $pdo->beginTransaction();

    // Contents
    $stmt = $pdo->prepare("INSERT INTO contents (title, type, level, subject, description, status) VALUES (?, 'quiz', ?, ?, ?, 'published')");
    $stmt->execute([
        $data['contents']['title'],
        $data['contents']['level'],
        $data['contents']['subject'],
        $data['contents']['description']
    ]);
    $id = $pdo->lastInsertId();

    $pdo->commit();

    echo "🎉 PREMIER QUIZ IMPORTÉ ! ID: $id\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "❌ " . $e->getMessage() . "\n";
}
?>
