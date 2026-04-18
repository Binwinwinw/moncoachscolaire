<?php
// Audit des pools de quiz
require dirname(__DIR__, 2) . '/src/config/config.php';

$quizDir = dirname(__DIR__, 2) . '/src/data/quiz';
$items = scandir($quizDir);

$pools = [];
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    $filePath = $quizDir . '/' . $item;
    if (!is_file($filePath)) continue;

    $json = json_decode(file_get_contents($filePath), true);
    if (!is_array($json)) continue;

    $level = $json['level'] ?? 'unknown';
    $subject = $json['subject'] ?? 'unknown';
    $key = $level . '|' . $subject;

    if (!isset($pools[$key])) {
        $pools[$key] = 0;
    }
    $pools[$key]++;
}

echo "=== POOLS DE QUIZ PAR NIVEAU/MATIÈRE ===\n\n";
$totalQuiz = 0;
foreach (array_keys($pools) as $key) {
    list($level, $subject) = explode('|', $key);
    $count = $pools[$key];
    $totalQuiz += $count;
    $warning = ($count < 5) ? ' ⚠️ PETIT POOL' : '';
    printf("[%-12s] %-20s: %4d quiz%s\n", $level, $subject, $count, $warning);
}

echo "\n=== STATISTIQUES ===\n";
echo "Total pools: " . count($pools) . "\n";
echo "Total quiz: " . $totalQuiz . "\n";

$critical = array_filter($pools, fn($c) => $c < 5);
echo "\nPools critiques (< 5): " . count($critical) . "\n";
foreach (array_keys($critical) as $key) {
    list($level, $subject) = explode('|', $key);
    printf("- %-12s / %-20s = %d quiz\n", $level, $subject, $critical[$key]);
}

echo "\nConclusion: L'anti-répétition dépend du stock réel. Les petits pools";
echo "\nforceront des répétitions rapides (comportement attendu).\n";
?>
