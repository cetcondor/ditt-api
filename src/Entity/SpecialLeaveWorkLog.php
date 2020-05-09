<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class SpecialLeaveWorkLog implements WorkLogInterface
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
     * @var SpecialLeaveWorkLogSupport[]
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

    public function setId(?int $id): SpecialLeaveWorkLog
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): SpecialLeaveWorkLog
    {
        $this->date = $date;

        return $this;
    }

    public function getTimeApproved(): ?\DateTimeImmutable
    {
        return $this->timeApproved;
    }

    public function setTimeApproved(?\DateTimeImmutable $timeApproved): SpecialLeaveWorkLog
    {
        $this->timeApproved = $timeApproved;

        return $this;
    }

    public function markApproved(): SpecialLeaveWorkLog
    {
        $this->timeApproved = new \DateTimeImmutable();

        return $this;
    }

    public function markRejected(string $rejectionMessage): SpecialLeaveWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;
        $this->timeRejected = new \DateTimeImmutable();

        return $this;
    }

    public function getTimeRejected(): ?\DateTimeImmutable
    {
        return $this->timeRejected;
    }

    public function setTimeRejected(?\DateTimeImmutable $timeRejected): SpecialLeaveWorkLog
    {
        $this->timeRejected = $timeRejected;

        return $this;
    }

    public function getRejectionMessage(): ?string
    {
        return $this->rejectionMessage;
    }

    public function setRejectionMessage(?string $rejectionMessage): SpecialLeaveWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;

        return $this;
    }

    /**
     * @return SpecialLeaveWorkLogSupport[]
     */
    public function getSupport(): array
    {
        if ($this->support instanceof Collection) {
            return $this->support->toArray();
        }

        return $this->support;
    }

    /**
     * @param SpecialLeaveWorkLogSupport[]|Collection $support
     */
    public function setSupport(array $support): SpecialLeaveWorkLog
    {
        $this->support = $support;

        return $this;
    }

    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    /**
     * @return SpecialLeaveWorkLog
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
