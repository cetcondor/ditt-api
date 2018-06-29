<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\VacationWorkLog;
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
     * @param User $supervisor
     * @return mixed
     */
    public function findAllWaitingForApproval(User $supervisor)
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
     * @param User $user
     * @param int $year
     * @return int
     */
    public function getRemainingVacationDays(User $user, int $year): int
    {
        try {
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
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }
}
