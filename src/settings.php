<?php
/**
 * PHP version 7.2
 * src\settings.php
 */

return [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Monolog settings
        'logger' => [
            'name'      => 'TDWApi',
            'path'      => __DIR__ . '/../logs/logs' . (
                isset($_ENV['docker'])
                    ? '_docker'
                    : ''
                ),
            'maxfiles'  => 2,    // The maximal amount of files to keep (0 means unlimited)
            'level'     => \Monolog\Logger::INFO,
        ],
    ],
];
