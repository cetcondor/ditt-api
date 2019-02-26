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

    /**
     * @return SupportedYear
     */
    public function getYear(): SupportedYear
    {
        return $this->year;
    }

    /**
     * @param SupportedYear $year
     * @return WorkHours
     */
    public function setYear(SupportedYear $year): WorkHours
    {
        $this->year = $year;

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
     * @return WorkHours
     */
    public function setMonth(int $month): WorkHours
    {
        $this->month = $month;

        return $this;
    }

    /**
     * @return float
     */
    public function getRequiredHours(): float
    {
        return $this->requiredHours;
    }

    /**
     * @param float $requiredHours
     * @return WorkHours
     */
    public function setRequiredHours(float $requiredHours): WorkHours
    {
        $this->requiredHours = $requiredHours;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return WorkHours
     */
    public function setUser(User $user): WorkHours
    {
        $this->user = $user;

        return $this;
    }
}
