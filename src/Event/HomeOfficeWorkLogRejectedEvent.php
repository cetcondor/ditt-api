<?php

namespace App\Event;

use App\Entity\HomeOfficeWorkLog;
use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class HomeOfficeWorkLogRejectedEvent extends Event
{
    const REJECTED = 'event.home_office_work_log.rejected';

    /**
     * @var HomeOfficeWorkLog
     */
    private $homeOfficeWorkLog;

    /**
     * @var User|null
     */
    private $supervisor;

    /**
     * @param HomeOfficeWorkLog $homeOfficeWorkLog
     * @param User|null $supervisor
     */
    public function __construct(HomeOfficeWorkLog $homeOfficeWorkLog, ?User $supervisor)
    {
        $this->homeOfficeWorkLog = $homeOfficeWorkLog;
        $this->supervisor = $supervisor;
    }

    /**
     * @return HomeOfficeWorkLog
     */
    public function getHomeOfficeWorkLog(): HomeOfficeWorkLog
    {
        return $this->homeOfficeWorkLog;
    }

    /**
     * @return User|null
     */
    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
