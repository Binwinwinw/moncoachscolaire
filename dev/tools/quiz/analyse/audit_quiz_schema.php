<?php
/**
 * Audit de la structure finale des quiz runtime JSON.
 * Usage : php dev/tools/quiz/analyse/audit_quiz_schema.php [limit]
 */

$root = dirname(__DIR__, 4);
$quizDir = $root . '/src/data/quiz';
$answersDir = $root . '/src/data/quiz_answers';
$limit = isset($argv[1]) ? max(1, (int) $argv[1]) : 0;

function loadJsonFile(string $path): ?array
{
    if (!is_file($path)) {
        return null;
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return null;
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function validateQuizPayload(array $quizData): array
{
    $errors = [];
    $contents = $quizData['contents'] ?? null;
    $quiz = $quizData['quiz'] ?? null;

    if (!is_array($contents)) {
        $errors[] = 'contents_missing';
    }
    if (!is_array($quiz)) {
        $errors[] = 'quiz_missing';
        return $errors;
    }

    foreach (['title', 'level', 'subject'] as $key) {
        if (empty($contents[$key] ?? '')) {
            $errors[] = 'contents_' . $key . '_missing';
        }
        if (empty($quiz[$key] ?? '')) {
            $errors[] = 'quiz_' . $key . '_missing';
        }
    }

    $questions = $quiz['questions'] ?? null;
    if (!is_array($questions) || empty($questions)) {
        $errors[] = 'questions_missing';
        return $errors;
    }

    foreach ($questions as $index => $question) {
        if (!is_array($question)) {
            $errors[] = 'question_' . ($index + 1) . '_not_object';
            continue;
        }

        $type = str_replace('_', '-', (string) ($question['type'] ?? ''));
        if (!in_array($type, ['qcm', 'vrai-faux', 'open', 'texte'], true)) {
            $errors[] = 'question_' . ($index + 1) . '_type_invalid';
        }

        if (empty($question['question'] ?? '')) {
            $errors[] = 'question_' . ($index + 1) . '_text_missing';
        }

        if ($type === 'qcm') {
            $choices = $question['choices'] ?? [];
            if (!is_array($choices) || count($choices) < 2) {
                $errors[] = 'question_' . ($index + 1) . '_choices_invalid';
            }
        }

        foreach (['answer', 'correct', 'correct_option', 'correct_answer', 'correction'] as $forbidden) {
            if (array_key_exists($forbidden, $question)) {
                $errors[] = 'question_' . ($index + 1) . '_leaks_' . $forbidden;
            }
        }
    }

    return $errors;
}

function validateAnswersPayload(array $answersData, int $expectedCount): array
{
    $errors = [];
    $quiz = $answersData['quiz'] ?? null;

    if (!is_array($quiz)) {
        return ['answers_quiz_missing'];
    }

    $answers = $quiz['answers'] ?? null;
    if (!is_array($answers) || empty($answers)) {
        return ['answers_missing'];
    }

    if (count($answers) !== $expectedCount) {
        $errors[] = 'answers_count_mismatch';
    }

    foreach ($answers as $index => $answer) {
        if (!is_array($answer)) {
            $errors[] = 'answer_' . ($index + 1) . '_not_object';
            continue;
        }

        if (!array_key_exists('index', $answer)) {
            $errors[] = 'answer_' . ($index + 1) . '_index_missing';
        }
        if (!array_key_exists('question_id', $answer)) {
            $errors[] = 'answer_' . ($index + 1) . '_question_id_missing';
        }
        if (empty($answer['type'] ?? '')) {
            $errors[] = 'answer_' . ($index + 1) . '_type_missing';
        }
        if (!array_key_exists('answer', $answer)) {
            $errors[] = 'answer_' . ($index + 1) . '_answer_missing';
        }
        if (empty($answer['correction'] ?? '')) {
            $errors[] = 'answer_' . ($index + 1) . '_correction_missing';
        }
    }

    return $errors;
}

echo "🔍 Audit structure finale des quiz runtime\n";
echo "📁 Quiz dir: $quizDir\n";
echo "📁 Answers dir: $answersDir\n\n";

$quizFiles = glob($quizDir . '/*.json') ?: [];
sort($quizFiles, SORT_NATURAL);
if ($limit > 0) {
    $quizFiles = array_slice($quizFiles, 0, $limit);
}

$total = count($quizFiles);
$valid = 0;
$invalid = 0;
$issuesByType = [];
$examples = [];

foreach ($quizFiles as $quizPath) {
    $quizId = pathinfo($quizPath, PATHINFO_FILENAME);
    $quizData = loadJsonFile($quizPath);
    $answersPath = $answersDir . '/' . $quizId . '.json';
    $answersData = loadJsonFile($answersPath);

    $issues = [];

    if ($quizData === null) {
        $issues[] = 'quiz_json_invalid';
    } else {
        $issues = array_merge($issues, validateQuizPayload($quizData));
    }

    if ($answersData === null) {
        $issues[] = 'answers_json_missing_or_invalid';
    } else {
        $expectedCount = count($quizData['quiz']['questions'] ?? []);
        $issues = array_merge($issues, validateAnswersPayload($answersData, $expectedCount));
    }

    if (empty($issues)) {
        $valid++;
    } else {
        $invalid++;
        foreach ($issues as $issue) {
            $issuesByType[$issue] = ($issuesByType[$issue] ?? 0) + 1;
        }
        if (count($examples) < 20) {
            $examples[] = [
                'quiz_id' => $quizId,
                'issues' => $issues,
            ];
        }
    }
}

echo "📊 Résumé\n";
echo "  Quiz audités : $total\n";
echo "  Valides : $valid\n";
echo "  Invalides : $invalid\n\n";

if (!empty($issuesByType)) {
    arsort($issuesByType);
    echo "🚨 Top anomalies\n";
    foreach ($issuesByType as $issue => $count) {
        echo "  - $issue : $count\n";
    }
    echo "\n";
}

if (!empty($examples)) {
    echo "📝 Exemples\n";
    foreach ($examples as $example) {
        echo '  - Quiz ' . $example['quiz_id'] . ' : ' . implode(', ', $example['issues']) . "\n";
    }
    echo "\n";
}

echo $invalid === 0 ? "✅ Structure runtime conforme\n" : "⚠️ Des écarts structurels subsistent\n";
exit(0);
