<?php

namespace App\Entity;

class SupportedYear
{
    /**
     * @var int
     */
    private $year;

    /**
     * @var SupportedHoliday[]
     */
    private $supportedHolidays;

    public function __construct()
    {
        $this->year = (int) date('Y');
        $this->supportedHolidays = [];
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     * @return SupportedYear
     */
    public function setYear(int $year): SupportedYear
    {
        $this->year = $year;

        return $this;
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
     * @return SupportedYear
     */
    public function setSupportedHolidays(array $supportedHolidays): SupportedYear
    {
        $this->supportedHolidays = $supportedHolidays;

        return $this;
    }
}
