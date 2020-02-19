<?php

namespace App\Repository;

use App\Entity\ParentalLeaveWorkLog;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ParentalLeaveWorkLogRepository
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
        $repository = $entityManager->getRepository(ParentalLeaveWorkLog::class);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @param int[] $ids
     * @return ParentalLeaveWorkLog[]
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->repository->createQueryBuilder('plwl');

        return $qb->select('plwl')
            ->where($qb->expr()->in('plwl.id', $ids))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ParentalLeaveWorkLog[]
     */
    public function findAllByWorkMonth(WorkMonth $workMonth): array
    {
        return $this->repository->createQueryBuilder('plwl')
            ->select('plwl')
            ->where('plwl.workMonth = :workMonth')
            ->setParameter('workMonth', $workMonth)
            ->getQuery()
            ->getResult();
    }
}
