<?php

namespace App\Event;

use App\Entity\TimeOffWorkLog;
use Symfony\Component\EventDispatcher\Event;

class TimeOffWorkLogCanceledEvent extends Event
{
    const CANCELED = 'event.time_off_work_log.canceled';

    /**
     * @var TimeOffWorkLog
     */
    private $timeOffWorkLog;

    /**
     * @param TimeOffWorkLog $timeOffWorkLog
     */
    public function __construct(TimeOffWorkLog $timeOffWorkLog)
    {
        $this->timeOffWorkLog = $timeOffWorkLog;
    }

    /**
     * @return TimeOffWorkLog
     */
    public function getTimeOffWorkLog(): TimeOffWorkLog
    {
        return $this->timeOffWorkLog;
    }
}
