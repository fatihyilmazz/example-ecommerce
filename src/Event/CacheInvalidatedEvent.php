<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class CacheInvalidatedEvent extends Event
{
    private $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
