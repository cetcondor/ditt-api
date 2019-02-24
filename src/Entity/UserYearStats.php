<?php

namespace App\Entity;

class UserYearStats
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|User
     */
    private $user;

    /**
     * @var null|SupportedYear
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

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return UserYearStats
     */
    public function setId(?int $id): UserYearStats
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return UserYearStats
     */
    public function setUser(?User $user): UserYearStats
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return SupportedYear|null
     */
    public function getYear(): ?SupportedYear
    {
        return $this->year;
    }

    /**
     * @param SupportedYear|null $year
     * @return UserYearStats
     */
    public function setYear(?SupportedYear $year): UserYearStats
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return float
     */
    public function getWorkedHours(): float
    {
        return $this->workedHours;
    }

    /**
     * @param float $workedHours
     * @return UserYearStats
     */
    public function setWorkedHours(float $workedHours): UserYearStats
    {
        $this->workedHours = $workedHours;

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
     * @return UserYearStats
     */
    public function setRequiredHours(float $requiredHours): UserYearStats
    {
        $this->requiredHours = $requiredHours;

        return $this;
    }

    /**
     * @return int
     */
    public function getVacationDaysUsed(): int
    {
        return $this->vacationDaysUsed;
    }

    /**
     * @param int $vacationDaysUsed
     * @return UserYearStats
     */
    public function setVacationDaysUsed(int $vacationDaysUsed): UserYearStats
    {
        $this->vacationDaysUsed = $vacationDaysUsed;

        return $this;
    }
}
