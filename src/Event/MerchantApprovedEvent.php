<?php

namespace App\Event;

use App\Entity\User;
use App\Entity\Merchant;
use Symfony\Contracts\EventDispatcher\Event;

class MerchantApprovedEvent extends Event
{
    const NAME = 'merchant.approved';

    /**
     * @var Merchant
     */
    protected $merchant;

    /**
     * @var User
     */
    protected $user;

    /**
     * @param Merchant $merchant
     * @param User $user
     */
    public function __construct(Merchant $merchant, User $user)
    {
        $this->merchant = $merchant;
        $this->user = $user;
    }

    /**
     * @return Merchant
     */
    public function getMerchant()
    {
        return $this->merchant;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
