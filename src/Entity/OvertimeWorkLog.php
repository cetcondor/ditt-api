<?php

namespace App\Entity;

class OvertimeWorkLog implements WorkLogInterface
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
     * @return OvertimeWorkLog
     */
    public function setId(?int $id): OvertimeWorkLog
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
     * @return OvertimeWorkLog
     */
    public function setDate(\DateTimeImmutable $date): OvertimeWorkLog
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
     * @return OvertimeWorkLog
     */
    public function setTimeApproved(?\DateTimeImmutable $timeApproved): OvertimeWorkLog
    {
        $this->timeApproved = $timeApproved;

        return $this;
    }

    /**
     * @return OvertimeWorkLog
     */
    public function markApproved(): OvertimeWorkLog
    {
        $this->timeApproved = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @param string $rejectionMessage
     * @return OvertimeWorkLog
     */
    public function markRejected(string $rejectionMessage): OvertimeWorkLog
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
     * @return OvertimeWorkLog
     */
    public function setTimeRejected(?\DateTimeImmutable $timeRejected): OvertimeWorkLog
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
     * @return OvertimeWorkLog
     */
    public function setRejectionMessage(?string $rejectionMessage): OvertimeWorkLog
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
     * @return OvertimeWorkLog
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
