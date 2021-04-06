<?php

namespace App\Event;

use App\Entity\TimeOffWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class TimeOffWorkLogCanceledEvent extends Event implements SpecialWorkLogEventInterface
{
    const EVENT = 'event.time_off_work_log.canceled';

    private TimeOffWorkLog $workLog;
    private ?User $supervisor;

    public function __construct(TimeOffWorkLog $workLog, ?User $supervisor)
    {
        $this->workLog = $workLog;
        $this->supervisor = $supervisor;
    }

    public function getWorkLog(): TimeOffWorkLog
    {
        return $this->workLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
