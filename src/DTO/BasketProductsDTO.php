<?php

namespace App\DTO;

use App\Entity\BasketProduct;

class BasketProductsDTO
{
    /**
     * @var BasketProduct[]|null
     */
    private $basketProducts;

    /**
     * @return BasketProduct[]|null
     */
    public function getBasketProducts(): ?array
    {
        return $this->basketProducts;
    }

    /**
     * @param BasketProduct[]|null $basketProducts
     */
    public function setBasketProducts(?array $basketProducts): void
    {
        $this->basketProducts = $basketProducts;
    }
}
