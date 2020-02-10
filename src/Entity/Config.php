<?php

namespace App\Entity;

class Config
{
    /**
     * @var int
     */
    private $id = 1;

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

    public function getId(): int
    {
        // Not used but cannot be removed because normalizer expects identifier
        return 1;
    }

    public function setId(int $id): int
    {
        // Not used but cannot be removed because normalizer expects identifier
        return 1;
    }

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
     */
    public function setSupportedYears(array $supportedYears): Config
    {
        $this->supportedYears = $supportedYears;

        return $this;
    }

    public function getWorkedHoursLimits(): array
    {
        return $this->getRawConfig()['workedHoursLimits'];
    }

    private function getRawConfig(): array
    {
        return include __DIR__ . '/../../config/config.php';
    }
}
