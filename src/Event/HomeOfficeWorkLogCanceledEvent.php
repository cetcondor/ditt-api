<?php

namespace App\Event;

use App\Entity\HomeOfficeWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class HomeOfficeWorkLogCanceledEvent extends Event implements SpecialWorkLogEventInterface
{
    const EVENT = 'event.home_office_work_log.canceled';

    private HomeOfficeWorkLog $workLog;
    private ?User $supervisor;

    public function __construct(HomeOfficeWorkLog $workLog, ?User $supervisor)
    {
        $this->workLog = $workLog;
        $this->supervisor = $supervisor;
    }

    public function getWorkLog(): HomeOfficeWorkLog
    {
        return $this->workLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
