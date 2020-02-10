<?php

namespace unit\Service;

use App\Entity\BusinessTripWorkLog;
use App\Entity\Config;
use App\Entity\HomeOfficeWorkLog;
use App\Entity\SickDayWorkLog;
use App\Entity\SupportedYear;
use App\Entity\User;
use App\Entity\VacationWorkLog;
use App\Entity\WorkHours;
use App\Entity\WorkLog;
use App\Entity\WorkMonth;
use App\Repository\BusinessTripWorkLogRepository;
use App\Repository\HomeOfficeWorkLogRepository;
use App\Repository\SickDayWorkLogRepository;
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

        $service = $this->getWorkMonthService($prophet, [], [], [], [], [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateWorkLogs(\UnitTester $I): void
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
                ->setEndTime(new \DateTimeImmutable('2018-01-01 16:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(4, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateWorkLogsAboveLowerLimit(\UnitTester $I): void
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
                ->setEndTime(new \DateTimeImmutable('2018-01-01 17:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(6.5, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateWorkLogsAboveUpperLimit(\UnitTester $I): void
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
                ->setEndTime(new \DateTimeImmutable('2018-01-01 20:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], [], $workLogs, $workHours);

        $I->assertEquals(9.25, $service->calculateWorkedHours($workMonth));
    }

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
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];

        $service = $this->getWorkMonthService($prophet, $businessTripWorkLogs, [], [], [], [], $workHours);

        $I->assertEquals(6, $service->calculateWorkedHours($workMonth));
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
                ->setDate(new \DateTimeImmutable('2018-01-01')),
        ];

        $service = $this->getWorkMonthService($prophet, $businessTripWorkLogs, [], [], [], [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 16:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $businessTripWorkLogs, [], [], [], $workLogs, $workHours);

        $I->assertEquals(4, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveLowerLimit(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 17:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $businessTripWorkLogs, [], [], [], $workLogs, $workHours);

        $I->assertEquals(6.5, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedBusinessTripWorkLogsAboveUpperLimit(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 20:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, $businessTripWorkLogs, [], [], [], $workLogs, $workHours);

        $I->assertEquals(9.25, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLog = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $homeOfficeWorkLog, [], [], [], $workHours);

        $I->assertEquals(6, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateUnapprovedHomeOfficeWorkLogsWithoutWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLog = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $homeOfficeWorkLog, [], [], [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 16:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $homeOfficeWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(4, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveLowerLimit(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 17:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $homeOfficeWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(6.5, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedHomeOfficeWorkLogsAboveUpperLimit(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 08:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 20:00:00')),
        ];

        $service = $this->getWorkMonthService($prophet, [], $homeOfficeWorkLogs, [], [], $workLogs, $workHours);

        $I->assertEquals(9.25, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateSickDayWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $sickDayWorkLogs = [
            (new SickDayWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], $sickDayWorkLogs, [], [], $workHours);

        $I->assertEquals(6, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateApprovedVacationWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59'))
                ->setDate(new \DateTimeImmutable('2018-01-01')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $vacationWorkLogs, [], $workHours);

        $I->assertEquals(6, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testCalculateUnapprovedVacationWorkLogs(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01')),
        ];

        $service = $this->getWorkMonthService($prophet, [], [], [], $vacationWorkLogs, [], $workHours);

        $I->assertEquals(0, $service->calculateWorkedHours($workMonth));
    }

    /**
     * @throws \Exception
     */
    public function testAll(\UnitTester $I): void
    {
        $prophet = new Prophet();
        $workMonth = $this->getWorkMonth($prophet);
        $workHours = $this->getWorkHours($prophet);

        $businessTripWorkLogs = [
            (new BusinessTripWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];
        $homeOfficeWorkLogs = [
            (new HomeOfficeWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];
        $workLogs = [
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 10:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 12:00:00')),
            (new WorkLog())
                ->setStartTime(new \DateTimeImmutable('2018-01-01 14:00:00'))
                ->setEndTime(new \DateTimeImmutable('2018-01-01 18:15:00')),
        ];
        $sickDayWorkLogs = [
            (new SickDayWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01')),
        ];
        $vacationWorkLogs = [
            (new VacationWorkLog())
                ->setDate(new \DateTimeImmutable('2018-01-01'))
                ->setTimeApproved(new \DateTimeImmutable('2018-01-01 23:59:59')),
        ];

        $service = $this->getWorkMonthService(
            $prophet,
            $businessTripWorkLogs,
            $homeOfficeWorkLogs,
            $sickDayWorkLogs,
            $vacationWorkLogs,
            $workLogs,
            $workHours
        );

        $I->assertEquals(5.75, $service->calculateWorkedHours($workMonth));
    }

    private function getWorkMonth(Prophet $prophet): WorkMonth
    {
        $workMonth = $prophet->prophesize(WorkMonth::class);
        $workMonth->getYear()->willReturn((new SupportedYear())->setYear(2018));
        $workMonth->getMonth()->willReturn(1);
        $workMonth->getUser()->willReturn(new User());

        return $workMonth->reveal();
    }

    private function getWorkHours(Prophet $prophet): WorkHours
    {
        $workMonth = $prophet->prophesize(WorkHours::class);
        $workMonth->getYear()->willReturn((new SupportedYear())->setYear(2018));
        $workMonth->getMonth()->willReturn(1);
        $workMonth->getRequiredHours()->willReturn(6);
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
                'changeBy' => -1800,
                'limit' => 21600,
            ],
            'upperLimit' => [
                'changeBy' => -2700,
                'limit' => 32400,
            ],
        ]);

        $service = $prophet->prophesize(ConfigService::class);
        $service->getConfig()->willReturn($config);

        return $service->reveal();
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

    private function getSickDayWorkLogRepository(Prophet $prophet, array $workLogs): SickDayWorkLogRepository
    {
        $repository = $prophet->prophesize(SickDayWorkLogRepository::class);
        $repository->findAllByWorkMonth(Argument::type(WorkMonth::class))->willReturn($workLogs);

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

    /**
     * @param BusinessTripWorkLog[] $businessTripWorkLogs
     * @param HomeOfficeWorkLog[] $homeOfficeWorkLogs
     * @param SickDayWorkLog[] $sickDayWorkLogs
     * @param VacationWorkLog[] $vacationWorkLogs
     * @param WorkLog[] $workLogs
     */
    private function getWorkMonthService(
        Prophet $prophet,
        array $businessTripWorkLogs,
        array $homeOfficeWorkLogs,
        array $sickDayWorkLogs,
        array $vacationWorkLogs,
        array $workLogs,
        WorkHours $workHours
    ): WorkMonthService {
        return new WorkMonthService(
            $this->getEntityManager($prophet),
            $this->getConfigService($prophet),
            $this->getBusinessTripWorkLogRepository($prophet, $businessTripWorkLogs),
            $this->getHomeOfficeWorkLogRepository($prophet, $homeOfficeWorkLogs),
            $this->getSickDayWorkLogRepository($prophet, $sickDayWorkLogs),
            $this->getUserYearStatsRepository($prophet),
            $this->getVacationWorkLogRepository($prophet, $vacationWorkLogs),
            $this->getWorkLogRepository($prophet, $workLogs),
            $this->getWorkHoursRepository($prophet, $workHours)
        );
    }
}
