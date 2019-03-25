<?php

namespace App\Event;

use App\Entity\TimeOffWorkLog;
use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class TimeOffWorkLogRejectedEvent extends Event
{
    const REJECTED = 'event.time_off_work_log.rejected';

    /**
     * @var TimeOffWorkLog
     */
    private $timeOffWorkLog;

    /**
     * @var User|null
     */
    private $supervisor;

    /**
     * @param TimeOffWorkLog $timeOffWorkLog
     * @param User|null $supervisor
     */
    public function __construct(TimeOffWorkLog $timeOffWorkLog, ?User $supervisor)
    {
        $this->timeOffWorkLog = $timeOffWorkLog;
        $this->supervisor = $supervisor;
    }

    /**
     * @return TimeOffWorkLog
     */
    public function getTimeOffWorkLog(): TimeOffWorkLog
    {
        return $this->timeOffWorkLog;
    }

    /**
     * @return User|null
     */
    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
