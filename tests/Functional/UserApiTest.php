<?php
/**
 * PHP version 7.2
 * tests/Functional/UserApiTest.php
 */

namespace TDW\Tests\GCuest\Functional;

use Slim\Http\StatusCode;
use TDW\GCuest\Error;
use TDW\GCuest\Utils;

/**
 * Class UserApiTest
 */
class UserApiTest extends BaseTestCase
{
    /** @var string path para la gestión de usuarios */
    private static $ruta_base;

    /**
     * Se ejecuta una vez al inicio de las pruebas de la clase UserApiTest
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$ruta_base = $_ENV['RUTA_API'] . '/users';

        self::$admin = [
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
     * Test GET /users 401 UNAUTHORIZED
     */
    public function testCGetUser401(): void
    {
        $response = $this->runApp('GET', self::$ruta_base);

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
     * Test GET /users/userId 401 UNAUTHORIZED
     */
    public function testGetUser401(): void
    {
        $response = $this->runApp('GET', self::$ruta_base . '/1');

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
     * Test POST /users 401 UNAUTHORIZED
     */
    public function testPostUser401(): void
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
     * Test POST /users
     *
     * @return array user data
     */
    public function testPostUser201(): array
    {
        $p_data = [
            'username'  => self::$faker->userName,
            'email'     => self::$faker->email,
            'password'  => self::$faker->password,
            'enabled'   => self::$faker->boolean,
            'isAdmin'   => self::$faker->boolean,
            'isMaestro' => self::$faker->boolean
        ];
        $headers = $this->getTokenHeaders(
            self::$admin['username'],
            self::$admin['password']
        );
        $response = $this->runApp('POST', self::$ruta_base, $p_data, $headers);

        self::assertSame(201, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $responseUser = json_decode((string) $response->getBody(), true);
        $userData = $responseUser['usuario'];
        self::assertNotEquals($userData['id'], 0);
        self::assertSame($p_data['username'], $userData['username']);
        self::assertSame($p_data['email'], $userData['email']);
        self::assertEquals($p_data['isAdmin'], $userData['admin']);
        self::assertEquals($p_data['isMaestro'], $userData['maestro']);
        self::assertEquals($p_data['enabled'], $userData['enabled']);

        return $userData;
    }

    /**
     * Test POST /users 422
     * @param string $username
     * @param string $email
     * @param string $password
     *
     * @dataProvider dataProviderPostUser422
     * @depends testPostUser201
     */
    public function testPostUser422(?string $username, ?string $email, ?string $password): void
    {
        $p_data = [];
        if (null !== $username) {
            $p_data['username'] = $username;
        }
        if (null !== $email) {
            $p_data['email'] = $email;
        }
        if (null !== $password) {
            $p_data['password'] = $password;
        }
        $response = $this->runApp(
            'POST',
            self::$ruta_base,
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(422, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertJson($r_body);
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_UNPROCESSABLE_ENTITY, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_UNPROCESSABLE_ENTITY],
            $r_data['message']
        );
    }

    /**
     * Test POST /users 400
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201
     */
    public function testPostUser400(array $user): void
    {
        // Mismo username
        $p_data = [
            'username' => $user['username'],
            'email'    => self::$faker->email,
            'password' => self::$faker->password
        ];
        $response = $this->runApp(
            'POST',
            self::$ruta_base,
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(400, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertJson($r_body);
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_BAD_REQUEST, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_BAD_REQUEST],
            $r_data['message']
        );

        // Mismo email
        $p_data = [
            'username' => self::$faker->userName,
            'email'    => $user['email'],
            'password' => self::$faker->password
        ];
        $response = $this->runApp(
            'POST',
            self::$ruta_base,
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(400, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertJson($r_body);
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_BAD_REQUEST, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_BAD_REQUEST],
            $r_data['message']
        );
    }

    /**
     * Test GET /users
     *
     * @depends testPostUser201
     */
    public function testCGetAllUsers200(): void
    {
        $response = $this->runApp(
            'GET',
            self::$ruta_base,
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(200, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertJson($r_body);
        self::assertStringContainsString('usuarios', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertArrayHasKey('usuarios', $r_data);
        self::assertIsArray($r_data['usuarios']);
    }

    /**
     * Test GET /users/userId
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201
     */
    public function testGetUser200(array $user): void
    {
        $response = $this->runApp(
            'GET',
            self::$ruta_base . '/' . $user['id'],
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(200, $response->getStatusCode(), 'Headers: ' . json_encode($this->getTokenHeaders()));
        self::assertJson((string) $response->getBody());
        $user_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($user, $user_aux['usuario']);
    }

    /**
     * Test GET /users/username/{username} 204 Ok
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201
     */
    public function testGetUsername204(array $user): void
    {
        $response = $this->runApp(
            'GET',
            self::$ruta_base . '/username/' . \urlencode($user['username'])
        );

        self::assertSame(204, $response->getStatusCode(), 'Headers: ' . json_encode($this->getTokenHeaders()));
    }

    /**
     * Test PUT /users/userId   209
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201
     *
     * @return array modified user data
     */
    public function testPutUser209(array $user): array
    {
        $p_data = [
            'username'  => self::$faker->userName,
            'email'     => self::$faker->email,
            'password'  => self::$faker->password,
            'enabled'   => self::$faker->boolean,
            'isAdmin'   => self::$faker->boolean,
            'isMaestro' => self::$faker->boolean
        ];

        $response = $this->runApp(
            'PUT',
            self::$ruta_base . '/' . $user['id'],
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(209, $response->getStatusCode(), 'ERROR: ' . $response->getBody());
        self::assertJson((string) $response->getBody());
        $user_aux = json_decode((string) $response->getBody(), true);
        self::assertSame($user['id'], $user_aux['usuario']['id']);
        self::assertSame($p_data['username'], $user_aux['usuario']['username']);
        self::assertSame($p_data['email'], $user_aux['usuario']['email']);
        self::assertEquals($p_data['enabled'], $user_aux['usuario']['enabled']);
        self::assertEquals($p_data['isAdmin'], $user_aux['usuario']['admin']);
        self::assertEquals($p_data['isMaestro'], $user_aux['usuario']['maestro']);

        return $user_aux['usuario'];
    }

    /**
     * Test PUT /users 400
     *
     * @param array $user user returned by testPutUser200()
     *
     * @depends testPutUser209
     */
    public function testPutUser400(array $user): void
    {
        // username already exists
        $p_data = [
            'username' => $user['username']
        ];
        $response = $this->runApp(
            'PUT',
            self::$ruta_base . '/' . $user['id'],
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(400, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertJson($r_body);
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_BAD_REQUEST, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_BAD_REQUEST],
            $r_data['message']
        );

        // e-mail already exists
        $p_data = [
            'email' => $user['email']
        ];
        $response = $this->runApp(
            'PUT',
            self::$ruta_base . '/' . $user['id'],
            $p_data,
            $this->getTokenHeaders()
        );

        self::assertSame(400, $response->getStatusCode());
        $r_body = (string) $response->getBody();
        self::assertJson($r_body);
        self::assertStringContainsString('code', $r_body);
        self::assertStringContainsString('message', $r_body);
        $r_data = json_decode($r_body, true);
        self::assertSame(StatusCode::HTTP_BAD_REQUEST, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_BAD_REQUEST],
            $r_data['message']
        );
    }

    /**
     * Test PUT /users/userId 401 UNAUTHORIZED
     */
    public function testPutUser401(): void
    {
        $response = $this->runApp('PUT', self::$ruta_base . '/1');

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
     * Test OPTIONS /users[/userId]
     */
    public function testOptionsUser(): void
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
     * Test DELETE /users/userId
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPostUser201
     * @depends testPostUser400
     * @depends testGetUser200
     * @depends testPutUser400
     * @depends testGetUsername204
     *
     * @return int userId
     */
    public function testDeleteUser204(array $user): int
    {
        $response = $this->runApp(
            'DELETE',
            self::$ruta_base . '/' . $user['id'],
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty((string) $response->getBody());

        return $user['id'];
    }

    /**
     * Test GET /users/username/{username} 404 Not Found
     *
     * @param array $user user returned by testPostUser201()
     *
     * @depends testPutUser209
     * @depends testDeleteUser204
     */
    public function testGetUsername404(array $user): void
    {
        $response = $this->runApp(
            'GET',
            self::$ruta_base . '/username/' . \urlencode($user['username'])
        );

        self::assertSame(404, $response->getStatusCode(), 'Headers: ' . json_encode($this->getTokenHeaders()));
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
     * Test DELETE /users/userId 401 UNAUTHORIZED
     */
    public function testDeleteUser401(): void
    {
        $response = $this->runApp('DELETE', self::$ruta_base . '/1');

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
     * Test DELETE /users/userId 404 Not Found
     *
     * @param int $userId user id. returned by testDeleteUser204()
     *
     * @depends testDeleteUser204
     */
    public function testDeleteUser404(int $userId): void
    {
        $response = $this->runApp(
            'DELETE',
            self::$ruta_base . '/' . $userId,
            null,
            $this->getTokenHeaders()
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
     * Test GET /users/userId 404 Not Found
     *
     * @param int $userId user id. returned by testDeleteUser204()
     *
     * @depends testDeleteUser204
     */
    public function testGetUser404(int $userId): void
    {
        $response = $this->runApp(
            'GET',
            self::$ruta_base . '/' . $userId,
            null,
            $this->getTokenHeaders()
        );

        self::assertSame(StatusCode::HTTP_NOT_FOUND, $response->getStatusCode());
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
     * Test PUT /users/userId 404 Not Found
     *
     * @param int $userId user id. returned by testDeleteUser204()
     *
     * @depends testDeleteUser204
     */
    public function testPutUser404(int $userId): void
    {
        $response = $this->runApp(
            'PUT',
            self::$ruta_base . '/' . $userId,
            null,
            $this->getTokenHeaders()
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
     * Test GET /users/userId      403 Forbidden
     *
     * @depends testPutUser404
     */
    public function testGetUser403(): array
    {
        // Añade un nuevo usuario NO admin
        $p_data = [
            'username' => self::$faker->userName,
            'email'    => self::$faker->email,
            'password' => self::$faker->password,
            'isAdmin'  => false
        ];
        $response = $this->runApp(
            'POST',
            self::$ruta_base,
            $p_data,
            $this->getTokenHeaders()
        );
        self::assertSame(201, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $user = json_decode((string) $response->getBody(), true);

        $response = $this->runApp(
            'GET',
            self::$ruta_base . '/' . self::$admin['id'],
            null,
            $this->getTokenHeaders($p_data['username'], $p_data['password'])
        );
        self::assertSame(403, $response->getStatusCode());
        self::assertJson((string)$response->getBody());
        $r_data = json_decode($response->getBody(), true);
        self::assertSame(StatusCode::HTTP_FORBIDDEN, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_FORBIDDEN],
            $r_data['message']
        );

        return $user['usuario'];
    }

    /**
     * Test POST /users      403 Forbidden
     *
     * @depends testGetUser403
     */
    public function testPostUser403(): void
    {
        $response = $this->runApp(
            'POST',
            self::$ruta_base,
            [],
            $this->getTokenHeaders()
        );
        self::assertSame(403, $response->getStatusCode());
        self::assertJson((string)$response->getBody());
        $r_data = json_decode($response->getBody(), true);
        self::assertSame(StatusCode::HTTP_FORBIDDEN, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_FORBIDDEN],
            $r_data['message']
        );
    }

    /**
     * Test PUT /users/userId      403 Forbidden
     *
     * @param array $user user returned by testCGetUser403()
     *
     * @depends testGetUser403
     */
    public function testPutUser403(array $user): void
    {
        $response = $this->runApp(
            'PUT',
            self::$ruta_base . '/' . ($user['id'] + 1),
            null,
            $this->getTokenHeaders()
        );
        self::assertSame(403, $response->getStatusCode());
        self::assertJson((string)$response->getBody());
        $r_data = json_decode($response->getBody(), true);
        self::assertSame(StatusCode::HTTP_FORBIDDEN, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_FORBIDDEN],
            $r_data['message']
        );
    }

    /**
     * Test DELETE /users/userId      403 Forbidden
     *
     * @param array $user user returned by testCGetUser403()
     *
     * @depends testGetUser403
     */
    public function testDeleteUser403(array $user): void
    {
        $response = $this->runApp(
            'DELETE',
            self::$ruta_base . '/' . ($user['id'] + 1),
            null,
            $this->getTokenHeaders()
        );
        self::assertSame(403, $response->getStatusCode());
        self::assertJson((string)$response->getBody());
        $r_data = json_decode($response->getBody(), true);
        self::assertSame(StatusCode::HTTP_FORBIDDEN, $r_data['code']);
        self::assertSame(
            Error::MESSAGES[StatusCode::HTTP_FORBIDDEN],
            $r_data['message']
        );
    }

    public function dataProviderPostUser422(): array
    {
        $faker = \Faker\Factory::create('es_ES');
        $fakeUsername = $faker->userName;
        $fakeEmail = $faker->email;
        $fakePasswd = $faker->password;

        return [
            'empty_data'  => [ null, null, null ],
            'no_username' => [ null, $fakeEmail, $fakePasswd ],
            'no_email'    => [ $fakeUsername, null, $fakePasswd ],
            'no_passwd'   => [ $fakeUsername, $fakeEmail, null ],
            'no_us_pa'    => [ null, $fakeEmail, null ],
            'no_em_pa'    => [ $fakeUsername, null, null ],
        ];
    }
}
