<?php

namespace App\Repository;

use App\Entity\Contract;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ContractRepository
{
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository(Contract::class);
        $this->repository = $repository;
    }

    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    public function findContractsBetweenDates(User $user, \DateTimeImmutable $dateFrom, \DateTimeImmutable $dateTo): array
    {
        return $this->repository->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.startDateTime <= :dateTo')
            ->andWhere('c.endDateTime >= :dateFrom OR c.endDateTime IS NULL')
            ->setParameter('user', $user->getId())
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->getQuery()
            ->getResult();
    }
}
