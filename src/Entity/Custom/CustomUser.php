<?php

namespace App\Entity\Custom;

class CustomUser
{
    private $id;

    private $shopName;

    private $firstName;

    private $lastName;

    private $phoneNumber;

    private $sectorId;

    private $verifiedNumber;

    public function __construct(int $id, string $firstName, string $lastName, string  $shopName, string $phoneNumber, int $sectorId, bool $verifiedNumber)
    {
        $this->id = $id;
        $this->shopName = $shopName;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phoneNumber = $phoneNumber;
        $this->sectorId = $sectorId;
        $this->verifiedNumber = $verifiedNumber;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getShopName(): string
    {
        return $this->shopName;
    }

    /**
     * @param string $shopName
     */
    public function setShopName(string $shopName): void
    {
        $this->shopName = $shopName;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
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
     * @return bool
     */
    public function isVerifiedNumber(): bool
    {
        return $this->verifiedNumber;
    }

    /**
     * @param bool $verifiedNumber
     */
    public function setVerifiedNumber(bool $verifiedNumber): void
    {
        $this->verifiedNumber = $verifiedNumber;
    }

}
