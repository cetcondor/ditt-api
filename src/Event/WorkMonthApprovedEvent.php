<?php

namespace App\Event;

use App\Entity\User;
use App\Entity\WorkMonth;
use Symfony\Contracts\EventDispatcher\Event;

class WorkMonthApprovedEvent extends Event
{
    const APPROVED = 'event.work_month.approved';
    /**
     * @var WorkMonth
     */
    private $workMonth;

    /**
     * @var User|null
     */
    private $supervisor;

    public function __construct(WorkMonth $workMonth, ?User $supervisor)
    {
        $this->workMonth = $workMonth;
        $this->supervisor = $supervisor;
    }

    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
