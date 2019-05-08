<?php
/**
 * PHP version 7.2
 * apiTDWUsers - src/Controller/LoginController.php
 */

namespace TDW\GCuest\Controller;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use TDW\GCuest\Entity\Usuario;
use TDW\GCuest\Error;
use TDW\GCuest\Utils;

/**
 * Class CuestionController
 */
class LoginController
{
    /** @var ContainerInterface $container */
    protected $container;

    /** @var \Monolog\Logger $logger */
    protected $logger;

    // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $this->container->get('logger');
    }

    /**
     * POST /login
     *
     * @OA\Post(
     *     path        = "/login",
     *     tags        = { "login" },
     *     summary     = "Returns TDW api token",
     *     description = "Returns TDW api authorization token.",
     *     operationId = "tdw_post_login",
     *     @OA\RequestBody(
     *         required= true,
     *         @OA\MediaType(
     *             mediaType = "application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      property="_username",
     *                      description="User name",
     *                      type="string"
     *                 ),
     *                 @OA\Property(
     *                      property="_password",
     *                      description="User password",
     *                      type="string",
     *                      format="password"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response    = 200,
     *          description = "TDW Users api token",
     *          @OA\Header(
     *              header      = "X-Token",
     *              description = "api authorization token",
     *              @OA\Schema(
     *                  type="string"
     *              )
     *          ),
     *          @OA\JsonContent(
     *              type        = "object",
     *              example     = {
     *                  "token": "<JSON web token>"
     *              }
     *          )
     *     ),
     *     @OA\Response(
     *          response    = 404,
     *          ref         = "#/components/responses/404_Resource_Not_Found_Response"
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function post(Request $request, Response $response): Response
    {
        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true);

        /** @var Usuario $user */
        $user = null;
        if (isset($req_data['_username'], $req_data['_password'])) {
            $user = Utils::getEntityManager()
                ->getRepository(Usuario::class)
                ->findOneBy([ 'username' => $req_data['_username'] ]);
        }

        if (null === $user || !$user->validatePassword($req_data['_password'])) {    // 404
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }

        $json_web_token = Utils::getToken(
            $user->getId(),
            $user->getUsername(),
            $user->isAdmin()
        );
        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [ 'uid' => $user->getId(), 'status' => StatusCode::HTTP_OK ] // 200
        );

        return $response
            ->withJson([ 'token' => $json_web_token ])
            ->withAddedHeader('X-Token', $json_web_token);
    }
}
