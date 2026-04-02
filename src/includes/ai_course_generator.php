<?php

/**
 * Générateur de cours automatique avec IA
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Génère un cours à partir d'un ou plusieurs exercices
 */
function generateCourseFromExercises($exerciseIds, $aiProvider = 'perplexity')
{
    global $pdo;

    // 1. Récupérer les exercices
    $exercises = getExercisesByIds($exerciseIds);

    if (empty($exercises)) {
        return ['error' => 'Aucun exercice trouvé'];
    }

    // 2. Analyser le contenu des exercices
    $analysis = analyzeExercises($exercises);

    // 3. Générer le cours avec l'IA
    $courseJson = generateCourseWithAI($analysis, $aiProvider);

    // 4. Sauvegarder en BDD
    $courseId = saveCourseToDatabase($courseJson);

    // 5. Lier les exercices au cours
    linkExercisesToCourse($exerciseIds, $courseId);

    return [
        'success' => true,
        'course_id' => $courseId,
        'course_data' => $courseJson,
    ];
}

/**
 * Analyse les exercices pour extraire les concepts clés
 */
function analyzeExercises($exercises)
{
    $concepts = [];
    $subject = $exercises[0]['Subject'] ?? 'Général';
    $level = $exercises[0]['Level'] ?? '6ème';

    foreach ($exercises as $ex) {
        // Extraire les concepts depuis le titre, contenu, domaine
        $concepts[] = [
            'title' => $ex['Title'],
            'content' => $ex['Content'],
            'domain' => $ex['Domain'] ?? '',
            'competence' => $ex['Competence'] ?? '',
        ];
    }

    return [
        'subject' => $subject,
        'level' => $level,
        'concepts' => $concepts,
        'exercise_count' => count($exercises),
    ];
}

/**
 * Génère le cours avec l'IA (Perplexity, OpenAI, Claude...)
 */
function generateCourseWithAI($analysis, $provider = 'perplexity')
{
    // Construire le prompt
    $prompt = buildCoursePrompt($analysis);

    // Appeler l'API de l'IA
    $response = callAIProvider($provider, $prompt);

    // Parser la réponse JSON
    $courseJson = json_decode($response, true);

    return $courseJson;
}

/**
 * Construit le prompt pour l'IA
 */
function buildCoursePrompt($analysis)
{
    $conceptsList = '';
    foreach ($analysis['concepts'] as $c) {
        $conceptsList .= "- {$c['title']}: {$c['domain']}\n";
    }

    $prompt = <<<PROMPT
Tu es un expert pédagogique. Génère un cours complet en JSON pour un élève de {$analysis['level']} en {$analysis['subject']}.

**Concepts à couvrir :**
{$conceptsList}

**Structure JSON attendue :**
{
  "title": "Titre du cours",
  "subject": "{$analysis['subject']}",
  "level": "{$analysis['level']}",
  "duration": 30,
  "difficulty": "moyen",
  "introduction": "Introduction claire et motivante...",
  "objectives": ["Objectif 1", "Objectif 2", "Objectif 3"],
  "sections": [
    {
      "id": 1,
      "title": "Section 1",
      "type": "theory",
      "content": "Contenu détaillé...",
      "examples": [
        {
          "input": "Exemple",
          "output": "Résultat",
          "explanation": "Pourquoi"
        }
      ]
    }
  ],
  "key_points": ["Point clé 1", "Point clé 2"],
  "summary": "Résumé du cours..."
}

**Consignes :**
- Langage simple et adapté au niveau {$analysis['level']}
- 3-5 sections maximum
- Exemples concrets et pertinents
- Focus sur la compréhension, pas la mémorisation
- Ton encourageant et positif

Réponds UNIQUEMENT avec le JSON valide, sans texte avant ou après.
PROMPT;

    return $prompt;
}

/**
 * Appelle l'API de l'IA (adaptable selon le provider)
 */
function callAIProvider($provider, $prompt)
{
    switch ($provider) {
        case 'perplexity':
            return callPerplexityAPI($prompt);
        case 'openai':
            return callOpenAIAPI($prompt);
        case 'claude':
            return callClaudeAPI($prompt);
        case 'gemini':
            return callGeminiAPI($prompt);
        case 'groq':
            return callGroqAPI($prompt);
        case 'ollama':
            return callOllamaAPI($prompt);
        default:
            throw new Exception("Provider inconnu: $provider");
    }
}

/**
 * Appel à l'API Ollama (container local)
 */
function callOllamaAPI($prompt)
{
    $ollamaUrl = getenv('OLLAMA_API_URL') ?: 'http://localhost:11434/api/generate';
    $model = getenv('OLLAMA_MODEL') ?: 'llama3';
    $data = [
        'model' => $model,
        'prompt' => $prompt,
        'stream' => false
    ];

    $ch = curl_init($ollamaUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    // Bypass SSL en local uniquement
    if (php_sapi_name() !== 'cli' && (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false)) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("Ollama cURL error ($httpCode): $curlError");
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Ollama JSON parse error: ' . json_last_error_msg());
    }
    if (isset($result['error'])) {
        throw new Exception('Ollama API error: ' . ($result['error']['message'] ?? json_encode($result['error'])));
    }
    // Ollama renvoie le texte généré dans 'response' (pas de structure OpenAI)
    if (empty($result['response'])) {
        throw new Exception('Ollama API renvoie un message vide');
    }

    return $result['response'];
}

/**
 * Exemple d'appel à Perplexity API
 */
function callPerplexityAPI($prompt)
{
    $apiKey = getenv('PERPLEXITY_API_KEY') ?: 'your_api_key_here';

    $data = [
        'model' => 'llama-3.1-sonar-large-128k-online',
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un expert pédagogique qui génère des cours en JSON.'],
            ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => 0.7,
        'max_tokens' => 4000,
    ];

    $ch = curl_init('https://api.perplexity.ai/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // Bypass SSL en local uniquement (XAMPP)
    if (php_sapi_name() !== 'cli' && (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false)) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("Perplexity cURL error ($httpCode): $curlError");
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Perplexity JSON parse error: ' . json_last_error_msg());
    }
    if (isset($result['error'])) {
        throw new Exception('Perplexity API error: ' . ($result['error']['message'] ?? json_encode($result['error'])));
    }
    if (empty($result['choices'][0]['message']['content'])) {
        throw new Exception('Perplexity API renvoie un message vide');
    }

    return $result['choices'][0]['message']['content'];
}

/**
 * Appel à l'API OpenAI
 */
function callOpenAIAPI($prompt)
{
    $apiKey = getenv('OPENAI_API_KEY') ?: 'your_api_key_here';

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un expert pédagogique.'],
            ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => 0.7,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // Bypass SSL en local uniquement (XAMPP)
    if (php_sapi_name() !== 'cli' && (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false)) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("OpenAI cURL error ($httpCode): $curlError");
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('OpenAI JSON parse error: ' . json_last_error_msg());
    }
    if (isset($result['error'])) {
        throw new Exception('OpenAI API error: ' . ($result['error']['message'] ?? json_encode($result['error'])));
    }
    if (empty($result['choices'][0]['message']['content'])) {
        throw new Exception('OpenAI API renvoie un message vide');
    }

    return $result['choices'][0]['message']['content'];
}

/**
 * Appel à l'API Gemini (Google AI Studio)
 */
function callGeminiAPI($prompt)
{
    $apiKey = getenv('GEMINI_API_KEY') ?: 'your_api_key_here';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 4000,
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // Bypass SSL en local uniquement (XAMPP)
    if (php_sapi_name() !== 'cli' && (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false)) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("Gemini cURL error ($httpCode): $curlError");
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Gemini JSON parse error: ' . json_last_error_msg());
    }
    if (isset($result['error'])) {
        throw new Exception('Gemini API error: ' . ($result['error']['message'] ?? json_encode($result['error'])));
    }
    if (empty($result['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception('Gemini API renvoie un message vide');
    }

    return $result['candidates'][0]['content']['parts'][0]['text'];
}

/**
 * Appel à l'API Groq (Llama 3)
 */
function callGroqAPI($prompt)
{
    $apiKey = getenv('GROQ_API_KEY') ?: 'your_api_key_here';

    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un expert pédagogique.'],
            ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => 0.7,
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // Bypass SSL en local uniquement (XAMPP)
    if (php_sapi_name() !== 'cli' && (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false)) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("Groq cURL error: $curlError");
    }

    $result = json_decode($response, true);

    if (isset($result['error'])) {
        throw new Exception("Groq API error: " . ($result['error']['message'] ?? json_encode($result['error'])));
    }

    return $result['choices'][0]['message']['content'] ?? '{}';
}

/**
 * Sauvegarde le cours en BDD
 */
function saveCourseToDatabase($courseJson)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO courses (Title, Subject, Level, Content, Description, CourseNumber, Slug, Difficulty, Duration, CreatedAt)
        VALUES (:title, :subject, :level, :content, :description, :course_number, :slug, :difficulty, :duration, NOW())
    ");

    $slug = generateSlug($courseJson['title']);
    $courseNumber = getNextCourseNumber($courseJson['subject'], $courseJson['level']);

    $stmt->execute([
        'title' => $courseJson['title'],
        'subject' => $courseJson['subject'],
        'level' => $courseJson['level'],
        'content' => json_encode($courseJson, JSON_UNESCAPED_UNICODE),
        'description' => $courseJson['introduction'] ?? '',
        'course_number' => $courseNumber,
        'slug' => $slug,
        'difficulty' => $courseJson['difficulty'] ?? 'moyen',
        'duration' => $courseJson['duration'] ?? 30,
    ]);

    return $pdo->lastInsertId();
}

/**
 * Lie les exercices au cours
 */
function linkExercisesToCourse($exerciseIds, $courseId)
{
    global $pdo;

    foreach ($exerciseIds as $exId) {
        // Récupérer les LinkedCourses actuels
        $stmt = $pdo->prepare("SELECT LinkedCourses FROM exercises WHERE Id = ?");
        $stmt->execute([$exId]);
        $current = $stmt->fetchColumn();

        $linkedCourses = $current ? json_decode($current, true) : [];

        // Ajouter le nouveau cours s'il n'existe pas déjà
        if (!in_array($courseId, $linkedCourses)) {
            $linkedCourses[] = $courseId;
        }

        // Mettre à jour
        $stmt = $pdo->prepare("UPDATE exercises SET LinkedCourses = ? WHERE Id = ?");
        $stmt->execute([json_encode($linkedCourses), $exId]);
    }
}

// Fonctions utilitaires
function generateSlug($title)
{
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

function getNextCourseNumber($subject, $level)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT MAX(CourseNumber) FROM courses WHERE Subject = ? AND Level = ?");
    $stmt->execute([$subject, $level]);
    return ($stmt->fetchColumn() ?: 0) + 1;
}

function getExercisesByIds($ids)
{
    global $pdo;
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM exercises WHERE Id IN ($placeholders)");
    $stmt->execute($ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
