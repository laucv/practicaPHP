<?php
/**
 * PHP version 7.2
 * src\Error.php
 */

namespace TDW\GCuest;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

/**
 * Class Error
 */
class Error
{
    // Error messages
    public const MESSAGES = [
        StatusCode::HTTP_BAD_REQUEST            // 400
            => '`Bad Request` User name or e-mail already exists',
        StatusCode::HTTP_UNAUTHORIZED           // 401
            => 'UNAUTHORIZED: invalid `Authorization` header',
        StatusCode::HTTP_FORBIDDEN              // 403
            => '`Forbidden` You don\'t have permission to access',
        StatusCode::HTTP_NOT_FOUND              // 404
            => 'Resource not found',
        StatusCode::HTTP_CONFLICT               // 409
            => '`Conflict`: the creator does not exist or is not a teacher.',
        StatusCode::HTTP_UNPROCESSABLE_ENTITY   // 422
            => '`Unprocessable entity` Username, e-mail or password is left out',
        StatusCode::HTTP_METHOD_NOT_ALLOWED     // 405
            => 'Method not allowed',
        StatusCode::HTTP_NOT_IMPLEMENTED        // 501
            => 'Not Implemented',
    ];

    /**
     * @param ContainerInterface $app
     * @param Request $request
     * @param Response $response
     * @param int $statusCode
     *
     * @return Response
     */
    public static function error(
        ContainerInterface $app,
        Request $request,
        Response $response,
        int $statusCode
    ): Response {
        $userId = $app->get('jwt')->user_id ?? 0;
        /** @noinspection PhpUndefinedFieldInspection */
        $app->get('logger')->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [ 'uid' => $userId, 'status' => $statusCode ]
        );

        return $response
            ->withJson(
                [
                    'code' => $statusCode,
                    'message' => self::MESSAGES[$statusCode]
                ],
                $statusCode
            );
    }
}
