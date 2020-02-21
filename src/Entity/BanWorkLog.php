<?php

namespace App\Entity;

class BanWorkLog implements SupervisorWorkLogInterface
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
     * @var int
     */
    private $workTimeLimit;

    /**
     * @var WorkMonth
     */
    private $workMonth;

    public function __construct()
    {
        $this->date = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
        $this->workTimeLimit = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): BanWorkLog
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): SupervisorWorkLogInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getWorkTimeLimit(): int
    {
        return $this->workTimeLimit;
    }

    public function setWorkTimeLimit(int $workTimeLimit): BanWorkLog
    {
        $this->workTimeLimit = $workTimeLimit;

        return $this;
    }

    public function getWorkMonth(): WorkMonth
    {
        return $this->workMonth;
    }

    /**
     * @return BanWorkLog
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
