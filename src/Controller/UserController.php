<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserPasswordResetEvent;
use App\Repository\UserRepository;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UserController extends AbstractController
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtTokenManager;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        JWTTokenManagerInterface $jwtTokenManager,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository,
        UserService $userService,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->jwtTokenManager = $jwtTokenManager;
        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getUserByApiToken(string $apiToken): Response
    {
        $user = $this->userRepository->getByApiToken($apiToken);
        if (!$user || !$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User with api token %d was not found', $apiToken));
        }

        return JsonResponse::create(
            $this->normalizer->normalize(
                $user,
                User::class,
                ['groups' => ['user_out_api_token_detail']]
            ),
            JsonResponse::HTTP_OK
        );
    }

    public function refreshToken(): Response
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !$token->getUser() instanceof User) {
            return JsonResponse::create(
                ['detail' => 'Cannot refresh token without user'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        return JsonResponse::create(
            ['token' => $this->jwtTokenManager->create($token->getUser())],
            JsonResponse::HTTP_OK
        );
    }

    public function newPassword(Request $request): Response
    {
        $data = json_decode((string) $request->getContent());
        if (!$data || !isset($data->resetPasswordToken) || !isset($data->newPlainPassword)) {
            return JsonResponse::create(
                ['detail' => 'Fields resetPasswordToken and newPlainPassword are required.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userRepository->getByResetPasswordToken($data->resetPasswordToken);
        if (!$user) {
            return JsonResponse::create(
                ['detail' => 'User with entered reset password token was not found.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $constraintViolations = $this->validator->validatePropertyValue(
            User::class,
            'plainPassword',
            $data->newPlainPassword
        );

        if (count($constraintViolations) > 0) {
            return JsonResponse::create(
                ['detail' => 'Entered password is not valid.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $this->userService->setNewPassword($user, $data->newPlainPassword);

        return JsonResponse::create(null, JsonResponse::HTTP_OK);
    }

    public function resetPassword(Request $request): Response
    {
        $data = json_decode((string) $request->getContent());
        if (!$data || !isset($data->email)) {
            return JsonResponse::create(
                ['detail' => 'email field is required.'], JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userRepository->getByEmail($data->email);
        if (!$user) {
            return JsonResponse::create(
                ['detail' => sprintf('User with email %s was not found', $data->email)],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        try {
            $this->userService->setResetPasswordToken($user);
        } catch (\Exception $e) {
            return JsonResponse::create(
                ['detail' => 'Unable to generate reset password token.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $this->eventDispatcher->dispatch(
            new UserPasswordResetEvent($user),
            UserPasswordResetEvent::RESET
        );

        return JsonResponse::create(null, JsonResponse::HTTP_OK);
    }

    /**
     * @throws \Exception
     */
    public function renewApiToken(Request $request, int $id): Response
    {
        $user = $this->userRepository->getRepository()->find($id);
        if (!$user || !$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User with id %d was not found', $id));
        }

        $this->userService->renewApiToken($user);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $user,
                User::class,
                ['groups' => ['user_out_api_token_detail']]
            ),
            JsonResponse::HTTP_OK
        );
    }

    /**
     * @throws \Exception
     */
    public function renewICalToken(Request $request, int $id): Response
    {
        $user = $this->userRepository->getRepository()->find($id);
        if (!$user || !$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User with id %d was not found', $id));
        }

        $this->userService->renewICalToken($user);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $user,
                User::class,
                ['groups' => ['user_out_ical_token_detail']]
            ),
            JsonResponse::HTTP_OK
        );
    }

    /**
     * @throws \Exception
     */
    public function resetApiToken(Request $request, int $id): Response
    {
        $user = $this->userRepository->getRepository()->find($id);
        if (!$user || !$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User with id %d was not found', $id));
        }

        $this->userService->resetApiToken($user);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $user,
                User::class,
                ['groups' => ['user_out_api_token_detail']]
            ),
            JsonResponse::HTTP_OK
        );
    }

    /**
     * @throws \Exception
     */
    public function resetICalToken(Request $request, int $id): Response
    {
        $user = $this->userRepository->getRepository()->find($id);
        if (!$user || !$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User with id %d was not found', $id));
        }

        $this->userService->resetICalToken($user);

        return JsonResponse::create(
            $this->normalizer->normalize(
                $user,
                User::class,
                ['groups' => ['user_out_ical_token_detail']]
            ),
            JsonResponse::HTTP_OK
        );
    }

    public function supervisedUsers(Request $request, int $id): Response
    {
        $user = $this->userRepository->getRepository()->find($id);
        if (!$user || !$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User with id %d was not found', $id));
        }

        $supervisedUsers = $user->getAllSupervised();
        $order = $request->query->get('order') ?: [];
        $isActiveFilter = $request->query->get('isActive');

        if (boolval($isActiveFilter)) {
            $supervisedUsers = array_filter($supervisedUsers, function (User $supervisedUser) {
                return $supervisedUser->getIsActive();
            });
        }

        if (isset($order['lastName']) && in_array($order['lastName'], ['asc', 'desc'])) {
            usort($supervisedUsers, function (User $a, User $b) use ($order) {
                if ($order['lastName'] === 'desc') {
                    return strcasecmp($b->getLastName(), $a->getLastName());
                }

                return strcasecmp($a->getLastName(), $b->getLastName());
            });
        }

        $normalizedUsers = [];
        foreach ($supervisedUsers as $user) {
            $normalizedUsers[] = $this->normalizer->normalize(
                $user,
                User::class,
                ['groups' => ['supervised_user_out_list']]
            );
        }

        return JsonResponse::create($normalizedUsers, JsonResponse::HTTP_OK);
    }
}
