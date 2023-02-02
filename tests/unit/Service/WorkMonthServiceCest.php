<?php

namespace unit\Service;

use App\Entity\BanWorkLog;
use App\Entity\BusinessTripWorkLog;
use App\Entity\Config;
use App\Entity\HomeOfficeWorkLog;
use App\Entity\MaternityProtectionWorkLog;
use App\Entity\ParentalLeaveWorkLog;
use App\Entity\SickDayWorkLog;
use App\Entity\SupportedHoliday;
use App\Entity\SupportedYear;
use App\Entity\TimeOffWorkLog;
use App\Entity\User;
use App\Entity\VacationWorkLog;
use App\Entity\WorkHours;
use App\Entity\WorkLog;
use App\Entity\WorkMonth;
use App\Repository\BanWorkLogRepository;
use App\Repository\BusinessTripWorkLogRepository;
use App\Repository\HomeOfficeWorkLogRepository;
use App\Repository\MaternityProtectionWorkLogRepository;
use App\Repository\ParentalLeaveWorkLogRepository;
use App\Repository\SickDayWorkLogRepository;
use App\Repository\SpecialLeaveWorkLogRepository;
use App\Repository\TimeOffWorkLogRepository;
use App\Repository\TrainingWorkLogRepository;
use App\Repository\UserYearStatsRepository;
use App\Repository\VacationWorkLogRepository;
use App\Repository\WorkHoursRepository;
use App\Repository\WorkLogRepository;
use App\Service\ConfigService;
use App\Service\WorkMonthService;
use Doctrine\ORM\EntityManager;
use Prophecy\Argument;
use Prophecy\Prophet;

class WorkMonthServiceCest
{
    /**
     * @throws \Exception
     */
    public function testCalculateNoWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateShortStandardWorkLogsWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateShortStandardWorkLogsWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 16:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsMoreThan6HoursLong(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21000, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveLowerLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveLowerLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:35:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveLowerLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveMediumLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(24300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveMediumLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(24300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveMediumLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(25200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveUpperLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(33300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveUpperLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(34200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveUpperLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 19:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 21:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(36000, $service->calculateWorkedHours($workMonth));
    }

    // Standard work log with work month work time correction

    /**
     * @throws \Exception
     */
    public function testCalculateShortStandardWorkLogsWithoutBreakWithPositiveWorkTimeCorrection(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet, 3600);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(18000, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateShortStandardWorkLogsWithoutBreakWithNegativeWorkTimeCorrection(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet, -3600);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(10800, $service->calculateWorkedHours($workMonth));
    }

    // Standard work log during public holidays

    /**
     * @throws \Exception
     */
    public function testCalculateShortStandardWorkLogsWithoutBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(19440, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateShortStandardWorkLogsWithLongBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 15:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 16:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 17:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(19440, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsMoreThan6HoursLongDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 12:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(28350, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveLowerLimitWithoutBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 14:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(29160, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveLowerLimitWithShortBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 14:35:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(30375, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveLowerLimitWithLongBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 17:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(30375, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveMediumLimitWithoutBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 15:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(31590, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveMediumLimitWithShortBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(32805, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveMediumLimitWithLongBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 18:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(34020, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveUpperLimitWithoutBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 18:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(44955, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveUpperLimitWithShortBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(46170, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateStandardWorkLogsAboveUpperLimitWithLongBreakDuringPublicHolidays(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 18:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-01 19:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 21:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(48600, $service->calculateWorkedHours($workMonth));
    }

    // Ban work logs

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithShortStandardWorkLogsWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithShortStandardWorkLogsWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 16:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsMoreThan6HoursLong(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsAboveLowerLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsAboveLowerLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:35:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsAboveLowerLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsAboveMediumLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsAboveMediumLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsAboveMediumLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsAboveUpperLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsAboveUpperLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateBanWorkLogWithStandardWorkLogsAboveUpperLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $banWorkLogs = [
            (new BanWorkLog())
                ->setWorkTimeLimit(7200)
                ->setWorkMonth($workMonth)
                ->setDate(new \DateTimeImmutable('2018-01-02 10:00:00')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 19:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 21:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $banWorkLogs, [], [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(7200, $service->calculateWorkedHours($workMonth));
    }

    // Business trip work logs

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], [], $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateUnapprovedBusinessTripWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsWithOneStandardWorkLogMoreThan6HoursLong(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21000, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveLowerLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveLowerLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:35:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveLowerLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveMediumLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(23400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveMediumLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(24300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveMediumLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(25200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveUpperLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(33300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveUpperLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(34200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveUpperLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 19:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 20:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 21:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $businessTripWorkLogs, [], [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(36000, $service->calculateWorkedHours($workMonth));
    }

    // Home office work logs

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], [], $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateUnapprovedHomeOfficeWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsWithOneStandardWorkLogMoreThan6HoursLong(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21000, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveLowerLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveLowerLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:35:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveLowerLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveMediumLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(23400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveMediumLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(24300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveMediumLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(25200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveUpperLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(33300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveUpperLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(34200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveUpperLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 19:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 20:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 21:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $homeOfficeWorkLogs, [], [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(36000, $service->calculateWorkedHours($workMonth));
    }

    // Maternity protection work logs

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], [], $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsWithOneStandardWorkLogMoreThan6HoursLong(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsAboveLowerLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsAboveLowerLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:35:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsAboveLowerLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsAboveMediumLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsAboveMediumLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsAboveMediumLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 18:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 19:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsAboveUpperLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsAboveUpperLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateMaternityProtectionWorkLogsAboveUpperLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $maternityProtectionWorkLogs = [
            (new MaternityProtectionWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 19:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 20:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 21:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $maternityProtectionWorkLogs, [], [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    // Parental leave work logs

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsWithOneStandardWorkLogMoreThan6HoursLong(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21000, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsAboveLowerLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsAboveLowerLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:35:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsAboveLowerLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsAboveMediumLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(23400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsAboveMediumLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(24300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsAboveMediumLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 18:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 19:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(28800, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsAboveUpperLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(33300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsAboveUpperLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(34200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateParentalLeaveWorkLogsAboveUpperLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 19:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 20:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 21:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(36000, $service->calculateWorkedHours($workMonth));
    }

    // Sick day work logs

    /**
     * @throws \Exception
     */
    public function testCalculateSickDayWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $sickDayWorkLogs = [
            (new SickDayWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], $sickDayWorkLogs, [], [], [], [], [], $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateSickDayWorkLogsInSumLessThatRequiredHoursPerDay(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $sickDayWorkLogs = [
            (new SickDayWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 10:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:15:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], $sickDayWorkLogs, [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateSickDayWorkLogsInSumGreaterThatRequiredHoursPerDay(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $sickDayWorkLogs = [
            (new SickDayWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], $sickDayWorkLogs, [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    // Time off work logs

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateUnapprovedTimeOffWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(14400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsWithOneStandardWorkLogMoreThan6HoursLong(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(21000, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsAboveLowerLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsAboveLowerLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:35:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsAboveLowerLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(22500, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsAboveMediumLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(23400, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsAboveMediumLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(24300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsAboveMediumLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(25200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsAboveUpperLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(33300, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsAboveUpperLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(34200, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedTimeOffWorkLogsAboveUpperLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $timeOffWorkLogs = [
            (new TimeOffWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 19:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 20:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 21:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], $timeOffWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(36000, $service->calculateWorkedHours($workMonth));
    }

    // Vacation work logs

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, [], $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateUnapprovedVacationWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsWithOneStandardWorkLogMoreThan6HoursLong(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsAboveLowerLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsAboveLowerLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 14:35:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsAboveLowerLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 16:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 17:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:15:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsAboveMediumLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsAboveMediumLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 15:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsAboveMediumLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 17:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 18:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 19:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsAboveUpperLimitWithoutBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:05:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsAboveUpperLimitWithShortBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 11:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 11:05:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:05:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 12:20:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 18:20:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogsAboveUpperLimitWithLongBreak(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-02'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-02 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 12:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 19:00:00')),
            (new WorkLog())
                ->setWorkMonth($workMonth)
                ->setStartTime(new \DateTimeImmutable('2018-01-02 20:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-02 21:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], $vacationWorkLogs, $workLogs, $workHours);

        $I->assertEquals(21600, $service->calculateWorkedHours($workMonth));
    }

    public function testCalculateRequiredHours(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], [], [], [], [], [], [], $workHours);

        $I->assertEquals(475200, $service->calculateRequiredHours($workMonth));
    }

    public function testCalculateRequiredHoursWithParentalLeave(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $parentalLeaveWorkLogs = [
            (new ParentalLeaveWorkLog())->setDate(new \DateTimeImmutable('2018-01-02')),
            (new ParentalLeaveWorkLog())->setDate(new \DateTimeImmutable('2018-01-02')),
            (new ParentalLeaveWorkLog())->setDate(new \DateTimeImmutable('2018-01-03')),
            (new ParentalLeaveWorkLog())->setDate(new \DateTimeImmutable('2018-01-04')),
            (new ParentalLeaveWorkLog())->setDate(new \DateTimeImmutable('2018-01-05')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $parentalLeaveWorkLogs, [], [], [], [], [], [], $workHours);

        $I->assertEquals(388800, $service->calculateRequiredHours($workMonth));
    }

    private function getWorkMonth(Prophet $prophet, ?int $workTimeCorrection = null): WorkMonth
    {
        $workMonth = $prophet->prophesize(WorkMonth::class);
        $workMonth->getYear()->willReturn((new SupportedYear())->setYear(2018));
        $workMonth->getMonth()->willReturn(1);
        $workMonth->getUser()->willReturn(new User());

        if ($workTimeCorrection) {
            $workMonth->getWorkTimeCorrection()->willReturn($workTimeCorrection);
        } else {
            $workMonth->getWorkTimeCorrection()->willReturn(0);
        }

        return $workMonth->reveal();
    }

    private function getWorkHours(Prophet $prophet): WorkHours
    {
        $workMonth = $prophet->prophesize(WorkHours::class);
        $workMonth->getYear()->willReturn((new SupportedYear())->setYear(2018));
        $workMonth->getMonth()->willReturn(1);
        $workMonth->getRequiredHours()->willReturn(21600);
        $workMonth->getUser()->willReturn(new User());

        return $workMonth->reveal();
    }

    private function getEntityManager(Prophet $prophet): EntityManager
    {
        $entityManager = $prophet->prophesize(EntityManager::class);

        return $entityManager->reveal();
    }

    /**
     * @return BusinessTripWorkLogRepository
     */
    private function getConfigService(Prophet $prophet): ConfigService
    {
        $config = $prophet->prophesize(Config::class);
        $config->getWorkedHoursLimits()->willReturn([
            'lowerLimit' => [
                'changeBy' => -900,
                'limit' => 21600,
            ],
            'mediumLimit' => [
                'changeBy' => -1800,
                'limit' => 22500,
            ],
            'upperLimit' => [
                'changeBy' => -2700,
                'limit' => 34200,
            ],
        ]);

        $supportedYear = (new SupportedYear())->setYear(2018);
        $supportedHoliday = (new SupportedHoliday())->setYear($supportedYear)
            ->setMonth(1)
            ->setDay(1);

        $config->getSupportedHolidays()->willReturn([
            $supportedHoliday,
        ]);

        $service = $prophet->prophesize(ConfigService::class);
        $service->getConfig()->willReturn($config);

        return $service->reveal();
    }

    private function getBanWorkLogRepository(Prophet $prophet, array $workLogs): BanWorkLogRepository
    {
        $repository = $prophet->prophesize(BanWorkLogRepository::class);
        $repository->findAllByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getBusinessTripWorkLogRepository(Prophet $prophet, array $workLogs): BusinessTripWorkLogRepository
    {
        $repository = $prophet->prophesize(BusinessTripWorkLogRepository::class);
        $repository->findAllApprovedByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getHomeOfficeWorkLogRepository(Prophet $prophet, array $workLogs): HomeOfficeWorkLogRepository
    {
        $repository = $prophet->prophesize(HomeOfficeWorkLogRepository::class);
        $repository->findAllApprovedByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getMaternityProtectionWorkLogRepository(Prophet $prophet, array $workLogs): MaternityProtectionWorkLogRepository
    {
        $repository = $prophet->prophesize(MaternityProtectionWorkLogRepository::class);
        $repository->findAllByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getParentalLeaveWorkLogRepository(Prophet $prophet, array $workLogs): ParentalLeaveWorkLogRepository
    {
        $repository = $prophet->prophesize(ParentalLeaveWorkLogRepository::class);
        $repository->findAllByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getSickDayWorkLogRepository(Prophet $prophet, array $workLogs): SickDayWorkLogRepository
    {
        $repository = $prophet->prophesize(SickDayWorkLogRepository::class);
        $repository->findAllByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getSpecialLeaveWorkLogRepository(Prophet $prophet, array $workLogs): SpecialLeaveWorkLogRepository
    {
        $repository = $prophet->prophesize(SpecialLeaveWorkLogRepository::class);
        $repository->findAllApprovedByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getTimeOffWorkLogRepository(Prophet $prophet, array $workLogs): TimeOffWorkLogRepository
    {
        $repository = $prophet->prophesize(TimeOffWorkLogRepository::class);
        $repository->findAllApprovedByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getTrainingWorkLogRepository(Prophet $prophet, array $workLogs): TrainingWorkLogRepository
    {
        $repository = $prophet->prophesize(TrainingWorkLogRepository::class);
        $repository->findAllApprovedByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getUserYearStatsRepository(Prophet $prophet): UserYearStatsRepository
    {
        $repository = $prophet->prophesize(UserYearStatsRepository::class);

        return $repository->reveal();
    }

    private function getVacationWorkLogRepository(Prophet $prophet, array $workLogs): VacationWorkLogRepository
    {
        $repository = $prophet->prophesize(VacationWorkLogRepository::class);
        $repository->findAllApprovedByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getWorkLogRepository(Prophet $prophet, array $workLogs): WorkLogRepository
    {
        $repository = $prophet->prophesize(WorkLogRepository::class);
        $repository->findAllByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

        return $repository->reveal();
    }

    private function getWorkHoursRepository(Prophet $prophet, WorkHours $workHours): WorkHoursRepository
    {
        $repository = $prophet->prophesize(WorkHoursRepository::class);
        $repository->findOne(
            Argument::type(SupportedYear::class),
            Argument::type('int'),
            Argument::type(User::class)
        )->willReturn($workHours);

        return $repository->reveal();
    }

    private function getWorkMonthService(
        Prophet $prophet,
        array $banWorkLogs,
        array $businessTripWorkLogs,
        array $homeOfficeWorkLogs,
        array $maternityProtectionWorkLogs,
        array $parentalLeaveWorkLogs,
        array $sickDayWorkLogs,
        array $specialLeaveWorkLogs,
        array $timeOffWorkLogs,
        array $trainingWorkLogs,
        array $vacationWorkLogs,
        array $workLogs,
        WorkHours $workHours
    ): WorkMonthService {
        return new WorkMonthService(
            $this->getEntityManager($prophet),
            $this->getConfigService($prophet),
            $this->getBanWorkLogRepository($prophet, $banWorkLogs),
            $this->getBusinessTripWorkLogRepository($prophet, $businessTripWorkLogs),
            $this->getHomeOfficeWorkLogRepository($prophet, $homeOfficeWorkLogs),
            $this->getMaternityProtectionWorkLogRepository($prophet, $maternityProtectionWorkLogs),
            $this->getParentalLeaveWorkLogRepository($prophet, $parentalLeaveWorkLogs),
            $this->getSickDayWorkLogRepository($prophet, $sickDayWorkLogs),
            $this->getSpecialLeaveWorkLogRepository($prophet, $specialLeaveWorkLogs),
            $this->getTimeOffWorkLogRepository($prophet, $timeOffWorkLogs),
            $this->getTrainingWorkLogRepository($prophet, $trainingWorkLogs),
            $this->getUserYearStatsRepository($prophet),
            $this->getVacationWorkLogRepository($prophet, $vacationWorkLogs),
            $this->getWorkLogRepository($prophet, $workLogs),
            $this->getWorkHoursRepository($prophet, $workHours)
        );
    }
}
