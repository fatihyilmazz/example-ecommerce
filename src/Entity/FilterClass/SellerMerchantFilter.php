<?php

namespace App\Entity\FilterClass;

class SellerMerchantFilter
{
    const DEFAULT_LIMIT = 10;

    const ORDER_BY_OPTION_IDS_ID = 1;
    const ORDER_BY_OPTION_STATUS_ID = 2;
    const ORDER_BY_OPTION_NUMBER_OF_PRODUCT_ID = 3;
    const ORDER_BY_OPTION_NUMBER_OF_ORDER_ID = 4;

    const SORT_BY_OPTION_ASCENDING_ID = 1;
    const SORT_BY_OPTION_DESCENDING_ID = 2;

    /**
     * @var array|null
     */
    private $sellerMerchantIds;

    /**
     * @var bool|null
     */
    private $isActive;

    /**
     * @var bool|null
     */
    private $paginate = true;

    /**
     * @var int
     */
    private $orderByOption = self::ORDER_BY_OPTION_IDS_ID;

    /**
     * @var int|null
     */
    private $sectorId;

    /**
     * @var string|null
     */
    private $orderBy = 'M.id';

    /**
     * @var int
     */
    private $sortByOption = self::SORT_BY_OPTION_DESCENDING_ID;

    /**
     * @var string|null
     */
    private $sortBy = 'DESC';

    /**
     * @var int|null
     */
    private $limit;

    /**
     * @var int|null
     */
    private $page;

    /**
     * @return array|null
     */
    public function getSellerMerchantIds(): ?array
    {
        return $this->sellerMerchantIds;
    }

    /**
     * @param array|null $sellerMerchantIds
     */
    public function setSellerMerchantIds(?array $sellerMerchantIds): void
    {
        $this->sellerMerchantIds = $sellerMerchantIds;
    }

    /**
     * @return bool|null
     */
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @param bool|null $isActive
     */
    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return bool|null
     */
    public function getPaginate(): ?bool
    {
        return $this->paginate;
    }

    /**
     * @param bool|null $paginate
     */
    public function setPaginate(?bool $paginate): void
    {
        $this->paginate = $paginate;
    }

    /**
     * @return int
     */
    public function getOrderByOption(): int
    {
        return $this->orderByOption;
    }

    /**
     * @param int $orderByOption
     */
    public function setOrderByOption(int $orderByOption): void
    {
        $this->orderByOption = $orderByOption;
    }

    /**
     * @return int|null
     */
    public function getSectorId(): ?int
    {
        return $this->sectorId;
    }

    /**
     * @param int|null $sectorId
     */
    public function setSectorId(?int $sectorId): void
    {
        $this->sectorId = $sectorId;
    }

    /**
     * @return string|null
     */
    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    /**
     * @param string|null $orderBy
     */
    public function setOrderBy(?string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }
    /**
     * @return int
     */
    public function getSortByOption(): int
    {
        return $this->sortByOption;
    }

    /**
     * @param int $sortByOption
     */
    public function setSortByOption(int $sortByOption): void
    {
        $this->sortByOption = $sortByOption;
    }

    /**
     * @return string|null
     */
    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    /**
     * @param string|null $sortBy
     */
    public function setSortBy(?string $sortBy): void
    {
        $this->sortBy = $sortBy;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     */
    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int|null $page
     */
    public function setPage(?int $page): void
    {
        $this->page = $page;
    }
}
