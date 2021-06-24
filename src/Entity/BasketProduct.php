<?php

namespace App\Entity;

use App\Entity\Custom\Campaign;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table("basket_products")
 * @ORM\Entity(repositoryClass="App\Repository\BasketProductRepository")
 * @ORM\EntityListeners({"App\EventListener\BasketProductEventListener"})
 *
 * @Serializer\ExclusionPolicy("all")
 */
class BasketProduct
{
    const NO_CAMPAIGN = 0;
    const CAMPAIGN_APPLIED = 1;
    const CAMPAIGN_NOT_APPLIED = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Basket", inversedBy="basketProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $basket;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Merchant")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Serializer\Expose()
     */
    private $merchant;

    /**
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose()
     */
    private $productId;

    /**
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose()
     */
    private $quantity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var array|null
     */
    private $product;

    /**
     * @var float
     *
     * @Serializer\Expose()
     */
    private $unitPriceWithoutTax;

    /**
     * @var float
     *
     * @Serializer\Expose()
     */
    private $unitPrice;

    /**
     * @var float
     *
     * @Serializer\Expose()
     */
    private $unitPriceTL;

    /**
     * @var float
     *
     * @Serializer\Expose()
     */
    private $totalPriceWithoutTax;

    /**
     * @var float
     *
     * @Serializer\Expose()
     */
    private $totalPrice;

    /**
     * @var float
     *
     * @Serializer\Expose()
     */
    private $totalPriceTL;

    /**
     * @var float
     */
    private $exchangeRate;

    /**
     * @var float
     */
    private $desi;

    /**
     * @var float
     */
    private $cargoPrice;

    /**
     * @var float
     */
    private $cargoPriceTL;

    /**
     * @var Campaign|null
     */
    private $campaign;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBasket(): ?Basket
    {
        return $this->basket;
    }

    public function setBasket(?Basket $basket): self
    {
        $this->basket = $basket;

        return $this;
    }

    public function getMerchant(): ?Merchant
    {
        return $this->merchant;
    }

    public function setMerchant(?Merchant $merchant): self
    {
        $this->merchant = $merchant;

        return $this;
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

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
     * @return float
     */
    public function getUnitPriceWithoutTax(): float
    {
        return $this->unitPriceWithoutTax;
    }

    /**
     * @param float $unitPriceWithoutTax
     */
    public function setUnitPriceWithoutTax(float $unitPriceWithoutTax): void
    {
        $this->unitPriceWithoutTax = $unitPriceWithoutTax;
    }

    /**
     * @return float
     */
    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    /**
     * @param float $unitPrice
     */
    public function setUnitPrice(float $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return float
     */
    public function getUnitPriceTL(): float
    {
        return $this->unitPriceTL;
    }

    /**
     * @param float $unitPriceTL
     */
    public function setUnitPriceTL(float $unitPriceTL): void
    {
        $this->unitPriceTL = $unitPriceTL;
    }

    /**
     * @return float
     */
    public function getTotalPriceWithoutTax(): float
    {
        return $this->totalPriceWithoutTax;
    }

    /**
     * @param float $totalPriceWithoutTax
     */
    public function setTotalPriceWithoutTax(float $totalPriceWithoutTax): void
    {
        $this->totalPriceWithoutTax = $totalPriceWithoutTax;
    }

    /**
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    /**
     * @param float $totalPrice
     */
    public function setTotalPrice(float $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    /**
     * @return float
     */
    public function getTotalPriceTL(): float
    {
        return $this->totalPriceTL;
    }

    /**
     * @param float $totalPriceTL
     */
    public function setTotalPriceTL(float $totalPriceTL): void
    {
        $this->totalPriceTL = $totalPriceTL;
    }

    /**
     * @return float
     */
    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }

    /**
     * @param float $exchangeRate
     */
    public function setExchangeRate(float $exchangeRate): void
    {
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @return float
     */
    public function getDesi(): float
    {
        return $this->desi;
    }

    /**
     * @param float $desi
     */
    public function setDesi(float $desi): void
    {
        $this->desi = $desi;
    }

    /**
     * @return float
     */
    public function getCargoPrice(): float
    {
        return $this->cargoPrice;
    }

    /**
     * @param float $cargoPrice
     */
    public function setCargoPrice(float $cargoPrice): void
    {
        $this->cargoPrice = $cargoPrice;
    }

    /**
     * @return float
     */
    public function getCargoPriceTL(): float
    {
        return $this->cargoPriceTL;
    }

    /**
     * @param float $cargoPriceTL
     */
    public function setCargoPriceTL(float $cargoPriceTL): void
    {
        $this->cargoPriceTL = $cargoPriceTL;
    }

    /**
     * @return Campaign|null
     */
    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    /**
     * @param Campaign $campaign
     */
    public function setCampaign(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }
}
