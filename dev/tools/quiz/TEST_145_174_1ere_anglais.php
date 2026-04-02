<?php

declare(strict_types=1);

$options = getopt('', ['dry-run', 'apply', 'start::', 'end::']);
$dryRun = !isset($options['apply']) || isset($options['dry-run']);

$startId = isset($options['start']) ? (int) $options['start'] : 145;
$endId = isset($options['end']) ? (int) $options['end'] : 174;

if ($startId > $endId) {
    fwrite(STDERR, "Erreur: --start doit etre inferieur ou egal a --end.\n");
    exit(1);
}

$baseDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR;
$outputRoot = $baseDir . 'src/data/quiz_enriched/';
$quizDir = $outputRoot . 'quiz/';
$answersDir = $outputRoot . 'quiz_answers/';

if (!is_dir($quizDir) && !mkdir($quizDir, 0777, true) && !is_dir($quizDir)) {
    fwrite(STDERR, "Erreur: impossible de creer $quizDir\n");
    exit(1);
}

if (!is_dir($answersDir) && !mkdir($answersDir, 0777, true) && !is_dir($answersDir)) {
    fwrite(STDERR, "Erreur: impossible de creer $answersDir\n");
    exit(1);
}

echo 'Generation 1ere anglais IDs ' . $startId . '-' . $endId . PHP_EOL;
echo 'Mode: ' . ($dryRun ? 'DRY-RUN (aucune ecriture)' : 'APPLY (ecriture active)') . PHP_EOL;
echo 'Sortie: ' . $outputRoot . PHP_EOL . PHP_EOL;

$axes1ere = [
    'Identities/exchanges',
    'Art/power',
    'Myths/heroes',
    'Places/power',
    'Idea/progress',
    'Diversity/inclusion',
    'Fashion/identity',
    'Travel/tourism',
    'Environment',
    'Music/identity',
    'Social media',
    'Tech revolution',
    'Inequalities',
    'Future jobs',
    'Work/leisure',
    'Education systems',
];

function buildThemeSet(int $id, array $axes): array
{
    $idx = ($id - 145) % count($axes);
    return [
        $axes[$idx],
        $axes[($idx + 4) % count($axes)],
        $axes[($idx + 8) % count($axes)],
    ];
}

function pickVariant(array $variants, int $seed): array
{
    return $variants[$seed % count($variants)];
}

function buildQuestionVariants(string $axis): array
{
    return [
        [
            'qcm' => "In the axis '$axis', what is the central focus?",
            'choices' => [
                'Random grammar drills',
                'Cultural interaction and identity construction',
                'Only vocabulary memorization',
                'No link with society',
            ],
            'vf' => "The axis '$axis' is relevant to B1+ oral and written argumentation.",
            'text' => "Give a concrete example related to '$axis' and explain its social impact in 4-5 sentences.",
            'placeholder' => 'Write your answer in English (4-5 sentences).',
        ],
        [
            'qcm' => "Which objective best matches the axis '$axis'?",
            'choices' => [
                'Memorizing isolated words only',
                'Analyzing social representations and intercultural dynamics',
                'Avoiding any personal opinion',
                'Practicing punctuation without context',
            ],
            'vf' => "Studying '$axis' helps students build nuanced opinions with evidence.",
            'text' => "Write a short argument about '$axis' using one example from current events and one personal observation.",
            'placeholder' => 'Write your answer in English (5 sentences).',
        ],
        [
            'qcm' => "In the curriculum, '$axis' mainly trains students to:",
            'choices' => [
                'Repeat fixed phrases without analysis',
                'Interpret cultural issues and justify a point of view',
                'Translate word by word only',
                'Avoid debating controversial topics',
            ],
            'vf' => "The axis '$axis' can be connected to debates on identity, media, or citizenship.",
            'text' => "Provide a mini case study linked to '$axis' and discuss one benefit and one limit.",
            'placeholder' => 'Write your answer in English (5-6 sentences).',
        ],
    ];
}

function correctionPrefix(int $id): string
{
    $prefixes = [
        'Expected answer:',
        'Correct answer:',
        'Model correction:',
    ];

    return $prefixes[$id % count($prefixes)];
}

function generateQuiz(int $id, array $axes): array
{
    [$a1, $a2, $a3] = buildThemeSet($id, $axes);
    $serie = $id - 124;

    $v1 = pickVariant(buildQuestionVariants($a1), $id);
    $v2 = pickVariant(buildQuestionVariants($a2), $id + 1);
    $v3 = pickVariant(buildQuestionVariants($a3), $id + 2);

    $questions = [
        [
            'type' => 'qcm',
            'question' => $v1['qcm'],
            'choices' => $v1['choices'],
            'id' => 1,
        ],
        [
            'type' => 'vrai-faux',
            'question' => $v1['vf'],
            'id' => 2,
        ],
        [
            'type' => 'texte',
            'question' => $v1['text'],
            'placeholder' => $v1['placeholder'],
            'id' => 3,
        ],
        [
            'type' => 'qcm',
            'question' => $v2['qcm'],
            'choices' => $v2['choices'],
            'id' => 4,
        ],
        [
            'type' => 'vrai-faux',
            'question' => $v2['vf'],
            'id' => 5,
        ],
        [
            'type' => 'texte',
            'question' => $v2['text'],
            'placeholder' => $v2['placeholder'],
            'id' => 6,
        ],
        [
            'type' => 'qcm',
            'question' => $v3['qcm'],
            'choices' => $v3['choices'],
            'id' => 7,
        ],
        [
            'type' => 'vrai-faux',
            'question' => $v3['vf'],
            'id' => 8,
        ],
    ];

    return [
        'contents' => [
            'title' => "Quiz Diagnostic 1ere Anglais - Serie $serie",
            'type' => 'quiz',
            'level' => '1ere',
            'subject' => 'Anglais',
            'description' => "Diagnostic Anglais 1ere - axes $a1, $a2, $a3 - expression argumentee B1+.",
            'status' => 'published',
            'created_at' => '2026-03-08 00:05:00',
            'updated_at' => '2026-03-10 23:00:00',
        ],
        'quiz' => [
            'title' => "Quiz Diagnostic 1ere Anglais - Serie $serie",
            'level' => '1ere',
            'subject' => 'Anglais',
            'question_count' => 8,
            'passing_score' => 70,
            'time_limit_minutes' => 15,
            'questions' => $questions,
        ],
    ];
}

function generateAnswers(int $id, array $axes): array
{
    [$a1, $a2, $a3] = buildThemeSet($id, $axes);
    $serie = $id - 124;
    $prefix = correctionPrefix($id);

    $answers = [
        [
            'index' => 0,
            'question_id' => 1,
            'type' => 'qcm',
            'correction' => "$prefix Cultural interaction and identity construction. In '$a1', students examine how exchanges shape identity and social perception in English-speaking contexts.",
        ],
        [
            'index' => 1,
            'question_id' => 2,
            'type' => 'vrai-faux',
            'correction' => "$prefix TRUE. This axis develops B1+ argumentation skills: expressing nuance, organizing ideas, and supporting claims with examples.",
        ],
        [
            'index' => 2,
            'question_id' => 3,
            'type' => 'texte',
            'correction' => "Model output: provide one concrete situation linked to '$a1', explain a positive effect and a possible drawback, then end with a justified personal opinion.",
        ],
        [
            'index' => 3,
            'question_id' => 4,
            'type' => 'qcm',
            'correction' => "$prefix Analyzing social representations and intercultural dynamics. In '$a2', learners identify who influences public opinion and through which channels.",
        ],
        [
            'index' => 4,
            'question_id' => 5,
            'type' => 'vrai-faux',
            'correction' => "$prefix TRUE. '$a2' is directly connected to current issues such as media campaigns, representation, and social power structures.",
        ],
        [
            'index' => 5,
            'question_id' => 6,
            'type' => 'texte',
            'correction' => "Model output: describe one real case related to '$a2', identify actors and stakes, then defend your position with two clear arguments and one connector of contrast.",
        ],
        [
            'index' => 6,
            'question_id' => 7,
            'type' => 'qcm',
            'correction' => "$prefix Interpreting cultural issues and justifying a point of view. The axis '$a3' supports critical discussion of innovation, ethics, and social impact.",
        ],
        [
            'index' => 7,
            'question_id' => 8,
            'type' => 'vrai-faux',
            'correction' => "$prefix TRUE. '$a3' can be used for present-day topics (AI, climate transition, future of work) with structured argumentation.",
        ],
    ];

    return [
        'contents' => [
            'title' => "Quiz Diagnostic 1ere Anglais - Serie $serie",
            'level' => '1ere',
            'subject' => 'Anglais',
        ],
        'quiz' => [
            'title' => "Quiz Diagnostic 1ere Anglais - Serie $serie",
            'question_count' => 8,
            'answers' => $answers,
            'level' => '1ere',
            'subject' => 'Anglais',
        ],
    ];
}

$generated = 0;
for ($id = $startId; $id <= $endId; $id++) {
    $quizPayload = generateQuiz($id, $axes1ere);
    $answersPayload = generateAnswers($id, $axes1ere);

    $quizPath = $quizDir . $id . '.json';
    $answersPath = $answersDir . $id . '.json';

    $quizJson = json_encode($quizPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $answersJson = json_encode($answersPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($quizJson === false || $answersJson === false) {
        fwrite(STDERR, "Erreur JSON pour l'ID $id\n");
        continue;
    }

    if ($dryRun) {
        echo "[DRY-RUN] ID $id -> $quizPath | $answersPath\n";
    } else {
        file_put_contents($quizPath, $quizJson . PHP_EOL);
        file_put_contents($answersPath, $answersJson . PHP_EOL);
        echo "[OK] ID $id ecrit\n";
    }

    $generated++;
}

echo PHP_EOL . 'Total traite: ' . $generated . ' quiz.' . PHP_EOL;
echo $dryRun
    ? "Dry-run termine: aucun fichier n'a ete ecrit.\n"
    : "Generation terminee: fichiers ecrits dans $outputRoot\n";
