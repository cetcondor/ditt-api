<?php

namespace App\Event;

use App\Entity\User;
use App\Entity\WorkMonth;
use Symfony\Component\EventDispatcher\Event;

class WorkMonthApprovedEvent extends Event
{
    const APPROVED = 'event.work_month.approved';
    /**
     * @var WorkMonth
     */
    private $workMonth;

    /**
     * @var User
     */
    private $supervisor;

    /**
     * @param WorkMonth $workMonth
     * @param User $supervisor
     */
    public function __construct(WorkMonth $workMonth, User $supervisor)
    {
        $this->workMonth = $workMonth;
        $this->supervisor = $supervisor;
    }

    /**
     * @return WorkMonth
     */
    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    /**
     * @return User
     */
    public function getSupervisor(): User
    {
        return $this->supervisor;
    }
}
