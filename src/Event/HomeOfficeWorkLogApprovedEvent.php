<?php

namespace App\Event;

use App\Entity\HomeOfficeWorkLog;
use App\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class HomeOfficeWorkLogApprovedEvent extends Event
{
    const APPROVED = 'event.home_office_work_log.approved';

    /**
     * @var HomeOfficeWorkLog
     */
    private $homeOfficeWorkLog;

    /**
     * @var User
     */
    private $supervisor;

    /**
     * @param HomeOfficeWorkLog $homeOfficeWorkLog
     * @param User $supervisor
     */
    public function __construct(HomeOfficeWorkLog $homeOfficeWorkLog, User $supervisor)
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
     * @return User
     */
    public function getSupervisor(): User
    {
        return $this->supervisor;
    }
}
