<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\WorkLog;
use App\Repository\WorkMonthRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CreateWorkLogSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var WorkMonthRepository
     */
    private $workMonthRepository;

    /**
     * CreateWorkLogSubscriber constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param WorkMonthRepository $workMonthRepository
     */
    public function __construct(TokenStorageInterface $tokenStorage, WorkMonthRepository $workMonthRepository)
    {
        $this->tokenStorage = $tokenStorage;
        $this->workMonthRepository = $workMonthRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [['addWorkMonth', EventPriorities::PRE_VALIDATE]],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     * @throws EntityNotFoundException
     */
    public function addWorkMonth(GetResponseForControllerResultEvent $event): void
    {
        $workLog = $event->getControllerResult();
        if (!$workLog instanceof WorkLog) {
            return;
        }

        $method = $event->getRequest()->getMethod();
        try {
            if (Request::METHOD_POST !== $method || $workLog->getWorkMonth()) {
                return;
            }
        } catch (\TypeError $e) {
            $token = $this->tokenStorage->getToken();

            if (!$token) {
                throw new EntityNotFoundException('Cannot create work log without user.');
            }

            $workMonth = $this->workMonthRepository->findByWorkLogAndUser($workLog, $token->getUser());

            if (!$workMonth) {
                throw new EntityNotFoundException('Cannot create work log without work month.');
            }

            $workLog->setWorkMonth($workMonth);
        }
    }
}
