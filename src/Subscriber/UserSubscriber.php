<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Repository\WorkHoursRepository;
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
     * @var WorkHoursRepository
     */
    private $workHoursRepository;

    /**
     * @var WorkMonthService
     */
    private $workMonthService;

    /**
     * @param EntityManagerInterface $entityManager
     * @param WorkHoursRepository $workHoursRepository
     * @param WorkMonthService $workMonthService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        WorkHoursRepository $workHoursRepository,
        WorkMonthService $workMonthService
    ) {
        $this->entityManager = $entityManager;
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
                ['editWorkHours', EventPriorities::PRE_WRITE],
            ],
        ];
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

        for ($year = 2018; $year <= 2021; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $workMonths[] = (new WorkMonth())
                    ->setYear($year)
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
}
