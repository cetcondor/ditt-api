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
     * @var BusinessTripWorkLog[]|Collection
     */
    private $businessTripWorkLogs;

    /**
     * @var HomeOfficeWorkLog[]|Collection
     */
    private $homeOfficeWorkLogs;

    /**
     * @var OvertimeWorkLog[]|Collection
     */
    private $overtimeWorkLogs;

    /**
     * @var SickDayWorkLog[]|Collection
     */
    private $sickDayWorkLogs;

    /**
     * @var TimeOffWorkLog[]|Collection
     */
    private $timeOffWorkLogs;

    /**
     * @var VacationWorkLog[]|Collection
     */
    private $vacationWorkLogs;

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
        $this->businessTripWorkLogs = new ArrayCollection();
        $this->homeOfficeWorkLogs = new ArrayCollection();
        $this->overtimeWorkLogs = new ArrayCollection();
        $this->sickDayWorkLogs = new ArrayCollection();
        $this->timeOffWorkLogs = new ArrayCollection();
        $this->vacationWorkLogs = new ArrayCollection();
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
     * @return BusinessTripWorkLog[]
     */
    public function getBusinessTripWorkLogs(): array
    {
        if ($this->businessTripWorkLogs instanceof Collection) {
            return $this->businessTripWorkLogs->toArray();
        }

        return $this->businessTripWorkLogs;
    }

    /**
     * @param BusinessTripWorkLog[]|Collection $businessTripWorkLogs
     * @return WorkMonth
     */
    public function setBusinessTripWorkLogs($businessTripWorkLogs): WorkMonth
    {
        $this->businessTripWorkLogs = $businessTripWorkLogs;

        return $this;
    }

    /**
     * @return HomeOfficeWorkLog[]
     */
    public function getHomeOfficeWorkLogs(): array
    {
        if ($this->homeOfficeWorkLogs instanceof Collection) {
            return $this->homeOfficeWorkLogs->toArray();
        }

        return $this->homeOfficeWorkLogs;
    }

    /**
     * @param HomeOfficeWorkLog[]|Collection $homeOfficeWorkLogs
     * @return WorkMonth
     */
    public function setHomeOfficeWorkLogs($homeOfficeWorkLogs): WorkMonth
    {
        $this->homeOfficeWorkLogs = $homeOfficeWorkLogs;

        return $this;
    }

    /**
     * @return OvertimeWorkLog[]
     */
    public function getOvertimeWorkLogs(): array
    {
        if ($this->overtimeWorkLogs instanceof Collection) {
            return $this->overtimeWorkLogs->toArray();
        }

        return $this->overtimeWorkLogs;
    }

    /**
     * @param OvertimeWorkLog[]|Collection $overtimeWorkLogs
     * @return WorkMonth
     */
    public function setOvertimeWorkLogs($overtimeWorkLogs): WorkMonth
    {
        $this->overtimeWorkLogs = $overtimeWorkLogs;

        return $this;
    }

    /**
     * @return SickDayWorkLog[]
     */
    public function getSickDayWorkLogs(): array
    {
        if ($this->sickDayWorkLogs instanceof Collection) {
            return $this->sickDayWorkLogs->toArray();
        }

        return $this->sickDayWorkLogs;
    }

    /**
     * @param SickDayWorkLog[]|Collection $sickDayWorkLogs
     * @return WorkMonth
     */
    public function setSickDayWorkLogs($sickDayWorkLogs): WorkMonth
    {
        $this->sickDayWorkLogs = $sickDayWorkLogs;

        return $this;
    }

    /**
     * @return TimeOffWorkLog[]
     */
    public function getTimeOffWorkLogs(): array
    {
        if ($this->timeOffWorkLogs instanceof Collection) {
            return $this->timeOffWorkLogs->toArray();
        }

        return $this->timeOffWorkLogs;
    }

    /**
     * @param TimeOffWorkLog[]|Collection $timeOffWorkLogs
     * @return WorkMonth
     */
    public function setTimeOffWorkLogs($timeOffWorkLogs): WorkMonth
    {
        $this->timeOffWorkLogs = $timeOffWorkLogs;

        return $this;
    }

    /**
     * @return VacationWorkLog[]
     */
    public function getVacationWorkLogs(): array
    {
        if ($this->vacationWorkLogs instanceof Collection) {
            return $this->vacationWorkLogs->toArray();
        }

        return $this->vacationWorkLogs;
    }

    /**
     * @param VacationWorkLog[]|Collection $vacationWorkLogs
     * @return WorkMonth
     */
    public function setVacationWorkLogs($vacationWorkLogs): WorkMonth
    {
        $this->vacationWorkLogs = $vacationWorkLogs;

        return $this;
    }

    /**
     * @return WorkLog[]
     */
    public function getWorkLogs(): array
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
