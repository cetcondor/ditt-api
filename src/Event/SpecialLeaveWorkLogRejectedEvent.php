<?php

namespace App\Event;

use App\Entity\SpecialLeaveWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class SpecialLeaveWorkLogRejectedEvent extends Event implements SpecialWorkLogEventInterface
{
    const EVENT = 'event.special_leave_work_log.rejected';

    private SpecialLeaveWorkLog $workLog;
    private ?User $supervisor;

    public function __construct(SpecialLeaveWorkLog $workLog, ?User $supervisor)
    {
        $this->workLog = $workLog;
        $this->supervisor = $supervisor;
    }

    public function getWorkLog(): SpecialLeaveWorkLog
    {
        return $this->workLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
