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
     * @param string $email
     * @return User|null
     */
    public function getByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->repository->findOneBy(['email' => $email]);

        return $user;
    }
}
