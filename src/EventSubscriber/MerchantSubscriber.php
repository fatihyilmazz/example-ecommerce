<?php

namespace App\EventSubscriber;

use App\Service\MerchantService;
use App\Event\MerchantApprovedEvent;
use App\Event\MerchantRegisteredEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MerchantSubscriber implements EventSubscriberInterface
{
    /**
     * @var MerchantService
     */
    protected $merchantService;

    /**
     * @param MerchantService $merchantService
     */
    public function __construct(MerchantService $merchantService)
    {
        $this->merchantService = $merchantService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            MerchantApprovedEvent::NAME => 'onMerchantApprovedEvent',
            MerchantRegisteredEvent::NAME => 'onMerchantRegisteredEvent',
        ];
    }

    /**
     * @param MerchantApprovedEvent $event
     */
    public function onMerchantApprovedEvent(MerchantApprovedEvent $event)
    {
        $this->merchantService->sendApprovedMailToMerchant($event->getUser());
    }

    /**
     * @param MerchantRegisteredEvent $event
     */
    public function onMerchantRegisteredEvent(MerchantRegisteredEvent $event)
    {
        $this->merchantService->sendRegisteredMailToMerchant($event->getUser());
    }
}
