<?php

namespace App\Service;

use App\Entity\BusinessTripWorkLog;
use App\Entity\HomeOfficeWorkLog;
use App\Entity\SickDayWorkLog;
use App\Entity\VacationWorkLog;
use App\Entity\WorkLog;
use App\Entity\WorkMonth;
use App\Repository\BusinessTripWorkLogRepository;
use App\Repository\HomeOfficeWorkLogRepository;
use App\Repository\SickDayWorkLogRepository;
use App\Repository\UserYearStatsRepository;
use App\Repository\VacationWorkLogRepository;
use App\Repository\WorkHoursRepository;
use App\Repository\WorkLogRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class WorkMonthService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var BusinessTripWorkLogRepository
     */
    private $businessTripWorkLogRepository;

    /**
     * @var HomeOfficeWorkLogRepository
     */
    private $homeOfficeWorkLogRepository;

    /**
     * @var SickDayWorkLogRepository
     */
    private $sickDayWorkLogRepository;

    /**
     * @var UserYearStatsRepository
     */
    private $userYearStatsRepository;

    /**
     * @var VacationWorkLogRepository
     */
    private $vacationWorkLogRepository;

    /**
     * @var WorkLogRepository
     */
    private $workLogRepository;

    /**
     * @var WorkHoursRepository
     */
    private $workHoursRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConfigService $configService,
        BusinessTripWorkLogRepository $businessTripWorkLogRepository,
        HomeOfficeWorkLogRepository $homeOfficeWorkLogRepository,
        SickDayWorkLogRepository $sickDayWorkLogRepository,
        UserYearStatsRepository $userYearStatsRepository,
        VacationWorkLogRepository $vacationWorkLogRepository,
        WorkLogRepository $workLogRepository,
        WorkHoursRepository $workHoursRepository
    ) {
        $this->entityManager = $entityManager;
        $this->configService = $configService;
        $this->businessTripWorkLogRepository = $businessTripWorkLogRepository;
        $this->homeOfficeWorkLogRepository = $homeOfficeWorkLogRepository;
        $this->sickDayWorkLogRepository = $sickDayWorkLogRepository;
        $this->userYearStatsRepository = $userYearStatsRepository;
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
        $this->workLogRepository = $workLogRepository;
        $this->workHoursRepository = $workHoursRepository;
    }

    /**
     * @param WorkMonth[] $workMonths
     */
    public function createWorkMonths(array $workMonths): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($workMonths) {
            foreach ($workMonths as $workMonth) {
                if (!$workMonth instanceof WorkMonth) {
                    throw new \TypeError('Entity is not of type WorkMonth.');
                }

                $em->persist($workMonth);
            }
        });
    }

    /**
     * @throws \Exception
     */
    public function markApproved(WorkMonth $workMonth): void
    {
        $workMonth->markApproved();

        $userYearStats = $this->userYearStatsRepository->findByUserAndYear(
            $workMonth->getUser(),
            $workMonth->getYear()
        );

        if (!$userYearStats) {
            throw new \Exception('User year stats has not been found.');
        }

        $userYearStats->setRequiredHours($userYearStats->getRequiredHours() + $this->calculateRequiredHours($workMonth));
        $userYearStats->setWorkedHours($userYearStats->getWorkedHours() + $this->calculateWorkedHours($workMonth));

        $this->entityManager->flush();
    }

    public function markWaitingForApproval(WorkMonth $workMonth): void
    {
        $workMonth->markWaitingForApproval();
        $this->entityManager->flush();
    }

    /**
     * @throws \Exception
     */
    public function calculateWorkedHours(WorkMonth $workMonth): float
    {
        $allWorkLogs = [];
        $allWorkLogWorkedHours = [];

        for ($day = 1; $day < 32; ++$day) {
            $allWorkLogs[$day] = [];
            $allWorkLogWorkedHours[$day] = 0;
        }

        $businessTripWorkLogs = $this->businessTripWorkLogRepository->findAllApprovedByWorkMonth($workMonth);
        $homeOfficeWorkLogs = $this->homeOfficeWorkLogRepository->findAllApprovedByWorkMonth($workMonth);
        $sickDayWorkLogs = $this->sickDayWorkLogRepository->findAllByWorkMonth($workMonth);
        $vacationWorkLogs = $this->vacationWorkLogRepository->findAllApprovedByWorkMonth($workMonth);
        $workLogs = $this->workLogRepository->findAllByWorkMonth($workMonth);

        foreach ($businessTripWorkLogs as $businessTripWorkLog) {
            $day = (int) $businessTripWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $businessTripWorkLog;
        }

        foreach ($homeOfficeWorkLogs as $homeOfficeWorkLog) {
            $day = (int) $homeOfficeWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $homeOfficeWorkLog;
        }

        foreach ($sickDayWorkLogs as $sickDayWorkLog) {
            $day = (int) $sickDayWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $sickDayWorkLog;
        }

        foreach ($vacationWorkLogs as $vacationWorkLog) {
            $day = (int) $vacationWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $vacationWorkLog;
        }

        foreach ($workLogs as $workLog) {
            $day = (int) $workLog->getStartTime()->format('d');
            $allWorkLogs[$day][] = $workLog;
        }

        $workHours = $this->workHoursRepository->findOne(
            $workMonth->getYear(),
            $workMonth->getMonth(),
            $workMonth->getUser()
        );

        if (!$workHours) {
            throw new \Exception('Work hours has not been found.');
        }

        foreach ($allWorkLogs as $day => $allWorkLogsByDay) {
            $workedHours = 0;
            $containsBusinessDay = false;
            $containsHomeDay = false;
            $containsSickDay = false;
            $containsVacationDay = false;

            foreach ($allWorkLogsByDay as $workLog) {
                if ($workLog instanceof WorkLog) {
                    $timeDiff = $workLog->getEndTime()->diff($workLog->getStartTime());
                    $workedHours += $timeDiff->h + ($timeDiff->i / 60);
                }

                if ($workLog instanceof BusinessTripWorkLog && $workLog->getTimeApproved()) {
                    $containsBusinessDay = true;
                }

                if ($workLog instanceof HomeOfficeWorkLog && $workLog->getTimeApproved()) {
                    $containsHomeDay = true;
                }

                if ($workLog instanceof SickDayWorkLog) {
                    $containsSickDay = true;
                }

                if ($workLog instanceof VacationWorkLog && $workLog->getTimeApproved()) {
                    $containsVacationDay = true;
                }
            }

            $config = $this->configService->getConfig();

            $lowerLimit = $config->getWorkedHoursLimits()['lowerLimit'];
            $upperLimit = $config->getWorkedHoursLimits()['upperLimit'];

            if (
                $workedHours > $lowerLimit['limit'] / 3600
                && $workedHours <= $upperLimit['limit'] / 3600
            ) {
                $workedHours += ($lowerLimit['changeBy'] / 3600);
            } elseif ($workedHours > $upperLimit['limit'] / 3600) {
                $workedHours += ($upperLimit['changeBy'] / 3600);
            }

            if (
                $workedHours === 0
                && ($containsBusinessDay || $containsHomeDay || $containsSickDay || $containsVacationDay)
            ) {
                $workedHours = $workHours->getRequiredHours();
            }

            $allWorkLogWorkedHours[$day] = $workedHours;
        }

        return array_sum($allWorkLogWorkedHours);
    }

    /**
     * @throws \Exception
     */
    public function calculateRequiredHours(WorkMonth $workMonth): float
    {
        $config = $this->configService->getConfig();
        $workingDaysInMonth = 0;

        $isWeekend = function ($date) {
            return $date->format('l') === 'Saturday' || $date->format('l') === 'Sunday';
        };

        $isHoliday = function ($date) use ($config) {
            foreach ($config->getSupportedHolidays() as $supportedHoliday) {
                if ($supportedHoliday->getDate()->format('Y-m-d') === $date->format('Y-m-d')) {
                    return true;
                }
            }

            return false;
        };

        for (
            $date = (new \DateTime())->setDate($workMonth->getYear()->getYear(), $workMonth->getMonth(), 1);
            (int) $date->format('m') === $workMonth->getMonth();
            $date->add(new \DateInterval('P1D'))
        ) {
            if (!$isWeekend($date) && !$isHoliday($date)) {
                ++$workingDaysInMonth;
            }
        }

        $workHours = $this->workHoursRepository->findOne(
            $workMonth->getYear(),
            $workMonth->getMonth(),
            $workMonth->getUser()
        );

        if (!$workHours) {
            throw new \Exception('Work hours has not been found.');
        }

        return $workingDaysInMonth * $workHours->getRequiredHours();
    }
}
