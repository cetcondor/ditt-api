<?php

namespace App\Event;

use App\Entity\OvertimeWorkLog;
use Symfony\Component\EventDispatcher\Event;

class OvertimeWorkLogCanceledEvent extends Event
{
    const CANCELED = 'event.overtime_work_log.canceled';

    /**
     * @var OvertimeWorkLog
     */
    private $overtimeWorkLog;

    /**
     * @param OvertimeWorkLog $overtimeWorkLog
     */
    public function __construct(OvertimeWorkLog $overtimeWorkLog)
    {
        $this->overtimeWorkLog = $overtimeWorkLog;
    }

    /**
     * @return OvertimeWorkLog
     */
    public function getOvertimeWorkLog(): OvertimeWorkLog
    {
        return $this->overtimeWorkLog;
    }
}
