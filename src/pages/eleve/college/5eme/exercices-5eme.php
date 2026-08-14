<?php

$page_css = 'college/5eme/exercices-5eme.css';
$page_class = 'page-exercices-5eme';
$page_theme_level = '5eme';

$srcRoot = dirname(__DIR__, 4);

require_once $srcRoot . '/config/student_levels.php';
require_once $srcRoot . '/includes/exercises_level_template.php';

$levelConfig = get_student_level_config('5eme');
if ($levelConfig === null) {
    http_response_code(500);
    echo '<main class="main-content"><p>Configuration du niveau introuvable.</p></main>';
    return;
}

render_exercises_level_template($levelConfig);
