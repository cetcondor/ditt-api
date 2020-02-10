<?php

namespace App\Repository;

use App\Entity\SupportedYear;
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

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(VacationWorkLog::class);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @param int[] $ids
     * @return VacationWorkLog[]
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->repository->createQueryBuilder('vwl');

        return $qb->select('vwl')
            ->where($qb->expr()->in('vwl.id', $ids))
            ->getQuery()
            ->getResult();
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
     * @return mixed
     */
    public function findAllWaitingForApprovalBySupervisor(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('vwl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('vwl')
            ->leftJoin('vwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $supervisedUserIds),
                $qb->expr()->isNull('vwl.timeApproved'),
                $qb->expr()->isNull('vwl.timeRejected')
            ))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return VacationWorkLog[]
     */
    public function findAllRecent(): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('vwl');

        return $qb
            ->select('vwl')
            ->leftJoin('vwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('vwl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return VacationWorkLog[]
     */
    public function findAllRecentBySupervisor(User $supervisor): array
    {
        $date = new \DateTime();
        $date->modify('-1 month');
        $previousMonth = $date->format('m');
        $previousYear = $date->format('Y');

        $qb = $this->repository->createQueryBuilder('vwl');

        $supervisedUserIds = array_map(function (User $supervisedUser) {
            return $supervisedUser->getId();
        }, $supervisor->getAllSupervised());

        if (count($supervisedUserIds) === 0) {
            return [];
        }

        return $qb
            ->select('vwl')
            ->leftJoin('vwl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->in('wm.user', $supervisedUserIds),
                $qb->expr()->gte('wm.month', ':previousMonth'),
                $qb->expr()->gte('wm.year', ':previousYear')
            ))
            ->setParameter('previousMonth', $previousMonth)
            ->setParameter('previousYear', $previousYear)
            ->orderBy('vwl.date', 'desc')
            ->getQuery()
            ->getResult();
    }

    /**
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
     * @throws NonUniqueResultException
     */
    public function getRemainingVacationDays(User $user, SupportedYear $year): int
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

        $vacationDays = 0;
        $vacationDaysCorrection = 0;

        foreach ($user->getVacations() as $vacation) {
            if ($vacation->getYear()->getYear() === $year->getYear()) {
                $vacationDays = $vacation->getVacationDays();
                $vacationDaysCorrection = $vacation->getVacationDaysCorrection();

                break;
            }
        }

        return ($vacationDays + $vacationDaysCorrection) - $vacationWorkLogsCount;
    }
}
