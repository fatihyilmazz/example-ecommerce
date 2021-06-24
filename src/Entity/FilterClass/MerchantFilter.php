<?php

namespace App\Entity\FilterClass;

class MerchantFilter
{
    const DEFAULT_LIMIT = 10;

    const MARKETPLACE_STATUS_ACTIVE = 1;
    const MARKETPLACE_STATUS_PASSIVE = 2;
    const MARKETPLACE_STATUS_PENDING_APPROVAL = 3;
    const MARKETPLACE_STATUS_NO_APPLICATION = 4;
    const MARKETPLACE_STATUS_REJECTED = 5;
    const MARKETPLACE_STATUS_EXIST = 6;


    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null;
     */
    private $shopName;

    /**
     * @var string|null
     */
    private $ownerName;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $phoneNumber;

    /**
     * @var string|null
     */
    private $iban;

    /**
     * @var string|null
     */
    private $taxOffice;

    /**
     * @var string|null
     */
    private $taxNumber;

    /**
     * @var string|null
     */
    private $currentCode;

    /**
     * @var int|null
     */
    private $currentGroupId;

    /**
     * @var int|null
     */
    private $segmentId;

    /**
     * @var array|null
     */
    private $sectorIds;

    /**
     * @var bool|null
     */
    private $isActive;

    /**
     * @var  int|null
     */
    private $marketplaceStatusId;

    /**
     * @var  bool|null
     */
    private $isVerified = true;

    /**
     * @var  \DateTime|null
     */
    private $startedAt;

    /**
     * @var  \DateTime|null
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
    public function getShopName(): ?string
    {
        return $this->shopName;
    }

    /**
     * @param string|null $shopName
     */
    public function setShopName(?string $shopName): void
    {
        $this->shopName = $shopName;
    }

    /**
     * @return string|null
     */
    public function getOwnerName(): ?string
    {
        return $this->ownerName;
    }

    /**
     * @param string|null $ownerName
     */
    public function setOwnerName(?string $ownerName): void
    {
        $this->ownerName = $ownerName;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string|null
     */
    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @param string|null $iban
     */
    public function setIban(?string $iban): void
    {
        $this->iban = $iban;
    }

    /**
     * @return string|null
     */
    public function getTaxOffice(): ?string
    {
        return $this->taxOffice;
    }

    /**
     * @param string|null $taxOffice
     */
    public function setTaxOffice(?string $taxOffice): void
    {
        $this->taxOffice = $taxOffice;
    }

    /**
     * @return string|null
     */
    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    /**
     * @param string|null $taxNumber
     */
    public function setTaxNumber(?string $taxNumber): void
    {
        $this->taxNumber = $taxNumber;
    }

    /**
     * @return string|null
     */
    public function getCurrentCode(): ?string
    {
        return $this->currentCode;
    }

    /**
     * @param string|null $currentCode
     */
    public function setCurrentCode(?string $currentCode): void
    {
        $this->currentCode = $currentCode;
    }

    /**
     * @return int|null
     */
    public function getCurrentGroupId(): ?int
    {
        return $this->currentGroupId;
    }

    /**
     * @param int|null $currentGroupId
     */
    public function setCurrentGroupId(?int $currentGroupId): void
    {
        $this->currentGroupId = $currentGroupId;
    }

    /**
     * @return int|null
     */
    public function getSegmentId(): ?int
    {
        return $this->segmentId;
    }

    /**
     * @param int|null $segmentId
     */
    public function setSegmentId(?int $segmentId): void
    {
        $this->segmentId = $segmentId;
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
     * @return int|null
     */
    public function getMarketplaceStatusId(): ?int
    {
        return $this->marketplaceStatusId;
    }

    /**
     * @param int|null $marketplaceStatusId
     */
    public function setMarketplaceStatusId(?int $marketplaceStatusId): void
    {
        $this->marketplaceStatusId = $marketplaceStatusId;
    }

    /**
     * @return \bool|null
     */
    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    /**
     * @param \bool|null $isVerified
     */
    public function setIsVerified(?bool $isVerified): void
    {
        $this->isVerified = $isVerified;
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
