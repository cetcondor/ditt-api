<?php

namespace App\Event;

use App\Entity\TimeOffWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class TimeOffWorkLogCanceledEvent extends Event
{
    const CANCELED = 'event.time_off_work_log.canceled';

    /**
     * @var TimeOffWorkLog
     */
    private $timeOffWorkLog;
    /**
     * @var User|null
     */
    private $supervisor;

    public function __construct(TimeOffWorkLog $timeOffWorkLog, ?User $supervisor)
    {
        $this->timeOffWorkLog = $timeOffWorkLog;
        $this->supervisor = $supervisor;
    }

    public function getTimeOffWorkLog(): TimeOffWorkLog
    {
        return $this->timeOffWorkLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
