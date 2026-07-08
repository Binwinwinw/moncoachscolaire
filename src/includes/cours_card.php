<?php

/**
 * Carte HTML d'un cours pour le hub ?page=cours
 */

if (!function_exists('getCourseById') && is_file(__DIR__ . '/course_markdown_loader.php')) {
    require_once __DIR__ . '/course_markdown_loader.php';
}

if (!function_exists('renderCourseCard')) {
    /**
     * @param array<string, mixed> $course
     * @param array<string, mixed> $options
     */
    function renderCourseCard(array $course, array $options = []): void
    {
        $defaultOptions = [
            'showDetails' => true,
            'cardClass' => '',
        ];
        $options = array_merge($defaultOptions, $options);

        $id = (int) ($course['Id'] ?? $course['id'] ?? 0);
        $title = htmlspecialchars((string) ($course['Title'] ?? $course['title'] ?? 'Cours'), ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars((string) ($course['Subject'] ?? $course['subject'] ?? ''), ENT_QUOTES, 'UTF-8');
        $level = htmlspecialchars((string) ($course['Level'] ?? $course['level'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = (string) ($course['Description'] ?? $course['description'] ?? '');

        if ($description === '' && !empty($course['markdown']['introduction'])) {
            $description = (string) $course['markdown']['introduction'];
        }

        $descriptionPlain = trim(strip_tags($description));
        if (mb_strlen($descriptionPlain) > 280) {
            $descriptionPlain = mb_substr($descriptionPlain, 0, 277) . '…';
        }

        $detailUrl = '#';
        if ($id > 0 && function_exists('site_url')) {
            $levelSlug = function_exists('normalize_level_for_url')
                ? normalize_level_for_url($course['Level'] ?? $course['level'] ?? '')
                : strtolower((string) ($course['Level'] ?? ''));
            $detailUrl = site_url('pages/cours-detail', [
                'id' => $id,
                'matiere' => $course['Subject'] ?? '',
                'niveau' => $levelSlug,
            ]);
        }

        $cardClass = trim('cours-card cours-card-hub bg-white rounded-2xl shadow-lg p-6 max-w-2xl w-full ' . ($options['cardClass'] ?? ''));

        echo '<article class="' . htmlspecialchars($cardClass, ENT_QUOTES, 'UTF-8') . '" data-course-id="' . $id . '">';
        echo '<header class="cours-card-header mb-4 flex flex-wrap items-start justify-between gap-2">';
        echo '<div>';
        echo '<h3 class="text-xl font-bold text-slate-800">' . $title . '</h3>';
        echo '<p class="text-sm text-slate-500 mt-1">' . $subject . ' · ' . $level . '</p>';
        echo '</div>';
        echo '<span class="inline-flex items-center px-3 py-1 rounded-full bg-theme-soft text-theme text-sm font-semibold">📚 Cours</span>';
        echo '</header>';

        if ($options['showDetails'] && $descriptionPlain !== '') {
            echo '<p class="cours-card-excerpt text-slate-600 leading-relaxed mb-4">' . htmlspecialchars($descriptionPlain, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        if (!empty($course['markdown']['sections']) && is_array($course['markdown']['sections'])) {
            echo '<ul class="cours-card-sections text-sm text-slate-600 mb-4 list-disc list-inside space-y-1">';
            foreach (array_slice($course['markdown']['sections'], 0, 3) as $section) {
                $sectionTitle = htmlspecialchars((string) ($section['title'] ?? ''), ENT_QUOTES, 'UTF-8');
                if ($sectionTitle !== '') {
                    echo '<li>' . $sectionTitle . '</li>';
                }
            }
            echo '</ul>';
        }

        echo '<footer class="cours-card-footer flex flex-wrap gap-3">';
        echo '<a href="' . htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') . '" class="btn-theme-primary inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg">📖 Voir le cours complet</a>';
        echo '</footer>';
        echo '</article>';
    }
}
