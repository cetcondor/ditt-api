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
        $this->year = null;
    }

    /**
     * @return int
     */
    public function getDay(): int
    {
        return $this->day;
    }

    /**
     * @param int $day
     * @return SupportedHoliday
     */
    public function setDay(int $day): SupportedHoliday
    {
        $this->day = $day;

        return $this;
    }

    /**
     * @return int
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * @param int $month
     * @return SupportedHoliday
     */
    public function setMonth(int $month): SupportedHoliday
    {
        $this->month = $month;

        return $this;
    }

    /**
     * @return SupportedYear
     */
    public function getYear(): SupportedYear
    {
        return $this->year;
    }

    /**
     * @param SupportedYear $year
     * @return SupportedHoliday
     */
    public function setYear(SupportedYear $year): SupportedHoliday
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @throws \Exception
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())
            ->setDate($this->getYear(), $this->getMonth(), $this->getDay())
            ->setTime(0, 0, 0, 0);
    }
}
