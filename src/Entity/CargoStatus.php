<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("cargo_statuses")
 * @ORM\Entity(repositoryClass="App\Repository\CargoStatusRepository")
 */
class CargoStatus
{
    const ID_PENDING     = 1;
    const ID_SHIPPED     = 2;
    const ID_DELIVERED   = 3;
    const ID_REJECT      = 4;
    const ID_UNDELIVERED = 5;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
