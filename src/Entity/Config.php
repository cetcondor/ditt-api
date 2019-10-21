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
     * @return bool
     */
    public function isHolidayToday(): bool
    {
        $dateTime = new \DateTimeImmutable();

        foreach ($this->getSupportedHolidays() as $supportedHoliday) {
            if (
                $supportedHoliday->getDay() === intval($dateTime->format('j'))
                && $supportedHoliday->getMonth() === intval($dateTime->format('n'))
                && $supportedHoliday->getYear()->getYear() === intval($dateTime->format('Y'))
            ) {
                return true;
            }
        }

        return false;
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
