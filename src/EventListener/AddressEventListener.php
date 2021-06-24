<?php

namespace App\EventListener;

use App\Entity\Address;
use Doctrine\ORM\Event\LifecycleEventArgs;

class AddressEventListener
{
    /**
     * @param Address $address
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function prePersist(Address $address, LifecycleEventArgs $args)
    {
        $address->setCreatedAt(new \DateTime());
    }
}
