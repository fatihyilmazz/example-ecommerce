<?php

namespace App\Entity\Custom;

class Campaign
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $discountTypeId;

    /**
     * @var float
     */
    private $discount;

    /**
     * @var int|null
     */
    private $minQuantity;

    /**
     * @var int|null
     */
    private $maxQuantity;

    /**
     * @var float
     */
    private $discountAmount;

    /**
     * @var float
     */
    private $discountAmountTL;

    /**
     * @var int
     */
    private $quantityForCampaign;

    /**
     * @var float
     */
    private $merchantPrice;

    /**
     * @var float
     */
    private $unitPriceWithoutTaxForCampaign;

    /**
     * @var float
     */
    private $unitPriceForCampaign;

    /**
     * @var float
     */
    private $totalPriceWithoutTaxForCampaign;

    /**
     * @var float
     */
    private $totalTaxForCampaign;

    /**
     * @var float
     */
    private $totalPriceForCampaign;

    /**
     * @var float
     */
    private $unitPriceWithoutTaxForCampaignTL;

    /**
     * @var float
     */
    private $unitPriceForCampaignTL;

    /**
     * @var float
     */
    private $totalPriceWithoutTaxForCampaignTL;

    /**
     * @var float
     */
    private $totalTaxForCampaignTL;

    /**
     * @var float
     */
    private $totalPriceForCampaignTL;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $unitPriceWithoutTax;

    /**
     * @var float
     */
    private $unitPrice;

    /**
     * @var float
     */
    private $totalPriceWithoutTax;

    /**
     * @var float
     */
    private $totalTax;

    /**
     * @var float
     */
    private $totalPrice;

    /**
     * @var float
     */
    private $unitPriceWithoutTaxTL;

    /**
     * @var float
     */
    private $unitPriceTL;

    /**
     * @var float
     */
    private $totalPriceWithoutTaxTL;

    /**
     * @var float
     */
    private $totalTaxTL;

    /**
     * @var float
     */
    private $totalPriceTL;

    /**
     * @var float
     */
    private $grandSubTotal;

    /**
     * @var float
     */
    private $grandTotalTax;

    /**
     * @var float
     */
    private $grandTotalDiscountAmount;

    /**
     * @var float
     */
    private $grandTotal;

    /**
     * @var float
     */
    private $grandSubTotalTL;

    /**
     * @var float
     */
    private $grandTotalTaxTL;

    /**
     * @var float
     */
    private $grandTotalDiscountAmountTL;

    /**
     * @var float
     */
    private $grandTotalTL;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getDiscountTypeId(): int
    {
        return $this->discountTypeId;
    }

    /**
     * @param int $discountTypeId
     */
    public function setDiscountTypeId(int $discountTypeId): void
    {
        $this->discountTypeId = $discountTypeId;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    /**
     * @return float
     */
    public function getDiscountAmount(): float
    {
        return $this->discountAmount;
    }

    /**
     * @param float $discountAmount
     */
    public function setDiscountAmount(float $discountAmount): void
    {
        $this->discountAmount = $discountAmount;
    }

    /**
     * @return float
     */
    public function getDiscountAmountTL(): float
    {
        return $this->discountAmountTL;
    }

    /**
     * @param float $discountAmountTL
     */
    public function setDiscountAmountTL(float $discountAmountTL): void
    {
        $this->discountAmountTL = $discountAmountTL;
    }

    /**
     * @return int
     */
    public function getQuantityForCampaign(): int
    {
        return $this->quantityForCampaign;
    }

    /**
     * @param int $quantityForCampaign
     */
    public function setQuantityForCampaign(int $quantityForCampaign): void
    {
        $this->quantityForCampaign = $quantityForCampaign;
    }

    /**
     * @return float
     */
    public function getUnitPriceWithoutTaxForCampaign(): float
    {
        return $this->unitPriceWithoutTaxForCampaign;
    }

    /**
     * @param float $unitPriceWithoutTaxForCampaign
     */
    public function setUnitPriceWithoutTaxForCampaign(float $unitPriceWithoutTaxForCampaign): void
    {
        $this->unitPriceWithoutTaxForCampaign = $unitPriceWithoutTaxForCampaign;
    }

    /**
     * @return float|null
     */
    public function getMerchantPrice(): ?float
    {
        return $this->merchantPrice;
    }

    /**
     * @param float $merchantPrice
     */
    public function setMerchantPrice(float $merchantPrice): void
    {
        $this->merchantPrice = $merchantPrice;
    }

    /**
     * @return float
     */
    public function getUnitPriceForCampaign(): float
    {
        return $this->unitPriceForCampaign;
    }

    /**
     * @param float $unitPriceForCampaign
     */
    public function setUnitPriceForCampaign(float $unitPriceForCampaign): void
    {
        $this->unitPriceForCampaign = $unitPriceForCampaign;
    }

    /**
     * @return float
     */
    public function getTotalPriceWithoutTaxForCampaign(): float
    {
        return $this->totalPriceWithoutTaxForCampaign;
    }

    /**
     * @param float $totalPriceWithoutTaxForCampaign
     */
    public function setTotalPriceWithoutTaxForCampaign(float $totalPriceWithoutTaxForCampaign): void
    {
        $this->totalPriceWithoutTaxForCampaign = $totalPriceWithoutTaxForCampaign;
    }

    /**
     * @return float
     */
    public function getTotalTaxForCampaign(): float
    {
        return $this->totalTaxForCampaign;
    }

    /**
     * @param float $totalTaxForCampaign
     */
    public function setTotalTaxForCampaign(float $totalTaxForCampaign): void
    {
        $this->totalTaxForCampaign = $totalTaxForCampaign;
    }

    /**
     * @return float
     */
    public function getTotalPriceForCampaign(): float
    {
        return $this->totalPriceForCampaign;
    }

    /**
     * @param float $totalPriceForCampaign
     */
    public function setTotalPriceForCampaign(float $totalPriceForCampaign): void
    {
        $this->totalPriceForCampaign = $totalPriceForCampaign;
    }

    /**
     * @return float
     */
    public function getUnitPriceWithoutTaxForCampaignTL(): float
    {
        return $this->unitPriceWithoutTaxForCampaignTL;
    }

    /**
     * @param float $unitPriceWithoutTaxForCampaignTL
     */
    public function setUnitPriceWithoutTaxForCampaignTL(float $unitPriceWithoutTaxForCampaignTL): void
    {
        $this->unitPriceWithoutTaxForCampaignTL = $unitPriceWithoutTaxForCampaignTL;
    }

    /**
     * @return float
     */
    public function getUnitPriceForCampaignTL(): float
    {
        return $this->unitPriceForCampaignTL;
    }

    /**
     * @param float $unitPriceForCampaignTL
     */
    public function setUnitPriceForCampaignTL(float $unitPriceForCampaignTL): void
    {
        $this->unitPriceForCampaignTL = $unitPriceForCampaignTL;
    }

    /**
     * @return float
     */
    public function getTotalPriceWithoutTaxForCampaignTL(): float
    {
        return $this->totalPriceWithoutTaxForCampaignTL;
    }

    /**
     * @param float $totalPriceWithoutTaxForCampaignTL
     */
    public function setTotalPriceWithoutTaxForCampaignTL(float $totalPriceWithoutTaxForCampaignTL): void
    {
        $this->totalPriceWithoutTaxForCampaignTL = $totalPriceWithoutTaxForCampaignTL;
    }

    /**
     * @return float
     */
    public function getTotalTaxForCampaignTL(): float
    {
        return $this->totalTaxForCampaignTL;
    }

    /**
     * @param float $totalTaxForCampaignTL
     */
    public function setTotalTaxForCampaignTL(float $totalTaxForCampaignTL): void
    {
        $this->totalTaxForCampaignTL = $totalTaxForCampaignTL;
    }

    /**
     * @return float
     */
    public function getTotalPriceForCampaignTL(): float
    {
        return $this->totalPriceForCampaignTL;
    }

    /**
     * @param float $totalPriceForCampaignTL
     */
    public function setTotalPriceForCampaignTL(float $totalPriceForCampaignTL): void
    {
        $this->totalPriceForCampaignTL = $totalPriceForCampaignTL;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
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
    public function getTotalTax(): float
    {
        return $this->totalTax;
    }

    /**
     * @param float $totalTax
     */
    public function setTotalTax(float $totalTax): void
    {
        $this->totalTax = $totalTax;
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
    public function getUnitPriceWithoutTaxTL(): float
    {
        return $this->unitPriceWithoutTaxTL;
    }

    /**
     * @param float $unitPriceWithoutTaxTL
     */
    public function setUnitPriceWithoutTaxTL(float $unitPriceWithoutTaxTL): void
    {
        $this->unitPriceWithoutTaxTL = $unitPriceWithoutTaxTL;
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
    public function getTotalPriceWithoutTaxTL(): float
    {
        return $this->totalPriceWithoutTaxTL;
    }

    /**
     * @param float $totalPriceWithoutTaxTL
     */
    public function setTotalPriceWithoutTaxTL(float $totalPriceWithoutTaxTL): void
    {
        $this->totalPriceWithoutTaxTL = $totalPriceWithoutTaxTL;
    }

    /**
     * @return float
     */
    public function getTotalTaxTL(): float
    {
        return $this->totalTaxTL;
    }

    /**
     * @param float $totalTaxTL
     */
    public function setTotalTaxTL(float $totalTaxTL): void
    {
        $this->totalTaxTL = $totalTaxTL;
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
    public function getGrandSubTotal(): float
    {
        return $this->grandSubTotal;
    }

    /**
     * @param float $grandSubTotal
     */
    public function setGrandSubTotal(float $grandSubTotal): void
    {
        $this->grandSubTotal = $grandSubTotal;
    }

    /**
     * @return float
     */
    public function getGrandTotalTax(): float
    {
        return $this->grandTotalTax;
    }

    /**
     * @param float $grandTotalTax
     */
    public function setGrandTotalTax(float $grandTotalTax): void
    {
        $this->grandTotalTax = $grandTotalTax;
    }

    /**
     * @return float
     */
    public function getGrandTotal(): float
    {
        return $this->grandTotal;
    }

    /**
     * @return float
     */
    public function getGrandTotalDiscountAmount(): float
    {
        return $this->grandTotalDiscountAmount;
    }

    /**
     * @param float $grandTotalDiscountAmount
     */
    public function setGrandTotalDiscountAmount(float $grandTotalDiscountAmount): void
    {
        $this->grandTotalDiscountAmount = $grandTotalDiscountAmount;
    }

    /**
     * @param float $grandTotal
     */
    public function setGrandTotal(float $grandTotal): void
    {
        $this->grandTotal = $grandTotal;
    }

    /**
     * @return float
     */
    public function getGrandSubTotalTL(): float
    {
        return $this->grandSubTotalTL;
    }

    /**
     * @param float $grandSubTotalTL
     */
    public function setGrandSubTotalTL(float $grandSubTotalTL): void
    {
        $this->grandSubTotalTL = $grandSubTotalTL;
    }

    /**
     * @return float
     */
    public function getGrandTotalTaxTL(): float
    {
        return $this->grandTotalTaxTL;
    }

    /**
     * @param float $grandTotalTaxTL
     */
    public function setGrandTotalTaxTL(float $grandTotalTaxTL): void
    {
        $this->grandTotalTaxTL = $grandTotalTaxTL;
    }

    /**
     * @return float
     */
    public function getGrandTotalDiscountAmountTL(): float
    {
        return $this->grandTotalDiscountAmountTL;
    }

    /**
     * @param float $grandTotalDiscountAmountTL
     */
    public function setGrandTotalDiscountAmountTL(float $grandTotalDiscountAmountTL): void
    {
        $this->grandTotalDiscountAmountTL = $grandTotalDiscountAmountTL;
    }

    /**
     * @return float
     */
    public function getGrandTotalTL(): float
    {
        return $this->grandTotalTL;
    }

    /**
     * @param float $grandTotalTL
     */
    public function setGrandTotalTL(float $grandTotalTL): void
    {
        $this->grandTotalTL = $grandTotalTL;
    }

    /**
     * @return int|null
     */
    public function getMinQuantity(): ?int
    {
        return $this->minQuantity;
    }

    /**
     * @param int|null $minQuantity
     */
    public function setMinQuantity(?int $minQuantity): void
    {
        $this->minQuantity = $minQuantity;
    }

    /**
     * @return int|null
     */
    public function getMaxQuantity(): ?int
    {
        return $this->maxQuantity;
    }

    /**
     * @param int|null $maxQuantity
     */
    public function setMaxQuantity(?int $maxQuantity): void
    {
        $this->maxQuantity = $maxQuantity;
    }
}
