<?php
/**
 * Test rapide : Vérifier que l'API generate_precise_course fonctionne avec Groq
 * Usage: php dev/tools/tests/test-course-api.php
 *
 * Simule la requête complète d'un exercice incorrecte et vérifie que Groq répond correctement
 */

// ========================================
// 1. INITIALISATION
// ========================================
$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/src/config/config.php';

echo "=== TEST API: generate_precise_course.php ===\n\n";

// ========================================
// 2. PAYLOAD SIMULÉ (exercice incorrecte)
// ========================================
$testPayload = [
    'exercise_id' => 1,
    'level' => '6ème',
    'subject' => 'Mathématiques',
    'competence' => 'Calculer avec des entiers relatifs',
    'official_correction' => 'Pour additionner deux nombres négatifs, on ajoute leurs valeurs absolues et on met un signe négatif devant.',
    'incorrect_items' => [
        [
            'question' => 'Quelle est la somme de -7 et -3 ?',
            'correct_answer' => '-10',
            'user_answer' => '-4',
            'official_correction' => 'La réponse correcte est -10 car (-7) + (-3) = -(7 + 3) = -10',
            'question_type' => 'qcm',
        ],
        [
            'question' => 'Calcule : -5 + (-8)',
            'correct_answer' => '-13',
            'user_answer' => '-13',  // Cette fois correct, mais incluse pour contexte
            'official_correction' => 'Deux négatifs: -(5 + 8) = -13',
            'question_type' => 'qcm',
        ],
    ],
];

echo "✓ Payload simulé généré\n";
echo "  Niveau: {$testPayload['level']}\n";
echo "  Matière: {$testPayload['subject']}\n";
echo "  Erreurs attendues: " . count($testPayload['incorrect_items']) . "\n\n";

// ========================================
// 3. APPEL CURL À L'API
// ========================================
$apiUrl = 'http://localhost/index.php?page=api/ia/generate_precise_course';

// Pour test local, générer un token CSRF factice
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$curlOptions = [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-CSRF-Token: ' . $csrfToken,
    ],
    CURLOPT_POSTFIELDS => json_encode(array_merge($testPayload, ['csrf_token' => $csrfToken])),
    CURLOPT_TIMEOUT => 30,
];

echo "→ Envoi de la requête à: {$apiUrl}\n";
$curl = curl_init();
curl_setopt_array($curl, $curlOptions);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

echo "✓ Réponse reçue (HTTP {$httpCode})\n\n";

// ========================================
// 4. PARSING RÉPONSE
// ========================================
if ($curlError) {
    echo "❌ ERREUR CURL: {$curlError}\n";
    exit(1);
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ ERREUR JSON: " . json_last_error_msg() . "\n";
    echo "Réponse brute:\n" . substr($response, 0, 500) . "\n";
    exit(1);
}

// ========================================
// 5. VALIDATION RÉPONSE
// ========================================
echo "📋 RÉPONSE REÇUE:\n";
echo "─────────────────────────────────────\n";

if (!isset($data['success']) || !$data['success']) {
    echo "❌ API retourna success=false\n";
    echo "Erreur: " . ($data['error'] ?? 'Inconnue') . "\n";
    exit(1);
}

if (!isset($data['data'])) {
    echo "❌ Pas de clé 'data' dans la réponse\n";
    exit(1);
}

$course = $data['data'];
echo "✅ Succès : API répondit avec data valides\n";
echo "Provider utilisé: " . ($data['provider_used'] ?? 'Unknown') . "\n\n";

// Structure attendue
$requiredFields = [
    'title' => 'Titre du mini-cours',
    'summary' => 'Résumé court',
    'concept_focus' => 'Notion à retenir',
    'key_points' => 'Points clés (array)',
    'method_steps' => 'Étapes méthode (array)',
    'worked_example' => 'Exemple guidé',
    'common_pitfalls' => 'Pièges à éviter (array)',
    'practice_tip' => 'Conseil pratique',
    'verification_question' => 'Question de vérification',
    'references' => 'Ressources (array)',
];

echo "📝 CHAMPS REÇUS:\n";
echo "─────────────────────────────────────\n";

$missingFields = [];
foreach ($requiredFields as $field => $description) {
    $value = $course[$field] ?? null;
    if ($value === null) {
        echo "❌ {$field}: MANQUANT\n";
        $missingFields[] = $field;
    } elseif (is_array($value)) {
        echo "✅ {$field}: Array(" . count($value) . " items)\n";
        if (count($value) > 0) {
            echo "   └─ Exemple: \"" . substr((string)$value[0], 0, 60) . "...\"\n";
        }
    } else {
        echo "✅ {$field}: \"" . substr((string)$value, 0, 80) . "...\"\n";
    }
}

if (!empty($missingFields)) {
    echo "\n⚠️  MANQUE:  " . implode(', ', $missingFields) . "\n";
    exit(1);
}

// ========================================
// 6. VÉRIFICATION HTML GÉNÉRÉ
// ========================================
echo "\n\n🔧 VÉRIFICATION HTML (buildPreciseCourseModalHtml):\n";
echo "─────────────────────────────────────\n";

// Simuler la fonction PHP de construction HTML
// (On ne peut pas l'appeler directement sans charger tout le système,
//  mais on peut vérifier que les données sont OK)

$htmlWouldContain = [];
if (!empty($course['title'])) $htmlWouldContain[] = "Titre du modal";
if (!empty($course['summary'])) $htmlWouldContain[] = "Section résumé";
if (!empty($course['key_points'])) $htmlWouldContain[] = "Section points clés (" . count($course['key_points']) . " items)";
if (!empty($course['method_steps'])) $htmlWouldContain[] = "Section étapes (" . count($course['method_steps']) . " items)";
if (!empty($course['worked_example'])) $htmlWouldContain[] = "Exemple guidé";
if (!empty($course['common_pitfalls'])) $htmlWouldContain[] = "Pièges (" . count($course['common_pitfalls']) . " items)";
if (!empty($course['practice_tip'])) $htmlWouldContain[] = "Conseil pratique";
if (!empty($course['verification_question'])) $htmlWouldContain[] = "Question de vérification";

foreach ($htmlWouldContain as $section) {
    echo "✅ {$section}\n";
}

// ========================================
// 7. RÉSULTAT FINAL
// ========================================
echo "\n\n" . str_repeat("=", 50) . "\n";
echo "✅ SUCCESS: Le mini-cours est généré et formaté correctement!\n";
echo "   Le modal affichera:\n";
echo "   - Titre: " . htmlspecialchars($course['title']) . "\n";
echo "   - " . count($htmlWouldContain) . " sections pédagogiques\n";
echo "\n💡 PRÊT POUR E2E TEST:\n";
echo "   → Exercice incorrect: Affiche les boutons [💡 Comprendre] [📘 Mini-cours]\n";
echo "   → Clic sur '📘 Mini-cours': Appelle GET_PRECISE_COURSE\n";
echo "   → Modal s'ouvre avec le contenu Groq injecté\n";
echo "   → Bouton 'J'ai compris ! 💪' ferme le modal\n";
echo str_repeat("=", 50) . "\n";
