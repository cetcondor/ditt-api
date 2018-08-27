<?php

namespace App\Service;

use App\Entity\BusinessTripWorkLog;
use App\Entity\Config;
use App\Entity\SickDayWorkLog;
use App\Entity\VacationWorkLog;
use App\Entity\WorkLog;
use App\Entity\WorkMonth;
use App\Repository\BusinessTripWorkLogRepository;
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
     * @var BusinessTripWorkLogRepository
     */
    private $businessTripWorkLogRepository;

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

    /**
     * @param EntityManagerInterface $entityManager
     * @param BusinessTripWorkLogRepository $businessTripWorkLogRepository
     * @param SickDayWorkLogRepository $sickDayWorkLogRepository
     * @param UserYearStatsRepository $userYearStatsRepository
     * @param VacationWorkLogRepository $vacationWorkLogRepository
     * @param WorkLogRepository $workLogRepository
     * @param WorkHoursRepository $workHoursRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        BusinessTripWorkLogRepository $businessTripWorkLogRepository,
        SickDayWorkLogRepository $sickDayWorkLogRepository,
        UserYearStatsRepository $userYearStatsRepository,
        VacationWorkLogRepository $vacationWorkLogRepository,
        WorkLogRepository $workLogRepository,
        WorkHoursRepository $workHoursRepository
    ) {
        $this->entityManager = $entityManager;
        $this->businessTripWorkLogRepository = $businessTripWorkLogRepository;
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
     * @param WorkMonth $workMonth
     * @throws \Exception
     */
    public function markApproved(WorkMonth $workMonth)
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

    /**
     * @param WorkMonth $workMonth
     */
    public function markWaitingForApproval(WorkMonth $workMonth): void
    {
        $workMonth->markWaitingForApproval();
        $this->entityManager->flush();
    }

    /**
     * @param WorkMonth $workMonth
     * @throws \Exception
     * @return float
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
        $sickDayWorkLogs = $this->sickDayWorkLogRepository->findAllByWorkMonth($workMonth);
        $vacationWorkLogs = $this->vacationWorkLogRepository->findAllApprovedByWorkMonth($workMonth);
        $workLogs = $this->workLogRepository->findAllByWorkMonth($workMonth);

        foreach ($businessTripWorkLogs as $businessTripWorkLog) {
            $day = (int) $businessTripWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $businessTripWorkLog;
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
            $specialWorkedHours = 0;
            $containsBusinessDay = false;

            foreach ($allWorkLogsByDay as $workLog) {
                if ($workLog instanceof WorkLog) {
                    $timeDiff = $workLog->getEndTime()->diff($workLog->getStartTime());
                    $workedHours += $timeDiff->h + ($timeDiff->i / 60);
                }

                if ($workLog instanceof BusinessTripWorkLog) {
                    $containsBusinessDay = true;
                }

                if ($workLog instanceof SickDayWorkLog || $workLog instanceof VacationWorkLog) {
                    $specialWorkedHours += $workHours->getRequiredHours();
                }
            }

            $lowerLimit = (new Config())->getWorkedHoursLimits()['lowerLimit'];
            $upperLimit = (new Config())->getWorkedHoursLimits()['upperLimit'];

            if (
                $workedHours > $lowerLimit['limit'] / 3600
                && $workedHours <= $upperLimit['limit'] / 3600
            ) {
                $workedHours += ($lowerLimit['changeBy'] / 3600);
            } elseif ($workedHours > $upperLimit['limit'] / 3600) {
                $workedHours += ($upperLimit['changeBy'] / 3600);
            }

            if ($containsBusinessDay && $workedHours < $workHours->getRequiredHours()) {
                $workedHours = $workHours->getRequiredHours();
            }

            $allWorkLogWorkedHours[$day] = $workedHours + $specialWorkedHours;
        }

        return array_sum($allWorkLogWorkedHours);
    }

    /**
     * @param WorkMonth $workMonth
     * @throws \Exception
     * @return float
     */
    public function calculateRequiredHours(WorkMonth $workMonth): float
    {
        $workingDaysInMonth = 0;

        $isWeekend = function ($date) {
            return $date->format('l') === 'Saturday' || $date->format('l') === 'Sunday';
        };

        $isHoliday = function ($date) {
            foreach ((new Config())->getSupportedHolidays() as $supportedHoliday) {
                if ($supportedHoliday->format('Y-m-d') === $date->format('Y-m-d')) {
                    return true;
                }
            }

            return false;
        };

        for (
            $date = (new \DateTime())->setDate($workMonth->getYear(), $workMonth->getMonth(), 1);
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
