<?php

namespace App\Repository;

use App\Entity\UserNotifications;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserNotificationsRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(UserNotifications::class);
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    public function findOne(int $id): ?UserNotifications
    {
        $userNotifications = $this->repository->find($id);

        if (!$userNotifications instanceof UserNotifications) {
            return null;
        }

        return $userNotifications;
    }
}
