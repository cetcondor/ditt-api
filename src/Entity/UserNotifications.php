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

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return UserNotifications
     */
    public function setId(?int $id): UserNotifications
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return UserNotifications
     */
    public function setUser(?User $user): UserNotifications
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param \DateTimeImmutable $dateTime
     * @return int
     */
    private function getDayTimeStamp(\DateTimeImmutable $dateTime): int
    {
        return (intval($dateTime->format('H')) * 3600) + (intval($dateTime->format('i')) * 60);
    }

    /**
     * @param bool $isHolidayToday
     * @return bool
     */
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

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSupervisorInfoMondayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoMondayTime;
    }

    /**
     * @param \DateTimeImmutable|null $supervisorInfoMondayTime
     * @return UserNotifications
     */
    public function setSupervisorInfoMondayTime(?\DateTimeImmutable $supervisorInfoMondayTime): UserNotifications
    {
        $this->supervisorInfoMondayTime = $supervisorInfoMondayTime;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSupervisorInfoTuesdayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoTuesdayTime;
    }

    /**
     * @param \DateTimeImmutable|null $supervisorInfoTuesdayTime
     * @return UserNotifications
     */
    public function setSupervisorInfoTuesdayTime(?\DateTimeImmutable $supervisorInfoTuesdayTime): UserNotifications
    {
        $this->supervisorInfoTuesdayTime = $supervisorInfoTuesdayTime;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSupervisorInfoWednesdayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoWednesdayTime;
    }

    /**
     * @param \DateTimeImmutable|null $supervisorInfoWednesdayTime
     * @return UserNotifications
     */
    public function setSupervisorInfoWednesdayTime(?\DateTimeImmutable $supervisorInfoWednesdayTime): UserNotifications
    {
        $this->supervisorInfoWednesdayTime = $supervisorInfoWednesdayTime;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSupervisorInfoThursdayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoThursdayTime;
    }

    /**
     * @param \DateTimeImmutable|null $supervisorInfoThursdayTime
     * @return UserNotifications
     */
    public function setSupervisorInfoThursdayTime(?\DateTimeImmutable $supervisorInfoThursdayTime): UserNotifications
    {
        $this->supervisorInfoThursdayTime = $supervisorInfoThursdayTime;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSupervisorInfoFridayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoFridayTime;
    }

    /**
     * @param \DateTimeImmutable|null $supervisorInfoFridayTime
     * @return UserNotifications
     */
    public function setSupervisorInfoFridayTime(?\DateTimeImmutable $supervisorInfoFridayTime): UserNotifications
    {
        $this->supervisorInfoFridayTime = $supervisorInfoFridayTime;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSupervisorInfoSaturdayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoSaturdayTime;
    }

    /**
     * @param \DateTimeImmutable|null $supervisorInfoSaturdayTime
     * @return UserNotifications
     */
    public function setSupervisorInfoSaturdayTime(?\DateTimeImmutable $supervisorInfoSaturdayTime): UserNotifications
    {
        $this->supervisorInfoSaturdayTime = $supervisorInfoSaturdayTime;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSupervisorInfoSundayTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoSundayTime;
    }

    /**
     * @param \DateTimeImmutable|null $supervisorInfoSundayTime
     * @return UserNotifications
     */
    public function setSupervisorInfoSundayTime(?\DateTimeImmutable $supervisorInfoSundayTime): UserNotifications
    {
        $this->supervisorInfoSundayTime = $supervisorInfoSundayTime;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSupervisorInfoSendOnHolidays(): bool
    {
        return $this->supervisorInfoSendOnHolidays;
    }

    /**
     * @param bool $supervisorInfoSendOnHolidays
     * @return UserNotifications
     */
    public function setSupervisorInfoSendOnHolidays(bool $supervisorInfoSendOnHolidays): UserNotifications
    {
        $this->supervisorInfoSendOnHolidays = $supervisorInfoSendOnHolidays;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSupervisorInfoLastNotificationDateTime(): ?\DateTimeImmutable
    {
        return $this->supervisorInfoLastNotificationDateTime;
    }

    /**
     * @param \DateTimeImmutable|null $supervisorInfoLastNotificationDateTime
     * @return UserNotifications
     */
    public function setSupervisorInfoLastNotificationDateTime(?\DateTimeImmutable $supervisorInfoLastNotificationDateTime): UserNotifications
    {
        $this->supervisorInfoLastNotificationDateTime = $supervisorInfoLastNotificationDateTime;

        return $this;
    }
}
