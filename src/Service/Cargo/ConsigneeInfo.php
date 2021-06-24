<?php

namespace App\Service\Cargo;

class ConsigneeInfo
{
    /**
     * @var string
     */
    private $fullName;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $cityId;

    /**
     * @var string
     */
    private $townName;

    /**
     * @var string
     */
    private $mobilePhoneNumber;

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param string $cityId
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
    }

    /**
     * @return string
     */
    public function getTownName()
    {
        return $this->townName;
    }

    /**
     * @param string $townName
     */
    public function setTownName($townName)
    {
        $this->townName = $townName;
    }

    /**
     * @return string
     */
    public function getMobilePhoneNumber()
    {
        return $this->mobilePhoneNumber;
    }

    /**
     * @param string $mobilePhoneNumber
     */
    public function setMobilePhoneNumber($mobilePhoneNumber)
    {
        $this->mobilePhoneNumber = $mobilePhoneNumber;
    }
}
