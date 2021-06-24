<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\CargoStatus;
use App\Service\OrderService;
use App\Service\BasketService;
use App\Event\OrderCompletedEvent;
use App\Event\OrderProductShippedEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderCompletedEvent::NAME => 'onOrderCompletedEvent',
            OrderProductShippedEvent::NAME => 'onOrderProductShippedEvent',
        ];
    }

    /**
     * @param OrderCompletedEvent $event
     */
    public function onOrderCompletedEvent(OrderCompletedEvent $event)
    {
        if (($user = $event->getOrder()->getUser()) instanceof User) {
            $this->container->get(BasketService::class)->clearBasket($user);
        }

        $orderService = $this->container->get(OrderService::class);
        $orderContainsOnlyBircomProducts = $orderService->getProductsOfBircomFromOrder($event->getOrder());

        if ($orderContainsOnlyBircomProducts instanceof Order) {
            $orderService->sendOrderToNetsis($orderContainsOnlyBircomProducts, $event->getOrder());
        }

        $orderService->updateStocksForCompletedOrders($event->getOrder());

        $orderService->sendOrderCreatedMail($event->getOrder());
    }

    /**
     * @param OrderProductShippedEvent $event
     */
    public function onOrderProductShippedEvent(OrderProductShippedEvent $event)
    {
        $orderProducts = $this->container->get(OrderService::class)->updateOrderProductsCargoStatus($event->getOrderProducts(), CargoStatus::ID_SHIPPED);
        if (!empty($orderProducts)) {
            // @TODO notify user for shipped order products
        }
    }
}
