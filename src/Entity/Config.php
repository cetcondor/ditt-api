<?php

namespace App\Entity;

class Config
{
    /**
     * @return \DateTime[]
     */
    public function getSupportedHolidays(): array
    {
        return $this->getRawConfig()['supportedHolidays'];
    }

    /**
     * @return int[]
     */
    public function getSupportedYear(): array
    {
        return $this->getRawConfig()['supportedYear'];
    }

    /**
     * @return array
     */
    public function getWorkedHoursLimits(): array
    {
        return $this->getRawConfig()['workedHoursLimits'];
    }

    /**
     * @return array
     */
    private function getRawConfig(): array
    {
        return include __DIR__ . '/../../config/config.php';
    }
}
