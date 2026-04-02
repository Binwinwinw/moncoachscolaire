<?php
$lines = file('index.php');
foreach ($lines as $i => $l) {
    printf("%4d: %s", $i+1, $l);
}
?>