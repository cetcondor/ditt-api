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
     * @var SupportedYear
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
     * @var MaternityProtectionWorkLog[]|Collection
     */
    private $maternityProtectionWorkLogs;

    /**
     * @var OvertimeWorkLog[]|Collection
     */
    private $overtimeWorkLogs;

    /**
     * @var ParentalLeaveWorkLog[]|Collection
     */
    private $parentalLeaveWorkLogs;

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
        $this->month = 0;
        $this->businessTripWorkLogs = new ArrayCollection();
        $this->homeOfficeWorkLogs = new ArrayCollection();
        $this->maternityProtectionWorkLogs = new ArrayCollection();
        $this->overtimeWorkLogs = new ArrayCollection();
        $this->parentalLeaveWorkLogs = new ArrayCollection();
        $this->sickDayWorkLogs = new ArrayCollection();
        $this->timeOffWorkLogs = new ArrayCollection();
        $this->vacationWorkLogs = new ArrayCollection();
        $this->workLogs = new ArrayCollection();
        $this->status = self::STATUS_OPENED;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): WorkMonth
    {
        $this->id = $id;

        return $this;
    }

    public function getYear(): SupportedYear
    {
        return $this->year;
    }

    public function setYear(SupportedYear $year): WorkMonth
    {
        $this->year = $year;

        return $this;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

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
     */
    public function setHomeOfficeWorkLogs($homeOfficeWorkLogs): WorkMonth
    {
        $this->homeOfficeWorkLogs = $homeOfficeWorkLogs;

        return $this;
    }

    /**
     * @return MaternityProtectionWorkLog[]
     */
    public function getMaternityProtectionWorkLogs(): array
    {
        if ($this->maternityProtectionWorkLogs instanceof Collection) {
            return $this->maternityProtectionWorkLogs->toArray();
        }

        return $this->maternityProtectionWorkLogs;
    }

    /**
     * @param MaternityProtectionWorkLog[]|Collection $maternityProtectionWorkLogs
     */
    public function setMaternityProtectionWorkLogs($maternityProtectionWorkLogs): WorkMonth
    {
        $this->maternityProtectionWorkLogs = $maternityProtectionWorkLogs;

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
     */
    public function setOvertimeWorkLogs($overtimeWorkLogs): WorkMonth
    {
        $this->overtimeWorkLogs = $overtimeWorkLogs;

        return $this;
    }

    /**
     * @return ParentalLeaveWorkLog[]
     */
    public function getParentalLeaveWorkLogs(): array
    {
        if ($this->parentalLeaveWorkLogs instanceof Collection) {
            return $this->parentalLeaveWorkLogs->toArray();
        }

        return $this->parentalLeaveWorkLogs;
    }

    /**
     * @param ParentalLeaveWorkLog[]|Collection $parentalLeaveWorkLogs
     */
    public function setParentalLeaveWorkLogs($parentalLeaveWorkLogs): WorkMonth
    {
        $this->parentalLeaveWorkLogs = $parentalLeaveWorkLogs;

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
     */
    public function setWorkLogs($workLogs): WorkMonth
    {
        $this->workLogs = $workLogs;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): WorkMonth
    {
        $this->status = $status;

        return $this;
    }

    public function markApproved(): WorkMonth
    {
        $this->status = self::STATUS_APPROVED;

        return $this;
    }

    public function markWaitingForApproval(): WorkMonth
    {
        $this->status = self::STATUS_WAITING_FOR_APPROVAL;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

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
