<?php

namespace App\Repository;

use App\Entity\SupportedYear;
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

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(UserYearStats::class);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    public function findByUserAndYear(User $user, SupportedYear $year): ?UserYearStats
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
