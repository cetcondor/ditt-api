<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class HomeOfficeWorkLog implements SpecialWorkLogInterface
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var int
     */
    private $plannedEndHour;

    /**
     * @var int
     */
    private $plannedEndMinute;

    /**
     * @var int
     */
    private $plannedStartHour;

    /**
     * @var int
     */
    private $plannedStartMinute;

    /**
     * @var \DateTimeImmutable|null
     */
    private $timeApproved;

    /**
     * @var \DateTimeImmutable|null
     */
    private $timeRejected;

    /**
     * @var string|null
     */
    private $rejectionMessage;

    /**
     * @var HomeOfficeWorkLogSupport[]
     */
    private $support;

    /**
     * @var WorkMonth
     */
    private $workMonth;

    public function __construct()
    {
        $this->date = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
        $this->support = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): HomeOfficeWorkLog
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): HomeOfficeWorkLog
    {
        $this->date = $date;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): HomeOfficeWorkLog
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPlannedEndHour(): int
    {
        return $this->plannedEndHour;
    }

    public function setPlannedEndHour(int $plannedEndHour): HomeOfficeWorkLog
    {
        $this->plannedEndHour = $plannedEndHour;

        return $this;
    }

    public function getPlannedEndMinute(): int
    {
        return $this->plannedEndMinute;
    }

    public function setPlannedEndMinute(int $plannedEndMinute): HomeOfficeWorkLog
    {
        $this->plannedEndMinute = $plannedEndMinute;

        return $this;
    }

    public function getPlannedStartHour(): int
    {
        return $this->plannedStartHour;
    }

    public function setPlannedStartHour(int $plannedStartHour): HomeOfficeWorkLog
    {
        $this->plannedStartHour = $plannedStartHour;

        return $this;
    }

    public function getPlannedStartMinute(): int
    {
        return $this->plannedStartMinute;
    }

    public function setPlannedStartMinute(int $plannedStartMinute): HomeOfficeWorkLog
    {
        $this->plannedStartMinute = $plannedStartMinute;

        return $this;
    }

    public function getTimeApproved(): ?\DateTimeImmutable
    {
        return $this->timeApproved;
    }

    public function setTimeApproved(?\DateTimeImmutable $timeApproved): HomeOfficeWorkLog
    {
        $this->timeApproved = $timeApproved;

        return $this;
    }

    public function markApproved(): HomeOfficeWorkLog
    {
        $this->timeApproved = new \DateTimeImmutable();

        return $this;
    }

    public function markRejected(string $rejectionMessage): HomeOfficeWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;
        $this->timeRejected = new \DateTimeImmutable();

        return $this;
    }

    public function getTimeRejected(): ?\DateTimeImmutable
    {
        return $this->timeRejected;
    }

    public function setTimeRejected(?\DateTimeImmutable $timeRejected): HomeOfficeWorkLog
    {
        $this->timeRejected = $timeRejected;

        return $this;
    }

    public function getRejectionMessage(): ?string
    {
        return $this->rejectionMessage;
    }

    public function setRejectionMessage(?string $rejectionMessage): HomeOfficeWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;

        return $this;
    }

    /**
     * @return HomeOfficeWorkLogSupport[]
     */
    public function getSupport(): array
    {
        if ($this->support instanceof Collection) {
            return $this->support->toArray();
        }

        return $this->support;
    }

    /**
     * @param HomeOfficeWorkLogSupport[]|Collection $support
     */
    public function setSupport(array $support): HomeOfficeWorkLog
    {
        $this->support = $support;

        return $this;
    }

    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    /**
     * @return HomeOfficeWorkLog
     */
    public function setWorkMonth(WorkMonth $workMonth): WorkLogInterface
    {
        $this->workMonth = $workMonth;

        return $this;
    }

    public function resolveWorkLogMonth(): int
    {
        return (int) $this->date->format('m');
    }

    public function resolveWorkLogYear(): int
    {
        return (int) $this->date->format('Y');
    }
}
