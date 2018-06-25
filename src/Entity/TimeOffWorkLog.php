<?php

namespace App\Entity;

class TimeOffWorkLog implements WorkLogInterface
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
     * @var WorkMonth
     */
    private $workMonth;

    public function __construct()
    {
        $this->date = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
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
     * @return TimeOffWorkLog
     */
    public function setId(?int $id): TimeOffWorkLog
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable $date
     * @return TimeOffWorkLog
     */
    public function setDate(\DateTimeImmutable $date): TimeOffWorkLog
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimeApproved(): ?\DateTimeImmutable
    {
        return $this->timeApproved;
    }

    /**
     * @param \DateTimeImmutable|null $timeApproved
     * @return TimeOffWorkLog
     */
    public function setTimeApproved(?\DateTimeImmutable $timeApproved): TimeOffWorkLog
    {
        $this->timeApproved = $timeApproved;

        return $this;
    }

    /**
     * @return TimeOffWorkLog
     */
    public function markApproved(): TimeOffWorkLog
    {
        $this->timeApproved = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @param string $rejectionMessage
     * @return TimeOffWorkLog
     */
    public function markRejected(string $rejectionMessage): TimeOffWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;
        $this->timeRejected = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getTimeRejected(): ?\DateTimeImmutable
    {
        return $this->timeRejected;
    }

    /**
     * @param \DateTimeImmutable|null $timeRejected
     * @return TimeOffWorkLog
     */
    public function setTimeRejected(?\DateTimeImmutable $timeRejected): TimeOffWorkLog
    {
        $this->timeRejected = $timeRejected;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRejectionMessage(): ?string
    {
        return $this->rejectionMessage;
    }

    /**
     * @param null|string $rejectionMessage
     * @return TimeOffWorkLog
     */
    public function setRejectionMessage(?string $rejectionMessage): TimeOffWorkLog
    {
        $this->rejectionMessage = $rejectionMessage;

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
     * @return TimeOffWorkLog
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
        return $this->date->format('m');
    }

    /**
     * @return int
     */
    public function resolveWorkLogYear(): int
    {
        return $this->date->format('Y');
    }
}
