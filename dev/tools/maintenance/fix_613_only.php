<?php
require_once __DIR__ . '/../db/connection.php';
$stmt = $pdo->prepare('UPDATE exercises SET Answer = ? WHERE Id = ?');
$ans = '1) verbe: arrivent, sujet: Les élèves 2) verbe: corrige, sujet: Le professeur 3) verbe: jouent, sujet: Marie et Jean 4) verbe: dort, sujet: Le chat 5) verbe: téléphonez, sujet: Vous';
$stmt->execute([$ans, 613]);
echo "Fixed #613\n";
