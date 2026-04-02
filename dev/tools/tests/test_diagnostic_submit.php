<?php
/**
 * Script de test smoke pour l'endpoint diagnostic/submit.php
 *
 * Usage: php dev/tools/test_diagnostic_submit.php [quiz_id]
 * Exemple: php dev/tools/test_diagnostic_submit.php 12
 *
 * Ce script charge un quiz, génère un mix de réponses correctes/incorrectes,
 * puis teste l'endpoint en simulant une requête POST.
 */

// Configuration
$quizId = isset($argv[1]) ? (int) $argv[1] : 12;
$rootDir = dirname(__DIR__, 2);
$quizAnswersPath = $rootDir . '/src/data/quiz_answers/' . $quizId . '.json';
$quizPath = $rootDir . '/src/data/quiz/' . $quizId . '.json';

echo "=== TEST SMOKE: diagnostic/submit.php ===\n";
echo "Quiz ID: $quizId\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Étape 1: Vérifier que les fichiers existent
echo "[1/5] Vérification des fichiers...\n";
if (!file_exists($quizPath)) {
    die("❌ ERREUR: Quiz introuvable à {$quizPath}\n");
}
if (!file_exists($quizAnswersPath)) {
    die("❌ ERREUR: Réponses introuvables à {$quizAnswersPath}\n");
}
echo "✅ Fichiers trouvés\n\n";

// Étape 2: Charger les réponses correctes
echo "[2/5] Chargement des réponses correctes...\n";
$answersData = json_decode(file_get_contents($quizAnswersPath), true);
if (!$answersData || !isset($answersData['quiz']['answers'])) {
    die("❌ ERREUR: Format de réponses invalide\n");
}

$correctAnswers = $answersData['quiz']['answers'];
$questionCount = count($correctAnswers);
echo "✅ {$questionCount} questions chargées\n\n";

// Étape 3: Générer un payload de test avec mix correct/incorrect
echo "[3/5] Génération du payload de test...\n";
$testAnswers = [];
$expectedCorrect = 0;

foreach ($correctAnswers as $i => $answerDef) {
    $key = "q{$i}";

    // Alternance: réponse correcte pour les indices pairs, incorrecte pour les impairs
    if ($i % 2 === 0) {
        // Réponse correcte pour diagnostic.js (format attendu par l'endpoint)
        if ($answerDef['type'] === 'vrai-faux' || $answerDef['type'] === 'vrai_faux') {
            // Convertir booléen en string "vrai"/"faux" (comme diagnostic.js)
            $testAnswers[$key] = $answerDef['answer'] ? 'vrai' : 'faux';
        } else {
            $testAnswers[$key] = (string) $answerDef['answer'];
        }
        $expectedCorrect++;
        echo "  q{$i}: ✓ réponse correcte ({$answerDef['type']})\n";
    } else {
        // Générer une réponse intentionnellement fausse selon le type
        switch ($answerDef['type']) {
            case 'vrai-faux':
            case 'vrai_faux':
                // Inverse de la bonne réponse
                $testAnswers[$key] = $answerDef['answer'] ? 'faux' : 'vrai';
                break;
            case 'qcm':
            case 'texte':
            case 'calcul':
            default:
                $testAnswers[$key] = "MAUVAISE_REPONSE_TEST";
                break;
        }
        echo "  q{$i}: ✗ réponse incorrecte ({$answerDef['type']})\n";
    }
}

$payload = [
    'quiz_id' => $quizId,
    'answers' => $testAnswers,
    'duration_seconds' => 120
];

$expectedScore = round(($expectedCorrect / $questionCount) * 100, 2);
echo "\n📊 Attendu: {$expectedCorrect}/{$questionCount} correctes = {$expectedScore}%\n\n";

// Étape 4: Test direct (simulation interne - plus fiable pour smoke test)
echo "[4/5] Test de l'endpoint (simulation directe)...\n";

// Démarrer session propre pour le test (en silence)
@session_start();

// Simulation de l'environnement d'exécution
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SESSION['user_id'] = 1; // Utilisateur de test
$_SESSION['user_level'] = '6eme';
$_SESSION['user_name'] = 'Test User';

// Configuration PDO de test (utilise la config réelle du projet)
if (file_exists($rootDir . '/src/config/config.php')) {
    require_once $rootDir . '/src/config/config.php';
}
if (file_exists($rootDir . '/src/database/connection.php')) {
    require_once $rootDir . '/src/database/connection.php';
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    echo "⚠️ AVERTISSEMENT: Connexion PDO non disponible - test limité\n\n";
}

// Capturer la sortie de l'endpoint (supprimer warnings/notices)
error_reporting(E_ERROR | E_PARSE);
ob_start();
$httpCode = 200;

$GLOBALS['_TEST_PAYLOAD'] = json_encode($payload, JSON_UNESCAPED_UNICODE);

// Simuler php://input via stream wrapper
stream_wrapper_unregister('php');
stream_wrapper_register('php', 'TestStreamWrapper');

// Charger l'endpoint
require $rootDir . '/src/api/diagnostic/submit.php';

$response = ob_get_clean();
$httpCode = (int) http_response_code();
error_reporting(E_ALL);

stream_wrapper_restore('php');

echo "✅ Endpoint exécuté\n";
$httpStatusDisplay = $httpCode > 0 ? (string) $httpCode : 'N/A (execution CLI)';
echo "HTTP Status: {$httpStatusDisplay}\n";

// Étape 5: Valider la réponse
echo "\n[5/5] Validation de la réponse...\n";

// Extraire le JSON de la réponse (ignorer les warnings PHP)
$jsonStart = strpos($response, '{');
if ($jsonStart !== false) {
    $response = substr($response, $jsonStart);
}

$result = json_decode($response, true);

if (!$result) {
    echo "❌ ERREUR: Réponse JSON invalide\n";
    echo "Réponse brute (premiers 500 caractères):\n" . substr($response, 0, 500) . "\n";
    exit(1);
}

if (!isset($result['success'])) {
    echo "❌ ERREUR: Champ 'success' manquant\n";
    var_dump($result);
    exit(1);
}

if ($result['success'] === false) {
    echo "❌ ERREUR serveur: " . ($result['error'] ?? 'inconnue') . "\n";
    exit(1);
}

// Validation des données retournées
$data = $result['data'] ?? [];
$actualScore = $data['score'] ?? null;
$correctCount = $data['correct_count'] ?? null;
$xpGained = $data['xp_gained'] ?? null;
$passed = $data['passed'] ?? null;

echo "\n=== RÉSULTATS ===\n";
echo "Score: {$actualScore}% (attendu: ~{$expectedScore}%)\n";
echo "Correct: {$correctCount}/{$questionCount}\n";
echo "Réussi: " . ($passed ? 'OUI' : 'NON') . "\n";
echo "XP gagné: {$xpGained}\n";

if (isset($data['feedback']['message'])) {
    echo "Message: {$data['feedback']['message']}\n";
}
if (isset($data['feedback']['strengths']) && count($data['feedback']['strengths']) > 0) {
    echo "Points forts: " . implode(', ', $data['feedback']['strengths']) . "\n";
}
if (isset($data['feedback']['to_review']) && count($data['feedback']['to_review']) > 0) {
    echo "À revoir: " . implode(', ', $data['feedback']['to_review']) . "\n";
}

// Validation finale
$scoreMatch = abs($actualScore - $expectedScore) < 2; // Tolérance de 2%
$countMatch = $correctCount === $expectedCorrect;

echo "\n=== VALIDATION ===\n";
echo ($scoreMatch ? "✅" : "❌") . " Score cohérent\n";
echo ($countMatch ? "✅" : "❌") . " Nombre de réponses correctes cohérent\n";
echo (isset($data['xp_gained']) ? "✅" : "❌") . " XP retourné\n";
echo (isset($data['feedback']['message']) ? "✅" : "❌") . " Message de feedback présent\n";

if ($scoreMatch && $countMatch && isset($data['xp_gained'])) {
    echo "\n🎉 TEST RÉUSSI - Endpoint fonctionnel!\n";
    exit(0);
} else {
    echo "\n⚠️ TEST PARTIELLEMENT RÉUSSI - Vérifier les incohérences ci-dessus\n";
    exit(1);
}

/**
 * Stream wrapper pour simuler php://input dans les tests directs
 */
class TestStreamWrapper
{
    public $position = 0;
    public $data;

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        if ($path === 'php://input') {
            $this->data = $GLOBALS['_TEST_PAYLOAD'] ?? '';
            $this->position = 0;
            return true;
        }
        return false;
    }

    public function stream_read($count)
    {
        $ret = substr($this->data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_eof()
    {
        return $this->position >= strlen($this->data);
    }

    public function stream_stat()
    {
        return [];
    }
}
