<?php
// Export des exercices de 6ème anglais au format enrichi
require_once __DIR__ . '/../src/database/connection.php';
require_once __DIR__ . '/../src/includes/exercice_loader.php';

$level = '6eme';
$subject = 'Anglais';
$exos = getExercisesByLevel($level, $subject, 50);

$result = [];
foreach ($exos as $exo) {
    // Déduction du type de réponse
    $type = 'texte';
    if (isset($exo['Content'])) {
        $c = strtolower($exo['Content']);
        if (strpos($c, 'coche') !== false || strpos($c, 'choisis') !== false || strpos($c, 'sélectionne') !== false) {
            $type = 'qcm';
        } elseif (strpos($c, 'calcule') !== false || strpos($c, 'donne la valeur') !== false) {
            $type = 'calcul';
        }
    }
    $result[] = [
        'Subject' => $exo['Subject'],
        'Level' => $exo['Level'],
        'Title' => $exo['Title'],
        'Content' => $exo['Content'],
        'Instruction' => $exo['Content'],
        'Answer' => $exo['Answer'],
        'AnswerType' => $type,
        'Choices' => null,
        'Tips' => 'Relis bien la consigne et vérifie ta réponse avant de valider.',
        'Domain' => $exo['Domain'] ?? '',
        'Competence' => $exo['Competence'] ?? '',
        'Difficulty' => $exo['Difficulty'] ?? '',
        'Identifier' => $exo['Identifier'] ?? '',
    ];
}
file_put_contents(__DIR__ . '/exemple_exercice_6eme_anglais.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Exercices normalisés exportés dans exemple_exercice_6eme_anglais.json\n";
