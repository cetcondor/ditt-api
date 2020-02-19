<?php

namespace App\Repository;

use App\Entity\SickDayUnpaidWorkLog;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SickDayUnpaidWorkLogRepository
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
        $repository = $entityManager->getRepository(SickDayUnpaidWorkLog::class);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @param int[] $ids
     * @return SickDayUnpaidWorkLog[]
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->repository->createQueryBuilder('sduwl');

        return $qb->select('sduwl')
            ->where($qb->expr()->in('sduwl.id', $ids))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return SickDayUnpaidWorkLog[]
     */
    public function findAllByWorkMonth(WorkMonth $workMonth): array
    {
        return $this->repository->createQueryBuilder('sduwl')
            ->select('sduwl')
            ->where('sduwl.workMonth = :workMonth')
            ->setParameter('workMonth', $workMonth)
            ->getQuery()
            ->getResult();
    }
}
