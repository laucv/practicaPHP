<?php
/**
 * PHP version 7.2
 * public/index.php
 */

if (PHP_SAPI === 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

$project_dir = dirname(__DIR__);
require $project_dir . '/vendor/autoload.php';

// Load the environment/configuration variables
\TDW\GCuest\Utils::loadEnv($project_dir);

// Instantiate the app
$settings = require $project_dir . '/src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require $project_dir . '/src/dependencies.php';

// Register middleware
require $project_dir . '/src/middleware.php';

// Register routes
require $project_dir . '/src/routes.php';

// Run app
try {
    $app->run();
} catch (\Exception $exception) {
    sprintf('EXCEPTION (%d): %s', $exception->getCode(), $exception->getMessage());
}
