<?php

namespace App\Subscriber;

use App\Security\ResourceVoter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserAccessSubscriber implements EventSubscriberInterface
{
    const ACCESS_METHODS_TO_PERMISSIONS = [
        Request::METHOD_GET => ResourceVoter::VIEW,
        Request::METHOD_POST => ResourceVoter::EDIT,
        Request::METHOD_PUT => ResourceVoter::EDIT,
        Request::METHOD_DELETE => ResourceVoter::EDIT,
    ];

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['checkPermissions', 0],
            ],
        ];
    }

    /**
     * @throws AccessDeniedException
     */
    public function checkPermissions(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        $resourceClass = $request->attributes->get('_api_resource_class');
        if ($resourceClass === null) {
            return;
        }

        $data = $request->attributes->get('data');
        if ($data === null) {
            $data = new $resourceClass();
        }

        $access = self::ACCESS_METHODS_TO_PERMISSIONS[$request->getMethod()] ?? 'none';

        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $item) {
                if (!$this->authorizationChecker->isGranted($access, $item)) {
                    throw new AccessDeniedException('Unauthorized operation.');
                }
            }

            return;
        }

        if (!$this->authorizationChecker->isGranted($access, $data)) {
            throw new AccessDeniedException('Unauthorized operation.');
        }
    }
}
