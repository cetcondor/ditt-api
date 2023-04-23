<?php

namespace App\Service;

use App\Entity\BanWorkLog;
use App\Entity\Config;
use App\Entity\Contract;
use App\Entity\MaternityProtectionWorkLog;
use App\Entity\SickDayWorkLog;
use App\Entity\SpecialLeaveWorkLog;
use App\Entity\VacationWorkLog;
use App\Entity\WorkLog;
use App\Entity\WorkMonth;
use App\Repository\BanWorkLogRepository;
use App\Repository\BusinessTripWorkLogRepository;
use App\Repository\ContractRepository;
use App\Repository\HomeOfficeWorkLogRepository;
use App\Repository\MaternityProtectionWorkLogRepository;
use App\Repository\ParentalLeaveWorkLogRepository;
use App\Repository\SickDayWorkLogRepository;
use App\Repository\SpecialLeaveWorkLogRepository;
use App\Repository\TimeOffWorkLogRepository;
use App\Repository\TrainingWorkLogRepository;
use App\Repository\UserYearStatsRepository;
use App\Repository\VacationWorkLogRepository;
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
     * @var ContractRepository
     */
    private $contractRepository;

    /**
     * @var BanWorkLogRepository
     */
    private $banWorkLogRepository;

    /**
     * @var BusinessTripWorkLogRepository
     */
    private $businessTripWorkLogRepository;

    /**
     * @var HomeOfficeWorkLogRepository
     */
    private $homeOfficeWorkLogRepository;

    /**
     * @var MaternityProtectionWorkLogRepository
     */
    private $maternityProtectionWorkLogRepository;

    /**
     * @var ParentalLeaveWorkLogRepository
     */
    private $parentalLeaveWorkLogRepository;

    /**
     * @var SickDayWorkLogRepository
     */
    private $sickDayWorkLogRepository;

    /**
     * @var SpecialLeaveWorkLogRepository
     */
    private $specialLeaveWorkLogRepository;

    /**
     * @var TimeOffWorkLogRepository
     */
    private $timeOffWorkLogRepository;

    /**
     * @var TrainingWorkLogRepository
     */
    private $trainingWorkLogRepository;

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

    public function __construct(
        EntityManagerInterface $entityManager,
        ConfigService $configService,
        ContractRepository $contractRepository,
        BanWorkLogRepository $banWorkLogRepository,
        BusinessTripWorkLogRepository $businessTripWorkLogRepository,
        HomeOfficeWorkLogRepository $homeOfficeWorkLogRepository,
        MaternityProtectionWorkLogRepository $maternityProtectionWorkLogRepository,
        ParentalLeaveWorkLogRepository $parentalLeaveWorkLogRepository,
        SickDayWorkLogRepository $sickDayWorkLogRepository,
        SpecialLeaveWorkLogRepository $specialLeaveWorkLogRepository,
        TimeOffWorkLogRepository $timeOffWorkLogRepository,
        TrainingWorkLogRepository $trainingWorkLogRepository,
        UserYearStatsRepository $userYearStatsRepository,
        VacationWorkLogRepository $vacationWorkLogRepository,
        WorkLogRepository $workLogRepository
    ) {
        $this->entityManager = $entityManager;
        $this->configService = $configService;
        $this->contractRepository = $contractRepository;
        $this->banWorkLogRepository = $banWorkLogRepository;
        $this->businessTripWorkLogRepository = $businessTripWorkLogRepository;
        $this->homeOfficeWorkLogRepository = $homeOfficeWorkLogRepository;
        $this->maternityProtectionWorkLogRepository = $maternityProtectionWorkLogRepository;
        $this->parentalLeaveWorkLogRepository = $parentalLeaveWorkLogRepository;
        $this->sickDayWorkLogRepository = $sickDayWorkLogRepository;
        $this->specialLeaveWorkLogRepository = $specialLeaveWorkLogRepository;
        $this->timeOffWorkLogRepository = $timeOffWorkLogRepository;
        $this->trainingWorkLogRepository = $trainingWorkLogRepository;
        $this->userYearStatsRepository = $userYearStatsRepository;
        $this->vacationWorkLogRepository = $vacationWorkLogRepository;
        $this->workLogRepository = $workLogRepository;
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

        $requiredHours = $this->calculateRequiredHours($workMonth);
        $workedHours = $this->calculateWorkedHours($workMonth);

        $userYearStats->setRequiredHours($userYearStats->getRequiredHours() + $requiredHours);
        $userYearStats->setWorkedHours($userYearStats->getWorkedHours() + $workedHours);
        $workMonth->setRequiredTime($requiredHours);
        $workMonth->setWorkedTime($workedHours);

        $this->entityManager->flush();
    }

    public function markWaitingForApproval(WorkMonth $workMonth): void
    {
        $workMonth->markWaitingForApproval();
        $this->entityManager->flush();
    }

    public function setWorkTimeCorrection(WorkMonth $workMonth, int $workTimeCorrection): void
    {
        $workMonth->setWorkTimeCorrection($workTimeCorrection);
        $this->entityManager->flush();
    }

    private function getWorkingHoursMap(WorkMonth $workMonth, Config $config): array
    {
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

        $dateFrom = (new \DateTimeImmutable($workMonth->getYear()->getYear() . '-' . $workMonth->getMonth() . '-01'))
            ->setTime(0, 0, 0);
        $dateTo = \DateTimeImmutable::createFromMutable(new \DateTime(sprintf('last day of %s', $dateFrom->format('Y-m'))))
            ->setTime(23, 59, 59);

        $contracts = $this->contractRepository->findContractsBetweenDates($workMonth->getUser(), $dateFrom, $dateTo);
        $workingHours = [];

        for ($day = 1; $day <= intval($dateTo->format('d')); ++$day) {
            $workingHours[$day] = 0;
        }

        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            for ($day = intval($dateFrom->format('d')); $day <= intval($dateTo->format('d')); ++$day) {
                $date = (new \DateTimeImmutable($workMonth->getYear()->getYear() . '-' . $workMonth->getMonth() . '-' . $day))
                    ->setTime(0, 0, 0);

                if ($isWeekend($date) || $isHoliday($date)) {
                    continue;
                }

                if (!$contract->getIsDayBased()) {
                    $weekStart = $date->modify('monday this week');
                    $weekEnd = $date->modify('sunday this week');
                    $workingDaysBeforeCurrentDay = 0;

                    for (
                        $currentDay = $weekStart;
                        $currentDay < $date && $currentDay <= $weekEnd;
                        $currentDay = $currentDay->modify('+1 day')
                    ) {
                        if ($isWeekend($currentDay) || $isHoliday($currentDay)) {
                            continue;
                        }

                        ++$workingDaysBeforeCurrentDay;
                    }

                    if ($workingDaysBeforeCurrentDay >= $contract->getWeeklyWorkingDays()) {
                        continue;
                    }
                }

                if (
                    $contract->getIsDayBased() && (
                        ($date->format('N') == 1 && !$contract->getIsMondayIncluded())
                        || ($date->format('N') == 2 && !$contract->getIsTuesdayIncluded())
                        || ($date->format('N') == 3 && !$contract->getIsWednesdayIncluded())
                        || ($date->format('N') == 4 && !$contract->getIsThursdayIncluded())
                        || ($date->format('N') == 5 && !$contract->getIsFridayIncluded())
                    )
                ) {
                    continue;
                }

                if (
                    $contract->getStartDateTime() <= $date
                    && (!$contract->getEndDateTime() || $contract->getEndDateTime() >= $date)
                ) {
                    $workingHours[$day] = ($contract->getWeeklyWorkingHours() / $contract->getWeeklyWorkingDays()) * 3600;
                }
            }
        }

        return $workingHours;
    }

    /**
     * @throws \Exception
     */
    public function calculateWorkedHours(WorkMonth $workMonth): float
    {
        $config = $this->configService->getConfig();
        $workingHours = $this->getWorkingHoursMap($workMonth, $config);

        for ($day = 1; $day < 32; ++$day) {
            $allWorkLogs[$day] = [];
            $allWorkLogWorkTime[$day] = 0;
        }

        $standardWorkLogs = $this->workLogRepository->findAllByWorkMonth($workMonth);
        $banWorkLogs = $this->banWorkLogRepository->findAllByWorkMonth($workMonth);
        $maternityProtectionWorkLogs = $this->maternityProtectionWorkLogRepository->findAllByWorkMonth($workMonth);
        $parentalLeaveWorkLogs = $this->parentalLeaveWorkLogRepository->findAllByWorkMonth($workMonth);
        $sickDayWorkLogs = $this->sickDayWorkLogRepository->findAllByWorkMonth($workMonth);
        $specialLeaveWorkLogs = $this->specialLeaveWorkLogRepository->findAllApprovedByWorkMonth($workMonth);
        $timeOffWorkLogs = $this->timeOffWorkLogRepository->findAllApprovedByWorkMonth($workMonth);
        $vacationWorkLogs = $this->vacationWorkLogRepository->findAllApprovedByWorkMonth($workMonth);

        foreach ($standardWorkLogs as $standardWorkLog) {
            $day = (int) $standardWorkLog->getStartTime()->format('d');
            $allWorkLogs[$day][] = $standardWorkLog;
        }

        foreach ($banWorkLogs as $banWorkLog) {
            $day = (int) $banWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $banWorkLog;
        }

        foreach ($maternityProtectionWorkLogs as $maternityProtectionWorkLog) {
            $day = (int) $maternityProtectionWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $maternityProtectionWorkLog;
        }

        foreach ($parentalLeaveWorkLogs as $parentalLeaveWorkLog) {
            $day = (int) $parentalLeaveWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $parentalLeaveWorkLog;
        }

        foreach ($sickDayWorkLogs as $sickDayWorkLog) {
            $day = (int) $sickDayWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $sickDayWorkLog;
        }

        foreach ($specialLeaveWorkLogs as $specialLeaveWorkLog) {
            $day = (int) $specialLeaveWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $specialLeaveWorkLog;
        }

        foreach ($timeOffWorkLogs as $timeOffWorkLog) {
            $day = (int) $timeOffWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $timeOffWorkLog;
        }

        foreach ($vacationWorkLogs as $vacationWorkLog) {
            $day = (int) $vacationWorkLog->getDate()->format('d');
            $allWorkLogs[$day][] = $vacationWorkLog;
        }

        // Calculate work time for each separately
        foreach ($allWorkLogs as $day => $allWorkLogsByDay) {
            $currentDate = (new \DateTimeImmutable())->setDate($workMonth->getYear()->getYear(), $workMonth->getMonth(), $day);

            $containsBanDay = false;
            $containsMaternityProtection = false;
            $containsSickDay = false;
            $containsSpecialLeaveDay = false;
            $containsVacationDay = false;

            $standardWorkLogs = [];

            $workTime = 0;
            $workTimeWithoutCorrection = 0;
            $breakTime = 0;

            $workTimeLimit = 24 * 3600;

            // Split work logs into groups by its type and calculate work time of standard work logs.
            foreach ($allWorkLogsByDay as $workLog) {
                if ($workLog instanceof WorkLog) {
                    $standardWorkLogs[] = $workLog;

                    // Get work time of current work log
                    $currentWorkTimeDiff = $workLog->getEndTime()->diff($workLog->getStartTime());
                    $currentWorkTime = ($currentWorkTimeDiff->h * 3600) + ($currentWorkTimeDiff->i * 60)
                        + $currentWorkTimeDiff->s;

                    // Add work time of current work log to total work time.
                    $workTimeWithoutCorrection += $currentWorkTime;

                    // If current work log is longer that 6 hours, 15 minutes break is added.
                    if ($currentWorkTime > 21600) {
                        $workTime += $currentWorkTime - 900;
                        $breakTime += 900;
                    } else {
                        $workTime += $currentWorkTime;
                    }
                } elseif ($workLog instanceof BanWorkLog) {
                    $containsBanDay = true;

                    if ($workLog->getWorkTimeLimit() < $workTimeLimit) {
                        $workTimeLimit = $workLog->getWorkTimeLimit();
                    }
                } elseif ($workLog instanceof MaternityProtectionWorkLog) {
                    $containsMaternityProtection = true;
                } elseif ($workLog instanceof SickDayWorkLog) {
                    $containsSickDay = true;
                } elseif ($workLog instanceof SpecialLeaveWorkLog && $workLog->getTimeApproved()) {
                    $containsSpecialLeaveDay = true;
                } elseif ($workLog instanceof VacationWorkLog && $workLog->getTimeApproved()) {
                    $containsVacationDay = true;
                }
            }

            // Ban work log correction if standard work log was entered before ban work log
            if ($containsBanDay) {
                if ($workTimeWithoutCorrection > $workTimeLimit) {
                    $workTimeWithoutCorrection = min($workTimeLimit, $workTimeWithoutCorrection);
                    $workTime = min($workTimeLimit, $workTime);
                }
            }

            // Calculate break time between standard work logs if there is more than one
            if (count($standardWorkLogs) > 1) {
                // Sort standard work logs by its start time
                usort($standardWorkLogs, function (WorkLog $a, WorkLog $b) {
                    if ($a->getStartTime() === $b->getStartTime()) {
                        return 0;
                    }

                    return $a->getStartTime() < $b->getStartTime() ? -1 : 1;
                });

                // Split standard work logs to first element and rest of array
                $previousWorkLog = $standardWorkLogs[0];
                $otherWorkLogs = array_slice($standardWorkLogs, 1);

                // Calculate break time between standard work logs
                foreach ($otherWorkLogs as $otherWorkLog) {
                    $currentBreakTimeDiff = $otherWorkLog->getStartTime()->diff($previousWorkLog->getEndTime());
                    $currentBreakTime = ($currentBreakTimeDiff->h * 3600) + ($currentBreakTimeDiff->i * 60)
                        + $currentBreakTimeDiff->s;

                    // Take in account only current break time that is equal or longer that 15 minutes
                    if ($currentBreakTime >= 900) {
                        $breakTime += $currentBreakTime;
                    }

                    $previousWorkLog = $otherWorkLog;
                }
            }

            // Correct work and break times according to necessary breaks
            if (count($standardWorkLogs) > 0) {
                $config = $this->configService->getConfig();

                $lowerLimit = $config->getWorkedHoursLimits()['lowerLimit'];
                $mediumLimit = $config->getWorkedHoursLimits()['mediumLimit'];
                $upperLimit = $config->getWorkedHoursLimits()['upperLimit'];

                if (
                    $workTimeWithoutCorrection > $lowerLimit['limit']
                    && $workTimeWithoutCorrection <= $mediumLimit['limit']
                    && $breakTime < abs($lowerLimit['changeBy'])
                ) {
                    $timeToDeduct = abs($lowerLimit['changeBy']) - $breakTime;
                    $workTime -= $timeToDeduct;
                } elseif (
                    $workTimeWithoutCorrection > $mediumLimit['limit']
                    && $workTimeWithoutCorrection <= $upperLimit['limit']
                    && $breakTime < abs($mediumLimit['changeBy'])
                ) {
                    $timeToDeduct = abs($mediumLimit['changeBy']) - $breakTime;
                    $workTime -= $timeToDeduct;
                } elseif (
                    $workTimeWithoutCorrection > $upperLimit['limit']
                    && $breakTime < abs($upperLimit['changeBy'])
                ) {
                    $timeToDeduct = abs($upperLimit['changeBy']) - $breakTime;
                    $workTime -= $timeToDeduct;
                }
            }

            if (
                (count($standardWorkLogs) === 0 && $containsSickDay)
                || $containsMaternityProtection || $containsSpecialLeaveDay || $containsVacationDay
            ) {
                $workTime = $workingHours[$day];
            } elseif ($containsSickDay && count($standardWorkLogs) > 0) {
                $workTime = min($workTimeWithoutCorrection, $workingHours[$day]);
            }

            $isHoliday = function ($date) use ($config) {
                foreach ($config->getSupportedHolidays() as $supportedHoliday) {
                    if ($supportedHoliday->getDate()->format('Y-m-d') === $date->format('Y-m-d')) {
                        return true;
                    }
                }

                return false;
            };

            // Increase times during sundays and public holidays
            if ($isHoliday($currentDate)) {
                $workTime *= 1.35;
            } elseif ($currentDate->format('l') === 'Sunday') {
                $workTime *= 1.25;
            }

            $allWorkLogWorkTime[$day] = $workTime;
        }

        // Apply work time correction for whole work month
        return array_sum($allWorkLogWorkTime) + ($workMonth->getWorkTimeCorrection());
    }

    /**
     * @throws \Exception
     */
    public function calculateRequiredHours(WorkMonth $workMonth): int
    {
        $config = $this->configService->getConfig();
        $workingHours = $this->getWorkingHoursMap($workMonth, $config);

        $requiredHours = 0;

        // Parental leave work logs are not counted as working hours
        $parentalLeaveWorkLogs = $this->parentalLeaveWorkLogRepository->findAllByWorkMonth($workMonth);
        foreach ($parentalLeaveWorkLogs as $parentalLeaveWorkLog) {
            $workingHours[intval($parentalLeaveWorkLog->getDate()->format('d'))] = 0;
        }

        for (
            $date = (new \DateTime())->setDate($workMonth->getYear()->getYear(), $workMonth->getMonth(), 1);
            (int) $date->format('m') === $workMonth->getMonth();
            $date->add(new \DateInterval('P1D'))
        ) {
            $requiredHours += $workingHours[intval($date->format('d'))];
        }

        return $requiredHours;
    }
}
