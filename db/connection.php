<?php
// Legacy compatibility wrapper
// Prior versions expected db/connection.php at project root. New location: src/database/connection.php
// This wrapper includes the new file when present.

if (is_file(__DIR__ . '/../src/database/connection.php')) {
    require_once __DIR__ . '/../src/database/connection.php';
} else {
    // Fail gracefully with a clear message
    trigger_error('Legacy DB connection wrapper: src/database/connection.php not found', E_USER_WARNING);
    // Define placeholders to avoid fatal errors in front-end when DB absent
    $pdo = null;
    $dbUnavailable = true;
}
