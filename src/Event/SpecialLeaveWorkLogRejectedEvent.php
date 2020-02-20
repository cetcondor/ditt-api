<?php

namespace App\Event;

use App\Entity\SpecialLeaveWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class SpecialLeaveWorkLogRejectedEvent extends Event
{
    const REJECTED = 'event.special_leave_work_log.rejected';

    /**
     * @var SpecialLeaveWorkLog
     */
    private $specialLeaveWorkLog;

    /**
     * @var User|null
     */
    private $supervisor;

    public function __construct(SpecialLeaveWorkLog $specialLeaveWorkLog, ?User $supervisor)
    {
        $this->specialLeaveWorkLog = $specialLeaveWorkLog;
        $this->supervisor = $supervisor;
    }

    public function getSpecialLeaveWorkLog(): SpecialLeaveWorkLog
    {
        return $this->specialLeaveWorkLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
