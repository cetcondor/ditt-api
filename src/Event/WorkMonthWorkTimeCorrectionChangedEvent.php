<?php

namespace App\Event;

use App\Entity\WorkMonth;
use Symfony\Contracts\EventDispatcher\Event;

class WorkMonthWorkTimeCorrectionChangedEvent extends Event
{
    const WORK_TIME_CORRECTION_CHANGED = 'event.work_month.work_time_correction.changed';

    private WorkMonth $workMonth;

    public function __construct(WorkMonth $workMonth)
    {
        $this->workMonth = $workMonth;
    }

    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }
}
