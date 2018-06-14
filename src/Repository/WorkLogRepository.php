<?php

namespace App\Repository;

use App\Entity\WorkLog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class WorkLogRepository
{
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
        $repository = $entityManager->getRepository(WorkLog::class);
        $this->repository = $repository;
    }

    /**
     * @param WorkLog $workLog
     * @return bool
     */
    public function getOverlaps(WorkLog $workLog): bool
    {
        try {
            return (int) $this->repository->createQueryBuilder('wl')
                ->select('COUNT(wl.id)')
                ->where('overlaps(:startTime, :endTime, wl.startTime, wl.endTime) = TRUE')
                ->andWhere('wl.workMonth = :workMonth')
                ->setParameter('startTime', $workLog->getStartTime())
                ->setParameter('endTime', $workLog->getEndTime())
                ->setParameter('workMonth', $workLog->getWorkMonth())
                ->getQuery()
                ->getSingleScalarResult() > 0;
        } catch (NonUniqueResultException $e) {
            return true;
        }
    }
}
