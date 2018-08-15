<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserYearStats;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserYearStatsRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
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
        $repository = $entityManager->getRepository(UserYearStats::class);

        $this->entityManager = $entityManager;
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
     * @param User $user
     * @param int $year
     * @return UserYearStats|null
     */
    public function findByUserAndYear(User $user, int $year): ?UserYearStats
    {
        /** @var UserYearStats|null $user */
        $userYearStats = $this->repository->findOneBy([
            'user' => $user,
            'year' => $year,
        ]);

        if (!$userYearStats instanceof UserYearStats) {
            return null;
        }

        return $userYearStats;
    }
}
