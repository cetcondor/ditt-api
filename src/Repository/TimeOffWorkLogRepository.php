<?php

namespace App\Repository;

use App\Entity\TimeOffWorkLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class TimeOffWorkLogRepository
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
        $repository = $entityManager->getRepository(TimeOffWorkLog::class);

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
     * @return mixed
     */
    public function findAllWaitingForApproval()
    {
        $qb = $this->repository->createQueryBuilder('towl');

        return $qb
            ->select('towl')
            ->leftJoin('towl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->isNull('towl.timeApproved'),
                $qb->expr()->isNull('towl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $supervisor
     * @return mixed
     */
    public function findAllWaitingForApprovalBySupervisor(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('towl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('towl')
            ->leftJoin('towl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $supervisedUserIds),
                $qb->expr()->isNull('towl.timeApproved'),
                $qb->expr()->isNull('towl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TimeOffWorkLog[]
     */
    public function findAllRecent(): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('towl');

        return $qb
            ->select('towl')
            ->leftJoin('towl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('towl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $supervisor
     * @return TimeOffWorkLog[]
     */
    public function findAllRecentBySupervisor(User $supervisor): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('towl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('towl')
            ->leftJoin('towl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $supervisedUserIds),
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('towl.date', 'desc')
            ->getQuery()
            ->getResult();
    }
}
