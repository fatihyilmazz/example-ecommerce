<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table("categories")
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\EntityListeners({"App\EventListener\CategoryEventListener"})
 */
class Category
{
    const CACHE_LIFETIME_ALL = 60 * 60 * 5;

    const CACHE_LIFETIME_SLUG = 60 * 60 * 5;

    const CACHE_KEY_BY_SECTOR_ID = 'categories-sector-';

    const CACHE_KEY_BY_SLUG = 'categories-slug-';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $pmCategoryId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(name="pm_parent_id", type="integer", nullable=true)
     */
    private $pmParentId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="subCategories", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Category", mappedBy="parent", fetch="EAGER", cascade={"remove"}, orphanRemoval=true)
     */
    private $subCategories;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sector", inversedBy="categories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sector;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getPmCategoryId(): ?int
    {
        return $this->pmCategoryId;
    }

    /**
     * @param int $pmCategoryId
     */
    public function setPmCategoryId(int $pmCategoryId): void
    {
        $this->pmCategoryId = $pmCategoryId;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return int
     */
    public function getPmParentId(): ?int
    {
        return $this->pmParentId;
    }

    /**
     * @param int $pmParentId
     */
    public function setPmParentId(int $pmParentId): void
    {
        $this->pmParentId = $pmParentId;
    }

    /**
     * @return $this|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * @param Category|null $parent
     *
     * @return $this
     */
    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    /**
     * @param $subCategories
     */
    public function setSubCategories($subCategories): void
    {
        $this->subCategories = $subCategories;
    }

    /**
     * @return Sector|null
     */
    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    /**
     * @param Sector|null $sector
     *
     * @return $this
     */
    public function setSector(?Sector $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    /**
     * @return bool|null
     */
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
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     *
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
    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTimeInterface|null $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
