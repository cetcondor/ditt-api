<?php

namespace App\Entity;

class Vacation
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var SupportedYear
     */
    private $year;

    /**
     * @var int
     */
    private $vacationDays;

    /**
     * @var int
     */
    private $vacationDaysCorrection;

    /**
     * @var int|null
     */
    private $remainingVacationDays;

    public function __construct()
    {
        $this->vacationDays = 0;
        $this->vacationDaysCorrection = 0;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Vacation
    {
        $this->user = $user;

        return $this;
    }

    public function getYear(): SupportedYear
    {
        return $this->year;
    }

    public function setYear(SupportedYear $year): Vacation
    {
        $this->year = $year;

        return $this;
    }

    public function getVacationDays(): int
    {
        return $this->vacationDays;
    }

    public function setVacationDays(int $vacationDays): Vacation
    {
        $this->vacationDays = $vacationDays;

        return $this;
    }

    public function getVacationDaysCorrection(): int
    {
        return $this->vacationDaysCorrection;
    }

    public function setVacationDaysCorrection(int $vacationDaysCorrection): Vacation
    {
        $this->vacationDaysCorrection = $vacationDaysCorrection;

        return $this;
    }

    public function getRemainingVacationDays(): ?int
    {
        return $this->remainingVacationDays;
    }

    public function setRemainingVacationDays(?int $remainingVacationDays): Vacation
    {
        $this->remainingVacationDays = $remainingVacationDays;

        return $this;
    }
}
