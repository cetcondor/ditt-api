<?php

namespace App\Entity;

class WorkLog implements WorkLogInterface
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     */
    private $startTime;

    /**
     * @var \DateTimeImmutable
     */
    private $endTime;

    /**
     * @var WorkMonth
     */
    private $workMonth;

    public function __construct()
    {
        $this->startTime = new \DateTimeImmutable();
        $this->endTime = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): WorkLog
    {
        $this->id = $id;

        return $this;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): WorkLog
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): \DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): WorkLog
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    /**
     * @return WorkLog
     */
    public function setWorkMonth(WorkMonth $workMonth): WorkLogInterface
    {
        $this->workMonth = $workMonth;

        return $this;
    }

    public function resolveWorkLogMonth(): int
    {
        return (int) $this->startTime->format('m');
    }

    public function resolveWorkLogYear(): int
    {
        return (int) $this->startTime->format('Y');
    }
}
