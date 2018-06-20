<?php

namespace App\Entity;

interface WorkLogInterface
{
    /**
     * @return WorkMonth
     */
    public function getWorkMonth(): WorkMonth;

    /**
     * @param WorkMonth $workMonth
     * @return WorkLogInterface
     */
    public function setWorkMonth(WorkMonth $workMonth): WorkLogInterface;

    /**
     * @return int
     */
    public function resolveWorkLogMonth(): int;

    /**
     * @return int
     */
    public function resolveWorkLogYear(): int;
}
