<?php
/**
 * PHP version 7.2
 * tests\Functional\LoginApiTest.php
 */

namespace TDW\Tests\GCuest\Functional;

use Faker\Factory;
use Slim\Http\StatusCode;
use TDW\GCuest\Error;
use TDW\GCuest\Utils;

/**
 * Class LoginApiTest
 */
class LoginApiTest extends BaseTestCase
{
    /** @var string path de login */
    private static $ruta_base;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        self::$ruta_base = $_ENV['RUTA_LOGIN'];

        $faker = Factory::create('es_ES');
        self::$admin = [
            'username' => $faker->userName,
            'email'    => $faker->email,
            'password' => $faker->password,
        ];

        // load user admin fixtures
        Utils::loadUserData(
            self::$admin['username'],
            self::$admin['email'],
            self::$admin['password'],
            true
        );
    }

    /**
     * Called after the last test of the test case class is run
     */
    public static function tearDownAfterClass(): void
    {
        Utils::updateSchema();
    }

    /**
     * Test POST /login 404 NOT FOUND
     * @param array $data
     * @dataProvider proveedorUsuarios()
     */
    public function testPostLogin404(array $data): void
    {
        $response = $this->runApp('POST', self::$ruta_base, $data);

        self::assertSame(StatusCode::HTTP_NOT_FOUND, $response->getStatusCode());
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
     * Test POST /login 200 OK
     */
    public function testPostLogin200(): void
    {
        $data = [
            '_username' => self::$admin['username'],
            '_password' => self::$admin['password']
        ];
        $response = $this->runApp('POST', self::$ruta_base, $data);

        self::assertSame(200, $response->getStatusCode());
        self::assertJson((string) $response->getBody());
        $r_body = (string) $response->getBody();
        self::assertTrue($response->hasHeader('X-Token'));
        $r_data = json_decode($r_body, true);
        self::assertNotEmpty($r_data['token']);
    }

    public function proveedorUsuarios(): array
    {
        $faker = Factory::create('es_ES');
        $fakeUsername = $faker->userName;
        $fakePasswd = $faker->password;

        try {
            return [
                'empty_user'  => [
                    [ ]
                ],
                'no_password' => [
                    [ 'username' => self::$admin['username'] ]
                ],
                'no_username' => [
                    [ 'password' => self::$admin['password'] ]
                ],
                'incorrect_username' => [
                    [ 'username' => $fakeUsername, 'password' => self::$admin['password'] ]
                ],
                'incorrect_passwd' => [
                    [ 'username' => self::$admin['username'], 'password' => $fakePasswd ]
                ],
            ];
        } catch (\Exception $e) {
            die('ERROR: ' . $e->getMessage());
        }
    }
}
