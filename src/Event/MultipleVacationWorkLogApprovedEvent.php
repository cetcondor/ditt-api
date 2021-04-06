<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class MultipleVacationWorkLogApprovedEvent extends Event implements MultipleSpecialWorkLogEventInterface
{
    const EVENT = 'event.vacation_work_log.multiple.approved';

    private array $workLogs;
    private ?User $supervisor;

    public function __construct(array $workLogs, ?User $supervisor)
    {
        $this->workLogs = $workLogs;
        $this->supervisor = $supervisor;
    }

    public function getWorkLogs(): array
    {
        return $this->workLogs;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
