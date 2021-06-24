<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("compared_products")
 * @ORM\Entity(repositoryClass="App\Repository\ComparedProductRepository")
 * @ORM\EntityListeners({"App\EventListener\ComparedProductEventListener"})
 */
class ComparedProduct
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comparedProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sector")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sector;

    /**
     * @ORM\Column(type="integer")
     */
    private $productId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Merchant")
     * @ORM\JoinColumn(nullable=false)
     */
    private $merchant;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var array
     */
    private $product;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Sector
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * @param Sector $sector
     */
    public function setSector(Sector $sector): void
    {
        $this->sector = $sector;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getProduct(): ?array
    {
        return $this->product;
    }

    /**
     * @param array|null $product
     */
    public function setProduct(?array $product): void
    {
        $this->product = $product;
    }

    /**
     * @return Merchant|null
     */
    public function getMerchant(): ?Merchant
    {
        return $this->merchant;
    }

    /**
     * @param Merchant|null $merchant
     */
    public function setMerchant(?Merchant $merchant): void
    {
        $this->merchant = $merchant;
    }
}
