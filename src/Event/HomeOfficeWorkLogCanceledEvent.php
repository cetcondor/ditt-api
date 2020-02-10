<?php

namespace App\Event;

use App\Entity\HomeOfficeWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class HomeOfficeWorkLogCanceledEvent extends Event
{
    const CANCELED = 'event.home_office_work_log.canceled';

    /**
     * @var HomeOfficeWorkLog
     */
    private $homeOfficeWorkLog;

    /**
     * @var User|null
     */
    private $supervisor;

    public function __construct(HomeOfficeWorkLog $homeOfficeWorkLog, ?User $supervisor)
    {
        $this->homeOfficeWorkLog = $homeOfficeWorkLog;
        $this->supervisor = $supervisor;
    }

    public function getHomeOfficeWorkLog(): HomeOfficeWorkLog
    {
        return $this->homeOfficeWorkLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
