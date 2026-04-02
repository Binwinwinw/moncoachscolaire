<?php
/**
 * dev/tools/test_send_contact.php
 * Test utility to exercise the send_contact endpoint from CLI.
 * Moved from dev/test_send_contact.php to dev/tools/ for better organization.
 */

ini_set('display_errors',1);
error_reporting(E_ALL);
// Ensure working directory is project root when running the script from dev/tools
chdir(__DIR__ . '/..');

// Simulate POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['nom'] = 'TestUserCLI';
$_POST['email'] = 'test@example.com';
$_POST['message'] = 'Test message from CLI file.';
// emulate AJAX
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

// Useful logs
file_put_contents(__DIR__ . '/../send_contact_exec.log', date('c') . ' - before include\n', FILE_APPEND);
include 'src/api/users/send_contact.php';
file_put_contents(__DIR__ . '/../send_contact_exec.log', date('c') . ' - after include\n', FILE_APPEND);

echo "\n--- SCRIPT END ---\n";
