<?php

namespace App\Entity\FilterClass;

use App\Entity\Merchant;

class OrdersOnlyHasMerchantsProductsFilter
{
    const DEFAULT_LIMIT = 10;

    /**
     * @var int|null
     */
    private $orderId;

    /**
     * @var string|null
     */
    private $userFullName;

    /**
     * @var int|null
     */
    private $productId;

    /**
     * @var array
     */
    private $sellerMerchant = [];

    /**
     * @var array|null
     */
    private $buyerMerchantIds;

    /**
     * @var array|null
     */
    private $userIds;

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
    private $orderBy = 'createdAt';

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
    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    /**
     * @param int|null $orderId
     */
    public function setOrderId(?int $orderId): void
    {
        $this->orderId = $orderId;
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
     * @return array
     */
    public function getSellerMerchant(): array
    {
        return $this->sellerMerchant;
    }

    /**
     * @param array $sellerMerchant
     */
    public function setSellerMerchant(array $sellerMerchant): void
    {
        $this->sellerMerchant = $sellerMerchant;
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
