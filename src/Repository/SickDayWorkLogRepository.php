<?php

namespace App\Repository;

use App\Entity\SickDayWorkLog;
use App\Entity\User;
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
    public function findAllCreatedByUserBetweenTwoDates(User $user, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): array
    {
        return $this->repository->createQueryBuilder('sdwl')
            ->select('sdwl')
            ->leftJoin('sdwl.workMonth', 'wm')
            ->where('sdwl.createdOn >= :dateFrom')
            ->andWhere('sdwl.createdOn <= :dateTo')
            ->andWhere('wm.user = :user')
            ->setParameter('dateFrom', $dateFrom->setTime(0, 0, 0))
            ->setParameter('dateTo', $dateTo->setTime(23, 59, 59))
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
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
