<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Repository\WorkHoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class EditUserSubscriber implements EventSubscriberInterface
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
     * @param EntityManagerInterface $entityManager
     * @param WorkHoursRepository $workHoursRepository
     */
    public function __construct(EntityManagerInterface $entityManager, WorkHoursRepository $workHoursRepository)
    {
        $this->entityManager = $entityManager;
        $this->workHoursRepository = $workHoursRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [['editWorkHours', EventPriorities::PRE_WRITE]],
        ];
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
