<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Contract;
use App\Entity\User;
use App\Entity\UserYearStats;
use App\Entity\WorkMonth;
use App\Event\UserChangedEvent;
use App\Repository\ContractRepository;
use App\Repository\UserNotificationsRepository;
use App\Repository\UserRepository;
use App\Repository\VacationRepository;
use App\Service\ConfigService;
use App\Service\UserService;
use App\Service\UserYearStatsService;
use App\Service\WorkMonthService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var ContractRepository
     */
    private $contractRepository;

    /**
     * @var UserNotificationsRepository
     */
    private $userNotificationsRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

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
     * @var WorkMonthService
     */
    private $workMonthService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        ConfigService $configService,
        ContractRepository $contractRepository,
        UserNotificationsRepository $userNotificationsRepository,
        UserRepository $userRepository,
        UserService $userService,
        UserYearStatsService $userYearStatsService,
        VacationRepository $vacationRepository,
        WorkMonthService $workMonthService
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->configService = $configService;
        $this->contractRepository = $contractRepository;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->userNotificationsRepository = $userNotificationsRepository;
        $this->userYearStatsService = $userYearStatsService;
        $this->vacationRepository = $vacationRepository;
        $this->workMonthService = $workMonthService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['createWorkMonths', EventPriorities::POST_WRITE],
                ['createYearStats', EventPriorities::POST_WRITE],
                ['editUser', EventPriorities::PRE_WRITE],
                ['fulfillLastApprovedWorkMonth', EventPriorities::PRE_SERIALIZE],
                ['fullfilRemainingVacationDays', EventPriorities::PRE_SERIALIZE],
            ],
        ];
    }

    public function createYearStats(RequestEvent $event): void
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

    public function createWorkMonths(RequestEvent $event): void
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

    public function editUser(RequestEvent $event): void
    {
        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_PUT !== $method) {
            return;
        }

        $oldContractsArray = $this->contractRepository->getRepository()->findBy(['user' => $user]);
        $newContractsArray = $user->getContracts();

        $didContractChanged = count($oldContractsArray) != count($newContractsArray);
        if (!$didContractChanged) {
            /** @var Contract $newContract */
            foreach ($newContractsArray as $newContract) {
                if ($newContract->getIsDayBased()) {
                    $workingDaysCount = ($newContract->getIsMondayIncluded() ? 1 : 0)
                        + ($newContract->getIsTuesdayIncluded() ? 1 : 0)
                        + ($newContract->getIsWednesdayIncluded() ? 1 : 0)
                        + ($newContract->getIsThursdayIncluded() ? 1 : 0)
                        + ($newContract->getIsFridayIncluded() ? 1 : 0);
                    $newContract->setWeeklyWorkingDays($workingDaysCount);
                }

                $foundOldContract = $this->contractRepository->getRepository()->find($newContract->getId());

                if ($foundOldContract == null) {
                    $didContractChanged = true;
                    break;
                }

                if (
                    $foundOldContract->getIsDayBased() != $newContract->getIsDayBased()
                    || $foundOldContract->getIsMondayIncluded() != $newContract->getIsMondayIncluded()
                    || $foundOldContract->getIsTuesdayIncluded() != $newContract->getIsTuesdayIncluded()
                    || $foundOldContract->getIsWednesdayIncluded() != $newContract->getIsWednesdayIncluded()
                    || $foundOldContract->getIsThursdayIncluded() != $newContract->getIsThursdayIncluded()
                    || $foundOldContract->getIsFridayIncluded() != $newContract->getIsFridayIncluded()
                    || $foundOldContract->getWeeklyWorkingDays() != $newContract->getWeeklyWorkingDays()
                    || $foundOldContract->getWeeklyWorkingHours() != $newContract->getWeeklyWorkingHours()
                ) {
                    $didContractChanged = true;
                    break;
                }
            }
        }

        foreach ($user->getContracts() as $detachedContract) {
            if (!$detachedContract->getId()) {
                continue;
            }

            /** @var Contract $attachedContract */
            $attachedContract = $this->contractRepository->getRepository()->find($detachedContract->getId());

            if ($attachedContract) {
                $attachedContract->setStartDateTime($detachedContract->getStartDateTime());
                $attachedContract->setEndDateTime($detachedContract->getEndDateTime());
                $attachedContract->setIsDayBased($detachedContract->getIsDayBased());
                $attachedContract->setIsMondayIncluded($detachedContract->getIsMondayIncluded());
                $attachedContract->setIsTuesdayIncluded($detachedContract->getIsTuesdayIncluded());
                $attachedContract->setIsWednesdayIncluded($detachedContract->getIsWednesdayIncluded());
                $attachedContract->setIsThursdayIncluded($detachedContract->getIsThursdayIncluded());
                $attachedContract->setIsFridayIncluded($detachedContract->getIsFridayIncluded());
                $attachedContract->setWeeklyWorkingDays($detachedContract->getWeeklyWorkingDays());
                $attachedContract->setWeeklyWorkingHours($detachedContract->getWeeklyWorkingHours());
            }
        }
        foreach ($oldContractsArray as $oldContract) {
            $isDeleted = true;
            foreach ($user->getContracts() as $newContract) {
                if ($oldContract->getId() == $newContract->getId()) {
                    $isDeleted = false;
                    break;
                }
            }

            if ($isDeleted) {
                $this->entityManager->remove($oldContract);
            }
        }

        $oldVacationsArray = [];
        $newVacationsArray = [];

        foreach ($user->getVacations() as $detachedVacation) {
            $attachedVacation = $this->vacationRepository->findOne(
                $detachedVacation->getYear(),
                $user
            );

            $oldVacationsArray[$detachedVacation->getYear()->getYear()] = [
                $detachedVacation->getVacationDays(),
                $detachedVacation->getVacationDaysCorrection(),
            ];
            $newVacationsArray[$attachedVacation->getYear()->getYear()] = [
                $attachedVacation->getVacationDays(),
                $attachedVacation->getVacationDaysCorrection(),
            ];

            if ($attachedVacation) {
                $attachedVacation->setVacationDays($detachedVacation->getVacationDays());
                $attachedVacation->setVacationDaysCorrection($detachedVacation->getVacationDaysCorrection());
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

        $user->setVacations(new ArrayCollection());
        $this->entityManager->flush();

        $didVacationsChanged = count($oldVacationsArray) != count($newVacationsArray);
        if (!$didVacationsChanged) {
            foreach ($newVacationsArray as $newYear => $newVacationData) {
                if (!array_key_exists($newYear, $oldVacationsArray)) {
                    $didVacationsChanged = true;
                    break;
                }

                $oldVacationData = $oldVacationsArray[$newYear];

                if ($oldVacationData[0] != $newVacationData[0] || $oldVacationData[1] != $newVacationData[1]) {
                    $didVacationsChanged = true;
                    break;
                }
            }
        }

        $this->eventDispatcher->dispatch(
            new UserChangedEvent($user, $didContractChanged, $didVacationsChanged),
            UserChangedEvent::CHANGED
        );
    }

    public function fulfillLastApprovedWorkMonth(RequestEvent $event): void
    {
        if ($event->getRequest()->get('_route') !== 'api_users_get_collection') {
            return;
        }

        $this->userService->fulfillLastApprovedWorkMonth($event->getControllerResult());
    }

    public function fullfilRemainingVacationDays(RequestEvent $event): void
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
