<?php

namespace App\Repository;

use App\Entity\WorkLog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

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
     * @param \App\Entity\WorkLog $workLog
     * @return int
     */
    public function getOverlaps(WorkLog $workLog): int
    {
        return (int) $this->repository->createQueryBuilder('wl')
            ->select('COUNT(wl.id)')
            ->where('overlaps(:startTime, :endTime, wl.startTime, wl.endTime) = TRUE')
            ->setParameter('startTime', $workLog->getStartTime())
            ->setParameter('endTime', $workLog->getEndTime())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
