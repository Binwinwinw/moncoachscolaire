<?php
$lines = file('index.php');
foreach ($lines as $i => $l) {
    $n = $i + 1;
    for ($j = 0; $j < strlen($l); $j++) {
        $c = $l[$j];
        if ($c === '{' || $c === '}') {
            echo $n . ": " . $c . "\n";
        }
    }
}
?>