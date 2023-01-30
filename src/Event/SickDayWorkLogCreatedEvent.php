<?php

namespace App\Event;

use App\Entity\SickDayWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class SickDayWorkLogCreatedEvent extends Event
{
    const EVENT = 'event.sick_day_work_log.created';

    private SickDayWorkLog $workLog;
    private ?User $supervisor;

    public function __construct(SickDayWorkLog $workLog, ?User $supervisor)
    {
        $this->workLog = $workLog;
        $this->supervisor = $supervisor;
    }

    public function getWorkLog(): SickDayWorkLog
    {
        return $this->workLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
