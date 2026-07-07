<?php
/**
 * Temporary script to run composer install from the browser.
 * WARNING: Delete this file from your live server immediately after running it!
 */

// Secure token to prevent unauthorized execution.
// Visit: http://your-domain.com/run_composer.php?token=sisal_composer_run_789
define('SECURE_TOKEN', 'sisal_composer_run_789');

if (!isset($_GET['token']) || $_GET['token'] !== SECURE_TOKEN) {
    header('HTTP/1.1 403 Forbidden');
    die('Unauthorized. Please provide the correct token parameter.');
}

header('Content-Type: text/plain');
ini_set('max_execution_time', 300); // 5 minutes limit

echo "=== Laravel Browser Composer Installer ===\n";
echo "Starting installation...\n\n";

// Change directory to the Laravel project root directory (parent of public/)
$projectRoot = dirname(__DIR__);
if (!chdir($projectRoot)) {
    die("Error: Could not change directory to project root: $projectRoot\n");
}
echo "Working Directory: " . getcwd() . "\n";

// Set HOME environment variable to a writable directory (required by composer)
putenv('HOME=/tmp');

// Command to execute
$command = 'composer install --no-dev --ignore-platform-req=ext-gd 2>&1';
echo "Executing: $command\n\n";

$output = [];
$return_var = 0;

if (function_exists('exec')) {
    exec($command, $output, $return_var);
    echo implode("\n", $output);
    echo "\n\nExit Code: " . $return_var . "\n";
} else {
    echo "Error: The PHP 'exec' function is disabled on your server php.ini (disable_functions).\n";
    echo "You may need to ask your hosting provider to temporarily enable 'exec' or use SSH.\n";
}

echo "\n==========================================\n";
echo "REMINDER: DELETE THIS FILE (public/run_composer.php) IMMEDIATELY!";
