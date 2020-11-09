<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserChangedEvent extends Event
{
    const CHANGED = 'event.user.changed';

    private User $user;
    private bool $didVacationsChanged;
    private bool $didWorkHoursChanged;

    public function __construct(User $user, bool $didVacationsChanged, bool $didWorkHoursChanged)
    {
        $this->user = $user;
        $this->didVacationsChanged = $didVacationsChanged;
        $this->didWorkHoursChanged = $didWorkHoursChanged;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getDidVacationsChanged(): bool
    {
        return $this->didVacationsChanged;
    }

    public function getDidWorkHoursChanged(): bool
    {
        return $this->didWorkHoursChanged;
    }
}
