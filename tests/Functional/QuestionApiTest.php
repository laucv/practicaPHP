<?php
/**
 * PHP version 7.2
 * apiTDWUsers - tests/Functional/QuestionApiTest.php
 */

namespace TDW\Tests\GCuest\Functional;

use Slim\Http\StatusCode;
use TDW\GCuest\Controller\CuestionController;
use TDW\GCuest\Entity\Cuestion;
use TDW\GCuest\Error;
use TDW\GCuest\Utils;

/**
 * Class QuestionApiTest
 *
 * @coversDefaultClass \TDW\GCuest\Controller\CuestionController
 */
class QuestionApiTest extends BaseTestCase
{
    /** @var string path para la gestión de cuestiones */
    private static $ruta_base;

    /** @var array $alumno */
    private static $alumno;

    /**
     * Se ejecuta una vez al inicio de las pruebas de la clase UserApiTest
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$ruta_base = $_ENV['RUTA_API'] . '/questions';

        self::$admin = [
            'username' => self::$faker->userName,
            'email'    => self::$faker->email,
            'password' => self::$faker->password,
        ];
        self::$alumno = [
            'username' => self::$faker->userName,
            'email'    => self::$faker->email,
            'password' => self::$faker->password,
        ];

        // load user admin fixtures
        self::$admin['id'] = Utils::loadUserData(
            self::$admin['username'],
            self::$admin['email'],
            self::$admin['password'],
            true
        );

        // load user alumno fixtures
        self::$alumno['id'] = Utils::loadUserData(
            self::$alumno['username'],
            self::$alumno['email'],
            self::$alumno['password'],
            false
        );
    }

    /**
     * Called after the last test of the test case class is run
     * Drop & Update the database schema
     */
    public static function tearDownAfterClass(): void
    {
        Utils::updateSchema();
    }

    /**
     * Test GET /questions 404
     *
     * @covers ::cget
     */
    public function testCGetAllQuestions404(): void
    {
        $response = $this->runApp(
            'GET',
            self::$ruta_base,
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(404, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $r_body = (string) $response->getBody();
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_NOT_FOUND, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_NOT_FOUND],
            $r_data['message']
        );
    }

    /**
     * Test POST /questions 401 UNAUTHORIZED
     */
    public function testPostQuestion401(): void
    {
        $response = $this->runApp('POST', self::$ruta_base);

        self::assertSame(401, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $r_body = (string) $response->getBody();
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_UNAUTHORIZED, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_UNAUTHORIZED],
            $r_data['message']
        );
    }

    /**
     * Test POST /questions
     *
     * @depends testCGetAllQuestions404
     * @covers ::post
     * @return array question data
     */
    public function testPostQuestion201(): array
    {
        $p_data = [ // Sin creador
            // 'creador'              => 1,
            'enunciadoDescripcion' => self::$faker->text(255),
            'enunciadoDisponible'  => self::$faker->boolean,
        ];
        $headers = $this->getTokenHeaders(
            self::$admin['username'],
            self::$admin['password']
        );
        $response = $this->runApp('POST', self::$ruta_base, $p_data, $headers);

        self::assertSame(201, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $responseQuestion = json_decode((string) $response->getBody(), true);
        $cuestionData = $responseQuestion['cuestion'];
        self::assertNotEquals($cuestionData['idCuestion'], 0);
        self::assertNull($cuestionData['creador']);
        self::assertSame($p_data['enunciadoDescripcion'], $cuestionData['enunciadoDescripcion']);
        self::assertEquals($p_data['enunciadoDisponible'], $cuestionData['enunciadoDisponible']);
        self::assertSame(Cuestion::CUESTION_CERRADA, $cuestionData['estado']);

        // Con creador
        $p_data = [
            'creador'              => 1,
            'enunciadoDescripcion' => self::$faker->text(255),
            'enunciadoDisponible'  => self::$faker->boolean,
        ];
        $response = $this->runApp('POST', self::$ruta_base, $p_data, $headers);

        self::assertSame(201, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $responseQuestion = json_decode((string) $response->getBody(), true);
        $cuestionData = $responseQuestion['cuestion'];
        self::assertNotEquals($cuestionData['idCuestion'], 0);
        self::assertSame($p_data['creador'], $cuestionData['creador']);
        self::assertSame($p_data['enunciadoDescripcion'], $cuestionData['enunciadoDescripcion']);
        self::assertEquals($p_data['enunciadoDisponible'], $cuestionData['enunciadoDisponible']);
        self::assertSame(Cuestion::CUESTION_CERRADA, $cuestionData['estado']);

        return $cuestionData;
    }

    /**
     * Test POST /questions 409 HTTP_CONFLICT
     *
     * @covers ::post
     * @depends testPostQuestion201
     */
    public function testPostQuestion409(): void
    {
        $p_data = [
            'creador'              => 2,
            'enunciadoDescripcion' => self::$faker->text(255),
            'enunciadoDisponible'  => self::$faker->boolean,
        ];
        $response = $this->runApp('POST', self::$ruta_base, $p_data, self::$headers);

        self::assertJson((string) $response->getBody());
        $r_body = (string) $response->getBody();
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_CONFLICT, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_CONFLICT],
            $r_data['message']
        );
    }

    /**
     * Test GET /questions
     *
     * @depends testPostQuestion201
     * @covers ::cget
     */
    public function testCGetAllQuestions200(): void
    {
        $response = $this->runApp(
            'GET',
            self::$ruta_base,
            null,
            self::$headers
        );

        self::assertSame(200, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertJson($r_body);
        self::assertStringContainsString('cuestiones', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertArrayHasKey('cuestiones', $r_data);
        self::assertIsArray($r_data['cuestiones']);
    }

    /**
     * Test GET /questions/questionId
     *
     * @param array $question Question returned by testPostQuestion201()
     * @covers ::get
     * @depends testPostQuestion201
     */
    public function testGetQuestion200(array $question): void
    {
        $response = $this->runApp(
            'GET',
            self::$ruta_base . '/' . $question['idCuestion'],
            null,
            self::$headers
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $user_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($question, $user_aux['cuestion']);
    }

    /**
     * Test PUT /questions/questionId 209
     *
     * @param array $question Question returned by testPostQuestion201()
     * @depends testPostQuestion201
     * @covers ::put
     * @return array modified question data
     */
    public function testPutQuestion209(array $question): array
    {
        $p_data = [
            'creador'              => 1,
            'enunciadoDescripcion' => self::$faker->text(255),
            'enunciadoDisponible'  => self::$faker->boolean,
            'estado'               => self::$faker->randomElement(Cuestion::CUESTION_ESTADOS),
        ];

        $response = $this->runApp(
            'PUT',
            self::$ruta_base . '/' . $question['idCuestion'],
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(209, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $question_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($question['idCuestion'], $question_aux['cuestion']['idCuestion']);
        self::assertSame($p_data['enunciadoDescripcion'], $question_aux['cuestion']['enunciadoDescripcion']);
        self::assertSame($p_data['enunciadoDisponible'], $question_aux['cuestion']['enunciadoDisponible']);
        self::assertSame($p_data['estado'], $question_aux['cuestion']['estado']);

        return $question_aux['cuestion'];
    }

    /**
     * Test OPTIONS /questions[/questionId]
     *
     * @covers ::options
     */
    public function testOptionsQuestions(): void
    {
        /**
         * Response
         *
         * @var \Slim\Http\Response $response
         */
        $response = $this->runApp(
            'OPTIONS',
            self::$ruta_base
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('Allow'));

        $response = $this->runApp(
            'OPTIONS',
            self::$ruta_base . '/' . self::$faker->randomDigitNotNull
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertNotEmpty($response->getHeader('Allow'));
    }

    /**
     * Test DELETE /questions/questionId
     *
     * @param array $question user returned by testPostUser201()
     *
     * @depends testPostQuestion201
     * @depends testCGetAllQuestions200
     * @depends testGetQuestion200
     * @depends testPutQuestion209
     * @covers ::delete
     *
     * @return int questionId
     */
    public function testDeleteQuestion204(array $question): int
    {
        $response = $this->runApp(
            'DELETE',
            self::$ruta_base . '/' . $question['idCuestion'],
            null,
            self::$headers
        );

        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty((string) $response->getBody());

        return $question['idCuestion'];
    }

    /**
     * Test METHOD /questions[/questionId] 401 UNAUTHORIZED
     *
     * @param string $method HTTP method
     * @param int $questionId question id.
     * @dataProvider provider_401_404
     */
    public function testMethodQuestion401(string $method, int $questionId): void
    {
        $response = $this->runApp(
            $method,
            self::$ruta_base . '/' . $questionId
        );

        self::assertSame(401, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $r_body = (string) $response->getBody();
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_UNAUTHORIZED, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_UNAUTHORIZED],
            $r_data['message']
        );
    }

    /**
     * Test METHOD /questions/questionId 404 Not Found
     *
     * @param string $method HTTP method
     * @param int $questionId question id.
     * @depends      testDeleteQuestion204
     * @dataProvider provider_401_404()
     */
    public function testQuestion404(string $method, int $questionId): void
    {
        $response = $this->runApp(
            $method,
            self::$ruta_base . '/' . $questionId,
            null,
            self::$headers
        );

        self::assertSame(404, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_NOT_FOUND, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_NOT_FOUND],
            $r_data['message']
        );
    }

    /**
     * 401/404 Data Provider
     * @return array
     */
    public function provider_401_404(): array
    {
        return [
            'methodGET'    => [ 'GET'   , 2 ],
            'methodDELETE' => [ 'DELETE', 2 ],
            'methodPUT'    => [ 'PUT'   , 2 ],
        ];
    }

    /**
     * Test METHOD /questions/questionId 403 Forbidden
     *
     * @param string $method HTTP method
     * @param int $questionId question id.
     * @dataProvider provider_403()
     */
    public function testQuestion403(string $method, ?int $questionId): void
    {
        self::$headers = $this->getTokenHeaders(
            self::$alumno['username'],
            self::$alumno['password']
        );
        $response = $this->runApp(
            $method,
            self::$ruta_base . ($questionId ?  '/' . $questionId : ''),
            null,
            self::$headers
        );

        self::assertSame(403, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_FORBIDDEN, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_FORBIDDEN],
            $r_data['message']
        );
    }

    /**
     * 403 Data Provider
     * @return array
     */
    public function provider_403(): array
    {
        return [
            'methodDELETE' => [ 'DELETE', 1 ],
            'methodPOST'   => [ 'POST'  , null ],
            'methodPUT'    => [ 'PUT'   , 1 ],
        ];
    }
}
