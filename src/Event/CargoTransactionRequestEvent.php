<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CargoTransactionRequestEvent extends Event
{
    const NAME = 'cargoTransactionRequest.createShipment';

    /**
     * @var string
     */
    protected $requestData;

    /**
     * @var string
     */
    protected $responseData;

    /***
     * @var string
     */
    protected $documentKey;

    /**
     * @param string $requestData
     * @param string $responseData
     * @param string $documentKey
     */
    public function __construct(string $requestData, string $responseData, string $documentKey)
    {
        $this->requestData = $requestData;
        $this->responseData = $responseData;
        $this->documentKey = $documentKey;
    }

    /**
     * @return string
     */
    public function getRequestData(): string
    {
        return $this->requestData;
    }

    /**
     * @return string
     */
    public function getResponseData(): string
    {
        return $this->responseData;
    }

    /**
     * @return string
     */
    public function getDocumentKey(): string
    {
        return $this->documentKey;
    }
}
