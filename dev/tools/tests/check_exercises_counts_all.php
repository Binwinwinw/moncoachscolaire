<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

$levels = ['6ème'];
$subjects = ['Mathématiques','Français','Histoire-Géographie','SVT','Anglais'];
foreach ($levels as $lvl){
    foreach ($subjects as $sub){
        $c = countExercisesByLevel($lvl, $sub);
        echo "$lvl / $sub : $c\n";
    }
}
