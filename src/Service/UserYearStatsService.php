<?php

namespace App\Service;

use App\Entity\UserYearStats;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class UserYearStatsService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param UserYearStats[] $userYearStats
     */
    public function createUserYearStats(array $userYearStats): void
    {
        $this->entityManager->transactional(function (EntityManager $em) use ($userYearStats) {
            foreach ($userYearStats as $userYearStat) {
                if (!$userYearStat instanceof UserYearStats) {
                    throw new \TypeError('Entity is not of type UserYearStats.');
                }

                $em->persist($userYearStat);
            }
        });
    }
}
