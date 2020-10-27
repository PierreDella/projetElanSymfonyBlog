<?php

namespace App\Entity;

use App\Repository\CompositionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompositionRepository::class)
 */
class Composition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantiteIngredient;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $uniteMesure;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantiteIngredient(): ?int
    {
        return $this->quantiteIngredient;
    }

    public function setQuantiteIngredient(int $quantiteIngredient): self
    {
        $this->quantiteIngredient = $quantiteIngredient;

        return $this;
    }

    public function getUniteMesure(): ?string
    {
        return $this->uniteMesure;
    }

    public function setUniteMesure(string $uniteMesure): self
    {
        $this->uniteMesure = $uniteMesure;

        return $this;
    }
}
