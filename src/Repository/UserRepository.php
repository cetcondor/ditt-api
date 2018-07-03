<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class UserRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(User::class);
        $this->repository = $repository;
        $this->entityManager = $entityManager;
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
     * @param string $resetPasswordToken
     * @return User|null
     */
    public function getByResetPasswordToken(string $resetPasswordToken): ?User
    {
        /** @var User|null $user */
        $user = $this->repository->findOneBy(['resetPasswordToken' => $resetPasswordToken]);

        return $user;
    }

    /**
     * @return User[]
     */
    public function getAllAdmins(): array
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(User::class, 'u');

        return $this->entityManager->createNativeQuery(
            'SELECT id, email FROM app_user AS "u" WHERE u.roles::jsonb @> \'["ROLE_ADMIN"]\'::jsonb',
            $rsm
        )->getResult();
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
