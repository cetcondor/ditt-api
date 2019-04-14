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
     * @param string $apiToken
     * @return User|null
     */
    public function getByApiToken(string $apiToken): ?User
    {
        /** @var User|null $user */
        $user = $this->repository->findOneBy(['apiToken' => $apiToken]);

        return $user;
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
            'SELECT * FROM app_user AS "u" WHERE u.roles::jsonb @> \'["ROLE_ADMIN"]\'::jsonb',
            $rsm
        )->getResult();
    }
}
