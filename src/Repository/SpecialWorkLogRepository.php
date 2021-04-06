<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SpecialWorkLogRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager, string $entityClassName)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository($entityClassName);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    public function findByIds(array $ids): array
    {
        $qb = $this->repository->createQueryBuilder('wl');

        return $qb->select('wl')
            ->where($qb->expr()->in('wl.id', $ids))
            ->getQuery()
            ->getResult();
    }

    public function findAllWaitingForApproval(): array
    {
        $qb = $this->repository->createQueryBuilder('wl');

        return $qb
            ->select('wl')
            ->leftJoin('wl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->isNull('wl.timeApproved'),
                $qb->expr()->isNull('wl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    public function findAllWaitingForApprovalBySupervisor(User $supervisor): array
    {
        $qb = $this->repository->createQueryBuilder('wl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('wl')
            ->leftJoin('wl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $supervisedUserIds),
                $qb->expr()->isNull('wl.timeApproved'),
                $qb->expr()->isNull('wl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    public function findAllRecent(): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('wl');

        return $qb
            ->select('wl')
            ->leftJoin('wl.workMonth', 'wm')
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
            ->orderBy('wl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    public function findAllRecentBySupervisor(User $supervisor): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('wl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('wl')
            ->leftJoin('wl.workMonth', 'wm')
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
            ->orderBy('wl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    public function findAllRecentWaitingAndApprovedByUser(User $user): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('wl');

        return $qb
            ->select('wl')
            ->leftJoin('wl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $user->getId()),
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear'),
                $qb->expr()->isNull('wl.timeRejected')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('wl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    public function findAllApprovedByWorkMonth(WorkMonth $workMonth): array
    {
        return $this->repository->createQueryBuilder('wl')
            ->select('wl')
            ->where('wl.workMonth = :workMonth')
            ->andWhere('wl.timeApproved IS NOT NULL')
            ->setParameter('workMonth', $workMonth)
            ->getQuery()
            ->getResult();
    }
}
