<?php

namespace App\Entity;

class Contract
{
    private $id;
    private ?User $user;
    private \DateTimeImmutable $startDateTime;
    private ?\DateTimeImmutable $endDateTime;
    private bool $isDayBased;
    private bool $isMondayIncluded;
    private bool $isTuesdayIncluded;
    private bool $isWednesdayIncluded;
    private bool $isThursdayIncluded;
    private bool $isFridayIncluded;
    private int $weeklyWorkingDays;
    private float $weeklyWorkingHours;

    public function __construct()
    {
        $this->startDateTime = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
        $this->endDateTime = null;
        $this->isDayBased = true;
        $this->isMondayIncluded = true;
        $this->isTuesdayIncluded = true;
        $this->isWednesdayIncluded = true;
        $this->isThursdayIncluded = true;
        $this->isFridayIncluded = true;
        $this->weeklyWorkingDays = 5;
        $this->weeklyWorkingHours = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Contract
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): Contract
    {
        $this->user = $user;

        return $this;
    }

    public function getStartDateTime(): \DateTimeImmutable
    {
        return $this->startDateTime;
    }

    public function setStartDateTime(\DateTimeImmutable $startDateTime): Contract
    {
        $this->startDateTime = $startDateTime->setTime(0, 0, 0, 0);

        return $this;
    }

    public function getEndDateTime(): ?\DateTimeImmutable
    {
        return $this->endDateTime;
    }

    public function setEndDateTime(?\DateTimeImmutable $endDateTime): Contract
    {
        if ($endDateTime) {
            $this->endDateTime = $endDateTime->setTime(23, 59, 59, 999);
        } else {
            $this->endDateTime = $endDateTime;
        }

        return $this;
    }

    public function getIsDayBased(): bool
    {
        return $this->isDayBased;
    }

    public function setIsDayBased(bool $isDayBased): Contract
    {
        $this->isDayBased = $isDayBased;

        return $this;
    }

    public function getIsMondayIncluded(): bool
    {
        return $this->isMondayIncluded;
    }

    public function setIsMondayIncluded(bool $isMondayIncluded): Contract
    {
        $this->isMondayIncluded = $isMondayIncluded;

        return $this;
    }

    public function getIsTuesdayIncluded(): bool
    {
        return $this->isTuesdayIncluded;
    }

    public function setIsTuesdayIncluded(bool $isTuesdayIncluded): Contract
    {
        $this->isTuesdayIncluded = $isTuesdayIncluded;

        return $this;
    }

    public function getIsWednesdayIncluded(): bool
    {
        return $this->isWednesdayIncluded;
    }

    public function setIsWednesdayIncluded(bool $isWednesdayIncluded): Contract
    {
        $this->isWednesdayIncluded = $isWednesdayIncluded;

        return $this;
    }

    public function getIsThursdayIncluded(): bool
    {
        return $this->isThursdayIncluded;
    }

    public function setIsThursdayIncluded(bool $isThursdayIncluded): Contract
    {
        $this->isThursdayIncluded = $isThursdayIncluded;

        return $this;
    }

    public function getIsFridayIncluded(): bool
    {
        return $this->isFridayIncluded;
    }

    public function setIsFridayIncluded(bool $isFridayIncluded): Contract
    {
        $this->isFridayIncluded = $isFridayIncluded;

        return $this;
    }

    public function getWeeklyWorkingDays(): int
    {
        return $this->weeklyWorkingDays;
    }

    public function setWeeklyWorkingDays(int $weeklyWorkingDays): Contract
    {
        $this->weeklyWorkingDays = $weeklyWorkingDays;

        return $this;
    }

    public function getWeeklyWorkingHours(): float
    {
        return $this->weeklyWorkingHours;
    }

    public function setWeeklyWorkingHours(float $weeklyWorkingHours): Contract
    {
        $this->weeklyWorkingHours = $weeklyWorkingHours;

        return $this;
    }
}
