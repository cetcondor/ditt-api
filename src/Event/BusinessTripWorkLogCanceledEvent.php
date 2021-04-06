<?php

namespace App\Event;

use App\Entity\BusinessTripWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class BusinessTripWorkLogCanceledEvent extends Event implements SpecialWorkLogEventInterface
{
    const EVENT = 'event.business_trip_work_log.canceled';

    private BusinessTripWorkLog $workLog;
    private ?User $supervisor;

    public function __construct(BusinessTripWorkLog $workLog, ?User $supervisor)
    {
        $this->workLog = $workLog;
        $this->supervisor = $supervisor;
    }

    public function getWorkLog(): BusinessTripWorkLog
    {
        return $this->workLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
