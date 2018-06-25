<?php

namespace App\Repository;

use App\Entity\TimeOffWorkLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class TimeOffWorkLogRepository
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
        $repository = $entityManager->getRepository(TimeOffWorkLog::class);
        $this->repository = $repository;
    }

    /**
     * @param User $supervisor
     * @return mixed
     */
    public function findAllWaitingForApproval(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('towl');

        return $qb
            ->select('towl')
            ->leftJoin('towl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('u.supervisor', ':supervisor'),
                $qb->expr()->isNull('towl.timeApproved'),
                $qb->expr()->isNull('towl.timeRejected')
            ))
            ->setParameter('supervisor', $supervisor)
            ->getQuery()
            ->getResult();
    }
}
