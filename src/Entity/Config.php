<?php

namespace App\Entity;

class Config
{
    /**
     * @return \DateTime[]
     */
    public function getSupportedHolidays(): array
    {
        return \config\config::getSupportedHolidays();
    }

    /**
     * @return int[]
     */
    public function getSupportedYear(): array
    {
        return \config\config::getSupportedYear();
    }

    /**
     * @return array
     */
    public function getWorkedHoursLimits(): array
    {
        return \config\config::getWorkedHoursLimits();
    }
}
