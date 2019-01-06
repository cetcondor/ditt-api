<?php

namespace App\Event;

use App\Entity\VacationWorkLog;
use Symfony\Component\EventDispatcher\Event;

class VacationWorkLogCanceledEvent extends Event
{
    const CANCELED = 'event.vacation_work_log.canceled';

    /**
     * @var VacationWorkLog
     */
    private $vacationWorkLog;

    /**
     * @param VacationWorkLog $vacationWorkLog
     */
    public function __construct(VacationWorkLog $vacationWorkLog)
    {
        $this->vacationWorkLog = $vacationWorkLog;
    }

    /**
     * @return VacationWorkLog
     */
    public function getVacationWorkLog(): VacationWorkLog
    {
        return $this->vacationWorkLog;
    }
}
