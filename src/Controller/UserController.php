<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\UserPasswordResetEvent;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends Controller
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

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

    /**
     * @param NormalizerInterface $normalizer
     * @param UserRepository $userRepository
     * @param UserService $userService
     * @param ValidatorInterface $validator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        NormalizerInterface $normalizer,
        UserRepository $userRepository,
        UserService $userService,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->normalizer = $normalizer;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $apiToken
     * @return Response
     */
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

    /**
     * @param Request $request
     * @return Response
     */
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
            UserPasswordResetEvent::RESET,
            new UserPasswordResetEvent($user)
        );

        return JsonResponse::create(null, JsonResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param int $id
     * @throws \Exception
     * @return Response
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
     * @param Request $request
     * @param int $id
     * @throws \Exception
     * @return Response
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
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function supervisedUsers(Request $request, int $id): Response
    {
        $user = $this->userRepository->getRepository()->find($id);
        if (!$user || !$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User with id %d was not found', $id));
        }

        $order = $request->query->get('order') ?: [];
        $isActiveFilter = $request->query->get('isActive');

        if (boolval($isActiveFilter)) {
            $users = $this->userRepository->getAllActiveUsersBySupervisor($user, $order);
        } else {
            $users = $this->userRepository->getAllUsersBySupervisor($user, $order);
        }

        $normalizedUsers = [];
        foreach ($users as $user) {
            $normalizedUsers[] = $this->normalizer->normalize(
                $user,
                User::class,
                ['groups' => ['supervised_user_out_list']]
            );
        }

        return JsonResponse::create($normalizedUsers, JsonResponse::HTTP_OK);
    }
}
