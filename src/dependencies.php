<?php
/**
 * PHP version 7.2
 * src\dependencies.php
 * DIC configuration
 */

use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use TDW\GCuest\Error;

/** @var ContainerInterface $container */
$container = $app->getContainer();

// monolog
$container['logger'] = function (ContainerInterface $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);

    $rotating = new Monolog\Handler\RotatingFileHandler(
        $settings['path'],
        $settings['maxfiles'],
        $settings['level']
    );
    $logger->pushHandler($rotating);

    return $logger;
};

// notFoundHandler
$container['notFoundHandler'] = function (ContainerInterface $c) {

    return function (/** @noinspection PhpUnusedParameterInspection */
        Request $request,
        Response $response
    ) use ($c) {

        return $c->get('response')
            ->withJson(
                [
                    'code'      => StatusCode::HTTP_NOT_FOUND,  // 404
                    'message'   => Error::MESSAGES[StatusCode::HTTP_NOT_FOUND]
                ],
                StatusCode::HTTP_NOT_FOUND
            );
    };
};

$container['jwt'] = function (ContainerInterface $container) {
    return new stdClass;
};
