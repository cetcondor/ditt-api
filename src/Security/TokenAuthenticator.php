<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        return $request->headers->has('apiToken');
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getCredentials(Request $request)
    {
        return [
            'apiToken' => $request->headers->get('apiToken'),
        ];
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = $credentials['apiToken'];

        if (null === $token) {
            return null;
        }

        return $this->userRepository->getByApiToken($token);
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return JsonResponse|Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $ipFilter = null;

        if (is_string(getenv('USER_API_TOKEN_IP_FILTER'))) {
            $ipFilter = json_decode(getenv('USER_API_TOKEN_IP_FILTER'));
        }

        if (is_array($ipFilter)) {
            $clientIp = null;

            if ($request->server->has('HTTP_X_FORWARDED_FOR')) {
                $clientIp = $request->server->get('HTTP_X_FORWARDED_FOR');
            } elseif ($request->server->has('HTTP_CLIENT_IP')) {
                $clientIp = $request->server->get('HTTP_CLIENT_IP');
            } else {
                $clientIp = $request->server->get('REMOTE_ADDR');
            }

            if (!in_array($clientIp, $ipFilter)) {
                return JsonResponse::create(
                    [
                        'clientIp' => $clientIp,
                        'detail' => 'IP address in not on the list of allowed IP addresses for usage with user\'s api token.',
                    ],
                    Response::HTTP_FORBIDDEN
                );
            }
        }

        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'code' => 401,
            'message' => 'Authentication required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
