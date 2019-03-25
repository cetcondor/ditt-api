<?php

namespace App\Event;

use App\Entity\BusinessTripWorkLog;
use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class BusinessTripWorkLogCanceledEvent extends Event
{
    const CANCELED = 'event.business_trip_work_log.canceled';

    /**
     * @var BusinessTripWorkLog
     */
    private $businessTripWorkLog;

    /**
     * @var User|null
     */
    private $supervisor;

    /**
     * @param BusinessTripWorkLog $businessTripWorkLog
     * @param User|null $supervisor
     */
    public function __construct(BusinessTripWorkLog $businessTripWorkLog, ?User $supervisor)
    {
        $this->businessTripWorkLog = $businessTripWorkLog;
        $this->supervisor = $supervisor;
    }

    /**
     * @return BusinessTripWorkLog
     */
    public function getBusinessTripWorkLog(): BusinessTripWorkLog
    {
        return $this->businessTripWorkLog;
    }

    /**
     * @return User|null
     */
    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
