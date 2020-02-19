<?php

namespace App\Entity;

interface SupervisorWorkLogInterface extends WorkLogInterface
{
    public function getDate(): \DateTimeImmutable;

    public function setDate(\DateTimeImmutable $date): SupervisorWorkLogInterface;
}
