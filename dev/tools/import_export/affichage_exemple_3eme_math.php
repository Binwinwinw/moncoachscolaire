<?php
// Affichage web interactif d'exercices enrichis (exemple 3ème maths)
// Placez ce fichier dans dev/ et ouvrez-le dans le navigateur

$jsonFile = __DIR__ . '/exemple_exercice_3eme_math.json';
$data = json_decode(file_get_contents($jsonFile), true);
if (!$data) {
    die('<p>Erreur de lecture du fichier JSON.</p>');
}
function renderChoices($choices, $name) {
    $html = '';
    foreach ($choices as $i => $choice) {
        $id = $name . '_' . $i;
        $html .= "<div><input type='radio' name='$name' id='$id' value='" . htmlspecialchars($choice) . "'> <label for='$id'>" . htmlspecialchars($choice) . "</label></div>\n";
    }
    return $html;
}
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Exercices 3ème Maths - Affichage interactif</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7fa; margin: 0; padding: 0; }
        .exo-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; margin: 2em auto; max-width: 600px; padding: 2em; }
        .exo-title { font-size: 1.3em; font-weight: bold; margin-bottom: 0.5em; }
        .exo-domain { color: #888; font-size: 0.95em; margin-bottom: 0.5em; }
        .exo-content { margin-bottom: 1em; }
        .exo-instruction { font-style: italic; color: #555; margin-bottom: 1em; }
        .exo-tips { background: #eaf7ea; border-left: 4px solid #4caf50; padding: 0.7em 1em; margin: 1em 0; border-radius: 4px; color: #2e7d32; }
        .exo-answer { background: #fffbe6; border-left: 4px solid #ffc107; padding: 0.7em 1em; margin: 1em 0; border-radius: 4px; color: #8d6e00; display: none; }
        .show-answer { margin-top: 1em; }
        .exo-meta { font-size: 0.9em; color: #888; margin-bottom: 0.5em; }
        .btn { background: #1976d2; color: #fff; border: none; border-radius: 4px; padding: 0.5em 1.2em; cursor: pointer; font-size: 1em; }
        .btn:hover { background: #125ea2; }
        .score-msg { font-weight: bold; color: #388e3c; margin-top: 1em; }
        .wrong-msg { font-weight: bold; color: #d32f2f; margin-top: 1em; }
    </style>
    <script>
    function toggleAnswer(id) {
        var el = document.getElementById('answer_' + id);
        if (el) el.style.display = (el.style.display === 'block') ? 'none' : 'block';
    }
    function validateExo(idx, type, answer) {
        let userVal = '';
        let correct = false;
        if (type === 'qcm') {
            let radios = document.getElementsByName('qcm_' + idx);
            for (let r of radios) { if (r.checked) userVal = r.value; }
            correct = (userVal.trim() === answer.trim());
        } else if (type === 'texte') {
            userVal = document.getElementById('txt_' + idx).value;
            correct = userVal.trim().length > 0 && answer && userVal.trim().toLowerCase() === answer.trim().toLowerCase();
        } else if (type === 'calcul') {
            userVal = document.getElementById('calc_' + idx).value;
            // On tolère les espaces et la casse
            correct = userVal.replace(/\s/g,'').toLowerCase() === answer.replace(/\s/g,'').toLowerCase();
        }
        let msg = document.getElementById('score_' + idx);
        if (correct) {
            msg.innerHTML = '✅ Bonne réponse ! +1 point';
            msg.className = 'score-msg';
        } else {
            msg.innerHTML = '❌ Mauvaise réponse.';
            msg.className = 'wrong-msg';
        }
    }
    </script>
</head>
<body>
<?php foreach ($data as $idx => $exo): ?>
    <div class="exo-card">
        <div class="exo-title"><?= htmlspecialchars($exo['Title']) ?></div>
        <div class="exo-domain">Domaine : <?= htmlspecialchars($exo['Domain'] ?? '') ?> | Compétence : <?= htmlspecialchars($exo['Competence'] ?? '') ?> | Difficulté : <?= htmlspecialchars($exo['Difficulty'] ?? '') ?></div>
        <div class="exo-meta">Niveau : <?= htmlspecialchars($exo['Level']) ?> | Matière : <?= htmlspecialchars($exo['Subject']) ?> | ID : <?= htmlspecialchars($exo['Identifier']) ?></div>
        <div class="exo-content"><?= nl2br(htmlspecialchars($exo['Content'])) ?></div>
        <div class="exo-instruction">Consigne : <?= htmlspecialchars($exo['Instruction'] ?? '') ?></div>
        <?php if ($exo['AnswerType'] === 'qcm' && !empty($exo['Choices'])): ?>
            <form onsubmit="return false;"><?= renderChoices($exo['Choices'], 'qcm_' . $idx) ?></form>
            <button class="btn" onclick="validateExo(<?= $idx ?>, 'qcm', <?= json_encode($exo['Answer']) ?>)">Valider</button>
        <?php elseif ($exo['AnswerType'] === 'texte'): ?>
            <textarea id="txt_<?= $idx ?>" rows="3" style="width:100%" placeholder="Écris ta réponse ici..."></textarea>
            <button class="btn" onclick="validateExo(<?= $idx ?>, 'texte', <?= json_encode($exo['Answer']) ?>)">Valider</button>
        <?php elseif ($exo['AnswerType'] === 'calcul'): ?>
            <input id="calc_<?= $idx ?>" type="text" style="width:200px" placeholder="Réponse (ex: 10 cm)">
            <button class="btn" onclick="validateExo(<?= $idx ?>, 'calcul', <?= json_encode($exo['Answer']) ?>)">Valider</button>
        <?php endif; ?>
        <div id="score_<?= $idx ?>"></div>
        <?php if (!empty($exo['Tips'])): ?>
            <div class="exo-tips"><b>Astuce :</b> <?= htmlspecialchars($exo['Tips']) ?></div>
        <?php endif; ?>
        <button class="btn show-answer" onclick="toggleAnswer(<?= $idx ?>)">Afficher la correction</button>
        <div class="exo-answer" id="answer_<?= $idx ?>"><b>Correction :</b> <?= nl2br(htmlspecialchars($exo['Answer'])) ?></div>
    </div>
<?php endforeach; ?>
</body>
</html>
