<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
     * @param NormalizerInterface $normalizer
     * @param UserRepository $userRepository
     */
    public function __construct(
        NormalizerInterface $normalizer,
        UserRepository $userRepository
    ) {
        $this->normalizer = $normalizer;
        $this->userRepository = $userRepository;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function supervisedUsers(int $id): Response
    {
        $user = $this->userRepository->getRepository()->find($id);
        if (!$user || !$user instanceof User) {
            throw $this->createNotFoundException(sprintf('User with id %d was not found', $id));
        }

        $users = [];
        foreach ($this->userRepository->getAllUsersBySupervisor($user) as $user) {
            $users[] = $this->normalizer->normalize(
                $user,
                User::class,
                ['groups' => ['supervised_user_out_list']]
            );
        }

        return JsonResponse::create($users, JsonResponse::HTTP_OK);
    }
}
