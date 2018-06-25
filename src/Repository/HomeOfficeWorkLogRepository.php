<?php

namespace App\Repository;

use App\Entity\HomeOfficeWorkLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class HomeOfficeWorkLogRepository
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
        $repository = $entityManager->getRepository(HomeOfficeWorkLog::class);
        $this->repository = $repository;
    }

    /**
     * @param User $supervisor
     * @return mixed
     */
    public function findAllWaitingForApproval(User $supervisor)
    {
        $qb = $this->repository->createQueryBuilder('howl');

        return $qb
            ->select('howl')
            ->leftJoin('howl.workMonth', 'wm')
            ->leftJoin('wm.user', 'u')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('u.supervisor', ':supervisor'),
                $qb->expr()->isNull('howl.timeApproved'),
                $qb->expr()->isNull('howl.timeRejected')
            ))
            ->setParameter('supervisor', $supervisor)
            ->getQuery()
            ->getResult();
    }
}
