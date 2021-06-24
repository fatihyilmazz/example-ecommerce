<?php

namespace App\Service\Cargo;

class ShipmentItem
{
    const TYPE_FILE     = 0;
    const TYPE_MI       = 1;
    const TYPE_PACKAGE  = 2;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $count;

    /**
     * @var float
     */
    private $desi;

    /**
     * @var float
     */
    private $weight;

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
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return float
     */
    public function getDesi()
    {
        return $this->desi;
    }

    /**
     * @param float $desi
     */
    public function setDesi($desi)
    {
        $this->desi = $desi;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }
}
