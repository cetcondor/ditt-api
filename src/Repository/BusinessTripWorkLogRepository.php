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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(BusinessTripWorkLog::class);

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
     * @param User $supervisor
     * @return mixed
     */
    public function findAllWaitingForApprovalBySupervisor(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('btwl');

        return $qb
            ->select('btwl')
            ->leftJoin('btwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('u.supervisor', ':supervisor'),
                $qb->expr()->isNull('btwl.timeApproved'),
                $qb->expr()->isNull('btwl.timeRejected')
            ))
            ->setParameter('supervisor', $supervisor)
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
            ->where($qb->expr()->andX(
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('btwl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $supervisor
     * @return BusinessTripWorkLog[]
     */
    public function findAllRecentBySupervisor(User $supervisor): array
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
                $qb->expr()->eq('u.supervisor', ':supervisor'),
                $qb->expr()->andX(
                    $qb->expr()->gte('wm.month', ':previousMonth'),
                    $qb->expr()->gte('wm.year', ':previousYear')
                )
            ))
            ->setParameter('supervisor', $supervisor)
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('btwl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param WorkMonth $workMonth
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
