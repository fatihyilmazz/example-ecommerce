<?php

namespace App\Entity\FilterClass;

class DefectiveProductFilter
{
    const DEFAULT_LIMIT = 10;

    /**
     * @var int
     */
    private $sellerMerchantId;

    /**
     * @var array|null
     */
    private $userIds;

    /**
     * @var array|null
     */
    private $orderIds;

    /**
     * @var array|null
     */
    private $orderProductIds;

    /**
     * @var array|null
     */
    private $defectiveProductReasonTypeIds;

    /**
     * @var array|null
     */
    private $defectiveProductReasonIds;

    /**
     * @var int
     */
    private $sectorId;

    /**
     * @var array|null
     */
    private $statusIds;

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
     * @return int
     */
    public function getSellerMerchantId(): int
    {
        return $this->sellerMerchantId;
    }

    /**
     * @param int $sellerMerchantId
     */
    public function setSellerMerchantId(int $sellerMerchantId): void
    {
        $this->sellerMerchantId = $sellerMerchantId;
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
    public function getOrderIds(): ?array
    {
        return $this->orderIds;
    }

    /**
     * @param array|null $orderIds
     */
    public function setOrderIds(?array $orderIds): void
    {
        $this->orderIds = $orderIds;
    }

    /**
     * @return array|null
     */
    public function getOrderProductIds(): ?array
    {
        return $this->orderProductIds;
    }

    /**
     * @param array|null $orderProductIds
     */
    public function setOrderProductIds(?array $orderProductIds): void
    {
        $this->orderProductIds = $orderProductIds;
    }

    /**
     * @return array|null
     */
    public function getDefectiveProductReasonTypeIds(): ?array
    {
        return $this->defectiveProductReasonTypeIds;
    }

    /**
     * @param array|null $defectiveProductReasonTypeIds
     */
    public function setDefectiveProductReasonTypeIds(?array $defectiveProductReasonTypeIds): void
    {
        $this->defectiveProductReasonTypeIds = $defectiveProductReasonTypeIds;
    }

    /**
     * @return array|null
     */
    public function getDefectiveProductReasonIds(): ?array
    {
        return $this->defectiveProductReasonIds;
    }

    /**
     * @param array|null $defectiveProductReasonIds
     */
    public function setDefectiveProductReasonIds(?array $defectiveProductReasonIds): void
    {
        $this->defectiveProductReasonIds = $defectiveProductReasonIds;
    }

    /**
     * @return int
     */
    public function getSectorId(): int
    {
        return $this->sectorId;
    }

    /**
     * @param int $sectorId
     */
    public function setSectorId(int $sectorId): void
    {
        $this->sectorId = $sectorId;
    }

    /**
     * @return array|null
     */
    public function getStatusIds(): ?array
    {
        return $this->statusIds;
    }

    /**
     * @param array|null $statusIds
     */
    public function setStatusIds(?array $statusIds): void
    {
        $this->statusIds = $statusIds;
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
