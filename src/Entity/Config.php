<?php

namespace App\Entity;

class Config
{
    /**
     * @var SupportedHoliday[]
     */
    private $supportedHolidays;

    /**
     * @var SupportedYear[]
     */
    private $supportedYears;

    public function __construct()
    {
        $this->supportedHolidays = [];
        $this->supportedYears = [];
    }

    /**
     * @return SupportedHoliday[]
     */
    public function getSupportedHolidays(): array
    {
        return $this->supportedHolidays;
    }

    /**
     * @param SupportedHoliday[] $supportedHolidays
     * @return Config
     */
    public function setSupportedHolidays(array $supportedHolidays): Config
    {
        $this->supportedHolidays = $supportedHolidays;

        return $this;
    }

    /**
     * @return SupportedYear[]
     */
    public function getSupportedYears(): array
    {
        return $this->supportedYears;
    }

    /**
     * @param SupportedYear[] $supportedYears
     * @return Config
     */
    public function setSupportedYears(array $supportedYears): Config
    {
        $this->supportedYears = $supportedYears;

        return $this;
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
