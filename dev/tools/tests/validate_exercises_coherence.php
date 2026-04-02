<?php
/**
 * Validation de cohérence des exercices stockés en base.
 * Heuristiques:
 *  - Réponse non vide
 *  - Si la question contient des chiffres/quantités, la réponse doit contenir au moins un chiffre
 *  - Similarité mots-clés question/réponse (Jaccard) >= seuil minimal
 *  - Si question contient "combien", "quelle quantité", "quel volume", la réponse doit contenir un nombre
 *  - Si question contient "vrai"/"faux" ou "choisis", tolère absence de nombre
 * Affiche les exercices suspects avec un score et les règles déclenchées.
 */

require_once __DIR__ . '/../db/connection.php';

if (!$pdo) {
    fwrite(STDERR, "DB indisponible\n");
    exit(2);
}

$stopwords = [
    'le','la','les','de','des','du','un','une','et','en','dans','pour','par','que','qui','quoi','quel','quelle','quels','quelles','au','aux','avec','sur','sous','ce','cet','cette','ces','son','sa','ses','leur','leurs','nos','vos','mes','tes','ma','ta','mon','ton','est','etre','être','a','à','au','aux','d','l','ne','pas','plus','moins','ou','où','mais','donc','or','ni','car','se','s','y','deux','trois','quatre','cinq','six','sept','huit','neuf','dix'
];

function tokenize($text, $stopwords) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
    $parts = preg_split('/\s+/', $text);
    $tokens = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '') continue;
        if (in_array($p, $stopwords, true)) continue;
        $tokens[] = $p;
    }
    return array_values(array_unique($tokens));
}

function jaccard($a, $b) {
    if (empty($a) || empty($b)) return 0.0;
    $ia = array_intersect($a, $b);
    $ua = array_unique(array_merge($a, $b));
    return count($ia) / max(1, count($ua));
}

function has_number($text) {
    return preg_match('/\d/', $text) === 1;
}

function requires_number($text) {
    $t = mb_strtolower($text, 'UTF-8');
    // Mots-clés de quantité
    $keywords = ['combien','quelle quantité','quel volume','quelle longueur','quelle aire','quel périmètre','quelle surface','quelle distance','quelle masse','quelle valeur','moyenne','probabilité','prix','coût','mesure','convertis','conversion','aire','périmètre','volume'];
    foreach ($keywords as $k) {
        if (str_contains($t, $k)) return true;
    }
    // Nombres accompagnés d'unités ou de fractions
    if (preg_match('/\d+\s*(cm|m|km|kg|g|mm|m²|m2|cm²|cm2|mm²|mm2|°|%|€|eur|euro|euros|ml|mL|l|L|h|min|s)/iu', $text)) return true;
    if (preg_match('/\d+\/\d+/', $text)) return true;
    if (preg_match('/\d+[\.,]\d+/', $text)) return true;
    // Ignore pure numérotation de liste (1) 2.) sans mots-clés ni unités
    return false;
}

function is_quantity_question($text) {
    $t = mb_strtolower($text, 'UTF-8');
    $keywords = ['combien','quelle quantité','quel volume','quelle longueur','quelle aire','quel périmètre','quelle surface','quelle distance','quelle masse','quelle valeur'];
    foreach ($keywords as $k) {
        if (str_contains($t, $k)) return true;
    }
    return false;
}

function is_true_false_question($text) {
    $t = mb_strtolower($text, 'UTF-8');
    return str_contains($t, 'vrai') || str_contains($t, 'faux') || str_contains($t, 'choisis') || str_contains($t, 'coche');
}

$sql = "SELECT Id, Level, Subject, Title, Content, Answer FROM Exercises ORDER BY Level, Subject, Id";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();

$suspects = [];
foreach ($rows as $row) {
    $id = $row['Id'];
    $title = $row['Title'] ?? '';
    $q = $row['Content'] ?? '';
    $a = $row['Answer'] ?? '';

    $issues = [];

    if (trim($a) === '') {
        $issues[] = 'Réponse vide';
    }

    $tokQ = tokenize($q . ' ' . $title, $stopwords);
    $tokA = tokenize($a, $stopwords);
    $sim = jaccard($tokQ, $tokA);
    if ($sim < 0.03) {
        $issues[] = 'Faible similarité question/réponse (' . number_format($sim, 3) . ')';
    }

    $answerHasNumber = has_number($a);
    $numericSubjects = ['Mathématiques','Physique-Chimie','SVT','Histoire-Géographie','Anglais'];
    if (in_array($row['Subject'], $numericSubjects, true)) {
        $questionNeedsNumber = requires_number($q);
        if ($questionNeedsNumber && !$answerHasNumber && !is_true_false_question($q)) {
            $issues[] = 'Question avec nombres mais réponse sans nombre';
        }
        if (is_quantity_question($q) && !$answerHasNumber) {
            $issues[] = 'Question de quantité sans nombre en réponse';
        }
    }

    if (!empty($issues)) {
        $suspects[] = [
            'Id' => $id,
            'Level' => $row['Level'],
            'Subject' => $row['Subject'],
            'Title' => $title,
            'Issues' => $issues,
        ];
    }
}

if (empty($suspects)) {
    echo "Aucune incohérence détectée selon ces heuristiques.\n";
    exit(0);
}

echo "Suspects: " . count($suspects) . "\n";
foreach ($suspects as $s) {
    echo "[{$s['Level']}][{$s['Subject']}] #{$s['Id']} {$s['Title']}\n";
    foreach ($s['Issues'] as $iss) {
        echo "  - {$iss}\n";
    }
}
