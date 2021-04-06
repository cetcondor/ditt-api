<?php

namespace App\Event;

use App\Entity\User;
use App\Entity\VacationWorkLog;
use Symfony\Contracts\EventDispatcher\Event;

class VacationWorkLogApprovedEvent extends Event implements SpecialWorkLogEventInterface
{
    const EVENT = 'event.vacation_work_log.approved';

    private VacationWorkLog $workLog;
    private ?User $supervisor;

    public function __construct(VacationWorkLog $workLog, ?User $supervisor)
    {
        $this->workLog = $workLog;
        $this->supervisor = $supervisor;
    }

    public function getWorkLog(): VacationWorkLog
    {
        return $this->workLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
