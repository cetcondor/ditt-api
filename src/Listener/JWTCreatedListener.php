<?php

namespace App\Listener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class JWTCreatedListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param JWTCreatedEvent $event
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return;
        }

        try {
            /** @var User $user */
            $user = $token->getUser();
        } catch (\TypeError $e) {
            return;
        }
        $payload['uid'] = $user->getId();

        $event->setData($payload);
    }
}
