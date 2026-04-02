<?php
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use MonCoachScolaire\Parsers\ParserRegistry;
use MonCoachScolaire\Parsers\MathParser;
use MonCoachScolaire\Parsers\FrancaisParser;
use MonCoachScolaire\Parsers\GenericParser;

$user = getenv('MCS_DB_USER') ?: 'root';
$pass = getenv('MCS_DB_PASS') ?: '';
$pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire', $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$registry = new ParserRegistry(new GenericParser());
$registry->register('mathématiques_probleme', new MathParser());
$registry->register('français_accord', new FrancaisParser());

$stmt = $pdo->query("SELECT Id, Subject, Content, Type, Level, Title FROM exercises"); // Sans LIMIT
$count = 0;
foreach ($stmt as $row) {
    // Injection Level/Title si vides dans le JSON
    $rawArr = [
        'Subject' => $row['Subject'],
        'Content' => $row['Content'],
        'Type' => $row['Type'],
        'Level' => $row['Level'] ?? '',
        'Title' => $row['Title'] ?? ''
    ];
    $raw = json_encode($rawArr, JSON_UNESCAPED_UNICODE);
    $parser = $registry->getParser($row['Subject'], $row['Type']);
    $parsed = $parser->parse($raw);
    $update = $pdo->prepare("UPDATE exercises SET structure_type = ?, sub_questions = ? WHERE Id = ?");
    $update->execute([
        $parsed->type ?? 'simple',
        json_encode($parsed->subQuestions ?? $parsed->questions ?? [], JSON_UNESCAPED_UNICODE),
        $row['Id']
    ]);
    $count++;
    if ($count % 100 == 0) echo "✅ $count/1122\n";
}
echo "🎉 $count exercices intégrés !\n";
