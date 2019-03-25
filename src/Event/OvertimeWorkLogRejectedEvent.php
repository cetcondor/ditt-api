<?php

namespace App\Event;

use App\Entity\OvertimeWorkLog;
use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class OvertimeWorkLogRejectedEvent extends Event
{
    const REJECTED = 'event.overtime_work_log.rejected';

    /**
     * @var OvertimeWorkLog
     */
    private $overtimeWorkLog;

    /**
     * @var User|null
     */
    private $supervisor;

    /**
     * @param OvertimeWorkLog $overtimeWorkLog
     * @param User|null $supervisor
     */
    public function __construct(OvertimeWorkLog $overtimeWorkLog, ?User $supervisor)
    {
        $this->overtimeWorkLog = $overtimeWorkLog;
        $this->supervisor = $supervisor;
    }

    /**
     * @return OvertimeWorkLog
     */
    public function getOvertimeWorkLog(): OvertimeWorkLog
    {
        return $this->overtimeWorkLog;
    }

    /**
     * @return User|null
     */
    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
