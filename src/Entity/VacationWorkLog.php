<?php

namespace App\Entity;

class VacationWorkLog implements WorkLogInterface
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
     * @return VacationWorkLog
     */
    public function setId(?int $id): VacationWorkLog
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
     * @return VacationWorkLog
     */
    public function setDate(\DateTimeImmutable $date): VacationWorkLog
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
     * @return VacationWorkLog
     */
    public function setTimeApproved(?\DateTimeImmutable $timeApproved): VacationWorkLog
    {
        $this->timeApproved = $timeApproved;

        return $this;
    }

    /**
     * @return VacationWorkLog
     */
    public function markApproved(): VacationWorkLog
    {
        $this->timeApproved = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @param string $rejectionMessage
     * @return VacationWorkLog
     */
    public function markRejected(string $rejectionMessage): VacationWorkLog
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
     * @return VacationWorkLog
     */
    public function setTimeRejected(?\DateTimeImmutable $timeRejected): VacationWorkLog
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
     * @return VacationWorkLog
     */
    public function setRejectionMessage(?string $rejectionMessage): VacationWorkLog
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
     * @return VacationWorkLog
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
