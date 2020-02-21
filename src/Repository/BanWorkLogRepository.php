<?php

namespace App\Repository;

use App\Entity\BanWorkLog;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class BanWorkLogRepository
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
        $repository = $entityManager->getRepository(BanWorkLog::class);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @param int[] $ids
     * @return BanWorkLog[]
     */
    public function findByIds(array $ids): array
    {
        $qb = $this->repository->createQueryBuilder('bwl');

        return $qb->select('bwl')
            ->where($qb->expr()->in('bwl.id', $ids))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return BanWorkLog[]
     */
    public function findAllByWorkMonth(WorkMonth $workMonth): array
    {
        return $this->repository->createQueryBuilder('bwl')
            ->select('bwl')
            ->where('bwl.workMonth = :workMonth')
            ->setParameter('workMonth', $workMonth)
            ->getQuery()
            ->getResult();
    }
}
