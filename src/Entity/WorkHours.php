<?php

namespace App\Entity;

class WorkHours
{
    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @var int
     */
    private $requiredHours;

    /**
     * @var User
     */
    private $user;

    public function __construct()
    {
        $this->year = 0;
        $this->month = 0;
        $this->requiredHours = 0;
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
     * @return WorkHours
     */
    public function setYear(int $year): WorkHours
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
     * @return int
     */
    public function getRequiredHours(): int
    {
        return $this->requiredHours;
    }

    /**
     * @param int $requiredHours
     * @return WorkHours
     */
    public function setRequiredHours(int $requiredHours): WorkHours
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
