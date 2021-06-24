<?php

namespace App\EventSubscriber;

use App\Event\HasConflictProductEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BasketSubscriber implements EventSubscriberInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ContainerInterface $container
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ContainerInterface $container,
        TranslatorInterface $translator
    ) {
        $this->container = $container;
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            HasConflictProductEvent::NAME => 'onHasConflictProductEvent',
            KernelEvents::RESPONSE => [
                ['onKernelResponse', 1]
            ],
        ];
    }

    /**
     * @param HasConflictProductEvent $event
     *
     * @throws \Exception
     */
    public function onHasConflictProductEvent(HasConflictProductEvent $event)
    {
        $this->container->get('session')->getFlashBag()->set('status', 'warning');
        $this->container->get('session')->getFlashBag()->set('type', 'conflictProducts');
        $this->container->get('session')->getFlashBag()->set(
            'message',
            $this->translator->trans('system.basket.conflict_products.insufficient_quantity')
        );

        $this->container->get('session')->getFlashBag()->set('conflictProducts', $event->getBasket()->getConflictProducts());
        $this->container->get('session')->getFlashBag()->set('redirectConflictProducts', true);
    }

    /**
     * @param ResponseEvent $event
     * @return ResponseEvent|void
     *
     * @throws \Exception
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (($event->getRequest()->getSession() instanceof SessionInterface) &&
            $event->getRequest()->getSession()->getFlashBag()->has('redirectConflictProducts')) {
            $response = new RedirectResponse($this->container->get('router')->generate('front.basket.index'));
            $event->setResponse($response);

            $event->getRequest()->getSession()->getFlashBag()->get('redirectConflictProducts');
        }

        return $event;
    }
}
