<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WorkLogInterface;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnexpectedResultException;

class WorkMonthRepository
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
        $repository = $entityManager->getRepository(WorkMonth::class);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    public function findByWorkLogAndUser(WorkLogInterface $workLog, User $user): ?WorkMonth
    {
        try {
            return $this->repository->createQueryBuilder('wm')
                ->select('wm')
                ->where('wm.month = :workLogMonth')
                ->andWhere('wm.year = :workLogYear')
                ->andWhere('wm.user = :user')
                ->setParameter('workLogMonth', $workLog->resolveWorkLogMonth())
                ->setParameter('workLogYear', $workLog->resolveWorkLogYear())
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleResult();
        } catch (UnexpectedResultException $e) {
            return null;
        }
    }
}
