<?php

$page_css = 'college/3eme/exercices-3eme.css';
$page_class = 'page-exercices-3eme';
$page_theme_level = '3eme';

$srcRoot = dirname(__DIR__, 4);

require_once $srcRoot . '/config/student_levels.php';
require_once $srcRoot . '/includes/exercises_level_template.php';

$levelConfig = get_student_level_config('3eme');
if ($levelConfig === null) {
    http_response_code(500);
    echo '<main class="main-content"><p>Configuration du niveau introuvable.</p></main>';
    return;
}

render_exercises_level_template($levelConfig);
