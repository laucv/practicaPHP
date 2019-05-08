<?php
/**
 * PHP version 7.2
 * apiTDWUsers - src/Controller/UsuarioController.php
 */

namespace TDW\GCuest\Controller;

use OpenApi\Annotations as OA;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use TDW\GCuest\Entity\Usuario;
use TDW\GCuest\Error;
use TDW\GCuest\Utils;

/**
 * Class RutasUsuario
 */
class UsuarioController
{
    /** @var string ruta api gestión usuarios  */
    public const PATH_USUARIOS = '/users';

    /** @var ContainerInterface $container */
    protected $container;

    /** @var \Firebase\JWT\JWT */
    protected $jwt;

    /** @var \Monolog\Logger $logger */
    protected $logger;

    // constructor receives container instance
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->jwt = $this->container->get('jwt');
        $this->logger = $this->container->get('logger');
    }

    /**
     * Summary: Returns all users
     *
     * @OA\Get(
     *     path        = "/users",
     *     tags        = { "Users" },
     *     summary     = "Returns all users",
     *     description = "Returns all users from the system that the user has access to.",
     *     operationId = "tdw_cget_users",
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 200,
     *          description = "Array of users",
     *          @OA\JsonContent(
     *              ref  = "#/components/schemas/UsersArray"
     *         )
     *     ),
     *     @OA\Response(
     *          response    = 401,
     *          ref         = "#/components/responses/401_Standard_Response"
     *     ),
     *     @OA\Response(
     *          response    = 403,
     *          ref         = "#/components/responses/403_Forbidden_Response"
     *     ),
     *     @OA\Response(
     *          response    = 404,
     *          ref         = "#/components/responses/404_Resource_Not_Found_Response"
     *     )
     * )
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function cget(Request $request, Response $response): Response
    {
        if (!$this->jwt->user_id) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_FORBIDDEN);
        }

        $usuarios = $this->jwt->isAdmin
            ? Utils::getEntityManager()->getRepository(Usuario::class)
                ->findAll()
            : Utils::getEntityManager()->getRepository(Usuario::class)
                ->findBy([ 'id' => $this->jwt->user_id ]);

        if (0 === count($usuarios)) {    // 404
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }

        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [ 'uid' => $this->jwt->user_id, 'status' => StatusCode::HTTP_OK ]
        );

        return $response
            ->withJson(
                [ 'usuarios' => $usuarios ],
                StatusCode::HTTP_OK // 200
            );
    }

    /**
     * Summary: Returns a user based on a single ID
     *
     * @OA\Get(
     *     path        = "/users/{userId}",
     *     tags        = { "Users" },
     *     summary     = "Returns a user based on a single ID",
     *     description = "Returns the user identified by `userId`.",
     *     operationId = "tdw_get_users",
     *     @OA\Parameter(
     *          ref    = "#/components/parameters/userId"
     *     ),
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 200,
     *          description = "User",
     *          @OA\JsonContent(
     *              ref  = "#/components/schemas/User"
     *         )
     *     ),
     *     @OA\Response(
     *          response    = 401,
     *          ref         = "#/components/responses/401_Standard_Response"
     *     ),
     *     @OA\Response(
     *          response    = 403,
     *          ref         = "#/components/responses/403_Forbidden_Response"
     *     ),
     *     @OA\Response(
     *          response    = 404,
     *          ref         = "#/components/responses/404_Resource_Not_Found_Response"
     *     )
     * )
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function get(Request $request, Response $response, array $args): Response
    {

        if (!$this->jwt->isAdmin
            && ($this->jwt->user_id !== (int) $args['id'])) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_FORBIDDEN);
        }

        $usuario = Utils::getEntityManager()->find(Usuario::class, $args['id']);
        if (null === $usuario) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }

        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [ 'uid' => $this->jwt->user_id, 'status' => StatusCode::HTTP_OK ]
        );

        return $response
            ->withJson(
                $usuario,
                StatusCode::HTTP_OK // 200
            );
    }

    /**
     * Summary: Returns status code 204 if username exists
     *
     * @OA\Get(
     *     path        = "/users/username/{username}",
     *     tags        = { "Users" },
     *     summary     = "Returns status code 204 if username exists",
     *     description = "Returns status code 204 if `username` exists.",
     *     operationId = "tdw_get_user_name",
     *     parameters  = {
     *          { "$ref" = "#/components/parameters/username" }
     *     },
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 204,
     *          description = "Username exists &lt;Response body is empty&gt;"
     *     ),
     *     @OA\Response(
     *          response    = 404,
     *          ref         = "#/components/responses/404_Resource_Not_Found_Response"
     *     )
     * )
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getUsername(Request $request, Response $response, array $args): Response
    {

        $usuario = Utils::getEntityManager()
            ->getRepository(Usuario::class)
            ->findOneBy([ 'username' => $args['username'] ]);

        if (null === $usuario) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }

        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [ 'uid' => 0, 'status' => StatusCode::HTTP_NO_CONTENT ]
        );

        return $response
            ->withStatus(StatusCode::HTTP_NO_CONTENT);  // 204
    }

    /**
     * Summary: Deletes a user
     *
     * @OA\Delete(
     *     path        = "/users/{userId}",
     *     tags        = { "Users" },
     *     summary     = "Deletes a user",
     *     description = "Deletes the user identified by `userId`.",
     *     operationId = "tdw_delete_users",
     *     parameters  = {
     *          { "$ref" = "#/components/parameters/userId" }
     *     },
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 204,
     *          description = "User deleted &lt;Response body is empty&gt;"
     *     ),
     *     @OA\Response(
     *          response    = 401,
     *          ref         = "#/components/responses/401_Standard_Response"
     *     ),
     *     @OA\Response(
     *          response    = 403,
     *          ref         = "#/components/responses/403_Forbidden_Response"
     *     ),
     *     @OA\Response(
     *          response    = 404,
     *          ref         = "#/components/responses/404_Resource_Not_Found_Response"
     *     )
     * )
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        if (!$this->jwt->isAdmin && ($this->jwt->user_id !== (int) $args['id'])) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_FORBIDDEN);
        }

        $entity_manager = Utils::getEntityManager();
        $usuario = $entity_manager->find(Usuario::class, $args['id']);

        if (null === $usuario) {    // 404
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }

        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [
                'uid' => $this->jwt->user_id,
                'status' => StatusCode::HTTP_NO_CONTENT
            ]
        );

        $entity_manager->remove($usuario);
        $entity_manager->flush();

        return $response->withStatus(StatusCode::HTTP_NO_CONTENT);  // 204
    }

    /**
     * Summary: Provides the list of HTTP supported methods
     *
     * @OA\Options(
     *     path        = "/users",
     *     tags        = { "Users" },
     *     summary     = "Provides the list of HTTP supported methods",
     *     description = "Return a `Allow` header with a comma separated list of HTTP supported methods.",
     *     operationId = "tdw_options_users",
     *     @OA\Response(
     *          response    = 200,
     *          description = "`Allow` header &lt;Response body is empty&gt;",
     *          @OA\Header(
     *              header      = "Allow",
     *              description = "List of HTTP supported methods",
     *              @OA\Schema(
     *                  type = "string"
     *              )
     *          )
     *     )
     * )
     *
     * @OA\Options(
     *     path        = "/users/{userId}",
     *     tags        = { "Users" },
     *     summary     = "Provides the list of HTTP supported methods",
     *     description = "Return a `Allow` header with a comma separated list of HTTP supported methods.",
     *     operationId = "tdw_options_users_id",
     *     parameters  = {
     *          { "$ref" = "#/components/parameters/userId" },
     *     },
     *     @OA\Response(
     *          response    = 200,
     *          description = "`Allow` header &lt;Response body is empty&gt;",
     *          @OA\Header(
     *              header      = "Allow",
     *              description = "List of HTTP supported methods",
     *              @OA\Schema(
     *                  type = "string"
     *              )
     *          )
     *     )
     * )
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function options(Request $request, Response $response, array $args): Response
    {
        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath()
        );

        $methods = isset($args['id'])
            ? [ 'GET', 'PUT', 'DELETE' ]
            : [ 'GET', 'POST' ];

        return $response
            ->withAddedHeader(
                'Allow',
                implode(', ', $methods)
            );
    }

    /**
     * Summary: Creates a new user
     *
     * @OA\Post(
     *     path        = "/users",
     *     tags        = { "Users" },
     *     summary     = "Creates a new user",
     *     description = "Creates a new user",
     *     operationId = "tdw_post_users",
     *     @OA\RequestBody(
     *         description = "`User` properties to add to the system",
     *         required    = true,
     *         @OA\JsonContent(
     *             ref = "#/components/schemas/UserData"
     *         )
     *     ),
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 201,
     *          description = "`Created`: user created",
     *          @OA\JsonContent(
     *              ref = "#/components/schemas/User"
     *         )
     *     ),
     *     @OA\Response(
     *          response    = 400,
     *          description = "`Bad Request`: username or e-mail already exists",
     *          @OA\JsonContent(
     *              ref = "#/components/schemas/Message",
     *              example = {
     *                  "code"    = 400,
     *                  "message" = "`Bad Request`: username or e-mail already exists"
     *              }
     *         )
     *     ),
     *     @OA\Response(
     *          response    = 401,
     *          ref         = "#/components/responses/401_Standard_Response"
     *     ),
     *     @OA\Response(
     *          response    = 403,
     *          ref         = "#/components/responses/403_Forbidden_Response"
     *     ),
     *     @OA\Response(
     *          response    = 422,
     *          description = "`Unprocessable entity`: username, e-mail or password is left out",
     *          @OA\JsonContent(
     *              ref = "#/components/schemas/Message",
     *              example = {
     *                  "code"    = 422,
     *                  "message" = "`Unprocessable entity`: username, e-mail or password is left out"
     *              }
     *         )
     *     )
     * )
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function post(Request $request, Response $response): Response
    {
        if (!$this->jwt->isAdmin) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_FORBIDDEN);
        }

        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true);

        if (!isset($req_data['username'], $req_data['email'], $req_data['password'])) { // 422 - Faltan datos
            return Error::error($this->container, $request, $response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

        // hay datos -> procesarlos
        /** @var \Doctrine\Common\Collections\Criteria $criteria */
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria
            ->where($criteria::expr()->eq('username', $req_data['username']))
            ->orWhere($criteria::expr()->eq('email', $req_data['email']));
        $entity_manager = Utils::getEntityManager();
        $usuario = $entity_manager
            ->getRepository(Usuario::class)
            ->matching($criteria);

        if (count($usuario)) {    // HTTP_BAD_REQUEST 400: username or e-mail already exists
            return Error::error($this->container, $request, $response, StatusCode::HTTP_BAD_REQUEST);
        }

        // 201
        $usuario = new Usuario(
            $req_data['username'],
            $req_data['email'],
            $req_data['password'],
            $req_data['enabled'] ?? true,
            $req_data['isMaestro'] ?? false,
            $req_data['isAdmin'] ?? false
        );
        $entity_manager->persist($usuario);
        $entity_manager->flush();

        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [ 'uid' => $this->jwt->user_id, 'status' => StatusCode::HTTP_CREATED ]
        );

        return $response->withJson($usuario, StatusCode::HTTP_CREATED); // 201
    }

    /**
     * Summary: Updates a user
     *
     * @OA\Put(
     *     path        = "/users/{userId}",
     *     tags        = { "Users" },
     *     summary     = "Updates a user",
     *     description = "Updates the user identified by `userId`.",
     *     operationId = "tdw_put_users",
     *     @OA\Parameter(
     *          ref    = "#/components/parameters/userId"
     *     ),
     *     @OA\RequestBody(
     *         description = "`User` data to update",
     *         required    = true,
     *         @OA\JsonContent(
     *             ref = "#/components/schemas/UserData"
     *         )
     *     ),
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 209,
     *          description = "`Content Returned`: user previously existed and is now updated",
     *          @OA\JsonContent(
     *              ref = "#/components/schemas/User"
     *         )
     *     ),
     *     @OA\Response(
     *          response    = 400,
     *          description = "`Bad Request`: username or e-mail already exists",
     *          @OA\JsonContent(
     *              ref ="#/components/schemas/Message",
     *              example = {
     *                  "code"    = 400,
     *                  "message" = "`Bad Request`: username or e-mail already exists"
     *              }
     *         )
     *     ),
     *     @OA\Response(
     *          response    = 401,
     *          ref         = "#/components/responses/401_Standard_Response"
     *     ),
     *     @OA\Response(
     *          response    = 403,
     *          ref         = "#/components/responses/403_Forbidden_Response"
     *     ),
     *     @OA\Response(
     *          response    = 404,
     *          ref         = "#/components/responses/404_Resource_Not_Found_Response"
     *     )
     * )
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function put(Request $request, Response $response, array $args): Response
    {
        if (!$this->jwt->isAdmin && ($this->jwt->user_id !== (int) $args['id'])) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_FORBIDDEN);
        }

        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true);
        // recuperar el usuario
        $entity_manager = Utils::getEntityManager();
        /** @var Usuario $user */
        $user = $entity_manager->find(Usuario::class, $args['id']);

        if (null === $user) {    // 404
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }

        if (isset($req_data['username'])) {
            $usuario = $entity_manager->getRepository(Usuario::class)->findOneBy(['username' => $req_data['username']]);
            if (null !== $usuario) {    // 400 BAD_REQUEST: username already exists
                return Error::error($this->container, $request, $response, StatusCode::HTTP_BAD_REQUEST);
            }
            $user->setUsername($req_data['username']);
        }

        if (isset($req_data['email'])) {
            $usuario = $entity_manager->getRepository(Usuario::class)->findOneBy(['email' => $req_data['email']]);
            if (null !== $usuario) {    // 400 BAD_REQUEST: e-mail already exists
                return Error::error($this->container, $request, $response, StatusCode::HTTP_BAD_REQUEST);
            }
            $user->setEmail($req_data['email']);
        }

        // password
        if (isset($req_data['password'])) {
            $user->setPassword($req_data['password']);
        }

        // enabled
        if (isset($req_data['enabled'])) {
            $user->setEnabled($req_data['enabled']);
        }

        // isMaestro
        if ($this->jwt->isAdmin && isset($req_data['isMaestro'])) {
            $user->setMaestro($req_data['isMaestro']);
        }

        // isAdmin
        if ($this->jwt->isAdmin && isset($req_data['isAdmin'])) {
            $user->setAdmin($req_data['isAdmin']);
        }

        // $entity_manager->merge($user);
        $entity_manager->flush();
        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [ 'uid' => $this->jwt->user_id, 'status' => 209 ]
        );

        return $response
            ->withJson($user)
            ->withStatus(209, 'Content Returned');
    }
}
