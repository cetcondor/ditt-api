<?php

namespace App\Entity;

class SupportedHoliday
{
    /**
     * @var int
     */
    private $day;

    /**
     * @var int
     */
    private $month;

    /**
     * @var SupportedYear
     */
    private $year;

    public function __construct()
    {
        $this->day = (int) date('j');
        $this->month = (int) date('n');
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function setDay(int $day): SupportedHoliday
    {
        $this->day = $day;

        return $this;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(int $month): SupportedHoliday
    {
        $this->month = $month;

        return $this;
    }

    public function getYear(): SupportedYear
    {
        return $this->year;
    }

    public function setYear(SupportedYear $year): SupportedHoliday
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function getDate(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())
            ->setDate($this->getYear()->getYear(), $this->getMonth(), $this->getDay())
            ->setTime(0, 0, 0, 0);
    }
}
