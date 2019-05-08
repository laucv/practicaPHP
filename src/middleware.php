<?php
/**
 * PHP version 7.2
 * src\middleware.php
 * Application middleware
 */

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Slim\Middleware\JwtAuthentication;
use TDW\GCuest\Error;

/**
 * Cabeceras CORS
 */
$app->add(
    function (Request $request, Response $response, callable $next) {
        /** @var Slim\Route $route */
        $route = $request->getAttribute('route');

        $methods = [];

        if (null !== $route) {
            $pattern = $route->getPattern();

            foreach ($this->router->getRoutes() as $route) {
                if ($pattern === $route->getPattern()) {
                    $methods = array_merge_recursive($methods, $route->getMethods());
                }
            }
            //Methods holds all of the HTTP Verbs that a particular route handles.
        } else {
            $methods[] = $request->getMethod();
        }

        /** @var Response $response */
        $response = $next($request, $response);

        return $response
            ->withAddedHeader(
                'Access-Control-Allow-Origin',
                '*'
            )
            ->withAddedHeader(
                'Access-Control-Allow-Headers',
                'X-Requested-With, Content-Type, Accept, Origin, api_key, Authorization'
            )
            ->withAddedHeader(
                'Access-Control-Allow-Methods',
                implode(', ', $methods)
            );
    }
);

/**
 * JWT - JSON Web Tokens Authentication
 */
$app->add(
    new JwtAuthentication(
        [
            'secure' => false,   // allow insecure usage (HTTP)
            // 'relaxed'       => ['localhost', '127.0.0.1', '0.0.0.0'],
            'secret' => getenv('JWT_SECRET'),
            'algorithm' => [ 'HS512', 'HS384' ],
            'environment' => 'HTTP_X_TOKEN',
            'header' => 'Authorization',
            'regexp' => '/Bearer\s+(.*)$/i',
            'rules'  => [
                new JwtAuthentication\RequestMethodRule(
                    [
                        'passthrough' => [ 'OPTIONS' ]        // 'GET'
                    ]
                ),
                new JwtAuthentication\RequestPathRule(
                    [
                        'passthrough' => [
                            getenv('RUTA_LOGIN'),
                            '/api/v1/users/username'
                        ]
                    ]
                )
            ],
            'logger' => $app->getContainer()->get('logger'),
            'path'   => getenv('RUTA_API'),
            'error'  => function (Request $request, Response $response) use ($app): Response {

                return Error::error($app->getContainer(), $request, $response, StatusCode::HTTP_UNAUTHORIZED);
            },
            'callback' => function (Request $request, Response $response, array $args) use ($container): Response {
                /** @noinspection OnlyWritesOnParameterInspection */
                $container['jwt'] = $args['decoded'];

                return $response;
            }
        ]
    )
);
