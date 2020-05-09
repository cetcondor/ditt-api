<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Entity\User;
use App\Entity\WorkLogSupportInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkLogSupportSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['addDetails', EventPriorities::PRE_VALIDATE],
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function addDetails(GetResponseForControllerResultEvent $event): void
    {
        $workLogSupport = $event->getControllerResult();
        if (!$workLogSupport instanceof WorkLogSupportInterface) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (Request::METHOD_POST !== $method) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token || !$token->getUser() instanceof User) {
            throw new InvalidArgumentException('Cannot create work log support without user.');
        }

        $workLogSupport->setDateTime(new \DateTimeImmutable());
        $workLogSupport->setSupportedBy($token->getUser());
    }
}
