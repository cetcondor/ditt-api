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
     * @var null|int
     */
    private $year;

    /**
     * @var int
     */
    private $vacationDaysUsed;

    public function __construct()
    {
        $this->id = null;
        $this->user = null;
        $this->year = null;
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
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * @param int|null $year
     * @return UserYearStats
     */
    public function setYear(?int $year): UserYearStats
    {
        $this->year = $year;

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
