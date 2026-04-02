# Crée le fichier
echo '<?php $d = json_decode(file_get_contents("dev/tools/quiz/college/diagnostic_6eme_mathematiques.json")); echo "✅ Clés: " . implode(", ", array_keys($d)) . "\n"; ?>' > test_json.php

# Teste
php test_json.php
rm test_json.php
