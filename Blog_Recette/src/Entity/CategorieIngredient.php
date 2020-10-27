<?php

namespace App\Entity;

use App\Repository\CategorieIngredientRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategorieIngredientRepository::class)
 */
class CategorieIngredient
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=75)
     */
    private $nomTypeIngredient;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomTypeIngredient(): ?string
    {
        return $this->nomTypeIngredient;
    }

    public function setNomTypeIngredient(string $nomTypeIngredient): self
    {
        $this->nomTypeIngredient = $nomTypeIngredient;

        return $this;
    }
}
