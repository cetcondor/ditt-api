<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Config;
use App\Entity\User;
use App\Entity\UserYearStats;
use App\Entity\WorkMonth;
use App\Repository\WorkHoursRepository;
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
     * @var UserService
     */
    private $userService;

    /**
     * @var UserYearStatsService
     */
    private $userYearStatsService;

    /**
     * @var WorkHoursRepository
     */
    private $workHoursRepository;

    /**
     * @var WorkMonthService
     */
    private $workMonthService;

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserService $userService
     * @param UserYearStatsService $userYearStatsService
     * @param WorkHoursRepository $workHoursRepository
     * @param WorkMonthService $workMonthService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        UserYearStatsService $userYearStatsService,
        WorkHoursRepository $workHoursRepository,
        WorkMonthService $workMonthService
    ) {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
        $this->userYearStatsService = $userYearStatsService;
        $this->workHoursRepository = $workHoursRepository;
        $this->workMonthService = $workMonthService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['createWorkMonths', EventPriorities::POST_WRITE],
                ['createYearStats', EventPriorities::POST_WRITE],
                ['editWorkHours', EventPriorities::PRE_WRITE],
                ['addRemainingVacationDaysByYear', EventPriorities::PRE_SERIALIZE],
            ],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
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

        foreach ((new Config())->getSupportedYear() as $supportedYear) {
            $userYearStats[] = (new UserYearStats())
                ->setYear($supportedYear)
                ->setUser($user);
        }

        $this->userYearStatsService->createUserYearStats($userYearStats);
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
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

        foreach ((new Config())->getSupportedYear() as $supportedYear) {
            for ($month = 1; $month <= 12; ++$month) {
                $workMonths[] = (new WorkMonth())
                    ->setYear($supportedYear)
                    ->setMonth($month)
                    ->setUser($user);
            }
        }

        $this->workMonthService->createWorkMonths($workMonths);
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function editWorkHours(GetResponseForControllerResultEvent $event): void
    {
        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_PUT !== $method) {
            return;
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

        $user->setWorkHours(new ArrayCollection());
        $this->entityManager->flush();
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function addRemainingVacationDaysByYear(GetResponseForControllerResultEvent $event): void
    {
        $user = $event->getControllerResult();
        if (!$user instanceof User) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_GET !== $method) {
            return;
        }

        $user->setRemainingVacationDaysByYear(
            $this->userService->calculateRemainingVacationDaysByYear($user)
        );
    }
}
