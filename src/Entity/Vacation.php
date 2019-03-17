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
     * @var int|null
     */
    private $remainingVacationDays;

    public function __construct()
    {
        $this->vacationDays = 0;
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
     * @return Vacation
     */
    public function setUser(User $user): Vacation
    {
        $this->user = $user;

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
     * @return Vacation
     */
    public function setYear(SupportedYear $year): Vacation
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return int
     */
    public function getVacationDays(): int
    {
        return $this->vacationDays;
    }

    /**
     * @param int $vacationDays
     * @return Vacation
     */
    public function setVacationDays(int $vacationDays): Vacation
    {
        $this->vacationDays = $vacationDays;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRemainingVacationDays(): ?int
    {
        return $this->remainingVacationDays;
    }

    /**
     * @param int|null $remainingVacationDays
     * @return Vacation
     */
    public function setRemainingVacationDays(?int $remainingVacationDays): Vacation
    {
        $this->remainingVacationDays = $remainingVacationDays;

        return $this;
    }
}
