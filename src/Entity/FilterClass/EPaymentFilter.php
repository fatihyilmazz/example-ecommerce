<?php

namespace App\Entity\FilterClass;

class EPaymentFilter
{
    const DEFAULT_LIMIT = 10;

    /**
     * @var int|null
     */
    private $merchantId;

    /**
     * @var int|null
     */
    private $ePaymentTypeId;

    /**
     * @var int|null
     */
    private $minAmount;

    /**
     * @var int|null
     */
    private $maxAmount;

    /**
     * @var \DateTime|null
     */
    private $startedAt;

    /**
     * @var \DateTime|null
     */
    private $endAt;

    /**
     * @var int|null
     */
    private $ePaymentStatusId;

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
    public function getMerchantId(): ?int
    {
        return $this->merchantId;
    }

    /**
     * @param int|null $merchantId
     */
    public function setMerchantId(?int $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return int|null
     */
    public function getEPaymentTypeId(): ?int
    {
        return $this->ePaymentTypeId;
    }

    /**
     * @param int|null $ePaymentTypeId
     */
    public function setEPaymentTypeId(?int $ePaymentTypeId): void
    {
        $this->ePaymentTypeId = $ePaymentTypeId;
    }

    /**
     * @return int|null
     */
    public function getMinAmount(): ?int
    {
        return $this->minAmount;
    }

    /**
     * @param int|null $minAmount
     */
    public function setMinAmount(?int $minAmount): void
    {
        $this->minAmount = $minAmount;
    }

    /**
     * @return int|null
     */
    public function getMaxAmount(): ?int
    {
        return $this->maxAmount;
    }

    /**
     * @param int|null $maxAmount
     */
    public function setMaxAmount(?int $maxAmount): void
    {
        $this->maxAmount = $maxAmount;
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
     * @return int|null
     */
    public function getEPaymentStatusId(): ?int
    {
        return $this->ePaymentStatusId;
    }

    /**
     * @param int|null $ePaymentStatusId
     */
    public function setEPaymentStatusId(?int $ePaymentStatusId): void
    {
        $this->ePaymentStatusId = $ePaymentStatusId;
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
