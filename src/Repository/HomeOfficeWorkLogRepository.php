<?php

namespace App\Repository;

use App\Entity\HomeOfficeWorkLog;
use App\Entity\User;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class HomeOfficeWorkLogRepository
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
        $repository = $entityManager->getRepository(HomeOfficeWorkLog::class);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @return mixed
     */
    public function findAllWaitingForApproval()
    {
        $qb = $this->repository->createQueryBuilder('howl');

        return $qb
            ->select('howl')
            ->leftJoin('howl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->isNull('howl.timeApproved'),
                $qb->expr()->isNull('howl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function findAllWaitingForApprovalBySupervisor(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('howl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('howl')
            ->leftJoin('howl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $supervisedUserIds),
                $qb->expr()->isNull('howl.timeApproved'),
                $qb->expr()->isNull('howl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return HomeOfficeWorkLog[]
     */
    public function findAllRecent(): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('howl');

        return $qb
            ->select('howl')
            ->leftJoin('howl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('howl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return HomeOfficeWorkLog[]
     */
    public function findAllRecentBySupervisor(User $supervisor): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('howl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('howl')
            ->leftJoin('howl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $supervisedUserIds),
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('howl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return HomeOfficeWorkLog[]
     */
    public function findAllApprovedByWorkMonth(WorkMonth $workMonth): array
    {
        return $this->repository->createQueryBuilder('howl')
            ->select('howl')
            ->where('howl.workMonth = :workMonth')
            ->andWhere('howl.timeApproved IS NOT NULL')
            ->setParameter('workMonth', $workMonth)
            ->getQuery()
            ->getResult();
    }
}
