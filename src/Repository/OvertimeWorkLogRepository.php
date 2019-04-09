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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(OvertimeWorkLog::class);

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
     * @param User $supervisor
     * @return mixed
     */
    public function findAllWaitingForApprovalBySupervisor(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('owl');

        return $qb
            ->select('owl')
            ->leftJoin('owl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('u.supervisor', ':supervisor'),
                $qb->expr()->isNull('owl.timeApproved'),
                $qb->expr()->isNull('owl.timeRejected')
            ))
            ->setParameter('supervisor', $supervisor)
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
            ->where($qb->expr()->andX(
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('owl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $supervisor
     * @return OvertimeWorkLog[]
     */
    public function findAllRecentBySupervisor(User $supervisor): array
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
            ->orderBy('owl.date', 'desc')
            ->getQuery()
            ->getResult();
    }
}
