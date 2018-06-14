<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WorkLog;
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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(WorkMonth::class);

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
     * @param WorkLog $workLog
     * @param User $user
     * @return WorkMonth|null
     */
    public function findByWorkLogAndUser(WorkLog $workLog, User $user): ?WorkMonth
    {
        try {
            return $this->repository->createQueryBuilder('wm')
                ->select('wm')
                ->where('wm.month = :workLogMonth')
                ->andWhere('wm.year = :workLogYear')
                ->andWhere('wm.user = :user')
                ->setParameter('workLogMonth', $workLog->getStartTime()->format('m'))
                ->setParameter('workLogYear', $workLog->getStartTime()->format('Y'))
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleResult();
        } catch (UnexpectedResultException $e) {
            return null;
        }
    }

    /**
     * @param WorkMonth $workMonth
     */
    public function markApproved(WorkMonth $workMonth)
    {
        $workMonth->markApproved();
        $this->entityManager->flush();
    }

    /**
     * @param WorkMonth $workMonth
     */
    public function markWaitingForApproval(WorkMonth $workMonth): void
    {
        $workMonth->markWaitingForApproval();
        $this->entityManager->flush();
    }
}
