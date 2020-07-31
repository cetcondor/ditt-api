<?php

namespace App\Repository;

use App\Entity\WorkLog;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class WorkLogRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(WorkLog::class);
        $this->repository = $repository;
    }

    /**
     * @return WorkLog[]
     */
    public function findAllByWorkMonth(WorkMonth $workMonth): array
    {
        return $this->repository->createQueryBuilder('wl')
            ->select('wl')
            ->where('wl.workMonth = :workMonth')
            ->setParameter('workMonth', $workMonth)
            ->getQuery()
            ->getResult();
    }

    public function getOverlaps(WorkLog $workLog): bool
    {
        try {
            $query = $this->repository->createQueryBuilder('wl')
                ->select('COUNT(wl.id)')
                ->where('overlaps(:startTime, :endTime, wl.startTime, wl.endTime) = TRUE')
                ->andWhere('wl.workMonth = :workMonth');

            if ($workLog->getId()) {
                $query = $query->andWhere('wl.id != :id')
                    ->setParameter('id', $workLog->getId());
            }

            return $query->setParameter('startTime', $workLog->getStartTime())
                    ->setParameter('endTime', $workLog->getEndTime())
                    ->setParameter('workMonth', $workLog->getWorkMonth())
                    ->getQuery()
                    ->getSingleScalarResult() > 0;
        } catch (NonUniqueResultException $e) {
            return true;
        }
    }
}
