<?php

namespace App\Event;

use App\Entity\TrainingWorkLog;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class TrainingWorkLogRejectedEvent extends Event implements SpecialWorkLogEventInterface
{
    const EVENT = 'event.training_work_log.rejected';

    private TrainingWorkLog $workLog;
    private ?User $supervisor;

    public function __construct(TrainingWorkLog $workLog, ?User $supervisor)
    {
        $this->workLog = $workLog;
        $this->supervisor = $supervisor;
    }

    public function getWorkLog(): TrainingWorkLog
    {
        return $this->workLog;
    }

    public function getSupervisor(): ?User
    {
        return $this->supervisor;
    }
}
