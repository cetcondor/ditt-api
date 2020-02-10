<?php

namespace App\Entity;

class WorkHours
{
    /**
     * @var SupportedYear
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @var float
     */
    private $requiredHours;

    /**
     * @var User
     */
    private $user;

    public function __construct()
    {
        $this->month = 0;
        $this->requiredHours = 0;
    }

    public function getYear(): SupportedYear
    {
        return $this->year;
    }

    public function setYear(SupportedYear $year): WorkHours
    {
        $this->year = $year;

        return $this;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(int $month): WorkHours
    {
        $this->month = $month;

        return $this;
    }

    public function getRequiredHours(): float
    {
        return $this->requiredHours;
    }

    public function setRequiredHours(float $requiredHours): WorkHours
    {
        $this->requiredHours = $requiredHours;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): WorkHours
    {
        $this->user = $user;

        return $this;
    }
}
