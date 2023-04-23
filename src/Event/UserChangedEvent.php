<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserChangedEvent extends Event
{
    const CHANGED = 'event.user.changed';

    private User $user;
    private bool $didContractsChanged;
    private bool $didVacationsChanged;

    public function __construct(
        User $user,
        bool $didContractsChanged,
        bool $didVacationsChanged
    ) {
        $this->user = $user;
        $this->didContractsChanged = $didContractsChanged;
        $this->didVacationsChanged = $didVacationsChanged;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getDidContractsChanged(): bool
    {
        return $this->didContractsChanged;
    }

    public function getDidVacationsChanged(): bool
    {
        return $this->didVacationsChanged;
    }
}
