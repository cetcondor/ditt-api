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
     * @var int
     */
    private $workedHours;

    /**
     * @var int
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

    public function getWorkedHours(): int
    {
        return $this->workedHours;
    }

    public function setWorkedHours(int $workedHours): UserYearStats
    {
        $this->workedHours = $workedHours;

        return $this;
    }

    public function getRequiredHours(): int
    {
        return $this->requiredHours;
    }

    public function setRequiredHours(int $requiredHours): UserYearStats
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
