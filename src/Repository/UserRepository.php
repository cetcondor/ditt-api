<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(User::class);
        $this->repository = $repository;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @param string $email
     * @return User|null
     */
    public function getByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->repository->findOneBy(['email' => $email]);

        return $user;
    }

    /**
     * @param User $user
     * @param array $order
     * @return User[]
     */
    public function getAllUsersBySupervisor(User $user, array $order = []): array
    {
        $users = $this->repository->findBy(
            ['supervisor' => $user],
            $order
        );

        if (!$users) {
            return [];
        }

        return $users;
    }

    /**
     * @param User $user
     * @param array $order
     * @return User[]
     */
    public function getAllActiveUsersBySupervisor(User $user, array $order = []): array
    {
        $users = $this->repository->findBy(
            [
                'isActive' => true,
                'supervisor' => $user,
            ],
            $order
        );

        if (!$users) {
            return [];
        }

        return $users;
    }
}
