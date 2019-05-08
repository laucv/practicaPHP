<?php
/**
 * PHP version 7.2
 * src\Entity\Categoria.php
 */

namespace TDW\GCuest\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Categoria
 *
 * @ORM\Entity()
 * @ORM\Table(
 *     name="categorias"
 * )
 */
class Categoria
{
    /**
     * @var int $idCategoria
     *
     * @ORM\Id()
     * @ORM\GeneratedValue( strategy="AUTO" )
     * @ORM\Column(
     *     name="idCategoria",
     *     type="integer"
     * )
     */
    protected $idCategoria;

    /**
     * @var string $nombreCategoria
     *
     * @ORM\Column(
     *     name="nombre_categoria",
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     */
    protected $nombreCategoria;

    /**
     * @var bool $disponible
     *
     * @ORM\Column(
     *     name="disponible",
     *     type="boolean",
     *     options={ "default" = true }
     * )
     */
    protected $disponible;

    /**
     * @var ArrayCollection|Cuestion[]
     *
     * @ORM\ManyToMany(
     *     targetEntity="Cuestion",
     *     inversedBy="categorias"
     * )
     * @ORM\JoinTable(
     *   name="cuestion_has_categoria",
     *   joinColumns={
     *       @ORM\JoinColumn(
     *           name="categoria_id",
     *           referencedColumnName="idCategoria"
     *       )
     *   },
     *   inverseJoinColumns={
     *       @ORM\JoinColumn(
     *           name="cuestion_id",
     *           referencedColumnName="idCuestion"
     *       )
     *   }
     * )
     */
    protected $cuestiones;

    /**
     * Categoria constructor.
     * @param string|null $nombreCategoria
     * @param bool $disponible
     */
    public function __construct(?string $nombreCategoria = null, bool $disponible = true)
    {
        $this->nombreCategoria = $nombreCategoria;
        $this->disponible = $disponible;
        $this->cuestiones = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->idCategoria;
    }

    /**
     * @return string|null
     */
    public function getNombre(): ?string
    {
        return $this->nombreCategoria;
    }

    /**
     * @param string $nombreCategoria
     * @return Categoria
     */
    public function setNombre(string $nombreCategoria): Categoria
    {
        $this->nombreCategoria = $nombreCategoria;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisponible(): bool
    {
        return $this->disponible;
    }

    /**
     * @param bool $disponible
     * @return Categoria
     */
    public function setDisponible(bool $disponible): Categoria
    {
        $this->disponible = $disponible;
        return $this;
    }

    /**
     * @return Collection|Cuestion[]
     */
    public function getCuestiones(): Collection
    {
        return $this->cuestiones;
    }

    /**
     * @param Cuestion $cuestion
     * @return bool
     */
    public function containsCuestion(Cuestion $cuestion): bool
    {
        return $this->cuestiones->contains($cuestion);
    }

    /**
     * Añade la cuestión a la categoría
     *
     * @param Cuestion $cuestion
     * @return Categoria
     */
    public function addCuestion(Cuestion $cuestion): Categoria
    {
        if ($this->cuestiones->contains($cuestion)) {
            return $this;
        }

        $this->cuestiones->add($cuestion);
        return $this;
    }

    /**
     * Elimina la cuestión de la categoría
     *
     * @param Cuestion $cuestion
     * @return Categoria|null La Categoría o nulo, si la categoría no contiene la cuestión
     */
    public function removeCuestion(Cuestion $cuestion): ?Categoria
    {
        if (!$this->cuestiones->contains($cuestion)) {
            return null;
        }

        $this->cuestiones->removeElement($cuestion);
        return $this;
    }

    private function getIdsCuestiones(): array
    {
        /** @var ArrayCollection $cod_cuestiones */
        $cod_cuestiones = $this->getCuestiones()->isEmpty()
            ? new ArrayCollection()
            : $this->getCuestiones()->map(
                function (Cuestion $cuestion) {
                    return $cuestion->getIdCuestion();
                }
            );

        return $cod_cuestiones->getValues();
    }

    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     *
     * @return string
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
     */
    public function __toString(): string
    {
        $cod_cuestiones = $this->getIdsCuestiones();
        $txt_cuestiones = '[' . implode(', ', $cod_cuestiones) . ']';
        return '[ cuestion ' .
            '(id=' . $this->getId() . ', ' .
            'nombre="' . $this->getNombre() . '", ' .
            'disponible="' . (int) $this->isDisponible() . '", ' .
            'cuestiones="' . $txt_cuestiones . '""' .
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
                'id' => $this->getId(),
                'nombre' => $this->getNombre(),
                'disponible' => $this->isDisponible(),
                'cuestiones' => $this->getIdsCuestiones(),
            ]
        ];
    }
}
