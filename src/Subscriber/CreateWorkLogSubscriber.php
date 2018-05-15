<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\WorkLog;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CreateWorkLogSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [['addUser', EventPriorities::PRE_VALIDATE]],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function addUser(GetResponseForControllerResultEvent $event): void
    {
        $workLog = $event->getControllerResult();
        if (!$workLog instanceof WorkLog) {
            return;
        }

        $method = $event->getRequest()->getMethod();
        try {
            if (Request::METHOD_POST !== $method || $workLog->getUser()) {
                return;
            }
        } catch (\TypeError $e) {
            $token = $this->tokenStorage->getToken();
            if ($token !== null) {
                $workLog->setUser($token->getUser());
            }
        }
    }
}
