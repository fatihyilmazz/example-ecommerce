<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table("cities")
 * @ORM\Entity(repositoryClass="App\Repository\CityRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class City
{
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
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\County", mappedBy="city")
     * @ORM\OrderBy(value={"name"="ASC"})
     */
    private $counties;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="cities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    public function __construct()
    {
        $this->counties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|County[]
     */
    public function getCounties(): Collection
    {
        return $this->counties;
    }

    public function addCounty(County $county): self
    {
        if (!$this->counties->contains($county)) {
            $this->counties[] = $county;
            $county->setCity($this);
        }

        return $this;
    }

    public function removeCounty(County $county): self
    {
        if ($this->counties->contains($county)) {
            $this->counties->removeElement($county);
            // set the owning side to null (unless already changed)
            if ($county->getCity() === $this) {
                $county->setCity(null);
            }
        }

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }
}
