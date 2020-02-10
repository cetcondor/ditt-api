<?php

namespace App\Entity;

class UserNotifications
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var \DateTimeImmutable|null
     */
    private $supervisorInfoMondayTime;
    /**
     * @var \DateTimeImmutable|null
     */
    private $supervisorInfoTuesdayTime;
    /**
     * @var \DateTimeImmutable|null
     */
    private $supervisorInfoWednesdayTime;
    /**
     * @var \DateTimeImmutable|null
     */
    private $supervisorInfoThursdayTime;
    /**
     * @var \DateTimeImmutable|null
     */
    private $supervisorInfoFridayTime;
    /**
     * @var \DateTimeImmutable|null
     */
    private $supervisorInfoSaturdayTime;
    /**
     * @var \DateTimeImmutable|null
     */
    private $supervisorInfoSundayTime;

    /**
     * @var bool
     */
    private $supervisorInfoSendOnHolidays;

    /**
     * @var \DateTimeImmutable|null
     */
    private $supervisorInfoLastNotificationDateTime;

    public function __construct()
    {
        $this->supervisorInfoMondayTime = new \DateTimeImmutable('08:00:00');
        $this->supervisorInfoTuesdayTime = new \DateTimeImmutable('08:00:00');
        $this->supervisorInfoWednesdayTime = new \DateTimeImmutable('08:00:00');
        $this->supervisorInfoThursdayTime = new \DateTimeImmutable('08:00:00');
        $this->supervisorInfoFridayTime = new \DateTimeImmutable('08:00:00');
        $this->supervisorInfoSendOnHolidays = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): UserNotifications
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): UserNotifications
    {
        $this->user = $user;

        return $this;
    }

    private function getDayTimeStamp(\DateTimeImmutable $dateTime): int
    {
        return (intval($dateTime->format('H')) * 3600) + (intval($dateTime->format('i')) * 60);
    }

    public function canSendSupervisorInfo(bool $isHolidayToday): bool
    {
        $currentDateTime = new \DateTimeImmutable();
        $currentDayTimeStamp = $this->getDayTimeStamp($currentDateTime);
        $currentDayOfWeek = intval($currentDateTime->format('w'));

        if ($isHolidayToday && !$this->isSupervisorInfoSendOnHolidays()) {
            return false;
        }

        $lastSentDateTime = $this->getSupervisorInfoLastNotificationDateTime();
        $lastSentDayOfWeek = null;

        if ($lastSentDateTime !== null) {
            $lastSentDayOfWeek = intval($lastSentDateTime->format('w'));
        }

        $sendNotificationTimes = [
            $this->getSupervisorInfoSundayTime(),
            $this->getSupervisorInfoMondayTime(),
            $this->getSupervisorInfoTuesdayTime(),
            $this->getSupervisorInfoWednesdayTime(),
            $this->getSupervisorInfoThursdayTime(),
            $this->getSupervisorInfoFridayTime(),
            $this->getSupervisorInfoSaturdayTime(),
        ];

        if (
            $lastSentDateTime === null
            && $sendNotificationTimes[$currentDayOfWeek] !== null
            && $this->getDayTimeStamp($sendNotificationTimes[$currentDayOfWeek]) <= $currentDayTimeStamp
        ) {
            return true;
        }

        if (
            $lastSentDateTime !== null
            && $lastSentDayOfWeek !== $currentDayOfWeek
            && $sendNotificationTimes[$currentDayOfWeek] !== null
            && $this->getDayTimeStamp($sendNotificationTimes[$currentDayOfWeek]) <= $currentDayTimeStamp
        ) {
            return true;
        }

        return false;
    }

    public function getSupervisorInfoMondayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoMondayTime;
    }

    public function setSupervisorInfoMondayTime(?\DateTimeImmutable $supervisorInfoMondayTime): UserNotifications
    {
        $this->supervisorInfoMondayTime = $supervisorInfoMondayTime;

        return $this;
    }

    public function getSupervisorInfoTuesdayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoTuesdayTime;
    }

    public function setSupervisorInfoTuesdayTime(?\DateTimeImmutable $supervisorInfoTuesdayTime): UserNotifications
    {
        $this->supervisorInfoTuesdayTime = $supervisorInfoTuesdayTime;

        return $this;
    }

    public function getSupervisorInfoWednesdayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoWednesdayTime;
    }

    public function setSupervisorInfoWednesdayTime(?\DateTimeImmutable $supervisorInfoWednesdayTime): UserNotifications
    {
        $this->supervisorInfoWednesdayTime = $supervisorInfoWednesdayTime;

        return $this;
    }

    public function getSupervisorInfoThursdayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoThursdayTime;
    }

    public function setSupervisorInfoThursdayTime(?\DateTimeImmutable $supervisorInfoThursdayTime): UserNotifications
    {
        $this->supervisorInfoThursdayTime = $supervisorInfoThursdayTime;

        return $this;
    }

    public function getSupervisorInfoFridayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoFridayTime;
    }

    public function setSupervisorInfoFridayTime(?\DateTimeImmutable $supervisorInfoFridayTime): UserNotifications
    {
        $this->supervisorInfoFridayTime = $supervisorInfoFridayTime;

        return $this;
    }

    public function getSupervisorInfoSaturdayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoSaturdayTime;
    }

    public function setSupervisorInfoSaturdayTime(?\DateTimeImmutable $supervisorInfoSaturdayTime): UserNotifications
    {
        $this->supervisorInfoSaturdayTime = $supervisorInfoSaturdayTime;

        return $this;
    }

    public function getSupervisorInfoSundayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoSundayTime;
    }

    public function setSupervisorInfoSundayTime(?\DateTimeImmutable $supervisorInfoSundayTime): UserNotifications
    {
        $this->supervisorInfoSundayTime = $supervisorInfoSundayTime;

        return $this;
    }

    public function isSupervisorInfoSendOnHolidays(): bool
    {
        return $this->supervisorInfoSendOnHolidays;
    }

    public function setSupervisorInfoSendOnHolidays(bool $supervisorInfoSendOnHolidays): UserNotifications
    {
        $this->supervisorInfoSendOnHolidays = $supervisorInfoSendOnHolidays;

        return $this;
    }

    public function getSupervisorInfoLastNotificationDateTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoLastNotificationDateTime;
    }

    public function setSupervisorInfoLastNotificationDateTime(?\DateTimeImmutable $supervisorInfoLastNotificationDateTime): UserNotifications
    {
        $this->supervisorInfoLastNotificationDateTime = $supervisorInfoLastNotificationDateTime;

        return $this;
    }
}
