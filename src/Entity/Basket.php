<?php

namespace App\Entity;

use App\DTO\BasketSummaryDTO;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table("baskets")
 * @ORM\Entity(repositoryClass="App\Repository\BasketRepository")
 * @ORM\EntityListeners({"App\EventListener\BasketEventListener"})
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Basket
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="baskets", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sector")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sector;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Address")
     * @ORM\JoinColumn(nullable=true)
     */
    private $shippingAddress;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Address")
     * @ORM\JoinColumn(nullable=true)
     */
    private $billingAddress;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BasketProduct", mappedBy="basket", orphanRemoval=true)
     *
     * @Serializer\Expose()
     */
    private $basketProducts;

    /**
     * @var BasketSummaryDTO
     *
     * @Serializer\Expose()
     */
    private $summary;

    /**
     * @var array
     */
    private $conflictProducts;

    public function __construct()
    {
        $this->basketProducts = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Sector|null
     */
    public function getSector(): ?Sector
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

    /**
     * @return Address|null
     */
    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    /**
     * @param Address|null $shippingAddress
     *
     * @return $this
     */
    public function setShippingAddress(?Address $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * @return Address|null
     */
    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    /**
     * @param Address|null $billingAddress
     *
     * @return $this
     */
    public function setBillingAddress(?Address $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|BasketProduct[]
     */
    public function getBasketProducts(): Collection
    {
        return $this->basketProducts;
    }

    /**
     * @param Collection $basketProducts
     *
     * @return Collection|BasketProduct[]
     */
    public function setBasketProducts(Collection $basketProducts): Collection
    {
        return $this->basketProducts = $basketProducts;
    }

    /**
     * @param BasketProduct $basketProduct
     *
     * @return $this
     */
    public function addBasketProduct(BasketProduct $basketProduct): self
    {
        if (!$this->basketProducts->contains($basketProduct)) {
            $this->basketProducts[] = $basketProduct;
            $basketProduct->setBasket($this);
        }

        return $this;
    }

    /**
     * @param BasketProduct $basketProduct
     *
     * @return $this
     */
    public function removeBasketProduct(BasketProduct $basketProduct): self
    {
        if ($this->basketProducts->contains($basketProduct)) {
            $this->basketProducts->removeElement($basketProduct);
            // set the owning side to null (unless already changed)
            if ($basketProduct->getBasket() === $this) {
                $basketProduct->setBasket(null);
            }
        }

        return $this;
    }

    /**
     * @return BasketSummaryDTO
     */
    public function getSummary(): BasketSummaryDTO
    {
        return $this->summary;
    }

    /**
     * @param BasketSummaryDTO $summary
     *
     * @return Basket
     */
    public function setSummary(BasketSummaryDTO $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @param $conflictProduct
     *
     * @return $this
     */
    public function addConflictProduct($conflictProduct): self
    {
        if (empty($this->conflictProducts)) {
            $this->conflictProducts[] = $conflictProduct;
        } else {
            if (!in_array($conflictProduct, $this->conflictProducts)) {
                $this->conflictProducts[] = $conflictProduct;
            }
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getConflictProducts(): ?array
    {
        return $this->conflictProducts ?? [];
    }

    /**
     * @return bool
     */
    public function hasConflictProduct(): bool
    {
        return count($this->conflictProducts ?? []) > 0;
    }
}
