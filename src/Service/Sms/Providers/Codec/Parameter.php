<?php

namespace App\Service\Sms\Providers\Codec;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Parameter
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->container->getParameter('codec_username');
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->container->getParameter('codec_password');
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->container->getParameter('codec_sender');
    }

    /**
     * @return string
     */
    public function getServiceCode()
    {
        return $this->container->getParameter('codec_service_code');
    }

    /**
     * @return string
     */
    public function getFastAPIWsdl()
    {
        return $this->container->getParameter('codec_fast_api_wsdl');
    }

    /**
     * @return string
     */
    public function getBulkAPIWsdl()
    {
        return $this->container->getParameter('codec_bulk_api_wsdl');
    }

    public function getResponseType()
    {
        return 3;
    }
}
