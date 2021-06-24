<?php

namespace App\Entity\FilterClass;

class NotificationFilter
{
    const DEFAULT_LIMIT = 10;
    const STATUS_ACTIVE = 1;
    const STATUS_PASSIVE = 2;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int|null
     */
    private $title;

    /**
     * @var int|null
     */
    private $sectorId;

    /**
     * @var bool|null
     */
    private $isActive;

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
    private $sortBy = 'ASC';

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
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getSectorId()
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
     * @return bool|null
     */
    public function isActive(): ?bool
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
        $this->limit = $limit > self::DEFAULT_LIMIT ? $limit : self::DEFAULT_LIMIT;
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
