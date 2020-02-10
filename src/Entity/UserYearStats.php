<?php

namespace App\Entity;

class UserYearStats
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var SupportedYear|null
     */
    private $year;

    /**
     * @var float
     */
    private $workedHours;

    /**
     * @var float
     */
    private $requiredHours;

    /**
     * @var int
     */
    private $vacationDaysUsed;

    public function __construct()
    {
        $this->id = null;
        $this->user = null;
        $this->year = null;
        $this->requiredHours = 0;
        $this->workedHours = 0;
        $this->vacationDaysUsed = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): UserYearStats
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): UserYearStats
    {
        $this->user = $user;

        return $this;
    }

    public function getYear(): ?SupportedYear
    {
        return $this->year;
    }

    public function setYear(?SupportedYear $year): UserYearStats
    {
        $this->year = $year;

        return $this;
    }

    public function getWorkedHours(): float
    {
        return $this->workedHours;
    }

    public function setWorkedHours(float $workedHours): UserYearStats
    {
        $this->workedHours = $workedHours;

        return $this;
    }

    public function getRequiredHours(): float
    {
        return $this->requiredHours;
    }

    public function setRequiredHours(float $requiredHours): UserYearStats
    {
        $this->requiredHours = $requiredHours;

        return $this;
    }

    public function getVacationDaysUsed(): int
    {
        return $this->vacationDaysUsed;
    }

    public function setVacationDaysUsed(int $vacationDaysUsed): UserYearStats
    {
        $this->vacationDaysUsed = $vacationDaysUsed;

        return $this;
    }
}
