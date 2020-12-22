<?php

namespace App\Repository;

use App\Entity\OvertimeWorkLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class OvertimeWorkLogRepository
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
        $repository = $entityManager->getRepository(OvertimeWorkLog::class);

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
        $qb = $this->repository->createQueryBuilder('owl');

        return $qb
            ->select('owl')
            ->leftJoin('owl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->isNull('owl.timeApproved'),
                $qb->expr()->isNull('owl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function findAllWaitingForApprovalBySupervisor(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('owl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('owl')
            ->leftJoin('owl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $supervisedUserIds),
                $qb->expr()->isNull('owl.timeApproved'),
                $qb->expr()->isNull('owl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return OvertimeWorkLog[]
     */
    public function findAllRecent(): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('owl');

        return $qb
            ->select('owl')
            ->leftJoin('owl.workMonth', 'wm')
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
            ->orderBy('owl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return OvertimeWorkLog[]
     */
    public function findAllRecentBySupervisor(User $supervisor): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('owl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('owl')
            ->leftJoin('owl.workMonth', 'wm')
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
            ->orderBy('owl.date', 'desc')
            ->getQuery()
            ->getResult();
    }
}
