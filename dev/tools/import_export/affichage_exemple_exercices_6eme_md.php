<?php
// Affichage interactif des exercices extraits des .md pour la 6ème
$file = __DIR__ . '/tools/exemple_exercices_6eme.json';
$data = json_decode(file_get_contents($file), true);
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Exemples Exercices 6ème (Markdown)</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 30px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ccc; padding: 30px; }
        h1 { text-align: center; color: #2a4d7a; }
        .exo { border-bottom: 1px solid #eee; margin-bottom: 30px; padding-bottom: 20px; }
        .exo:last-child { border-bottom: none; }
        .title { font-size: 1.2em; color: #1a2a4d; margin-bottom: 8px; }
        .meta { color: #888; font-size: 0.95em; margin-bottom: 8px; }
        .content, .instruction, .tips { margin-bottom: 10px; }
        .answer { background: #f0f8ff; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .tips { color: #2a7a2a; font-style: italic; }
        .id { color: #aaa; font-size: 0.9em; }
    </style>
</head>
<body>
<div class="container">
    <h1>Exemples Exercices 6ème (Markdown)</h1>
    <p><?= count($data) ?> exercices extraits des fichiers .md</p>
    <?php foreach ($data as $exo): ?>
        <div class="exo">
            <div class="title"><?= htmlspecialchars($exo['Title']) ?></div>
            <div class="meta">
                Niveau : <?= htmlspecialchars($exo['Level']) ?> | Matière : <?= htmlspecialchars($exo['Subject']) ?>
                <?php if (!empty($exo['Domain'])): ?> | Domaine : <?= htmlspecialchars($exo['Domain']) ?><?php endif; ?>
                <?php if (!empty($exo['Competence'])): ?> | Compétence : <?= htmlspecialchars($exo['Competence']) ?><?php endif; ?>
                <?php if (!empty($exo['Difficulty'])): ?> | Difficulté : <?= htmlspecialchars($exo['Difficulty']) ?><?php endif; ?>
                <?php if (!empty($exo['Identifier'])): ?> <span class="id">| ID : <?= htmlspecialchars($exo['Identifier']) ?></span><?php endif; ?>
            </div>
            <div class="content"><strong>Énoncé :</strong><br><?= nl2br(htmlspecialchars($exo['Content'])) ?></div>
            <?php if (!empty($exo['Instruction'])): ?>
                <div class="instruction"><strong>Consigne :</strong><br><?= nl2br(htmlspecialchars($exo['Instruction'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($exo['Tips'])): ?>
                <div class="tips">Astuce : <?= htmlspecialchars($exo['Tips']) ?></div>
            <?php endif; ?>
            <?php if (!empty($exo['Answer'])): ?>
                <details><summary>Correction / Réponse</summary>
                    <div class="answer"> <?= nl2br(htmlspecialchars($exo['Answer'])) ?> </div>
                </details>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
