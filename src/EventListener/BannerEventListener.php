<?php

namespace App\EventListener;

use App\Entity\Banner;
use App\Event\CacheInvalidatedEvent;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class BannerEventListener
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Banner $banner
     * @param LifecycleEventArgs $args
     *
     * @return void
     *
     * @throws \Exception
     */
    public function prePersist(Banner $banner, LifecycleEventArgs $args)
    {
        $banner->setCreatedAt(new \DateTime());
    }

    /**
     * @param Banner $banner
     * @param LifecycleEventArgs $args
     *
     * @return void
     */
    public function postPersist(Banner $banner, LifecycleEventArgs $args)
    {
        $this->fireCacheInvalidationEvent($banner);
    }

    /**
     * @param Banner $banner
     * @param LifecycleEventArgs $args
     *
     * @return void
     */
    public function postUpdate(Banner $banner, LifecycleEventArgs $args)
    {
        $this->fireCacheInvalidationEvent($banner);
    }

    /**
     * @param Banner $banner
     * @param LifecycleEventArgs $args
     *
     * @return void
     */
    public function postRemove(Banner $banner, LifecycleEventArgs $args)
    {
        $this->fireCacheInvalidationEvent($banner);
    }

    /**
     * @param Banner $banner
     */
    private function fireCacheInvalidationEvent(Banner $banner)
    {
        $this->eventDispatcher->dispatch(new CacheInvalidatedEvent($banner), CacheInvalidatedEvent::NAME_BANNER_UPDATED);
    }
}
