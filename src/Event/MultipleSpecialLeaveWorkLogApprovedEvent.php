<?php

namespace App\Event;

use App\Entity\SpecialLeaveWorkLog;
use App\Entity\User;
use App\Entity\VacationWorkLog;
use Symfony\Contracts\EventDispatcher\Event;

class MultipleSpecialLeaveWorkLogApprovedEvent extends Event
{
    const APPROVED = 'event.special_leave_work_log.multiple.approved';

    /**
     * @var SpecialLeaveWorkLog[]
     */
    private $specialLeaveWorkLogs;

    /**
     * @var User|null
     */
    private $supervisor;

    /**
     * @param SpecialLeaveWorkLog[] $specialLeaveWorkLogs
     */
    public function __construct(array $specialLeaveWorkLogs, ?User $supervisor)
    {
        $this->specialLeaveWorkLogs = $specialLeaveWorkLogs;
        $this->supervisor = $supervisor;
    }

    /**
     * @return VacationWorkLog[]
     */
    public function getSpecialLeaveWorkLogs(): array
    {
        return $this->specialLeaveWorkLogs;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
