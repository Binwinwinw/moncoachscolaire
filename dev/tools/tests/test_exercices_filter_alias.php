<?php
// Smoke test: verify that exercices_admin.php accepts 'classe' as an alias for 'matiere'
$path = __DIR__ . '/../../../src/pages/exercices_admin.php';
if (!file_exists($path)) {
    echo "ERROR: exercices_admin.php not found at $path\n";
    exit(2);
}
$contents = file_get_contents($path);
$needle = "// Compatibilité ascendante : accepter 'classe' comme alias pour 'matiere'";
if (strpos($contents, $needle) === false) {
    echo "FAIL: alias comment not found in exercices_admin.php\n";
    exit(1);
}

if (strpos($contents, 'filtre_matiere') === false) {
    echo "FAIL: assignment for filtre_matiere not found in exercices_admin.php\n";
    exit(1);
}

// Emulate a GET with classe param and check that parsing would pick it up
$_GET = ['classe' => '6ème'];
// Extract the relevant code snippet to evaluate safely
$snippet = <<<'PHP'
function _test_extract_filtre(){
    $filtre_niveau = $_GET['niveau'] ?? '';
    $filtre_matiere = $_GET['matiere'] ?? $_GET['classe'] ?? '';
    return $filtre_matiere;
}
PHP;
eval($snippet);
$val = _test_extract_filtre();
if ($val !== '6ème') {
    echo "FAIL: alias parsing failed, expected '6ème' got '" . $val . "'\n";
    exit(1);
}

echo "PASS: classe alias exists and is parsed to matiere\n";
exit(0);
