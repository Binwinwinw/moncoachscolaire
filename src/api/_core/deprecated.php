<?php

/**
 * API Core Deprecated Helper
 */

function api_deprecated(?string $replacement = null, int $sunsetDays = 90): void
{
    header('Deprecation: true');
    $sunset = gmdate('D, d M Y H:i:s \G\M\T', time() + ($sunsetDays * 86400));
    header('Sunset: ' . $sunset);
    if ($replacement) {
        header('Link: <' . $replacement . '>; rel="successor-version"');
    }

    if (function_exists('api_log')) {
        api_log('warning', 'Deprecated endpoint accessed', [
            'path' => $_SERVER['REQUEST_URI'] ?? '',
            'replacement' => $replacement,
        ]);
    }
}
