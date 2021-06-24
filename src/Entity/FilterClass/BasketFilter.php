<?php

namespace App\Entity\FilterClass;

class BasketFilter
{
    const DEFAULT_LIMIT = 10;

    /**
     * @var int|null
     */
    private $basketId;

    /**
     * @var string|null
     */
    private $userFullName;

    /**
     * @var int|null
     */
    private $productId;

    /**
     * @var array|null
     */
    private $sellerMerchantIds;

    /**
     * @var array|null
     */
    private $buyerMerchantIds;

    /**
     * @var array|null
     */
    private $userIds;

    /**
     * @var array|null
     */
    private $sectorIds;

    /**
     * @var int|null
     */
    private $minQuantity;

    /**
     * @var int|null
     */
    private $maxQuantity;

    /**
     * @var \DateTime|null
     */
    private $startedAt;

    /**
     * @var \DateTime|null
     */
    private $endAt;

    /**
     * @var bool|null
     */
    private $addSummary = true;

    /**
     * @var bool|null
     */
    private $paginate = true;

    /**
     * @var string|null
     */
    private $orderBy = 'id';

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
     * @return int|null
     */
    public function getBasketId(): ?int
    {
        return $this->basketId;
    }

    /**
     * @param int|null $basketId
     */
    public function setBasketId(?int $basketId): void
    {
        $this->basketId = $basketId;
    }

    /**
     * @return string|null
     */
    public function getUserFullName(): ?string
    {
        return $this->userFullName;
    }

    /**
     * @param string|null $userFullName
     */
    public function setUserFullName(?string $userFullName): void
    {
        $this->userFullName = $userFullName;
    }

    /**
     * @return int|null
     */
    public function getProductId(): ?int
    {
        return $this->productId;
    }

    /**
     * @param int|null $productId
     */
    public function setProductId(?int $productId): void
    {
        $this->productId = $productId;
    }

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
     * @return array|null
     */
    public function getBuyerMerchantIds(): ?array
    {
        return $this->buyerMerchantIds;
    }

    /**
     * @param array|null $buyerMerchantIds
     */
    public function setBuyerMerchantIds(?array $buyerMerchantIds): void
    {
        $this->buyerMerchantIds = $buyerMerchantIds;
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

    /**
     * @return array|null
     */
    public function getUserIds(): ?array
    {
        return $this->userIds;
    }

    /**
     * @param array|null $userIds
     */
    public function setUserIds(?array $userIds): void
    {
        $this->userIds = $userIds;
    }

    /**
     * @return array|null
     */
    public function getSectorIds(): ?array
    {
        return $this->sectorIds;
    }

    /**
     * @param array|null $sectorIds
     */
    public function setSectorIds(?array $sectorIds): void
    {
        $this->sectorIds = $sectorIds;
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

    /**
     * @return \DateTime|null
     */
    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTime|null $startedAt
     */
    public function setStartedAt(?\DateTime $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    /**
     * @param \DateTime|null $endAt
     */
    public function setEndAt(?\DateTime $endAt): void
    {
        $this->endAt = $endAt;
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
     * @return bool|null
     */
    public function getAddSummary(): ?bool
    {
        return $this->addSummary;
    }

    /**
     * @param bool|null $addSummary
     */
    public function setAddSummary(?bool $addSummary): void
    {
        $this->addSummary = $addSummary;
    }
}
