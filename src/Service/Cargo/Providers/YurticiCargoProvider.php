<?php

namespace App\Service\Cargo\Providers;

use App\Entity\CargoStatus;
use App\Entity\CargoCompany;
use App\Service\Cargo\ShipmentInfo;
use App\Event\CargoTransactionRequestEvent;
use App\Entity\CargoTransactionRequestStatus;
use App\Service\Cargo\Providers\YurticiCargo\Parameter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Service\Cargo\Providers\YurticiCargo\Request\CodData;
use App\Service\Cargo\Providers\YurticiCargo\Request\DocCargoData;
use App\Service\Cargo\Providers\YurticiCargo\Request\ShipmentData;
use App\Service\Cargo\Providers\YurticiCargo\Request\SpecialFieldData;
use App\Service\Cargo\Providers\YurticiCargo\Request\XSenderCustAddress;
use App\Service\Cargo\Providers\YurticiCargo\Request\XConsigneeCustAddress;
use App\Service\Cargo\Providers\YurticiCargo\Service\NgiShipmentInterfaceService;
use App\Service\Cargo\Providers\YurticiCargo\Service\WsReportWithReferenceService;

class YurticiCargoProvider implements ProviderInterface
{
    const OUTFLAG_SUCCESS           = 0;
    const OUTFLAG_UNEXPECTED_ERROR  = 2;

    const CARGO_EVENT_ID_BD = 'BD';
    const CARGO_EVENT_ID_BO = 'BO';
    const CARGO_EVENT_ID_FK = 'FK';
    const CARGO_EVENT_ID_FKK = 'FKK';
    const CARGO_EVENT_ID_GT = 'GT';
    const CARGO_EVENT_ID_IN = 'IN';
    const CARGO_EVENT_ID_OK = 'OK';
    const CARGO_EVENT_ID_TX = 'TX';
    const CARGO_EVENT_ID_YK = 'YK';

    const CARGO_REASON_ID_F = 'F';
    const CARGO_REASON_ID_I = 'İ';
    const CARGO_REASON_ID_TF = 'TF';
    const CARGO_REASON_ID_AAS = 'AAS';
    const CARGO_REASON_ID_FAF = 'FAF';
    const CARGO_REASON_ID_AAB = 'AAB';
    const CARGO_REASON_ID_AKE = 'AKE';
    const CARGO_REASON_ID_GDEE = 'GDEE';
    const CARGO_REASON_ID_HA = 'HA';
    const CARGO_REASON_ID_MI = 'Mİ';
    const CARGO_REASON_ID_MT = 'MT';
    const CARGO_REASON_ID_VMH = 'VMH';
    const CARGO_REASON_ID_ATN = 'ATN';
    const CARGO_REASON_ID_OK = 'OK';
    const CARGO_REASON_ID_AA = 'AA';
    const CARGO_REASON_ID_ST = 'ST';
    const CARGO_REASON_ID_KA = 'KA';
    const CARGO_REASON_ID_YY = 'YY';
    const CARGO_REASON_ID_ATD = 'ATD';
    const CARGO_REASON_ID_BGT = 'BGT';
    const CARGO_REASON_ID_GMI = 'GMI';
    const CARGO_REASON_ID_IDG = 'IDG';
    const CARGO_REASON_ID_IF = 'IF';
    const CARGO_REASON_ID_IGH = 'IGH';
    const CARGO_REASON_ID_KEG = 'KEG';
    const CARGO_REASON_ID_KH = 'KH';
    const CARGO_REASON_ID_KTS = 'KTS';
    const CARGO_REASON_ID_MSA = 'MSA';
    const CARGO_REASON_ID_STG = 'STG';
    const CARGO_REASON_ID_TZY = 'TZY';
    const CARGO_REASON_ID_YTY = 'YTY';
    const CARGO_REASON_ID_GOK = 'GOK';

    /**
     * @var \App\Service\Cargo\Providers\YurticiCargo\Parameter
     */
    protected $parameter;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->parameter = new Parameter($container);
        $this->container = $container;
    }

    /**
     * @return int
     */
    public function getCargoCompanyId()
    {
        return CargoCompany::YURTICI_ID;
    }

    /**
     * @param ShipmentInfo $shipmentInfo
     *
     * @return string|null
     */
    public function createShipment(ShipmentInfo $shipmentInfo)
    {
        //
    }

    /**
     * @param string $documentKey
     * @param string $description
     *
     * @return object|null
     */
    public function cancelShipment(string $documentKey, string $description = '')
    {
        //
    }

    /**
     * @param string $documentKey
     *
     * @return object|null
     */
    public function getShipmentInfo(string $documentKey)
    {
        //
    }

    /**
     * @param string $documentKey
     *
     * @return int|null
     */
    public function getShipmentStatus(string $documentKey)
    {
        //
    }

    /**
     * @param string $response
     *
     * @return int|null
     */
    public function getRequestStatusByResponse(string $response)
    {
        //
    }

    /**
     * @param string $documentKey
     *
     * @return int|null
     */
    public function getCargoTrackingIdByDocumentKey(string $documentKey)
    {
        //
    }

    /**
     * @param ShipmentInfo $shipmentInfo
     *
     * @return string
     */
    public function getFormattedCargoRequest(ShipmentInfo $shipmentInfo)
    {
        //
    }

    /**
     * @param ShipmentInfo $shipmentInfo
     *
     * @return string
     */
    public function getRequestDocumentKey(ShipmentInfo $shipmentInfo)
    {
        //
    }
}
