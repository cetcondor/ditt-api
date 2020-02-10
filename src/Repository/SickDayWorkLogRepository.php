<?php

namespace App\Repository;

use App\Entity\SickDayWorkLog;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SickDayWorkLogRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(SickDayWorkLog::class);

        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @return SickDayWorkLog[]
     */
    public function findAllByWorkMonth(WorkMonth $workMonth): array
    {
        return $this->repository->createQueryBuilder('sdwl')
            ->select('sdwl')
            ->where('sdwl.workMonth = :workMonth')
            ->setParameter('workMonth', $workMonth)
            ->getQuery()
            ->getResult();
    }
}
