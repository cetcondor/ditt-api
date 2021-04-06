<?php

namespace App\Event;

use App\Entity\SpecialWorkLogInterface;
use App\Entity\User;

interface SpecialWorkLogEventInterface
{
    public function getWorkLog(): SpecialWorkLogInterface;

    public function getSupervisor(): ?User;
}
