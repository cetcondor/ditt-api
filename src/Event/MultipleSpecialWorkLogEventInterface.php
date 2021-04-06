<?php

namespace App\Event;

use App\Entity\User;

interface MultipleSpecialWorkLogEventInterface
{
    public function getWorkLogs(): array;

    public function getSupervisor(): ?User;
}
