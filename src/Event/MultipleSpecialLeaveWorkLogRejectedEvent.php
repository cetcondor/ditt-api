<?php

namespace App\Event;

use App\Entity\SpecialLeaveWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class MultipleSpecialLeaveWorkLogRejectedEvent extends Event
{
    const REJECTED = 'event.special_leave_work_log.multiple.rejected';

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
     * @return SpecialLeaveWorkLog[]
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
