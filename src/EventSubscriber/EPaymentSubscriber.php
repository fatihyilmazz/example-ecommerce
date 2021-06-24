<?php

namespace App\EventSubscriber;

use App\Service\EPaymentService;
use App\Event\EPaymentCompletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EPaymentSubscriber implements EventSubscriberInterface
{
    /**
     * @var EPaymentService
     */
    protected $ePaymentService;

    /**
     * @param EPaymentService $ePaymentService
     */
    public function __construct(EPaymentService $ePaymentService)
    {
        $this->ePaymentService = $ePaymentService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EPaymentCompletedEvent::NAME => 'onEPaymentCompletedEvent',
        ];
    }

    /**
     * @param EPaymentCompletedEvent $event
     */
    public function onEPaymentCompletedEvent(EPaymentCompletedEvent $event)
    {
        $this->ePaymentService->sendEPaymentToNetsis($event->getEPayment());
    }
}
