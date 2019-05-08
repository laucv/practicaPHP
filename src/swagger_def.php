<?php
/**
 * PHP version 7.2
 * src/swagger_def.php
 */

use OpenApi\Annotations as OA;

/**
 * Global api definition
 *
 * @OA\Info(
 *     title       = "TDW REST api",
 *     version     = "2.0.0",
 *     description = "[UPM] TDW REST api operations",
 *     @OA\License(
 *          name="MIT",
 *          url="https://opensource.org/licenses/MIT"
 *     )
 * ),
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="TDW Production server (uses live data)"
 * ),
 * @OA\Tag(
 *     name="login",
 *     description="User login"
 * ),
 * @OA\Tag(
 *     name="Users",
 *     description="User management"
 * ),
 * @OA\Tag(
 *     name="Questions",
 *     description="Question management"
 * )
 */

/**
 * Security schema definition
 *
 * @OA\SecurityScheme(
 *     securityScheme = "TDWApiSecurity",
 *     type           = "http",
 *     scheme         = "bearer",
 *     bearerFormat   = "JWT"
 * )
 */

/**
 * Parameters definition
 *
 * @OA\Parameter(
 *      name        = "userId",
 *      in          = "path",
 *      description = "ID of user",
 *      required    = true,
 *      @OA\Schema(
 *          format  = "int64",
 *          type    = "integer"
 *      )
 * )
 *
 * @OA\Parameter(
 *      name        = "username",
 *      in          = "path",
 *      description = "User name",
 *      required    = true,
 *      @OA\Schema(
 *          type    = "string"
 *      )
 * )
 *
 * @OA\Parameter(
 *      name        = "questionId",
 *      in          = "path",
 *      description = "ID of question",
 *      required    = true,
 *      @OA\Schema(
 *          format  = "int64",
 *          type    = "integer"
 *      )
 * )
 */

/**
 * Message definition
 *
 * @OA\Schema(
 *     schema           = "Message",
 *     required         = { "code", "message" },
 *     example          = {
 *         "code"    = 200,
 *         "message" = "Ok"
 *     },
 *     @OA\Property(
 *          property    = "code",
 *          description = "Response code",
 *          type        = "integer",
 *          format      = "int64"
 *     ),
 *     @OA\Property(
 *          property    = "message",
 *          description = "Response message",
 *          type        = "string"
 *      )
 * )
 */

/**
 * Standard Response definitions
 *
 * @OA\Response(
 *     response         = "401_Standard_Response",
 *     description      = "`Unauthorized`: invalid `Authorization` header",
 *     @OA\JsonContent(
 *         ref          = "#/components/schemas/Message",
 *         example      = {
 *             "code"    = 401,
 *             "message" = "`Unauthorized`: invalid `Authorization` header"
 *         }
 *     )
 * )
 *
 * @OA\Response(
 *     response         = "403_Forbidden_Response",
 *     description      = "`Forbidden`: you don't have permission to access",
 *     @OA\JsonContent(
 *         ref          = "#/components/schemas/Message",
 *         example      = {
 *             "code"    = 403,
 *             "message" = "`Forbidden`: you don't have permission to access"
 *         }
 *     )
 * )
 *
 * @OA\Response(
 *     response         = "404_Resource_Not_Found_Response",
 *     description      = "`Not found`: resource not found",
 *     @OA\JsonContent(
 *         ref          = "#/components/schemas/Message",
 *         example      = {
 *             "code"    = 404,
 *             "message" = "`Not found`: resource not found"
 *         }
 *     )
 * )
 */
