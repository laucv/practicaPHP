<?php
/** 
 * PHP version 7.2
 * apiTDWUsers - src/Controller/CuestionController.php
 */

namespace TDW\GCuest\Controller;

use OpenApi\Annotations as OA;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use TDW\GCuest\Entity\Usuario;
use TDW\GCuest\Entity\Cuestion;
use TDW\GCuest\Error;
use TDW\GCuest\Utils;

/**
 * Class CuestionController
 */
class CuestionController
{
    /** @var string ruta api gestión cuestiones  */
    public const PATH_USUARIOS = '/questions';

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
     * Summary: Returns all questions
     *
     * @OA\Get(
     *     path        = "/questions",
     *     tags        = { "Questions" },
     *     summary     = "Returns all questions",
     *     description = "Returns all questions from the system that the user has access to.",
     *     operationId = "tdw_cget_questions",
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 200,
     *          description = "Array of questions",
     *          @OA\JsonContent(
     *              ref  = "#/components/schemas/QuestionsArray"
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
        if(0 === !$this->jwt->user_id){
            return Error::error($this->container, $request, $response, StatusCode::HTTP_UNAUTHORIZED);
        }

        $entity_manager = Utils::getEntityManager();
        $cuestion = $this->jwt->isAdmin
            ? $entity_manager->getRepository(Cuestion::class)
                ->findAll()
            : $entity_manager->getRepository(Cuestion::class)
                ->findBy([ 'id' => $this->jwt->user_id ]);

        if (0 === count($cuestion)) {    // 404
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }

        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [ 'uid' => $this->jwt->user_id, 'status' => StatusCode::HTTP_OK ]
        );

        return $response
            ->withJson(
                [ 'cuestiones' => $cuestion ],
                StatusCode::HTTP_OK // 200
            );
    }

    /**
     * Summary: Returns a question based on a single ID
     *
     * @OA\Get(
     *     path        = "/questions/{questionId}",
     *     tags        = { "Questions" },
     *     summary     = "Returns a question based on a single ID",
     *     description = "Returns the question identified by `questionId`.",
     *     operationId = "tdw_get_questions",
     *     @OA\Parameter(
     *          ref    = "#/components/parameters/questionId"
     *     ),
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 200,
     *          description = "Question",
     *          @OA\JsonContent(
     *              ref  = "#/components/schemas/Question"
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
        if (0 === $this->jwt->user_id) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_UNAUTHORIZED);
        }

        $entity_manager = Utils::getEntityManager();
        $cuestion = $entity_manager->find(Cuestion::class, $args['id']);

        if (null === $cuestion) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }

        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [
                'uid' => $this->jwt->user_id,
                'status' => StatusCode::HTTP_OK
            ]
        );

        return $response
            ->withJson(
                $cuestion,
                StatusCode::HTTP_OK // 200
            );
    }

    /**
     * Summary: Deletes a question
     *
     * @OA\Delete(
     *     path        = "/questions/{questionId}",
     *     tags        = { "Questions" },
     *     summary     = "Deletes a question",
     *     description = "Deletes the question identified by `questionId`.",
     *     operationId = "tdw_delete_questions",
     *     parameters={
     *          { "$ref" = "#/components/parameters/questionId" }
     *     },
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 204,
     *          description = "Question deleted &lt;Response body is empty&gt;"
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

        if (0 === $this->jwt->user_id) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_UNAUTHORIZED);
        }

        if (!$this->jwt->isMaestro) { // 403
            return Error::error($this->container, $request, $response, StatusCode::HTTP_FORBIDDEN);
        }

        $entity_manager = Utils::getEntityManager();
        $cuestion = $entity_manager->find(Cuestion::class, $args['id']);

        if (null === $cuestion) {    // 404
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }

        $entity_manager->remove($cuestion);
        $entity_manager->flush();
		
        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [
                'uid' => $this->jwt->user_id,
                'status' => StatusCode::HTTP_NO_CONTENT
            ]
        );
		
        return $response->withStatus(StatusCode::HTTP_NO_CONTENT);  // 204

    }

    /**
     * Summary: Provides the list of HTTP supported methods
     *
     * @OA\Options(
     *     path        = "/questions",
     *     tags        = { "Questions" },
     *     summary     = "Provides the list of HTTP supported methods",
     *     description = "Return a `Allow` header with a comma separated list of HTTP supported methods.",
     *     operationId = "tdw_options_questions",
     *     @OA\Response(
     *          response    = 200,
     *          description = "`Allow` header &lt;Response body is empty&gt;",
     *          @OA\Header(
     *              header      = "Allow",
     *              description = "List of HTTP supported methods",
     *              @OA\Schema(
     *                  type="string"
     *              )
     *          )
     *     )
     * )
     *
     * @OA\Options(
     *     path        = "/questions/{questionId}",
     *     tags        = { "Questions" },
     *     summary     = "Provides the list of HTTP supported methods",
     *     description = "Return a `Allow` header with a comma separated list of HTTP supported methods.",
     *     operationId = "tdw_options_questions_id",
     *     parameters={
     *          { "$ref" = "#/components/parameters/questionId" },
     *     },
     *     @OA\Response(
     *          response    = 200,
     *          description = "`Allow` header &lt;Response body is empty&gt;",
     *          @OA\Header(
     *              header      = "Allow",
     *              description = "List of HTTP supported methods",
     *              @OA\Schema(
     *                  type="string"
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
     * Summary: Creates a new question
     *
     * @OA\Post(
     *     path        = "/questions",
     *     tags        = { "Questions" },
     *     summary     = "Creates a new question",
     *     description = "Creates a new question",
     *     operationId = "tdw_post_questions",
     *     @OA\RequestBody(
     *         description = "`Question` properties to add to the system",
     *         required    = true,
     *         @OA\JsonContent(
     *             ref = "#/components/schemas/QuestionData"
     *         )
     *     ),
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 201,
     *          description = "`Created`: question created",
     *          @OA\JsonContent(
     *              ref  = "#/components/schemas/Question"
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
     *          response    = 409,
     *          description = "`Conflict`: the creator does not exist or is not a teacher.",
     *          @OA\JsonContent(
     *              ref  = "#/components/schemas/Message",
     *              example          = {
     *                   "code"      = 409,
     *                   "message"   = "`Conflict`: the creator does not exist or is not a teacher."
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
        if (0 === $this->jwt->user_id) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_UNAUTHORIZED);
        }

        if (!$this->jwt->isMaestro) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_FORBIDDEN);
        }

        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true);
        $entity_manager = Utils::getEntityManager();

        $creador = (isset($req_data['creador'])) ? $entity_manager->find(Usuario::class, $req_data["creador"]) : null;

        if(null !== $creador && !$creador->isMaestro()){ //Usuario no es maestro
            return Error::error($this->container, $request, $response, StatusCode::HTTP_CONFLICT);
        }

        // 201
        $cuestion = new Cuestion(
            $req_data['enunciadoDescripcion'],
            $creador,
            $req_data['enunciadoDisponible'] ?? false
        );
        $entity_manager->persist($cuestion);
        $entity_manager->flush();

        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [
                'uid' => $this->jwt->user_id,
                'status' => StatusCode::HTTP_CREATED
            ]
        );

        return $response->withJson($cuestion, StatusCode::HTTP_CREATED); // 201
    }

    /**
     * Summary: Updates a question
     *
     * @OA\Put(
     *     path        = "/questions/{questionId}",
     *     tags        = { "Questions" },
     *     summary     = "Updates a question",
     *     description = "Updates the question identified by `questionId`.",
     *     operationId = "tdw_put_questions",
     *     @OA\Parameter(
     *          ref    = "#/components/parameters/questionId"
     *     ),
     *     @OA\RequestBody(
     *         description = "`Question` data to update",
     *         required    = true,
     *         @OA\JsonContent(
     *             ref = "#/components/schemas/QuestionData"
     *         )
     *     ),
     *     security    = {
     *          { "TDWApiSecurity": {} }
     *     },
     *     @OA\Response(
     *          response    = 209,
     *          description = "`Content Returned`: question previously existed and is now updated",
     *          @OA\JsonContent(
     *              ref = "#/components/schemas/Question"
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
     *     ),
     *     @OA\Response(
     *          response    = 409,
     *          description = "`Conflict`: the creator does not exist or is not a teacher.",
     *          @OA\JsonContent(
     *              ref  = "#/components/schemas/Message",
     *              example          = {
     *                   "code"      = 409,
     *                   "message"   = "`Conflict`: the creator does not exist or is not a teacher."
     *              }
     *         )
     *     )
     * )
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function put(Request $request, Response $response, array $args): Response
    {
        if (0 === $this->jwt->user_id) {
            return Error::error($this->container, $request, $response, StatusCode::HTTP_UNAUTHORIZED);
        }

        if (!$this->jwt->isMaestro) { // 403
            return Error::error($this->container, $request, $response, StatusCode::HTTP_FORBIDDEN);
        }

        $req_data
            = $request->getParsedBody()
            ?? json_decode($request->getBody(), true);
        $entity_manager = Utils::getEntityManager();
        $cuestion = $entity_manager->find(Cuestion::class, $args['id']);

        if (null === $cuestion) {    // 404
            return Error::error($this->container, $request, $response, StatusCode::HTTP_NOT_FOUND);
        }
        if (isset($req_data['enunciadoDescripcion'])) {///////
            $cuestion->setEnunciadoDescripcion($req_data['enunciadoDescripcion']);
        }
        // enunciadoDisponible
        if (isset($req_data['enunciadoDisponible'])) {
            $cuestion->setEnunciadoDisponible($req_data['enunciadoDisponible']);
        }
        // creador
        if (isset($req_data['creador'])) {
            $creador = $entity_manager->find(Usuario::class, $req_data["creador"]);
            if(!$creador->isMaestro()){ //Usuario no es maestro
                return Error::error($this->container, $request, $response, StatusCode::HTTP_CONFLICT);
            }
            $cuestion->setCreador($creador);
        }
        // estado
        if (isset($req_data['estado'])) {
            if($req_data['estado'] === 'abierta') {
                $cuestion->abrirCuestion();
            }
            elseif ($req_data['estado'] === 'cerrada'){
                $cuestion->cerrarCuestion();
            }
        }

        //$entity_manager->merge($cuestion);
        $entity_manager->flush();

        $this->logger->info(
            $request->getMethod() . ' ' . $request->getUri()->getPath(),
            [ 'uid' => $this->jwt->user_id, 'status' => StatusCode::HTTP_OK ]
        );

        return $response->withJson($cuestion)->withStatus(209, 'Content Returned');
    }
}
