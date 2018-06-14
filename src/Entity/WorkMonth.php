<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class WorkMonth
{
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_OPENED = 'OPENED';
    const STATUS_WAITING_FOR_APPROVAL = 'WAITING_FOR_APPROVAL';

    const STATUSES = [
      self::STATUS_APPROVED,
      self::STATUS_OPENED,
      self::STATUS_WAITING_FOR_APPROVAL,
    ];

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @var WorkLog[]|Collection
     */
    private $workLogs;

    /**
     * @var string
     */
    private $status;

    /**
     * @var User
     */
    private $user;

    public function __construct()
    {
        $this->year = 0;
        $this->month = 0;
        $this->workLogs = new ArrayCollection();
        $this->status = self::STATUS_OPENED;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return WorkMonth
     */
    public function setId(int $id): WorkMonth
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     * @return WorkMonth
     */
    public function setYear(int $year): WorkMonth
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return int
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * @param int $month
     * @return WorkMonth
     */
    public function setMonth(int $month): WorkMonth
    {
        $this->month = $month;

        return $this;
    }

    /**
     * @return WorkLog[]
     */
    public function getWorkLogs()
    {
        if ($this->workLogs instanceof Collection) {
            return $this->workLogs->toArray();
        }

        return $this->workLogs;
    }

    /**
     * @param WorkLog[]|Collection $workLogs
     * @return WorkMonth
     */
    public function setWorkLogs($workLogs): WorkMonth
    {
        $this->workLogs = $workLogs;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return WorkMonth
     */
    public function setStatus(string $status): WorkMonth
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return WorkMonth
     */
    public function markApproved(): WorkMonth
    {
        $this->status = self::STATUS_APPROVED;

        return $this;
    }

    /**
     * @return WorkMonth
     */
    public function markWaitingForApproval(): WorkMonth
    {
        $this->status = self::STATUS_WAITING_FOR_APPROVAL;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return WorkMonth
     */
    public function setUser(User $user): WorkMonth
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string[]
     */
    public static function getConstantStatuses(): array
    {
        return self::STATUSES;
    }
}
