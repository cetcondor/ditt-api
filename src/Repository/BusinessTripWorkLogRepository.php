<?php

namespace App\Repository;

use App\Entity\BusinessTripWorkLog;
use App\Entity\User;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class BusinessTripWorkLogRepository
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
        $repository = $entityManager->getRepository(BusinessTripWorkLog::class);

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
        $qb = $this->repository->createQueryBuilder('btwl');

        return $qb
            ->select('btwl')
            ->leftJoin('btwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->isNull('btwl.timeApproved'),
                $qb->expr()->isNull('btwl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function findAllWaitingForApprovalBySupervisor(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('btwl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('btwl')
            ->leftJoin('btwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $supervisedUserIds),
                $qb->expr()->isNull('btwl.timeApproved'),
                $qb->expr()->isNull('btwl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return BusinessTripWorkLog[]
     */
    public function findAllRecent(): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('btwl');

        return $qb
            ->select('btwl')
            ->leftJoin('btwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->gte('wm.month', ':previousMonth'),
                    $qb->expr()->eq('wm.year', ':previousYear')
                ),
                $qb->expr()->gt('wm.year', ':previousYear')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('btwl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return BusinessTripWorkLog[]
     */
    public function findAllRecentBySupervisor(User $supervisor): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('btwl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('btwl')
            ->leftJoin('btwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->in('wm.user', $supervisedUserIds),
                    $qb->expr()->gte('wm.month', ':previousMonth'),
                    $qb->expr()->eq('wm.year', ':previousYear')
                ),
                $qb->expr()->andX(
                    $qb->expr()->in('wm.user', $supervisedUserIds),
                    $qb->expr()->gt('wm.year', ':previousYear')
                )
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('btwl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return BusinessTripWorkLog[]
     */
    public function findAllRecentWaitingAndApprovedByUser(User $user): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('btwl');

        return $qb
            ->select('btwl')
            ->leftJoin('btwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $user->getId()),
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear'),
                $qb->expr()->isNull('btwl.timeRejected')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('btwl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return BusinessTripWorkLog[]
     */
    public function findAllApprovedByWorkMonth(WorkMonth $workMonth): array
    {
        return $this->repository->createQueryBuilder('btwl')
            ->select('btwl')
            ->where('btwl.workMonth = :workMonth')
            ->andWhere('btwl.timeApproved IS NOT NULL')
            ->setParameter('workMonth', $workMonth)
            ->getQuery()
            ->getResult();
    }
}
