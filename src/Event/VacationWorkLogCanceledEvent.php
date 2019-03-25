<?php

namespace App\Event;

use App\Entity\User;
use App\Entity\VacationWorkLog;
use Symfony\Component\EventDispatcher\Event;

class VacationWorkLogCanceledEvent extends Event
{
    const CANCELED = 'event.vacation_work_log.canceled';

    /**
     * @var VacationWorkLog
     */
    private $vacationWorkLog;
    /**
     * @var User|null
     */
    private $supervisor;

    /**
     * @param VacationWorkLog $vacationWorkLog
     * @param User|null $supervisor
     */
    public function __construct(VacationWorkLog $vacationWorkLog, ?User $supervisor)
    {
        $this->vacationWorkLog = $vacationWorkLog;
        $this->supervisor = $supervisor;
    }

    /**
     * @return VacationWorkLog
     */
    public function getVacationWorkLog(): VacationWorkLog
    {
        return $this->vacationWorkLog;
    }

    /**
     * @return User|null
     */
    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
