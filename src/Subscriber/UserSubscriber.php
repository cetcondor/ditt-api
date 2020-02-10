<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Entity\UserYearStats;
use App\Entity\WorkMonth;
use App\Repository\UserNotificationsRepository;
use App\Repository\VacationRepository;
use App\Repository\WorkHoursRepository;
use App\Service\ConfigService;
use App\Service\UserService;
use App\Service\UserYearStatsService;
use App\Service\WorkMonthService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserSubscriber implements EventSubscriberInterface
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
     * @var UserNotificationsRepository
     */
    private $userNotificationsRepository;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var UserYearStatsService
     */
    private $userYearStatsService;

    /**
     * @var VacationRepository
     */
    private $vacationRepository;

    /**
     * @var WorkHoursRepository
     */
    private $workHoursRepository;

    /**
     * @var WorkMonthService
     */
    private $workMonthService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConfigService $configService,
        UserNotificationsRepository $userNotificationsRepository,
        UserService $userService,
        UserYearStatsService $userYearStatsService,
        VacationRepository $vacationRepository,
        WorkHoursRepository $workHoursRepository,
        WorkMonthService $workMonthService
    ) {
        $this->entityManager = $entityManager;
        $this->configService = $configService;
        $this->userService = $userService;
        $this->userNotificationsRepository = $userNotificationsRepository;
        $this->userYearStatsService = $userYearStatsService;
        $this->vacationRepository = $vacationRepository;
        $this->workHoursRepository = $workHoursRepository;
        $this->workMonthService = $workMonthService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['createWorkMonths', EventPriorities::POST_WRITE],
                ['createYearStats', EventPriorities::POST_WRITE],
                ['editUser', EventPriorities::PRE_WRITE],
                ['fullfilRemainingVacationDays', EventPriorities::PRE_SERIALIZE],
            ],
        ];
    }

    public function createYearStats(GetResponseForControllerResultEvent $event): void
    {
        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_POST !== $method) {
            return;
        }

        $userYearStats = [];
        $config = $this->configService->getConfig();

        foreach ($config->getSupportedYears() as $supportedYear) {
            $userYearStats[] = (new UserYearStats())
                ->setYear($supportedYear)
                ->setUser($user);
        }

        $this->userYearStatsService->createUserYearStats($userYearStats);
    }

    public function createWorkMonths(GetResponseForControllerResultEvent $event): void
    {
        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_POST !== $method) {
            return;
        }

        $workMonths = [];
        $config = $this->configService->getConfig();

        foreach ($config->getSupportedYears() as $supportedYear) {
            for ($month = 1; $month <= 12; ++$month) {
                $workMonths[] = (new WorkMonth())
                    ->setYear($supportedYear)
                    ->setMonth($month)
                    ->setUser($user);
            }
        }

        $this->workMonthService->createWorkMonths($workMonths);
    }

    public function editUser(GetResponseForControllerResultEvent $event): void
    {
        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_PUT !== $method) {
            return;
        }

        foreach ($user->getVacations() as $detachedVacation) {
            $attachedVacation = $this->vacationRepository->findOne(
                $detachedVacation->getYear(),
                $user
            );

            if ($attachedVacation) {
                $attachedVacation->setVacationDays($detachedVacation->getVacationDays());
                $attachedVacation->setVacationDaysCorrection($detachedVacation->getVacationDaysCorrection());
            }
        }

        foreach ($user->getWorkHours() as $detachedWorkHours) {
            $attachedWorkHours = $this->workHoursRepository->findOne(
                $detachedWorkHours->getYear(),
                $detachedWorkHours->getMonth(),
                $user
            );

            if ($attachedWorkHours) {
                $attachedWorkHours->setRequiredHours($detachedWorkHours->getRequiredHours());
            }
        }

        if ($user->getNotifications()->getId() !== null) {
            $attachedUserNotifications = $this->userNotificationsRepository->findOne($user->getNotifications()->getId());

            if ($attachedUserNotifications !== null) {
                $attachedUserNotifications->setSupervisorInfoMondayTime($user->getNotifications()->getSupervisorInfoMondayTime());
                $attachedUserNotifications->setSupervisorInfoTuesdayTime($user->getNotifications()->getSupervisorInfoTuesdayTime());
                $attachedUserNotifications->setSupervisorInfoWednesdayTime($user->getNotifications()->getSupervisorInfoWednesdayTime());
                $attachedUserNotifications->setSupervisorInfoThursdayTime($user->getNotifications()->getSupervisorInfoThursdayTime());
                $attachedUserNotifications->setSupervisorInfoFridayTime($user->getNotifications()->getSupervisorInfoFridayTime());
                $attachedUserNotifications->setSupervisorInfoSaturdayTime($user->getNotifications()->getSupervisorInfoSaturdayTime());
                $attachedUserNotifications->setSupervisorInfoSundayTime($user->getNotifications()->getSupervisorInfoSundayTime());
                $attachedUserNotifications->setSupervisorInfoSendOnHolidays($user->getNotifications()->isSupervisorInfoSendOnHolidays());

                $user->setNotifications($attachedUserNotifications);
            }
        }

        $user->setWorkHours(new ArrayCollection());
        $user->setVacations(new ArrayCollection());
        $this->entityManager->flush();
    }

    public function fullfilRemainingVacationDays(GetResponseForControllerResultEvent $event): void
    {
        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (
            Request::METHOD_GET !== $method
            && Request::METHOD_POST !== $method
            && Request::METHOD_PUT !== $method
        ) {
            return;
        }

        $this->userService->fullfilRemainingVacationDays($user);
    }
}
