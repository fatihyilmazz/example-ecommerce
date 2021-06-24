<?php

namespace App\EventSubscriber;

use App\Service\UserService;
use App\Event\UserUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserUpdatedEvent::NAME => 'onUserUpdatedEvent',
        ];
    }

    /**
     * @param UserUpdatedEvent $event
     */
    public function onUserUpdatedEvent(UserUpdatedEvent $event)
    {
        $this->userService->checkForPermissionChanges($event->getUser(), $event->getPreviousUser());
    }
}
