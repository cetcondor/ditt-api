<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\VacationWorkLog;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class VacationWorkLogRepository
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
        $repository = $entityManager->getRepository(VacationWorkLog::class);

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
        $qb = $this->repository->createQueryBuilder('vwl');

        return $qb
            ->select('vwl')
            ->leftJoin('vwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->isNull('vwl.timeApproved'),
                $qb->expr()->isNull('vwl.timeRejected')
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
        $qb = $this->repository->createQueryBuilder('vwl');

        return $qb
            ->select('vwl')
            ->leftJoin('vwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('u.supervisor', ':supervisor'),
                $qb->expr()->isNull('vwl.timeApproved'),
                $qb->expr()->isNull('vwl.timeRejected')
            ))
            ->setParameter('supervisor', $supervisor)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return VacationWorkLog[]
     */
    public function findAllRecent(): array
    {
        $date = new \DateTime();
        $currentMonth = $date->format('m');
        $currentYear = $date->format('Y');

        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('vwl');

        return $qb
            ->select('vwl')
            ->leftJoin('vwl.workMonth', 'wm')
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
            ->orderBy('vwl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $supervisor
     * @return VacationWorkLog[]
     */
    public function findAllRecentBySupervisor(User $supervisor): array
    {
        $date = new \DateTime();
        $currentMonth = $date->format('m');
        $currentYear = $date->format('Y');

        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('vwl');

        return $qb
            ->select('vwl')
            ->leftJoin('vwl.workMonth', 'wm')
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
            ->orderBy('vwl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param WorkMonth $workMonth
     * @return VacationWorkLog[]
     */
    public function findAllApprovedByWorkMonth(WorkMonth $workMonth): array
    {
        return $this->repository->createQueryBuilder('vwl')
            ->select('vwl')
            ->where('vwl.workMonth = :workMonth')
            ->andWhere('vwl.timeApproved IS NOT NULL')
            ->setParameter('workMonth', $workMonth)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param int $year
     * @throws NonUniqueResultException
     * @return int
     */
    public function getRemainingVacationDays(User $user, int $year): int
    {
        $vacationWorkLogsCount = (int) $this->repository->createQueryBuilder('vwl')
            ->select('COUNT(vwl.id)')
            ->leftJoin('vwl.workMonth', 'wm')
            ->where('wm.user = :user')
            ->andWhere('wm.year = :year')
            ->setParameter('user', $user)
            ->setParameter('year', $year)
            ->getQuery()
            ->getSingleScalarResult();

        return $user->getVacationDays() - $vacationWorkLogsCount;
    }
}
