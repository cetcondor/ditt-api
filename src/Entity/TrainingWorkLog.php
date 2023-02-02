<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class TrainingWorkLog implements SpecialWorkLogInterface
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
     * @var string
     */
    private $title;

    /**
     * @var string|null
     */
    private $comment;

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
     * @var TrainingWorkLogSupport[]
     */
    private $support;

    /**
     * @var WorkMonth
     */
    private $workMonth;

    public function __construct()
    {
        $this->date = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
        $this->title = '';
        $this->support = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): TrainingWorkLog
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): TrainingWorkLog
    {
        $this->date = $date;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): TrainingWorkLog
    {
        $this->title = $title;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): TrainingWorkLog
    {
        $this->comment = $comment;

        return $this;
    }

    public function getTimeApproved(): ?\DateTimeImmutable
    {
        return $this->timeApproved;
    }

    public function setTimeApproved(?\DateTimeImmutable $timeApproved): TrainingWorkLog
    {
        $this->timeApproved = $timeApproved;

        return $this;
    }

    public function markApproved(): TrainingWorkLog
    {
        $this->timeApproved = new \DateTimeImmutable();

        return $this;
    }

    public function markRejected(string $rejectionMessage): TrainingWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;
        $this->timeRejected = new \DateTimeImmutable();

        return $this;
    }

    public function getTimeRejected(): ?\DateTimeImmutable
    {
        return $this->timeRejected;
    }

    public function setTimeRejected(?\DateTimeImmutable $timeRejected): TrainingWorkLog
    {
        $this->timeRejected = $timeRejected;

        return $this;
    }

    public function getRejectionMessage(): ?string
    {
        return $this->rejectionMessage;
    }

    public function setRejectionMessage(?string $rejectionMessage): TrainingWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;

        return $this;
    }

    /**
     * @return TrainingWorkLogSupport[]
     */
    public function getSupport(): array
    {
        if ($this->support instanceof Collection) {
            return $this->support->toArray();
        }

        return $this->support;
    }

    /**
     * @param TrainingWorkLogSupport[]|Collection $support
     */
    public function setSupport(array $support): TrainingWorkLog
    {
        $this->support = $support;

        return $this;
    }

    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    /**
     * @return TrainingWorkLog
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
