<?php

namespace App\Event;

use App\Entity\OvertimeWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class OvertimeWorkLogApprovedEvent extends Event implements SpecialWorkLogEventInterface
{
    const EVENT = 'event.overtime_work_log.approved';

    private OvertimeWorkLog $workLog;
    private ?User $supervisor;

    public function __construct(OvertimeWorkLog $workLog, ?User $supervisor)
    {
        $this->workLog = $workLog;
        $this->supervisor = $supervisor;
    }

    public function getWorkLog(): OvertimeWorkLog
    {
        return $this->workLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
