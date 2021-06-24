<?php

namespace App\Service\Cargo\Providers\YurticiCargo;

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
        return $this->container->getParameter('yurtici_username');
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->container->getParameter('yurtici_password');
    }

    /**
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->container->getParameter('yurtici_default_language');
    }

    /**
     * @return int
     */
    public function getInvCustId()
    {
        return $this->container->getParameter('yurtici_inv_cust_id');
    }

    /**
     * @return string
     */
    public function getNgiCustomerAddressServiceWsdl()
    {
        return $this->container->getParameter('yurtici_ngi_customer_address_service_wsdl');
    }

    /**
     * @return string
     */
    public function getNgiShipmentInterfaceServiceWsdl()
    {
        return $this->container->getParameter('yurtici_ngi_shipment_interface_service_wsdl');
    }

    /**
     * @return string
     */
    public function getWsReportWithReferenceServiceWsdl()
    {
        return $this->container->getParameter('yurtici_ws_report_with_reference_service_wsdl');
    }

}
