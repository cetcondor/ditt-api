<?php

namespace App\Event;

use App\Entity\BusinessTripWorkLog;
use Symfony\Component\EventDispatcher\Event;

class BusinessTripWorkLogCanceledEvent extends Event
{
    const CANCELED = 'event.business_trip_work_log.canceled';

    /**
     * @var BusinessTripWorkLog
     */
    private $businessTripWorkLog;

    /**
     * @param BusinessTripWorkLog $businessTripWorkLog
     */
    public function __construct(BusinessTripWorkLog $businessTripWorkLog)
    {
        $this->businessTripWorkLog = $businessTripWorkLog;
    }

    /**
     * @return BusinessTripWorkLog
     */
    public function getBusinessTripWorkLog(): BusinessTripWorkLog
    {
        return $this->businessTripWorkLog;
    }
}
