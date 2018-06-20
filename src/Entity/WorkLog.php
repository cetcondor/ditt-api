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

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return WorkLog
     */
    public function setId(?int $id): WorkLog
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    /**
     * @param \DateTimeImmutable $startTime
     * @return WorkLog
     */
    public function setStartTime(\DateTimeImmutable $startTime): WorkLog
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEndTime(): \DateTimeImmutable
    {
        return $this->endTime;
    }

    /**
     * @param \DateTimeImmutable $endTime
     * @return WorkLog
     */
    public function setEndTime(\DateTimeImmutable $endTime): WorkLog
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * @return WorkMonth
     */
    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    /**
     * @param WorkMonth $workMonth
     * @return WorkLog
     */
    public function setWorkMonth(WorkMonth $workMonth): WorkLogInterface
    {
        $this->workMonth = $workMonth;

        return $this;
    }

    /**
     * @return int
     */
    public function resolveWorkLogMonth(): int
    {
        return $this->startTime->format('m');
    }

    /**
     * @return int
     */
    public function resolveWorkLogYear(): int
    {
        return $this->startTime->format('Y');
    }
}
