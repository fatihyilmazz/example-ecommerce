<?php

namespace App\Service\Cargo\Providers;

use App\Service\Cargo\ShipmentInfo;

interface ProviderInterface
{
    /**
     * @return int
     */
    public function getCargoCompanyId();

    /**
     * @param ShipmentInfo $shipmentInfo
     *
     * @return string|null
     */
    public function createShipment(ShipmentInfo $shipmentInfo);

    /**
     * @param string $documentKey
     * @param string $description
     *
     * @return object|null
     */
    public function cancelShipment(string $documentKey, string $description = '');

    /**
     * @param string $documentKey
     *
     * @return object|null
     */
    public function getShipmentInfo(string $documentKey);

    /**
     * @param string $documentKey
     *
     * @return int|null
     */
    public function getShipmentStatus(string $documentKey);

    /**
     * @param string $response
     *
     * @return int|null
     */
    public function getRequestStatusByResponse(string $response);

    /**
     * @param ShipmentInfo $shipmentInfo
     *
     * @return string
     */
    public function getFormattedCargoRequest(ShipmentInfo $shipmentInfo);

    /**
     * @param ShipmentInfo $shipmentInfo
     *
     * @return string
     */
    public function getRequestDocumentKey(ShipmentInfo $shipmentInfo);
}
