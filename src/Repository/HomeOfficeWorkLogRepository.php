<?php

namespace App\Repository;

use App\Entity\HomeOfficeWorkLog;
use App\Entity\User;
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

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(HomeOfficeWorkLog::class);

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
     * @param User $supervisor
     * @return mixed
     */
    public function findAllWaitingForApprovalBySupervisor(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('howl');

        return $qb
            ->select('howl')
            ->leftJoin('howl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('u.supervisor', ':supervisor'),
                $qb->expr()->isNull('howl.timeApproved'),
                $qb->expr()->isNull('howl.timeRejected')
            ))
            ->setParameter('supervisor', $supervisor)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return HomeOfficeWorkLog[]
     */
    public function findAllRecent(): array
    {
        $date = new \DateTime();
        $currentMonth = $date->format('m');
        $currentYear = $date->format('Y');

        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('howl');

        return $qb
            ->select('howl')
            ->leftJoin('howl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->orX(
                $qb->expr()->andX(
                    $qb->expr()->eq('wm.month', ':currentMonth'),
                    $qb->expr()->eq('wm.year', ':currentYear')
                ),
                $qb->expr()->andX(
                    $qb->expr()->eq('wm.month', ':previousMonth'),
                    $qb->expr()->eq('wm.year', ':previousYear')
                )
            ))
            ->setParameter('currentMonth', $currentMonth)
            ->setParameter('currentYear', $currentYear)
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('howl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $supervisor
     * @return HomeOfficeWorkLog[]
     */
    public function findAllRecentBySupervisor(User $supervisor): array
    {
        $date = new \DateTime();
        $currentMonth = $date->format('m');
        $currentYear = $date->format('Y');

        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('howl');

        return $qb
            ->select('howl')
            ->leftJoin('howl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('u.supervisor', ':supervisor'),
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->eq('wm.month', ':currentMonth'),
                        $qb->expr()->eq('wm.year', ':currentYear')
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->eq('wm.month', ':previousMonth'),
                        $qb->expr()->eq('wm.year', ':previousYear')
                    )
                )
            ))
            ->setParameter('supervisor', $supervisor)
            ->setParameter('currentMonth', $currentMonth)
            ->setParameter('currentYear', $currentYear)
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('howl.date', 'desc')
            ->getQuery()
            ->getResult();
    }
}
