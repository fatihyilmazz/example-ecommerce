<?php

namespace App\Service\Cargo;

class ShipmentInfo
{
    const TYPE_FILE     = 0;
    const TYPE_MI       = 1;
    const TYPE_PACKAGE  = 2;

    const PRODUCT_CODE_STA  = 'STA';

    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $totalCount;

    /**
     * @var float
     */
    private $totalDesi;

    /**
     * @var float
     */
    private $totalWeight;

    /**
     * @var string
     */
    private $productCode = self::PRODUCT_CODE_STA;

    /**
     * @var string
     */
    private $personGiver;

    /**
     * @var SenderInfo
     */
    private $senderInfo;

    /**
     * @var ConsigneeInfo
     */
    private $consigneeInfo;

    /**
     * @var ShipmentItem[]
     */
    private $shipmentItems;

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @return float
     */
    public function getTotalDesi()
    {
        return $this->totalDesi;
    }

    /**
     * @param float $totalDesi
     */
    public function setTotalDesi($totalDesi)
    {
        $this->totalDesi = $totalDesi;
    }

    /**
     * @return float
     */
    public function getTotalWeight()
    {
        return $this->totalWeight;
    }

    /**
     * @param float $totalWeight
     */
    public function setTotalWeight($totalWeight)
    {
        $this->totalWeight = $totalWeight;
    }

    /**
     * @return string
     */
    public function getProductCode()
    {
        return $this->productCode;
    }

    /**
     * @param string $productCode
     */
    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;
    }

    /**
     * @return string
     */
    public function getPersonGiver(): string
    {
        return $this->personGiver;
    }

    /**
     * @param string $personGiver
     */
    public function setPersonGiver(string $personGiver)
    {
        $this->personGiver = $personGiver;
    }

    /**
     * @return SenderInfo
     */
    public function getSenderInfo()
    {
        return $this->senderInfo;
    }

    /**
     * @param SenderInfo $senderInfo
     */
    public function setSenderInfo(SenderInfo $senderInfo)
    {
        $this->senderInfo = $senderInfo;
    }

    /**
     * @return ConsigneeInfo
     */
    public function getConsigneeInfo()
    {
        return $this->consigneeInfo;
    }

    /**
     * @param ConsigneeInfo $consigneeInfo
     */
    public function setConsigneeInfo(ConsigneeInfo $consigneeInfo)
    {
        $this->consigneeInfo = $consigneeInfo;
    }

    /**
     * @return ShipmentItem[]
     */
    public function getShipmentItems()
    {
        return $this->shipmentItems;
    }

    /**
     * @param ShipmentItem[] $shipmentItems
     */
    public function setShipmentItems(array $shipmentItems)
    {
        $this->shipmentItems = $shipmentItems;
    }
}
