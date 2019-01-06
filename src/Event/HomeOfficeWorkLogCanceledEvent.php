<?php

namespace App\Event;

use App\Entity\HomeOfficeWorkLog;
use Symfony\Component\EventDispatcher\Event;

class HomeOfficeWorkLogCanceledEvent extends Event
{
    const CANCELED = 'event.home_office_work_log.canceled';

    /**
     * @var HomeOfficeWorkLog
     */
    private $homeOfficeWorkLog;

    /**
     * @param HomeOfficeWorkLog $homeOfficeWorkLog
     */
    public function __construct(HomeOfficeWorkLog $homeOfficeWorkLog)
    {
        $this->homeOfficeWorkLog = $homeOfficeWorkLog;
    }

    /**
     * @return HomeOfficeWorkLog
     */
    public function getHomeOfficeWorkLog(): HomeOfficeWorkLog
    {
        return $this->homeOfficeWorkLog;
    }
}
