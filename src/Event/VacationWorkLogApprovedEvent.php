<?php

namespace App\Event;

use App\Entity\User;
use App\Entity\VacationWorkLog;
use Symfony\Component\EventDispatcher\Event;

class VacationWorkLogApprovedEvent extends Event
{
    const APPROVED = 'event.vacation_work_log.approved';

    /**
     * @var VacationWorkLog
     */
    private $vacationWorkLog;

    /**
     * @var User
     */
    private $supervisor;

    /**
     * @param VacationWorkLog $vacationWorkLog
     * @param User $supervisor
     */
    public function __construct(VacationWorkLog $vacationWorkLog, User $supervisor)
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
     * @return User
     */
    public function getSupervisor(): User
    {
        return $this->supervisor;
    }
}
