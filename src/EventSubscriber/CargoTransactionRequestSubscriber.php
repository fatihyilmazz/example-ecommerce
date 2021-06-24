<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use App\Service\CargoService;
use App\Event\CargoTransactionRequestEvent;
use App\Entity\CargoTransactionRequestStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CargoTransactionRequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CargoTransactionRequestEvent::NAME => 'onCreateShipmentRequestSentEvent',
        ];
    }

    /**
     * @param CargoTransactionRequestEvent $event
     */
    public function onCreateShipmentRequestSentEvent(CargoTransactionRequestEvent $event)
    {
        $cargoTransactionLog = $this->container->get(CargoService::class)->logCargoTransactionRequestCreateShipment($event->getRequestData(), $event->getResponseData(), $event->getDocumentKey());

        if ($cargoTransactionLog->getCargoTransactionStatus()->getId() == CargoTransactionRequestStatus::ID_FAILURE) {
            $this->logger->error('[CargoTransactionRequestSubscriber][onCreateShipmentRequestSentEvent] Cargo transaction failed.', [
                'cargoTransactionLogId' => $cargoTransactionLog->getId(),
            ]);
        }
    }
}
