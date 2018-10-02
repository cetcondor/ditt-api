<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\User;
use App\Entity\WorkLogInterface;
use App\Entity\WorkMonth;
use App\Repository\WorkMonthRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkLogSubscriber implements EventSubscriberInterface
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
     * WorkLogSubscriber constructor.
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
            KernelEvents::VIEW => [
                ['addWorkMonth', EventPriorities::PRE_VALIDATE],
                ['checkWorkMonthStatus', EventPriorities::PRE_WRITE],
                ['resetWorkMonthStatus', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     * @throws InvalidArgumentException
     */
    public function addWorkMonth(GetResponseForControllerResultEvent $event): void
    {
        $workLog = $event->getControllerResult();
        if (!$workLog instanceof WorkLogInterface) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_POST !== $method) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token || !$token->getUser() instanceof User) {
            throw new InvalidArgumentException('Cannot create work log without user.');
        }

        $workMonth = $this->workMonthRepository->findByWorkLogAndUser($workLog, $token->getUser());

        if (!$workMonth) {
            throw new InvalidArgumentException('Cannot create work log without work month.');
        }

        $workLog->setWorkMonth($workMonth);
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     * @throws InvalidArgumentException
     */
    public function checkWorkMonthStatus(GetResponseForControllerResultEvent $event): void
    {
        $workLog = $event->getControllerResult();
        if (!$workLog instanceof WorkLogInterface) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_POST !== $method && Request::METHOD_DELETE !== $method) {
            return;
        }

        if ($workLog->getWorkMonth()->getStatus() === WorkMonth::STATUS_APPROVED) {
            throw new InvalidArgumentException('Cannot add or delete work log to closed work month.');
        }
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function resetWorkMonthStatus(GetResponseForControllerResultEvent $event): void
    {
        $workLog = $event->getControllerResult();
        if (!$workLog instanceof WorkLogInterface) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_POST !== $method && Request::METHOD_PUT !== $method) {
            return;
        }

        if ($workLog->getWorkMonth()->getStatus() === WorkMonth::STATUS_WAITING_FOR_APPROVAL) {
            $workLog->getWorkMonth()->setStatus(WorkMonth::STATUS_OPENED);
        }
    }
}
