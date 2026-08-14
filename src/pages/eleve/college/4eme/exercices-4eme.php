<?php

// Set page-specific CSS and class names for theme-level styling
$page_css = 'college/4eme/exercices-4eme.css';
$page_class = 'page-exercices-4eme';
$page_theme_level = '4eme';

// Get configuration data for the current student level (4ème)
$srcRoot = dirname(__DIR__, 4);
require_once $srcRoot . '/config/student_levels.php';
require_once $srcRoot . '/includes/exercises_level_template.php';

$levelConfig = get_student_level_config('4eme');
if ($levelConfig === null) {
    http_response_code(500);
    echo '<main class="main-content"><p>Configuration du niveau introuvable.</p></main>';
    return;
}

// Render the exercise level template using configuration data
render_exercises_level_template($levelConfig);
