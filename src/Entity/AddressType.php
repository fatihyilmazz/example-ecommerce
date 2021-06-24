<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("address_types")
 * @ORM\Entity(repositoryClass="App\Repository\AddressTypeRepository")
 */
class AddressType
{
    const BILLING_ID   = 1;
    const DELIVERY_ID  = 2;
    const STORE_ID     = 3;
    const CUSTOMER_ID  = 4;

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
