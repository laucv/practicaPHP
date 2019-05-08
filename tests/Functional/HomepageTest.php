<?php
/**
 * PHP version 7.2
 * tests/Functional/HomepageTest.php
 */

namespace TDW\Tests\GCuest\Functional;

use Slim\Http\StatusCode;
use TDW\GCuest\Error;

/**
 * Class HomepageTest
 */
class HomepageTest extends BaseTestCase
{
    /**
     * Test that the index route returns a 302 status code
     *
     * @return void
     */
    public function testGetHomepage(): void
    {
        $response = $this->runApp('GET', '/');

        self::assertSame(302, $response->getStatusCode());
    }

    /**
     * Test that the index route won't accept a post request
     *
     * @return void
     */
    public function testPostHomepageNotAllowed(): void
    {
        $response = $this->runApp('POST', '/', ['test']);

        self::assertSame(405, $response->getStatusCode());
        self::assertStringContainsString(
            Error::MESSAGES[StatusCode::HTTP_METHOD_NOT_ALLOWED],
            (string) $response->getBody()
        );
    }

    /**
     * Implements RouteNotFound (tests notFoundHandler)
     *
     * @return void
     */
    public function testRouteNotFound(): void
    {
        $response = $this->runApp('PUT', '/products/abc');

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString(
            Error::MESSAGES[StatusCode::HTTP_NOT_FOUND],
            (string) $response->getBody()
        );
    }
}
