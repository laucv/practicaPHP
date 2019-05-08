<?php
/**
 * PHP version 7.2
 * tests\Entity\CuestionTest.php
 */

namespace TDW\Tests\GCuest\Entity;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use TDW\GCuest\Entity\Categoria;
use TDW\GCuest\Entity\Cuestion;
use TDW\GCuest\Entity\Usuario;

/**
 * Class CuestionTest
 *
 * @group   questions
 * @coversDefaultClass \TDW\GCuest\Entity\Cuestion
 */
class CuestionTest extends TestCase
{
    /**
     * @var Usuario $user
     */
    protected static $user;

    /**
     * @var Cuestion $cuestion
     */
    protected static $cuestion;

    /** @var \Faker\Generator $faker */
    private static $faker;

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     */
    public static function setUpBeforeClass(): void
    {
        try {
            self::$faker    = Factory::create('es_ES');
            self::$user     = new Usuario(
                self::$faker->userName,
                self::$faker->email,
                self::$faker->password,
                true,
                true
            );
            self::$cuestion = new Cuestion();
        } catch (\Exception $e) {
            sprintf('EXCEPCIÓN(%d): %s', $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @covers ::__construct()
     * @covers ::getIdCuestion
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     */
    public function testConstructor(): void
    {
        self::$cuestion = new Cuestion();
        self::assertSame(0, self::$cuestion->getIdCuestion());
        self::assertEmpty(self::$cuestion->getEnunciadoDescripcion());
        self::assertNull(self::$cuestion->getCreador());
        self::assertSame(Cuestion::CUESTION_CERRADA, self::$cuestion->getEstado());
        self::assertEmpty(self::$cuestion->getCategorias());
    }

    /**
     * Implements testSetEnunciadoDescripcion
     *
     * @covers ::setEnunciadoDescripcion
     * @covers ::getEnunciadoDescripcion
     */
    public function testSetEnunciadoDescripcion(): void
    {
        $enunciadoDescripcion = self::$faker->slug(3);
        self::$cuestion->setEnunciadoDescripcion($enunciadoDescripcion);
        self::assertSame(
            $enunciadoDescripcion,
            self::$cuestion->getEnunciadoDescripcion()
        );
    }

    /**
     * Implements testAbrirCuestion
     *
     * @covers ::abrirCuestion()
     * @covers ::cerrarCuestion()
     * @covers ::getEstado
     */
    public function testAbrirCerrarCuestion(): void
    {
        self::$cuestion->abrirCuestion();
        self::assertSame(Cuestion::CUESTION_ABIERTA, self::$cuestion->getEstado());
        self::$cuestion->cerrarCuestion();
        self::assertSame(Cuestion::CUESTION_CERRADA, self::$cuestion->getEstado());
    }

    /**
     * Implements testSetEnunciadoDisponible
     *
     * @covers ::setEnunciadoDisponible
     * @covers ::isEnunciadoDisponible
     */
    public function testIsSetEnunciadoDisponible(): void
    {
        self::$cuestion->setEnunciadoDisponible(true);
        self::assertTrue(self::$cuestion->isEnunciadoDisponible());
        self::$cuestion->setEnunciadoDisponible(false);
        self::assertFalse(self::$cuestion->isEnunciadoDisponible());
    }

    /**
     * Implements testGetSetCreador
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function testGetSetCreador(): void
    {
        self::assertNull(self::$cuestion->getCreador());
        self::$cuestion->setCreador(self::$user);
        self::assertSame(self::$user, self::$cuestion->getCreador());
    }

    /**
     * Implements testGetAddContainsRemoveCategoria
     *
     * @covers ::getCategorias
     * @covers ::addCategoria
     * @covers ::containsCategoria
     * @covers ::removeCategoria
     */
    public function testGetAddContainsRemoveCategoria(): void
    {
        self::assertEmpty(self::$cuestion->getCategorias());
        $propuesta = self::$faker->slug;
        $categoria = new Categoria($propuesta);
        self::$cuestion->addCategoria($categoria);
        self::assertNotEmpty(self::$cuestion->getCategorias());
        self::assertTrue(self::$cuestion->containsCategoria($categoria));
        self::$cuestion->removeCategoria($categoria);
        self::assertFalse(self::$cuestion->containsCategoria($categoria));
        self::assertEmpty(self::$cuestion->getCategorias());
        self::assertNull(self::$cuestion->removeCategoria($categoria));
    }

    /**
     * Implements test__toString
     */
    public function test__toString(): void
    {
        $descripcion = self::$faker->slug;
        self::$cuestion->setEnunciadoDescripcion($descripcion);
        self::assertStringContainsString($descripcion, self::$cuestion->__toString());
    }

    /**
     * Implements testJsonSerialize
     */
    public function testJsonSerialize(): void
    {
        $json = json_encode(self::$cuestion);
        self::assertJson((string) $json);
    }
}
