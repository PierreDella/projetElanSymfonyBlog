<?php

namespace App\Entity;

use App\Repository\CategorieRecetteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategorieRecetteRepository::class)
 */
class CategorieRecette
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $nomTypePlat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomTypePlat(): ?string
    {
        return $this->nomTypePlat;
    }

    public function setNomTypePlat(string $nomTypePlat): self
    {
        $this->nomTypePlat = $nomTypePlat;

        return $this;
    }
}
