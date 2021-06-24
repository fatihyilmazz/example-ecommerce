<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table("merchants")
 * @ORM\Entity(repositoryClass="App\Repository\MerchantRepository")
 * @ORM\EntityListeners({"App\EventListener\MerchantEventListener"})
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Merchant
{
    const ID_BIRCOM = 1;

    const CACHE_LIFETIME_ALL = 60 * 60 * 5;
    const CACHE_LIFETIME_ID = 60 * 60 * 6;

    const CACHE_TAG = 'merchants';

    const CACHE_KEY_IDS_AND_NAMES = 'merchants-ids-and-names';
    const CACHE_KEY_IDS_AND_NAMES_BY_SECTOR = 'merchants-ids-and-names-by-sector-';
    const CACHE_KEY_BY_ID = 'merchants-id-';
    const CACHE_KEY_DELETED_MERCHANT_IDS = 'deleted-merchant-Ids';
    const CACHE_KEY_INACTIVE_MERCHANT_IDS = 'inactive-merchant-Ids';
    const CACHE_KEY_CLOSED_MARKETPLACE_MERCHANT_IDS = 'closed-marketplace-merchant-Ids';

    const CACHE_KEY_NUMBER_OF_ACTIVE_MERCHANTS = 'number-of-active-merchants';
    const CACHE_KEY_NUMBER_OF_PASSIVE_MERCHANTS = 'number-of-passive-merchants';

    const CACHE_KEY_NUMBER_OF_ACTIVE_SELLER_MERCHANTS = 'number-of-active-seller-merchants';
    const CACHE_KEY_NUMBER_OF_PASSIVE_SELLER_MERCHANTS = 'number-of-passive-seller-merchants';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Serializer\Expose()
     */
    private $shopName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $shopDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logoUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bannerUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $landPhoneNumber;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $faxNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ibanName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $iban;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $taxOffice;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $taxNumber;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $merchantKey;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $currentCode;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $currentGroupId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $segmentId;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $sfLeadId;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $sfAccountId;

    /**
     * @ORM\ManyToOne(targetEntity="UserAgent")
     * @ORM\JoinColumn(nullable=true)
     */
    private $userAgent;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $contractFile;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $signatureFile;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $taxFile;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $journalFile;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $marketplaceClosedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $legalCompanyTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $returnAddress;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $verifiedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="merchant")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="merchant")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $orders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MerchantContact", mappedBy="merchant")
     */
    private $merchantContacts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MerchantSector", mappedBy="merchant", cascade={"persist"})
     */
    private $merchantSectors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MerchantHistory", mappedBy="merchant", fetch="EXTRA_LAZY")
     */
    private $merchantHistories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MerchantSectorHistory", mappedBy="merchant", fetch="EXTRA_LAZY")
     */
    private $merchantSectorHistories;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->merchantContacts = new ArrayCollection();
        $this->merchantSectors = new ArrayCollection();
        $this->merchantSectorHistories = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     *
     * @return $this
     */
    public function setShopName(?string $shopName): self
    {
        $this->shopName = $shopName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getShopDescription(): ?string
    {
        return $this->shopDescription;
    }

    /**
     * @param string $shopDescription
     *
     * @return $this
     */
    public function setShopDescription(string $shopDescription): self
    {
        $this->shopDescription = $shopDescription;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    /**
     * @param string|null $logoUrl
     *
     * @return $this
     */
    public function setLogoUrl(?string $logoUrl): self
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBannerUrl(): ?string
    {
        return $this->bannerUrl;
    }

    /**
     * @param string|null $bannerUrl
     *
     * @return $this
     */
    public function setBannerUrl(?string $bannerUrl): self
    {
        $this->bannerUrl = $bannerUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /**
     * @param string|null $website
     *
     * @return $this
     */
    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
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
     *
     * @return $this
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
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
     *
     * @return $this
     */
    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLandPhoneNumber(): ?string
    {
        return $this->landPhoneNumber;
    }

    /**
     * @param string $landPhoneNumber
     *
     * @return $this
     */
    public function setLandPhoneNumber(string $landPhoneNumber): self
    {
        $this->landPhoneNumber = $landPhoneNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFaxNumber(): ?string
    {
        return $this->faxNumber;
    }

    /**
     * @param string $faxNumber
     *
     * @return $this
     */
    public function setFaxNumber(string $faxNumber): self
    {
        $this->faxNumber = $faxNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIbanName(): ?string
    {
        return $this->ibanName;
    }

    /**
     * @param string|null $ibanName
     *
     * @return $this
     */
    public function setIbanName(?string $ibanName): self
    {
        $this->ibanName = $ibanName;

        return $this;
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
     *
     * @return $this
     */
    public function setIban(?string $iban): self
    {
        $this->iban = $iban;

        return $this;
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
     *
     * @return $this
     */
    public function setTaxOffice(?string $taxOffice): self
    {
        $this->taxOffice = $taxOffice;

        return $this;
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
     *
     * @return $this
     */
    public function setTaxNumber(?string $taxNumber): self
    {
        $this->taxNumber = $taxNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMerchantKey(): ?string
    {
        return $this->merchantKey;
    }

    /**
     * @param string $merchantKey
     *
     * @return $this
     */
    public function setMerchantKey(string $merchantKey): self
    {
        $this->merchantKey = $merchantKey;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrentCode(): ?string
    {
        return $this->currentCode;
    }

    /**
     * @param string $currentCode
     *
     * @return $this
     */
    public function setCurrentCode(string $currentCode): self
    {
        $this->currentCode = $currentCode;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCurrentGroupId(): ?int
    {
        return $this->currentGroupId;
    }

    /**
     * @param int $currentGroupId
     *
     * @return $this
     */
    public function setCurrentGroupId(int $currentGroupId): self
    {
        $this->currentGroupId = $currentGroupId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getSegmentId(): ?int
    {
        return $this->segmentId;
    }

    /**
     * @param int $segmentId
     *
     * @return $this
     */
    public function setSegmentId(int $segmentId): self
    {
        $this->segmentId = $segmentId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSfLeadId(): ?string
    {
        return $this->sfLeadId;
    }

    /**
     * @param string|null $sfLeadId
     *
     * @return $this
     */
    public function setSfLeadId(?string $sfLeadId): self
    {
        $this->sfLeadId = $sfLeadId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSfAccountId(): ?string
    {
        return $this->sfAccountId;
    }

    /**
     * @param string|null $sfAccountId
     *
     * @return $this
     */
    public function setSfAccountId(?string $sfAccountId): self
    {
        $this->sfAccountId = $sfAccountId;

        return $this;
    }

    /**
     * @return UserAgent|null
     */
    public function getUserAgent(): ?UserAgent
    {
        return $this->userAgent;
    }

    /**
     * @param UserAgent $userAgent
     */
    public function setUserAgent(UserAgent $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return $this
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContractFile(): ?string
    {
        return $this->contractFile;
    }

    /**
     * @param mixed $contractFile
     */
    public function setContractFile(?string $contractFile): void
    {
        $this->contractFile = $contractFile;
    }

    /**
     * @return string|null
     */
    public function getSignatureFile(): ?string
    {
        return $this->signatureFile;
    }

    /**
     * @param string|null $signatureFile
     */
    public function setSignatureFile(?string $signatureFile): void
    {
        $this->signatureFile = $signatureFile;
    }

    /**
     * @return string|null
     */
    public function getTaxFile(): ?string
    {
        return $this->taxFile;
    }

    /**
     * @param string|null $taxFile
     */
    public function setTaxFile(?string $taxFile): void
    {
        $this->taxFile = $taxFile;
    }

    /**
     * @return string|null
     */
    public function getJournalFile(): ?string
    {
        return $this->journalFile;
    }

    /**
     * @param string|null $journalFile
     */
    public function setJournalFile(?string $journalFile): void
    {
        $this->journalFile = $journalFile;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getMarketplaceClosedAt(): ?\DateTimeInterface
    {
        return $this->marketplaceClosedAt;
    }

    /**
     * @param \DateTimeInterface|null $marketplaceClosedAt
     */
    public function setMarketplaceClosedAt(?\DateTimeInterface $marketplaceClosedAt): void
    {
        $this->marketplaceClosedAt = $marketplaceClosedAt;
    }

    /**
     * @return string|null
     */
    public function getLegalCompanyTitle()
    {
        return $this->legalCompanyTitle;
    }

    /**
     * @param string|null $legalCompanyTitle
     *
     * @return $this
     */
    public function setLegalCompanyTitle(?string $legalCompanyTitle): self
    {
        $this->legalCompanyTitle = $legalCompanyTitle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnAddress()
    {
        return $this->returnAddress;
    }

    /**
     * @param string|null $returnAddress
     *
     * @return $this
     */
    public function setReturnAddress(?string $returnAddress): self
    {
        $this->returnAddress = $returnAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMerchantHistories()
    {
        return $this->merchantHistories;
    }

    /**
     * @param mixed $merchantHistories
     */
    public function setMerchantHistories($merchantHistories): void
    {
        $this->merchantHistories = $merchantHistories;
    }

    /**
     * @return Collection|MerchantSectorHistory[]
     */
    public function getMerchantSectorHistories()
    {
        return $this->merchantSectorHistories;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getVerifiedAt(): ?\DateTimeInterface
    {
        return $this->verifiedAt;
    }

    /**
     * @param \DateTimeInterface|null $verifiedAt
     *
     * @return $this
     */
    public function setVerifiedAt(?\DateTimeInterface $verifiedAt): self
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getApprovedAt(): ?\DateTimeInterface
    {
        return $this->approvedAt;
    }

    /**
     * @param \DateTimeInterface|null $approvedAt
     *
     * @return $this
     */
    public function setApprovedAt(?\DateTimeInterface $approvedAt): self
    {
        $this->approvedAt = $approvedAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTimeInterface|null $deletedAt
     * @return $this
     */
    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection|\App\Entity\Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    /**
     * @return Collection|\App\Entity\User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @return Collection|MerchantContact[]
     */
    public function getMerchantContacts(): Collection
    {
        return $this->merchantContacts;
    }

    /**
     * @param MerchantContact $merchantContact
     *
     * @return $this
     */
    public function addMerchantContact(MerchantContact $merchantContact): self
    {
        if (!$this->merchantContacts->contains($merchantContact)) {
            $this->merchantContacts[] = $merchantContact;
            $merchantContact->setMerchant($this);
        }

        return $this;
    }

    /**
     * @param MerchantContact $merchantContact
     *
     * @return $this
     */
    public function removeMerchantContact(MerchantContact $merchantContact): self
    {
        if ($this->merchantContacts->contains($merchantContact)) {
            $this->merchantContacts->removeElement($merchantContact);
            // set the owning side to null (unless already changed)
            if ($merchantContact->getMerchant() === $this) {
                $merchantContact->setMerchant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MerchantSector[]
     */
    public function getMerchantSectors(): Collection
    {
        return $this->merchantSectors;
    }

    /**
     * @param MerchantSector $merchantSector
     *
     * @return $this
     */
    public function addMerchantSector(MerchantSector $merchantSector): self
    {
        if (empty($merchantSector->getMerchant())) {
            $merchantSector->setMerchant($this);
        }

        if (!$this->merchantSectors->contains($merchantSector)) {
            $this->merchantSectors[] = $merchantSector;
        }

        return $this;
    }

    /**
     * @param MerchantSector $merchantSector
     *
     * @return $this
     */
    public function removeMerchantSector(MerchantSector $merchantSector): self
    {
        if ($this->merchantSectors->contains($merchantSector)) {
            if (!empty($merchantSector->getMerchant())) {
                $merchantSector->setMerchant(null);
            }

            $this->merchantSectors->removeElement($merchantSector);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isSeller()
    {
        return !empty($this->merchantKey);
    }
}
