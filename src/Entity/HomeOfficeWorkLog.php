<?php

namespace App\Entity;

class HomeOfficeWorkLog implements WorkLogInterface
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
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable $date
     * @return HomeOfficeWorkLog
     */
    public function setDate(\DateTimeImmutable $date): HomeOfficeWorkLog
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
     * @return HomeOfficeWorkLog
     */
    public function setTimeApproved(?\DateTimeImmutable $timeApproved): HomeOfficeWorkLog
    {
        $this->timeApproved = $timeApproved;

        return $this;
    }

    /**
     * @return HomeOfficeWorkLog
     */
    public function markApproved(): HomeOfficeWorkLog
    {
        $this->timeApproved = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @return HomeOfficeWorkLog
     */
    public function markRejected(): HomeOfficeWorkLog
    {
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
     * @return HomeOfficeWorkLog
     */
    public function setTimeRejected(?\DateTimeImmutable $timeRejected): HomeOfficeWorkLog
    {
        $this->timeRejected = $timeRejected;

        return $this;
    }

    /**
     * @param int|null $id
     * @return HomeOfficeWorkLog
     */
    public function setId(?int $id): HomeOfficeWorkLog
    {
        $this->id = $id;

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
     * @return HomeOfficeWorkLog
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
