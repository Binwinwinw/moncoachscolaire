<?php
ob_start();
require_once 'd:/Hostinger\public_html/moncoachscolaire/src/config/config.php';
$output = ob_get_clean();
echo "--- OUTPUT BEFORE CONFIG ---\n";
var_dump($output);
echo "--- END OUTPUT ---\n";
if (strlen($output) > 0) {
    echo "Bytes: ";
    for($i=0; $i<strlen($output); $i++) echo ord($output[$i]) . " ";
    echo "\n";
}
