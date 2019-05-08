<?php
/**
 * PHP version 7.2
 * src\Entity\Cuestion.php
 */

namespace TDW\GCuest\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Annotations as OA;

/**
 * Class Cuestion
 *
 * @ORM\Entity()
 * @ORM\Table(
 *     name="cuestiones",
 *     indexes={
 *         @ORM\Index(
 *             name="fk_creador_idx",
 *             columns={ "creador" }
 *         )
 *     }
 * )
 */
class Cuestion implements \JsonSerializable
{
    public const CUESTION_ABIERTA = 'abierta';
    public const CUESTION_CERRADA = 'cerrada';

    public const CUESTION_ESTADOS = [ self::CUESTION_ABIERTA, self::CUESTION_CERRADA ];

    /**
     * @var int $idCuestion
     *
     * @ORM\Id()
     * @ORM\GeneratedValue( strategy="AUTO" )
     * @ORM\Column(
     *     name="idCuestion",
     *     type="integer"
     * )
     */
    protected $idCuestion;

    /**
     * @var string|null $enunciadoDescripcion
     *
     * @ORM\Column(
     *     name="enum_descripcion",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     */
    protected $enunciadoDescripcion;

    /**
     * @var bool $enunciadoDisponible
     *
     * @ORM\Column(
     *     name="enum_disponible",
     *     type="boolean",
     *     options={ "default"=false }
     * )
     */
    protected $enunciadoDisponible;

    /**
     * @var Usuario|null $creador
     *
     * @ORM\ManyToOne(
     *     targetEntity="Usuario",
     *     inversedBy="cuestiones"
     *     )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(
     *     name="creador",
     *     referencedColumnName="id",
     *     nullable=true,
     *     onDelete="SET NULL"
     *     )
     * })
     */
    protected $creador;

    /**
     * @var string $estado
     *
     * @ORM\Column(
     *     name="estado",
     *     type="string",
     *     length=7,
     *     options={ "default"=Cuestion::CUESTION_CERRADA }
     * )
     */
    protected $estado = Cuestion::CUESTION_CERRADA;

    /**
     * @var ArrayCollection|Categoria[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Categoria",
     *     mappedBy="cuestiones"
     * )
     * @ORM\OrderBy({ "idCategoria" = "ASC" })
     */
    protected $categorias;

    /**
     * Cuestion constructor.
     *
     * @param string|null  $enunciadoDescripcion
     * @param Usuario|null $creador
     * @param bool         $enunciadoDisponible
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function __construct(
        ?string $enunciadoDescripcion = null,
        ?Usuario $creador = null,
        bool $enunciadoDisponible = false
    ) {
        $this->idCuestion = 0;
        $this->enunciadoDescripcion = $enunciadoDescripcion;
        (null !== $creador)
            ? $this->setCreador($creador)
            : null;
        $this->enunciadoDisponible = $enunciadoDisponible;
        $this->estado = self::CUESTION_CERRADA;
        $this->categorias = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getIdCuestion(): int
    {
        return $this->idCuestion;
    }

    /**
     * @return string|null
     */
    public function getEnunciadoDescripcion(): ?string
    {
        return $this->enunciadoDescripcion;
    }

    /**
     * @param string|null $enunciadoDescripcion
     * @return Cuestion
     */
    public function setEnunciadoDescripcion(?string $enunciadoDescripcion): Cuestion
    {
        $this->enunciadoDescripcion = $enunciadoDescripcion;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnunciadoDisponible(): bool
    {
        return $this->enunciadoDisponible;
    }

    /**
     * @param bool $disponible
     * @return Cuestion
     */
    public function setEnunciadoDisponible(bool $disponible): Cuestion
    {
        $this->enunciadoDisponible = $disponible;
        return $this;
    }

    /**
     * @return Usuario|null
     */
    public function getCreador(): ?Usuario
    {
        return $this->creador;
    }

    /**
     * @param Usuario|null $creador
     * @return Cuestion
     * @throws \Doctrine\ORM\ORMException
     */
    public function setCreador(?Usuario $creador): Cuestion
    {
        if ($creador && !$creador->isMaestro()) {
            throw new \Doctrine\ORM\ORMException('Creador debe ser maestro');
        }
        $this->creador = $creador;
        return $this;
    }

    /**
     * @return string
     */
    public function getEstado(): string
    {
        return $this->estado;
    }

    /**
     * @return Cuestion
     */
    public function abrirCuestion(): Cuestion
    {
        $this->estado = self::CUESTION_ABIERTA;
        return $this;
    }

    /**
     * @return Cuestion
     */
    public function cerrarCuestion(): Cuestion
    {
        $this->estado = self::CUESTION_CERRADA;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getCategorias(): Collection
    {
        return $this->categorias;
    }

    /**
     * @param Categoria $categoria
     * @return bool
     */
    public function containsCategoria(Categoria $categoria): bool
    {
        return $this->categorias->contains($categoria);
    }

    /**
     * Añade la categoría a la cuestión
     *
     * @param Categoria $categoria
     * @return Cuestion
     */
    public function addCategoria(Categoria $categoria): Cuestion
    {
        if ($this->categorias->contains($categoria)) {
            return $this;
        }

        $this->categorias->add($categoria);
        return $this;
    }

    /**
     * Elimina la categoría identificado por $categoria de la cuestión
     *
     * @param Categoria $categoria
     * @return Cuestion|null La cuestión o nulo, si la cuestión no contiene la categoría
     */
    public function removeCategoria(Categoria $categoria): ?Cuestion
    {
        if (!$this->categorias->contains($categoria)) {
            return null;
        }

        $this->categorias->removeElement($categoria);
        return $this;
    }

    /**
     * Get an array with the categories identifiers
     *
     * @return array
     */
    private function getIdsCategorias(): array
    {
        /** @var ArrayCollection $cod_categorias */
        $cod_categorias = $this->getCategorias()->isEmpty()
            ? new ArrayCollection()
            : $this->getCategorias()->map(
                function (Categoria $categoria) {
                    return $categoria->getId();
                }
            );

        return $cod_categorias->getValues();
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString()
    {
        $cod_categorias = $this->getIdsCategorias();
        $txt_categorias = '[ ' . implode(', ', $cod_categorias) . ' ]';
        return '[ cuestion ' .
            '(id=' . $this->getIdCuestion() . ', ' .
            'enunciadoDescripcion="' . $this->getEnunciadoDescripcion() . '", ' .
            'enunciadoDisponible=' . (int) $this->isEnunciadoDisponible() . ', ' .
            'creador=' . ($this->getCreador() ?? 0) . ', ' .
            'estado="' . $this->getEstado() . '" ' .
            'categorias=' . $txt_categorias .
            ') ]';
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'cuestion' => [
                'idCuestion' => $this->getIdCuestion(),
                'enunciadoDescripcion' => $this->getEnunciadoDescripcion(),
                'enunciadoDisponible' => $this->isEnunciadoDisponible(),
                'creador' => $this->getCreador() ? $this->getCreador()->getId() : null,
                'estado' => $this->getEstado(),
                'categorias' => $this->getIdsCategorias(),
            ]
        ];
    }
}

/**
 * Question definition
 *
 * @OA\Schema(
 *     schema = "Question",
 *     type   = "object",
 *     required = { "idCuestion" },
 *     @OA\Property(
 *          property    = "idCuestion",
 *          description = "Question Id",
 *          format      = "int64",
 *          type        = "integer"
 *      ),
 *      @OA\Property(
 *          property    = "enunciadoDescripcion",
 *          description = "Question description",
 *          type        = "string"
 *      ),
 *      @OA\Property(
 *          property    = "enunciadoDisponible",
 *          description = "Denotes if question is available",
 *          type        = "boolean"
 *      ),
 *      @OA\Property(
 *          property    = "creador",
 *          description = "Question's id creator",
 *          format      = "int64",
 *          type        = "integer"
 *      ),
 *      @OA\Property(
 *          property    = "estado",
 *          description = "Question's state",
 *          type        = "string"
 *      ),
 *      example = {
 *          "cuestion" = {
 *              "idCuestion"           = 805,
 *              "enunciadoDescripcion" = "Question description",
 *              "enunciadoDisponible"  = true,
 *              "creador"              = 7,
 *              "estado"               = "abierta"
 *          }
 *     }
 * )
 */

/**
 * Question data definition
 *
 * @OA\Schema(
 *      schema          = "QuestionData",
 *      @OA\Property(
 *          property    = "enunciadoDescripcion",
 *          description = "Question description",
 *          type        = "string"
 *      ),
 *      @OA\Property(
 *          property    = "enunciadoDisponible",
 *          description = "Denotes if question is available",
 *          type        = "boolean"
 *      ),
 *      @OA\Property(
 *          property    = "creador",
 *          description = "Question's id creator",
 *          format      = "int64",
 *          type        = "integer"
 *      ),
 *      @OA\Property(
 *          property    = "estado",
 *          description = "Question status",
 *          type        = "string"
 *      ),
 *      example = {
 *          "enunciadoDescripcion" = "Question description",
 *          "enunciadoDisponible"  = true,
 *          "creador"              = 501,
 *          "estado"               = "abierta"
 *      }
 * )
 */

/**
 * Question array definition
 *
 * @OA\Schema(
 *     schema           = "QuestionsArray",
 *     @OA\Property(
 *          property    = "cuestiones",
 *          description = "Questions array",
 *          type        = "array",
 *          @OA\Items(
 *              ref     = "#/components/schemas/Question"
 *          )
 *     )
 * )
 */
