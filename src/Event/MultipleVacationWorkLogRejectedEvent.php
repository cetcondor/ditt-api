<?php

namespace App\Event;

use App\Entity\User;
use App\Entity\VacationWorkLog;
use Symfony\Contracts\EventDispatcher\Event;

class MultipleVacationWorkLogRejectedEvent extends Event
{
    const REJECTED = 'event.vacation_work_log.multiple.rejected';

    /**
     * @var VacationWorkLog[]
     */
    private $vacationWorkLogs;

    /**
     * @var User|null
     */
    private $supervisor;

    /**
     * @param VacationWorkLog[] $vacationWorkLogs
     */
    public function __construct(array $vacationWorkLogs, ?User $supervisor)
    {
        $this->vacationWorkLogs = $vacationWorkLogs;
        $this->supervisor = $supervisor;
    }

    /**
     * @return VacationWorkLog[]
     */
    public function getVacationWorkLogs(): array
    {
        return $this->vacationWorkLogs;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
