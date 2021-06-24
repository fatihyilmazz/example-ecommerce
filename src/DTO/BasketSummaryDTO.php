<?php

namespace App\DTO;


class BasketSummaryDTO
{
    /**
     * @var float
     */
    private $subTotal;

    /**
     * @var float
     */
    private $grandTotalKDV;

    /**
     * @var float
     */
    private $grandTotal;

    /**
     * @var float
     */
    private $subTotalTL;

    /**
     * @var float
     */
    private $grandTotalKDVTL;

    /**
     * @var float
     */
    private $grandTotalTL;

    /**
     * @var float
     */
    private $couponDiscount;

    /**
     * @var float
     */
    private $couponDiscountTL;

    /**
     * @var float
     */
    private $campaignDiscount = 0;

    /**
     * @var float
     */
    private $campaignDiscountTL = 0;

    /**
     * @var float
     */
    private $totalDiscount = 0;

    /**
     * @var float
     */
    private $totalDiscountTL = 0;

    /**
     * @var float
     */
    private $cargoPrice;

    /**
     * @var float
     */
    private $cargoPriceTL;

    /**
     * @var bool
     */
    private $isAllBircomProducts;

    public function getSubTotal(): float
    {
        return $this->subTotal;
    }

    public function setSubTotal(float $subTotal): self
    {
        $this->subTotal = $subTotal;

        return $this;
    }

    public function getGrandTotalKDV(): float
    {
        return $this->grandTotalKDV;
    }

    public function setGrandTotalKDV(float $grandTotalKDV): self
    {
        $this->grandTotalKDV = $grandTotalKDV;

        return $this;
    }

    public function getGrandTotal(): float
    {
        return $this->grandTotal;
    }

    public function setGrandTotal(float $grandTotal): self
    {
        $this->grandTotal = $grandTotal;

        return $this;
    }

    public function getSubTotalTL(): float
    {
        return $this->subTotalTL;
    }

    public function setSubTotalTL(float $subTotalTL): self
    {
        $this->subTotalTL = $subTotalTL;

        return $this;
    }

    public function getGrandTotalKDVTL(): float
    {
        return $this->grandTotalKDVTL;
    }

    public function setGrandTotalKDVTL(float $grandTotalKDVTL): self
    {
        $this->grandTotalKDVTL = $grandTotalKDVTL;

        return $this;
    }

    public function getGrandTotalTL(): float
    {
        return $this->grandTotalTL;
    }

    public function setGrandTotalTL(float $grandTotalTL): self
    {
        $this->grandTotalTL = $grandTotalTL;

        return $this;
    }

    public function getCouponDiscount(): float
    {
        return $this->couponDiscount;
    }

    public function setCouponDiscount(float $couponDiscount): self
    {
        $this->couponDiscount = $couponDiscount;

        return $this;
    }

    public function getCouponDiscountTL(): float
    {
        return $this->couponDiscountTL;
    }

    public function setCouponDiscountTL(float $couponDiscountTL): self
    {
        $this->couponDiscountTL = $couponDiscountTL;

        return $this;
    }

    public function getCampaignDiscount(): float
    {
        return $this->campaignDiscount;
    }

    public function setCampaignDiscount(float $campaignDiscount): void
    {
        $this->campaignDiscount = $campaignDiscount;
    }

    public function getCampaignDiscountTL(): float
    {
        return $this->campaignDiscountTL;
    }


    public function setCampaignDiscountTL(float $campaignDiscountTL): void
    {
        $this->campaignDiscountTL = $campaignDiscountTL;
    }


    public function getTotalDiscount(): float
    {
        return $this->totalDiscount;
    }


    public function setTotalDiscount(float $totalDiscount): void
    {
        $this->totalDiscount = $totalDiscount;
    }

    public function getTotalDiscountTL(): float
    {
        return $this->totalDiscountTL;
    }

    public function setTotalDiscountTL(float $totalDiscountTL): void
    {
        $this->totalDiscountTL = $totalDiscountTL;
    }

    public function getCargoPrice(): float
    {
        return $this->cargoPrice;
    }

    public function setCargoPrice(float $cargoPrice): self
    {
        $this->cargoPrice = $cargoPrice;

        return $this;
    }

    public function getCargoPriceTL(): float
    {
        return $this->cargoPriceTL;
    }

    public function setCargoPriceTL(float $cargoPriceTL): self
    {
        $this->cargoPriceTL = $cargoPriceTL;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllBircomProducts(): bool
    {
        return $this->isAllBircomProducts;
    }

    /**
     * @param bool $isAllBircomProducts
     */
    public function setIsAllBircomProducts(bool $isAllBircomProducts): void
    {
        $this->isAllBircomProducts = $isAllBircomProducts;
    }
}
