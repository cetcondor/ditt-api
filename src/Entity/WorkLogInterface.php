<?php

namespace App\Entity;

interface WorkLogInterface
{
    public function getWorkMonth(): WorkMonth;

    public function setWorkMonth(WorkMonth $workMonth): WorkLogInterface;

    public function resolveWorkLogMonth(): int;

    public function resolveWorkLogYear(): int;
}
