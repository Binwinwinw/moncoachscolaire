<?php

require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

use MonCoachScolaire\Parsers\ParserRegistry;
use MonCoachScolaire\Parsers\MathParser;
use MonCoachScolaire\Parsers\FrancaisParser;
use MonCoachScolaire\Parsers\GenericParser;

$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire;charset=utf8mb4', $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$registry = new ParserRegistry(new GenericParser());
$registry->register('mathématiques', new MathParser());
$registry->register('français', new FrancaisParser());


$stmt = $pdo->query("SELECT Id, Subject, Content, Type FROM exercises");
$count = 0;
foreach ($stmt as $row) {
    try {
        $raw = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $parser = $registry->getParser($row['Subject'], $row['Type']);
        $parsed = $parser->parse($raw);

        $subQuestions = $parsed->subQuestions ?? $parsed->questions ?? [];
        $update = $pdo->prepare("UPDATE exercises SET structure_type = ?, sub_questions = ? WHERE Id = ?");
        $update->execute([
            $parsed->type ?? 'simple',
            json_encode($subQuestions, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            $row['Id']
        ]);
        $count++;
        echo "✅ ID {$row['Id']} intégré\n";
    } catch (Throwable $e) {
        echo "❌ ID {$row['Id']}: " . $e->getMessage() . "\n";
    }
}
echo "🎉 $count exercices intégrés (test LIMIT 5) !\n";
